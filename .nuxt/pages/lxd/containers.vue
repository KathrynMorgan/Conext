<template>
  <v-app>
    <v-snackbar top :timeout="snackbarTimeout" :color="snackbarColor" v-model="snackbar">
      {{ snackbarText }}
      <v-btn dark flat @click.native="snackbar = false">Close</v-btn>
    </v-snackbar>

    <v-content>
      <v-container fluid tag="section" id="grid">
        <v-layout row wrap>
          <v-flex d-flex xs12 order-xs5>
            <v-layout column>
              <v-flex tag="h1" class="display mb-2">
                LXD - Containers
                <!--<v-btn color="success" @click="newContainer()" style="float:right">New Container</v-btn>-->
              </v-flex>
              <v-flex>

                <v-alert v-if="alert.msg" :value="alert.msg" :outline="alert.outline" :color="alert.color" :icon="alert.icon" dismissible>
                  {{ alert.msg }}
                </v-alert>

                <v-data-table :headers="tableHeaders" :items="items" hide-actions class="elevation-1" :loading="tableLoading">
                  <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                  <template slot="items" slot-scope="props">
                    <tr>
                      <td><a href="javascript:void(0)" @click.stop="editContainer(props.item)">{{ props.item.name }}</a></td>
                      <td>
                        <span v-if="check_started_with_ip(props.item)">{{ props.item.network.eth0.addresses[0].address }}</span>
                        <span v-if="props.item.status === 'Running' && (props.item.network.eth0.addresses.length === 0 || isIP4(props.item.network.eth0.addresses[0].address) === false)">
                          <v-icon size="15" @click="initialize()" color="orange darken-2">fa fa-refresh</v-icon>
                        </span>
                        <span v-if="props.item.status === 'Stopped'">-</span>
                      </td>
                      <td>{{ props.item.cpu && props.item.cpu.usage ? Number(props.item.cpu.usage/1000000000).toFixed(2) + ' seconds' : '-' }}</td>
                      <td>{{ props.item.memory && props.item.memory.usage ? formatBytes(props.item.memory.usage) : '-' }}</td>
                      <td>{{ props.item.status }}</td>
                      <td class="px-0">
                        <v-menu offset-y left style="float:right" class="mr-3">
                          <v-btn icon class="mx-0" slot="activator">
                            <v-icon color="blue-grey lighten-3">view_headline</v-icon>
                          </v-btn>
                          <v-list>
                            <v-list-tile v-for="item in containerActions" :key="item.title" @click="stateContainer(item, props.item)" v-if="!item.state || item.state === props.item.status">
                              <v-list-tile-title>{{ item.title }}</v-list-tile-title>
                            </v-list-tile>
                          </v-list>
                        </v-menu>
                      </td>
                    </tr>
                  </template>
                  <template slot="no-data">
                    {{ tableLoading ? 'Fetching data, please wait...' : tableNoData }}
                  </template>
                </v-data-table>
              </v-flex>
            </v-layout>
          </v-flex>
        </v-layout>
      </v-container>

      <v-dialog v-model="consoleDialog"  fullscreen hide-overlay transition="dialog-bottom-transition" color="black" style="overflow-y:hidden;">
        <v-toolbar card dark color="black">
          <v-btn icon @click.native="consoleDialog = false" dark>
            <v-icon>close</v-icon>
          </v-btn>
          <v-toolbar-title>Console: {{ container.info && container.info.name }}</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-toolbar-items>
            <v-btn dark flat @click.native="consoleDialog = false">Close</v-btn>
          </v-toolbar-items>
        </v-toolbar>
        <div id="terminal"></div>
      </v-dialog>

      <v-dialog v-model="containerDialog" max-width="800px" scrollable v-if="container.info">
        <v-card tile>
          <v-toolbar card dark color="light-blue darken-3">
            <v-btn icon @click.native="containerDialog = false" dark>
              <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>Container: {{ container.info && container.info.name }}</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-toolbar-items v-if="activeTab !== 'tab-snapshots'">
              <v-btn dark flat @click.native="saveContainer()">Save</v-btn>
            </v-toolbar-items>
          </v-toolbar>
          <v-card-text style="padding: 0px;">
            <v-tabs v-model="activeTab">
              <!--<v-tab ripple :href="`#tab-information`" >Information</v-tab>-->
              <v-tab ripple :href="`#tab-configuration`">Configuration</v-tab>
              <v-tab ripple :href="`#tab-snapshots`">Snapshots</v-tab>
              <!--<v-tab-item :id="`tab-information`">-->
              <!--  <v-card flat style="overflow-x:hidden; overflow-y: auto; height:calc(100vh - 215px);">-->
              <!--    <v-card-text><pre>{{ container }}</pre></v-card-text>-->
              <!--  </v-card>-->
              <!--</v-tab-item>-->
              <v-tab-item :id="`tab-configuration`" v-if="container.info">
                <v-card flat style="overflow-x:hidden; overflow-y: auto; height:calc(100vh - 215px);">
                  <v-card-text>
                    <v-form ref="form" v-model="valid" lazy v-if="container.info.config">
                      <h2>General</h2>
                      <v-layout row wrap style="margin-top:-20px">
                        <v-flex xs6>
                          <v-card-text class="px-1">
                            <v-text-field v-model="container.info.name" label="Name" :rules="nameRule" @input="safe_name()" required></v-text-field>
                            <v-select :items="['default']" :rules="profilesRule" v-model="container.info.profiles" label="Profiles" multiple chips required></v-select>
                          </v-card-text>
                        </v-flex>
                        <v-flex xs6>
                          <v-card-text class="px-4">
                            <v-layout row wrap>
                              <v-flex xs6>
                                <h4>Auto Start</h4>
                                <v-switch :label="`${container.info.config['boot.autostart'] === '1' ? 'Yes' : 'No'}`" true-value="1" false-value="0" color="success" v-model="container.info.config['boot.autostart']"></v-switch>
                              </v-flex>
                              <v-flex xs6>
                                <h4>Ephemeral</h4>
                                <v-switch :label="`${container.info.ephemeral ? 'Yes' : 'No'}`" color="success" v-model="container.info.ephemeral"></v-switch>
                              </v-flex>
                           </v-layout>
                           <v-layout row wrap>
                              <v-flex xs6>
                                <h4>Privileged</h4>
                                <v-switch :label="`${container.info.config['security.privileged'] === '1' ? 'Yes' : 'No'}`" true-value="1" false-value="0" color="success" v-model="container.info.config['security.privileged']"></v-switch>
                              </v-flex>
                              <v-flex xs6>
                                <h4>Nesting</h4>
                                <v-switch :label="`${container.info.config['security.nesting'] === '1' ? 'Yes' : 'No'}`" true-value="1" false-value="0" color="success" v-model="container.info.config['security.nesting']"></v-switch>
                              </v-flex>
                           </v-layout>
                         </v-card-text>
                        </v-flex>
                      </v-layout>
                      <h2 style="margin-top:-15px">CPU</h2>
                      <v-layout row wrap>
                        <v-flex xs6>
                          <v-card-text class="px-1">
                            <h4 style="margin-bottom:-20px">CPU Cores ({{ container.info.config['limits.cpu'] }})</h4>
                            <v-slider v-model="container.info.config['limits.cpu']" thumb-label max="2" ticks></v-slider>
                            <h4 style="margin-bottom:-20px">Max Processes ({{ container.info.config['limits.processes'] }})</h4>
                            <v-slider v-model="container.info.config['limits.processes']" thumb-label max="20000" step="100" ticks></v-slider>
                          </v-card-text>
                        </v-flex>
                        <v-flex xs6>
                          <v-card-text class="px-1">
                            <h4 style="margin-bottom:-20px">CPU Allowance ({{ container.info.config['limits.cpu.allowance'] }}%)</h4>
                            <v-slider v-model="container.info.config['limits.cpu.allowance']" thumb-label max="100" step="1" ticks></v-slider>
                            <h4 style="margin-bottom:-20px">CPU Priority ({{ container.info.config['limits.cpu.priority'] }}/10)</h4>
                            <v-slider v-model="container.info.config['limits.cpu.priority']" thumb-label max="10" step="1" ticks></v-slider>
                          </v-card-text>
                        </v-flex>
                      </v-layout>
                      <h2 style="margin-top:-15px">Memory</h2>
                      <v-layout row wrap>
                        <v-flex xs6>
                          <v-card-text class="px-1">
                            <h4 style="margin-bottom:-20px">Memory ({{ container.info.config['limits.memory'] }}MB)</h4>
                            <v-slider v-model="container.info.config['limits.memory']" max="16000" thumb-label step="64" ticks></v-slider>
                            <h4 style="margin-bottom:-20px">Swap Priority ({{ container.info.config['limits.memory.swap.priority'] }}/10)</h4>
                            <v-slider v-model="container.info.config['limits.memory.swap.priority']" thumb-label max="10" step="1" ticks></v-slider>
                          </v-card-text>
                        </v-flex>
                        <v-flex xs6>
                          <v-card-text class="px-1">
                            <h4>Enforce</h4>
                            <v-switch :label="`${container.info.config['limits.memory.enforce'] === 'hard' ? 'Hard' : 'Soft'}`" true-value="hard" false-value="soft" color="success" v-model="container.info.config['limits.memory.enforce']"></v-switch>
                            <h4>Swap</h4>
                            <v-switch :label="`${container.info.config['limits.memory.swap'] === '1' ? 'Yes' : 'No'}`" true-value="1" false-value="0" color="success" v-model="container.info.config['limits.memory.swap']"></v-switch>
                          </v-card-text>
                        </v-flex>
                      </v-layout>
                      <v-layout row wrap>
                        <v-flex xs6>
                          <h2>Disk</h2>
                        </v-flex>
                        <v-flex xs6>
                          <h2>Network</h2>
                        </v-flex> 
                      </v-layout>
                      <v-layout row wrap>
                        <v-flex xs6>
                          <v-card-text class="px-1">
                            <h4 style="margin-bottom:-20px">Priority ({{ container.info.config['limits.disk.priority'] }}/10)</h4>
                            <v-slider v-model="container.info.config['limits.disk.priority']" thumb-label max="10" step="1" ticks></v-slider>
                          </v-card-text>
                        </v-flex>
                        <v-flex xs6>
                          <v-card-text class="px-1">
                            <h4 style="margin-bottom:-20px">Priority ({{ container.info.config['limits.network.priority'] }}/10)</h4>
                            <v-slider v-model="container.info.config['limits.network.priority']" thumb-label max="10" step="1" ticks></v-slider>
                          </v-card-text>
                        </v-flex>
                      </v-layout>
                    </v-form>
                  </v-card-text>
                </v-card>
              </v-tab-item>
              <v-tab-item :id="`tab-snapshots`">
                <v-card flat style="overflow-x:hidden; overflow-y: auto; height:calc(100vh - 215px);">
                  <snapshots :item="container" :key="editingIndex" @snackbar="setSnackbar"></snapshots>
                </v-card>
              </v-tab-item>
            </v-tabs>
          </v-card-text>
          <div style="flex: 1 1 auto;"></div>
        </v-card>
      </v-dialog>
      
      <!--<v-dialog v-model="newContainerDialog" max-width="800px" scrollable>-->
      <!--  <v-card tile>-->
      <!--    <v-toolbar card dark color="light-blue darken-3">-->
      <!--      <v-btn icon @click.native="containerDialog = false" dark>-->
      <!--        <v-icon>close</v-icon>-->
      <!--      </v-btn>-->
      <!--      <v-toolbar-title>New Container</v-toolbar-title>-->
      <!--      <v-spacer></v-spacer>-->
      <!--      <v-toolbar-items>-->
      <!--        <v-btn dark flat @click.native="newContainerDialog = false">Save</v-btn>-->
      <!--      </v-toolbar-items>-->
      <!--    </v-toolbar>-->
      <!--    <v-card-text>-->
      <!--      <v-form ref="nform" v-model="valid" lazy-validation>-->
      <!--        <v-text-field v-model="newItem.name" label="Name" :rules="nameRule" @input="safe_name()" hint="Enter name for new container." required></v-text-field>-->
      <!--        <v-select :items="['default']" :rules="profilesRule" v-model="newItem.image" label="Image" required></v-select>-->
      <!--        <v-select :items="['default']" :rules="profilesRule" v-model="newItem.profile" label="Profiles" multiple chips required></v-select>-->
      <!--        <v-switch :label="`${newItem.ephemeral ? 'Ephemeral' : 'Ephemeral'}`" color="success" v-model="newItem.ephemeral"></v-switch>-->
      <!--      </v-form>-->
      <!--    </v-card-text>-->
      <!--    <div style="flex: 1 1 auto;"></div>-->
      <!--  </v-card>-->
      <!--</v-dialog>-->
    </v-content>
  </v-app>
