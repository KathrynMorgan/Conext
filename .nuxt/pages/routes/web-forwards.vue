<template>
  <v-app>
    <!-- Snackbar Alert -->
    <v-snackbar top :timeout="snackbarTimeout" :color="snackbarColor" v-model="snackbar">
      {{ snackbarText }}
      <v-btn dark flat @click.native="snackbar = false">Close</v-btn>
    </v-snackbar>

    <v-content>
      
      <!-- Main Content -->
      <v-container fluid tag="section" id="grid">
        <v-layout row wrap>
          <v-flex d-flex xs12 order-xs5>
            <v-layout column>
              <v-flex tag="h1" class="display mb-2">
                Routes - Web Forwards
                <v-btn color="success" @click="dialog = true" style="float:right">New Forward</v-btn>
              </v-flex>
              <v-flex>
                <v-alert type="error" :value="error">
                  {{ error }}
                </v-alert>
                <p>Web forwards allow you to route HTTP/S traffic to containers or external upstreams.</p>
                <v-data-table :headers="tableHeaders" :items="items" hide-actions class="elevation-1" :loading="tableLoading">
                  <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                  <template slot="items" slot-scope="props">
                    <td><a href="javascript:void(0)" @click.stop="editItem(props.item)">{{ props.item.label }}</a></td>
                    <td>{{ props.item.ip }}</td>
                    <td>{{ props.item.port }}</td>
                    <td><ul><li style="list-style-type: none;" v-for="domain in props.item.ownDomain" :key="domain.name">{{ domain.name }}</li></ul></td>
                    <td><v-icon color="blue-grey lighten-3" v-if="props.item.ssl_type === 'letsencrypt'">https</v-icon></td>
                    <td>
                      <!--
                      <v-menu offset-y>
                        <v-btn icon class="mx-0" slot="activator">
                          <v-icon color="blue-grey lighten-3">view_headline</v-icon>
                        </v-btn>
                        <v-list>
                          <v-list-tile v-for="item in containerActions" :key="item.title" @click="actionContainer(item.title.toLowerCase(), props.item.name)">
                            <v-list-tile-title>{{ item.title }}</v-list-tile-title>
                          </v-list-tile>
                        </v-list>
                      </v-menu>

                      <v-btn icon class="mx-0" @click="editItem(props.item)">
                        <v-icon color="teal">edit</v-icon>
                      </v-btn>
                      -->
                      <v-btn icon class="mx-0" style="float:right" @click="deleteItem(props.item)">
                        <v-icon color="pink">delete</v-icon>
                      </v-btn>
                    </td>
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
      
      <!-- Fullscreen Dialog -->
      <v-dialog v-model="dialog" max-width="900px" scrollable>
        <v-card tile>
          <v-toolbar card dark color="light-blue darken-3">
            <v-btn icon @click.native="dialog = false" dark>
              <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>{{ editingIndex === -1 ? 'New' : 'Edit' }} Web Forward</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-toolbar-items>
              <v-btn dark flat @click.native="save()">Save</v-btn>
            </v-toolbar-items>
            <v-menu bottom right offset-y>
              <v-btn slot="activator" dark icon>
                <v-icon>more_vert</v-icon>
              </v-btn>
              <!--
              <v-list>
              <v-list-tile v-for="(item, i) in items" :key="i">
              <v-list-tile-title>{{ item.title }}</v-list-tile-title>
              </v-list-tile>
              </v-list>
              -->
            </v-menu>
          </v-toolbar>
          <v-card-text style="padding: 0px;">
            <v-card flat>
              <v-card-text>
                <!--
                <v-alert :value="true" outline color="info" icon="info" style="margin-bottom: 10px;">
                  <strong>Endpoint:</strong> {{loggedUser.sub}}/{{editingItem.version}}/{{editingItem.module}} [GET|POST|PUT|DELETE]
                </v-alert>
                -->
                <v-form ref="form" v-model="valid" lazy-validation>
                  <v-text-field v-model="editingItem.label" :rules="labelRule" label="Label:" placeholder="" required hint="Enter a label for the web forward." persistent-hint></v-text-field>

                  <h3 style="margin-top:15px">Domains</h3>
                  <v-layout row wrap>
                    <v-flex xs11>
                      <v-text-field v-model="newDomain" label="Domain:" hint="Enter a domain name to forward to upstream/s."></v-text-field>
                    </v-flex>
                    <v-flex xs1>
                      <v-btn flat icon color="success" @click.native="addDomain">
                        <v-icon>add</v-icon>
                      </v-btn>
                    </v-flex>
                  </v-layout>
                  <v-layout row wrap v-for="domain in editingItem.ownDomain" :key="domain.id">
                    <v-flex xs11>
                      <v-text-field v-model="domain.name" label="Domain:" hint="Empty or invalid domains are removed."></v-text-field>
                    </v-flex>
                    <v-flex xs1>
                      <v-btn flat icon color="error" @click.native="removeDomain(domain)">
                        <v-icon>remove</v-icon>
                      </v-btn>
                    </v-flex>
                  </v-layout>

                  <h3 style="margin-top:15px">Upstream/s</h3>
                  <v-layout row wrap>
                    <v-flex xs6>
                      <v-text-field v-model="newUpstream.ip" label="IP Address:" hint="Enter upstream IP address."></v-text-field>
                    </v-flex>
                    <v-flex xs5>
                      <v-text-field v-model="newUpstream.port" label="Port:" hint="Enter upstream port."></v-text-field>
                    </v-flex>
                    <v-flex xs1>
                      <v-btn flat icon color="success" @click.native="addUpstream">
                        <v-icon>add</v-icon>
                      </v-btn>
                    </v-flex>
                  </v-layout>
                  <v-layout row wrap v-for="upstream in editingItem.ownUpstream" :key="upstream.id">
                   <v-flex xs6>
                      <v-text-field v-model="upstream.ip" label="IP Address:" hint="Empty or invalid ips are removed."></v-text-field>
                    </v-flex>
                    <v-flex xs5>
                      <v-text-field v-model="upstream.port" label="Port:" hint="Empty or invalid ports are removed."></v-text-field>
                    </v-flex>
                    <v-flex xs1>
                      <v-btn flat icon color="error" @click.native="removeUpstream(upstream)">
                        <v-icon>remove</v-icon>
                      </v-btn>
                    </v-flex>
                  </v-layout>
            
                  <h3 style="margin-top:15px">SSL</h3>
                  <v-checkbox v-model="editingItem.letsencrypt" label="Let's Encrypt"></v-checkbox>
                </v-form>
              </v-card-text>
            </v-card>
          </v-card-text>
          <div style="flex: 1 1 auto;"></div>
        </v-card>
      </v-dialog>
    </v-content>
  </v-app>