</template>

<script>
  import { mapGetters, mapMutations } from 'vuex'
  import { setToken } from '~/utils/auth'
  import axios from 'axios'
  // components
  import snapshots from '~/components/lxd/snapshots.vue'
  
  import { Terminal } from 'xterm'
  import * as fit from 'xterm/lib/addons/fit/fit'
  import helpers from '~/utils/helpers'
  
  const container = require('~/components/lxd/container')
  
  var xterm;

  export default {
    mixins: [helpers],
    middleware: [
      'authenticated'
    ],
    components: {
      snapshots
    },
    computed: {
      ...mapGetters({
        isAuthenticated: 'auth/isAuthenticated',
        loggedUser: 'auth/loggedUser',
        loggedToken: 'auth/loggedToken'
      }),
      formTitle () {
        return this.editedIndex === -1 ? 'New Container' : 'Edit Container'
      },
      /*
      max_memory: function () {
        return (container.max_memory() / 1024) / 1024
      },
      max_cpu: function () {
        return container.max_cpu()
      }*/
    },
    data: () => ({
      valid: true,
      
      //
      alert: { msg: '', outline: false, color: 'info', icon: 'info' },

      // snackbar (notification)
      snackbar: false,
      snackbarColor: 'green',
      snackbarText: '',
      snackbarTimeout: 5000,

      // table & items
      items: [],
      editingIndex: -1,
      
      tableLoading: true,
      tableNoData: 'You have not added any containers.',
      tableHeaders: [
        { text: 'Name', value: 'name' },
        { text: 'IP', value: 'network.eth0.addresses[0].address' },
        { text: 'CPU', value: 'cpu.usage' },
        { text: 'Memory', value: 'memory.usage' },
        { text: 'Status', value: 'status' },
        { text: 'Actions', value: 'name', sortable: false, align: 'right' }
      ],
      
      // new container item
      newItem: {
        name: '',
        image: '',
        profile: ['default'],
        ephemeral: false
      },
      defaultItem: {
        name: '',
        image: '',
        profile: ['default'],
        ephemeral: false
      },

      // tab
      activeTab: 'tab-configuration',

      dialog: false,
      consoleDialog: false,
      containerDialog: false,
      newContainerDialog: false,
      containerActions: [
        { title: 'Console',  action: 'console', msg: '', state: 'Running' },
        { title: 'Start',  action: 'start', msg: 'Starting', state: 'Stopped' },
        { title: 'Stop',   action: 'stop', msg: 'Stopping', state: 'Running' },
        { title: 'Delete', action: 'delete', msg: 'Deleting', state: 'Stopped' },
        { title: 'Freeze', action: 'freeze', msg: 'Freezing', state: 'Running' },
        { title: 'Thaw', action: 'unfreeze', msg: 'Thawing', state: 'Frozen' },
        { title: 'Restart', action: 'restart', msg: 'Restarting', state: 'Running' },
        { title: 'Snapshot', action: 'snapshot', msg: 'Snapshotting' },
        { title: 'Image', action: 'image', msg: 'Imaging', state: 'Stopped' }
      ],
      
      container: container.empty(),
      nameRule: [
        v => !!v || 'Name is required.',
        v => (v && /^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/.test(v)) || 'Only letters, digits or hyphens. No leading hyphen or digit. Dots are converted to hyphens.',
        v => (v && isNaN(v.charAt(0))) || 'Only letters, digits or hyphens. No leading hyphen or digit. Dots are converted to hyphens.'
      ],
      profilesRule: [
        v => v.length >= 1 || 'At least one profile is required.'
      ],
      pollItem: 0
    }),
    beforeDestroy: function() {
      clearInterval(this.pollId);
    },
    mounted: function () {
      this.initialize()
      
      clearInterval(this.pollId);
      this.pollId = setInterval(function () {
        this.initialize()
      }.bind(this), 10000);
    },
    watch: {
      dialog (val) {
        val || this.close()
      }
    },
    methods: {
      async initialize () {
        //
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }
          axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
          //
          const response = await axios.get(this.loggedUser.sub + '/api/lxd/containers')
          this.items = response.data.data
        } catch (error) {
          this.items = [];
          this.tableNoData = 'No data.';
          this.alert = { msg: 'Could not fetch data from server.', outline: false, color: 'error', icon: 'error' };
        }
        this.tableLoading = false
      },

      async stateContainer (action, item) {
        // intercept console
        if (action.action === 'console') {
          this.container = {
            state: item,
            info: {name: item.name}
          }
          this.editContainer(item, false)
          setTimeout(() => {
            this.console()
          }, 300)
          
          this.consoleDialog = true;
          return
        }
        // intercept snapshot
        if (action.action === 'snapshot') {
          this.container = {
            state: item,
            info: {name: item.name}
          }
          this.snapshotContainer(item, false)
          return
        }
        // intercept image
        if (action.action === 'image') {
          //
          axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
          //
          const response = await axios.get(this.loggedUser.sub + '/api/lxd/containers/' + item.name)
          
          this.container = {
            state: item,
            info: container.infix(response.data.data),
          }

          this.imageContainer(this.container, false)
          return
        }
        // intercept delete
        if (action.action === 'delete') {
          this.deleteContainer(item)
          return
        }
        //
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          //
          axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
          //
          const response = await axios.put(this.loggedUser.sub + '/api/lxd/containers/' + item.name + '/state', {
              "action": action.action,
              "timeout": 30,
              "force": true,
              "stateful": false
          })
          
          //
          this.snackbar = true;
          this.snackbarTimeout = 2500
          this.snackbarText = action.msg + ' container.';
          
          setTimeout(() => this.initialize(), 1500)
        } catch (error) {
          this.alert = { msg: 'Could not fetch data from server.', outline: false, color: 'error', icon: 'error' };
        }
      },

      check_started_with_ip (container) {
        return (
          container.network &&
          container.network.eth0.addresses.length > 0 &&
          container.status === 'Running' &&
          this.isIP4(container.network.eth0.addresses[0].address)
        )
      },

      safe_name() {
        this.container.info.name = this.container.info.name.replace(".", "-");
      },
      
      setSnackbar (msg) {
        //
        this.snackbar = true;
        this.snackbarTimeout = 2500
        this.snackbarText = msg;
      },

      console () {
        //
        if (xterm) {
          xterm.destroy()
        }
        var width = 100
        var height = 80
        // bash in everything except Alpine which uses Ash
        let command
        if (this.os === 'Alpine') {
          command = 'ash'
        } else {
          command = 'bash'
        }

        var tmp = document.createElement ('a');
        tmp.href = this.loggedUser.sub;

        // init request for websocket connection
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
        //
        const response = axios.post(this.loggedUser.sub + '/api/lxd/containers/' + this.container.info.name + '/exec', {
          'command': [command],
          'environment': {
            'HOME': '/root',
            'TERM': 'xterm',
            'USER': 'root'
          },
          'wait-for-websocket': true,
          'interactive': true,
          'width': width,
          'height': height
        }).then(function (response) {

          response = response.data.data
          //
          Terminal.applyAddon(fit)
          xterm = new Terminal({
            useStyle: true,
            screenKeys: false,
            cursorBlink: true
          })
          
          //
          var operationId = response.id
          var secret = response.metadata.fds[0]

          var wssurl = 'wss://'+tmp.hostname+':8443/1.0/operations/' +
              operationId +
              '/websocket?secret=' +
              secret
          //
          var sock = new WebSocket(wssurl)
          sock.binaryType = 'blob';
          sock.rejectUnauthorized = false;

          sock.onopen = function (e) {
            //
            var previousResponse = null
            //
            xterm.open(document.getElementById('terminal'))

            window.addEventListener('resize', function (e) {
              var height = Math.max(Math.round(window.innerHeight / 19.50), 15)
              xterm.resize(0, height)
              xterm.fit()
            })
            
            height = Math.max(Math.round(window.innerHeight / 19.50), 15)
            xterm.resize(0, height)
            xterm.fit()

            //
            xterm.on('data', (data) => {
              sock.send(new Blob([data]))
            })

            //
            sock.onmessage = function (msg) {
              var reader = new FileReader();
              reader.addEventListener("loadend", () => {
                msg = reader.result
                if (previousResponse !== null && previousResponse.trim() === 'exit' && msg.trim() === '') {
                  xterm.destroy()
                }
                previousResponse = msg
                xterm.write(msg)
              });
              reader.readAsText(msg.data);
              xterm.fit()
            }
            //
            sock.onclose = function (msg) {
              xterm.destroy()
            }
          }
          
          sock.onerror = function (e) {
            xterm.writeln('An error occured.')
            xterm.destroy()
            //
            this.snackbar = true;
            this.snackbarTimeout = 5000
            this.snackbarColor = 'red'
            this.snackbarText = 'Websocket connection failed, have you have visited https://'+tmp.hostname+':8443 to accept the SSL certificate?';
          }
        }).catch(error => {
          //
          this.snackbar = true;
          this.snackbarTimeout = 5000
          this.snackbarColor = 'red'
          this.snackbarText = 'Websocket connection failed.';
        })
      },
      
      async editContainer (item, openDialog = true) {
        this.editingIndex = this.items.indexOf(item)
        //
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          //
          axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
          //
          const response = await axios.get(this.loggedUser.sub + '/api/lxd/containers/' + item.name)
          
          this.container = {
            state: item,
            info: container.infix(response.data.data),
          }
        } catch (error) {
          this.alert = { msg: 'Could not fetch data from server.', outline: false, color: 'error', icon: 'error' };
        }
        
        this.containerDialog = openDialog
      },
      
      async saveContainer () {
        if (this.$refs.form.validate()) {

          // remote
          try {
            if (!this.loggedUser) {
              this.$router.replace('/servers')
            }
            
            this.container.info = Object.assign({}, container.outfix(this.container.info))
  
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
            //
            const response = await axios.patch(this.loggedUser.sub + '/api/lxd/containers/' + this.container.info.name, {
              config: this.container.info.config,
              ephemeral: this.container.info.ephemeral,
              stateful: this.container.info.stateful,
              profiles: this.container.info.profiles
            })
            //
            this.snackbar = true;
            this.snackbarText = 'Container '+this.container.info.name+' configuration saved.';
          } catch (error) {
            this.error = 'Could not save container configuration.';
          }
        }
      },
      
      newContainer() {
        this.newContainerDialog = true
      },

      async snapshotContainer (item) {
        //
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          //
          axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
          //
          let response = await axios.post(this.loggedUser.sub + '/api/lxd/containers/'+ item.name +'/snapshots', {
              name: new Date().toISOString(),
              stateful: false
          })
          
          this.setSnackbar('Snapshotting container.')

        } catch (error) {
          this.alert = { msg: 'Could not snapshot container.', outline: false, color: 'error', icon: 'error' };
        }
      },
      
      async imageContainer (item) {
        //
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          //
          axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
          //
          const response = await axios.post(this.loggedUser.sub + '/api/lxd/images', {
            source: {
              type: 'container',
              name: item.info.name
            },
            public: false,
            properties: {
              description: item.info.name,
              label: (item.info.config['image.label'] ? item.info.config['image.label'] : ''),
              architecture: (item.info.config['image.architecture'] ? item.info.config['image.architecture'] : ''),
              build: new Date(),
              distribution: (item.info.config['image.distribution'] ? item.info.config['image.distribution'] : ''),
              os: (item.info.config['image.os'] ? item.info.config['image.os'] : ''),
              release: (item.info.config['image.release'] ? item.info.config['image.release'] : ''),
              version: (item.info.config['image.version'] ? item.info.config['image.version'] : '')
            },
            auto_update: false
          })
          
          this.setSnackbar('Imaging container.')
          
        } catch (error) {
          this.alert = { msg: 'Could not image container.', outline: false, color: 'error', icon: 'error' };
        }
      },

      deleteContainer (item) {
        this.$prompt.show({
          persistent: true,
          width: 400,
          toolbar: {
            color: 'red darken-3',
            closable: false,
          },
          title: 'Delete container?',
          text: 'Are you sure you want to delete the <b>'+item.label+'</b> container?<p class="text-md-center red--text"><br><b>This action cannot be undone!</b></p>',
          buttons: [
            {
              title: 'Yes',
              color: 'success',
              handler: async () => { 
                const index = this.items.indexOf(item)
                this.items.splice(index, 1)
                
                try {
                  if (!this.loggedUser) {
                    this.$router.replace('/servers')
                  }
        
                  //
                  axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
                  //
                  const response = await axios.delete(this.loggedUser.sub + '/api/lxd/containers/' + item.name)
                  
                  this.setSnackbar('Container deleted.')
                } catch (error) {
                  this.alert = { msg: 'Could not delete container.', outline: false, color: 'error', icon: 'error' };
                }
              }
            },
            {
              title: 'No',
              color: 'error'
            }
         ]
        })
      },

      close () {
        this.dialog = false
        if (xterm) {
          xterm.destroy()
        }
        setTimeout(() => {
          this.newItem = Object.assign({}, this.defaultItem)
        }, 300)
      },

      formatBytes (bytes, decimals) {
        if(bytes == 0) return '0 Bytes';
        var k = 1024,
            dm = decimals || 2,
            sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
            i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
      }

    }
  }
</script>

<style>
  .dialog--fullscreen {
    background-color: #000!important;
    overflow:hidden;
  }
  .terminal {
      background-color: #000!important;
      color: #fff;
      font-family: courier-new, courier, monospace !important;
      font-feature-settings: "liga" 0;
      font-size: 15px !important;
  }
  #terminal {
    background-color: #000 !important;
    overflow: hidden;
    width: 100%;
    height: calc(100vh - 65px);
    padding-left: 5px;
  }
</style>