</template>

<script>
  import { mapGetters, mapMutations } from 'vuex'
  import { setToken } from '~/utils/auth'
  import axios from 'axios'

  export default {
    middleware: [
      'authenticated'
    ],
    components: {},
    computed: {
      ...mapGetters({
        isAuthenticated: 'auth/isAuthenticated',
        loggedUser: 'auth/loggedUser',
        loggedToken: 'auth/loggedToken'
      })
    },
    data: () => ({
      // global error
      error: '',

      // snackbar (notification)
      snackbar: false,
      snackbarColor: 'green',
      snackbarText: '',
      snackbarTimeout: 5000,

      // table & items
      items: [],
      
      tableLoading: true,
      tableNoData: 'You have not added any web forwards.',
      tableHeaders: [
        { text: 'Label', value: 'label' },
        { text: 'IP', value: 'ip' },
        { text: 'Port', value: 'port' },
        { text: 'Domains', value: 'ownDomain' },
        { text: 'SSL', value: 'ssl_type' },
        { text: 'Actions', value: 'name', sortable: false, align: 'right' }
      ],
      itemActions: [
        { title: 'Start' },
        { title: 'Stop' },
        { title: 'Delete' }
      ],

      // dialog
      dialog: false,
      
      // item
      editingIndex: -1,
      editingItem: {
        id: -1,
        label: "",
        name: "",
        ssl_type: "",
        letsencrypt: false,
        added: "",
        updated: "",
        has_change: 1,
        has_error: 0,
        delete: 0,
        enabled: 1,
        update_ip: 0,
        ip: "",
        port: "",
        error: "",
        ownDomain: [],
        ownUpstream: []
      },
      defaultItem: {
        id: -1,
        label: "",
        name: "",
        ssl_type: "",
        letsencrypt: false,
        added: "",
        updated: "",
        has_change: 1,
        has_error: 0,
        delete: 0,
        enabled: 1,
        update_ip: 0,
        ip: "",
        port: "",
        error: "",
        ownDomain: [],
        ownUpstream: []
      },
      
      // new domain item
      newDomain: '',
      newUpstream: {
        ip: '',
        port: ''
      },
      
      // item form & validation
      valid: true,
      labelRule: [
        v => !!v || 'Label is required',
        v => (v && v.length <= 100) || 'Label must be less than 100 characters'
      ]
    }),
    mounted: function () {
      this.initialize()
    },
    watch: {
      dialog (val) {
        val || this.close()
      }
    },
    methods: {
      async initialize () {
        // fetch remote
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
          //
          const response = await axios.get(this.loggedUser.sub + '/api/routes/web-forwards')
          this.items = response.data.data
        } catch (error) {
          this.tableNoData = 'No data.';
          this.error = 'Could not fetch data from server.';
        }
        this.tableLoading = false
      },
      
      //
      addDomain () {
        if (!this.editingItem.ownDomain) {
          this.editingItem.ownDomain = [];
        }
        this.editingItem.ownDomain.unshift({name:this.newDomain})
        this.newDomain = '';
      }, 
      //
      removeDomain (item) {
        const index = this.editingItem.ownDomain.indexOf(item)
        this.editingItem.ownDomain.splice(index, 1)
      },
      
      //
      addUpstream () {
        if (!this.editingItem.ownUpstream) {
          this.editingItem.ownUpstream = [];
        }
        this.editingItem.ownUpstream.unshift({ip: this.newUpstream.ip, port: this.newUpstream.port})
        this.newUpstream = {
          ip: '',
          port: ''
        };
      }, 
      //
      removeUpstream (item) {
        const index = this.editingItem.ownUpstream.indexOf(item)
        this.editingItem.ownUpstream.splice(index, 1)
      },

      // create or edit item
      editItem (item) {
        this.editingIndex = this.items.indexOf(item)
        
        // ssl_type
        if (item.ssl_type === 'letsencrypt') {
          item.letsencrypt = true;
        }
        
        this.editingItem = Object.assign({}, item)
        this.dialog = true
      },

      // delete item
      async deleteItem (item) {
        const index = this.items.indexOf(item)
        
        // local
        //if (confirm('Are you sure you want to delete this item?')){
          this.items.splice(index, 1)
        //}

        // remote
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
          //
          const response = await axios.delete(this.loggedUser.sub + '/api/routes/web-forwards', { data: item })
          //
          this.snackbar = true;
          this.snackbarText = 'Web forward successfully deleted.';
          
        } catch (error) {
          this.error = 'Could not delete web forward from server.';
        }
      },

      // save item
      async save () {
        if (this.$refs.form.validate()) {
          
          if (this.newDomain.length > 0){
            this.addDomain()
          }
          if (this.newUpstream.ip){
            this.addUpstream()
          }

          // local
          if (this.editingIndex > -1) {
            Object.assign(this.items[this.editingIndex], this.editingItem)
          } else {
            this.items.push(Object.assign({}, this.editingItem))
          }
          
          // remote
          try {
            if (!this.loggedUser) {
              this.$router.replace('/servers')
            }
  
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
            //
            const response = await axios.post(this.loggedUser.sub + '/api/routes/web-forwards', this.editingItem)
            //
            this.snackbar = true;
            this.snackbarText = 'Web forward successfully saved.';
          } catch (error) {
            this.error = 'Could not save web forward to server.';
          }
          
          // reload data
          this.initialize()
          
          this.close()
        }
      },
      
      // close item dialog, and reset to default item
      close () {
        this.dialog = false
        setTimeout(() => {
          this.editingItem = Object.assign({}, this.defaultItem)
          this.editingIndex = -1
        }, 300)
      }

    }
  }
</script>

<style>

</style>
