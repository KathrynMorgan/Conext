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
                LXD - Images
                <!--<v-btn color="success" @click="dialog = true" style="float:right">New Forward</v-btn>-->
              </v-flex>
              <v-flex>
                <v-alert type="error" :value="error">
                  {{ error }}
                </v-alert>
                <v-tabs v-model="activeRemote" right @input="loadRemoteImages">
                  <v-tab ripple :href="`#${item}`" v-for="item in remotes" :key="item">{{ item }}</v-tab>
                  <v-tab-item :id="`${item}`" v-for="item in remotes" :key="item"></v-tab-item>
                </v-tabs>
                <v-tabs v-model="activeDistro" show-arrows>
                  <v-tab ripple :href="`#${dist}`" v-for="dist in distros_list" :key="dist">{{ dist }}</v-tab>
                  <v-tab-item :id="`${dist}`" v-for="dist in distros_list" :key="dist"></v-tab-item>
                </v-tabs>

                <v-data-table :headers="tableHeaders" :items="image_list" hide-actions class="elevation-1" :loading="tableLoading">
                  <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                  <template slot="items" slot-scope="props">
                    <td><a href="javascript:void(0)" @click.stop="editItem(props.item)">{{ props.item.properties.description }}</a></td>
                    <td>{{ props.item.properties.version }}</td>
                    <td>{{ ucfirst(props.item.properties.release) }}</td>
                    <td>{{ formatBytes(props.item.size) }}</td>
                    <td>{{ new Date(props.item.uploaded_at).toLocaleString() }}</td>
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

                      <v-tooltip left>
                        <v-btn slot="activator" icon class="mx-0" style="float:right" @click="deleteItem(props.item)" v-if="show_delete">
                          <v-icon color="pink">delete</v-icon>
                        </v-btn>
                        <span>Delete Image</span>
                      </v-tooltip>
                      <v-tooltip left>
                        <v-btn slot="activator" icon class="mx-0" style="float:right" @click="createContainer(props.item, false)">
                          <v-icon color="green">play_circle_outline</v-icon>
                        </v-btn>
                        <span>Launch Container</span>
                      </v-tooltip>
                    </td>
                  </template>
                  <template slot="no-data">
                    {{ tableLoading ? 'Fetching data, please wait...' : 'Remote has no images.' }}
                  </template>
                </v-data-table>
              </v-flex>
            </v-layout>
          </v-flex>
        </v-layout>
      </v-container>
      
      <!-- Fullscreen Dialog -->
      <v-dialog v-model="createDialog" max-width="900px" scrollable>
        <v-card tile>
          <v-toolbar card dark color="light-blue darken-3">
            <v-btn icon @click.native="createDialog = false" dark>
              <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>Launch Container</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-toolbar-items>
              <v-btn dark flat @click.native="createContainer(newItem, true)">Launch</v-btn>
            </v-toolbar-items>
            <v-menu bottom right offset-y>
              <v-btn slot="activator" dark icon>
                <v-icon>more_vert</v-icon>
              </v-btn>
            </v-menu>
          </v-toolbar>
          <v-card-text>
            <v-form ref="form" v-model="valid" lazy-validation>
              <v-text-field v-model="newItem.name" label="Name" :rules="nameRule" @input="safe_name()" hint="Enter name for new container." required></v-text-field>
              <v-select :items="[newItem.image]" v-model="newItem.image" label="Image" required disabled></v-select>
              <v-select :items="['default']" :rules="profilesRule" v-model="newItem.profile" label="Profiles" multiple chips required></v-select>
              <v-switch :label="`${newItem.ephemeral ? 'Ephemeral' : 'Ephemeral'}`" color="success" v-model="newItem.ephemeral"></v-switch>
            </v-form>
          </v-card-text>
          <div style="flex: 1 1 auto;"></div>
        </v-card>
      </v-dialog>
      
      <!-- Fullscreen Dialog -->
      <v-dialog v-model="dialog" max-width="900px" scrollable>
        <v-card tile>
          <v-toolbar card dark color="light-blue darken-3">
            <v-btn icon @click.native="dialog = false" dark>
              <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>.</v-toolbar-title>
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
                <!--<v-form ref="form" v-model="valid" lazy-validation>-->
                  <!--<v-text-field v-model="editingItem.label" :rules="labelRule" label="Label:" placeholder="" required hint="Enter a label for the port forward." persistent-hint></v-text-field>-->
                  <!--<v-text-field v-model="editingItem.ip" label="IP:" placeholder="" required hint="Enter the IP address for the port forward." persistent-hint></v-text-field>-->
                  <!--<v-text-field v-model="editingItem.port" label="External Port:" placeholder="" required hint="Enter the external port to forward." persistent-hint></v-text-field>-->
                  <!--<v-text-field v-model="editingItem.srv_port" label="Internal Port:" placeholder="" required hint="Enter the internal port to forward" persistent-hint></v-text-field>-->
                   <!--
                  <v-text-field v-model="editingItem.srv_type" label="Service Type:" placeholder="" required hint="Enter the service type." persistent-hint></v-text-field>
                  <v-text-field v-model="editingItem.srv_port" label="Service Port:" placeholder="" required hint="Enter the service port." persistent-hint></v-text-field>
                  <pre>{{editingItem}}</pre>
                  -->
                <!--</v-form>-->
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
      }),
      image_list: function () {
        return this.items.filter((row) => {
          if (this.activeDistro.toLowerCase() !== row.properties.os.toLowerCase()) {
            return false
          }
          return row
        })
      },
      distros_list: function () {
        return Array.from(new Set(this.distros))
      }
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
      remotes: [],
      distros: [],
      publicServers: ['images', 'ubuntu', 'ubuntu-daily'],
      showDelete: false,
      
      activeRemote: 'local',
      activeDistro: 'ubuntu',
      
      tableLoading: true,
      tableNoData: 'You have not added any port forwards.',
      tableHeaders: [
        { text: 'Description', value: 'properties.description' },
        { text: 'Version', value: 'properties.version' },
        { text: 'Release', value: 'properties.release' },
        { text: 'Size', value: 'size' },
        { text: 'Uploaded', value: 'uploaded_at' },
        { text: 'Actions', value: 'actions', sortable: false, align: 'right' }
      ],
      itemActions: [
        { title: 'Start' },
        { title: 'Stop' },
        { title: 'Delete' }
      ],

      // dialog
      dialog: false,
      createDialog: false,
      
      // item
      newItem: {
        name: '',
        image: '',
        image_fingerprint: '',
        profile: ['default'],
        ephemeral: false,
        remote: 'local'
      },
      defaultItem: {
        name: '',
        image: '',
        image_fingerprint: '',
        profile: ['default'],
        ephemeral: false,
        remote: 'local'
      },
      
      // item form & validation
      valid: true,
      nameRule: [
        v => !!v || 'Name is required.',
        v => (v && /^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/.test(v)) || 'Only letters, digits or hyphens. No leading hyphen or digit. Dots are converted to hyphens.',
        v => (v && isNaN(v.charAt(0))) || 'Only letters, digits or hyphens. No leading hyphen or digit. Dots are converted to hyphens.'
      ],
      profilesRule: [
        v => v.length >= 1 || 'At least one profile is required.'
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
          var response = await axios.get(this.loggedUser.sub + '/api/lxd/images/remotes')
          this.remotes = response.data.data
          
          //
          this.loadRemoteImages(this.activeRemote)

          //
        } catch (error) {
          this.tableNoData = 'No data.';
          this.error = 'Could not fetch data from server.';
        }
        this.tableLoading = false
      },
      
      async loadRemoteImages(remote = 'local') {
        //
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          //
          var response = await axios.get(this.loggedUser.sub + '/api/lxd/images?remote='+remote)
          this.items = response.data.data
          
          // assign distro list in background
          setTimeout(() => {
            this.distros = [];
            for (var key in this.items) {
              this.distros.push(this.items[key].properties.os.toLowerCase())
            }
          }, 0)
          
          // show delete button
          this.show_delete = this.items.length > 0 && !this.publicServers.includes(this.activeRemote)
          
          //
          this.activeDistro = 'ubuntu';

          //
        } catch (error) {
          this.tableNoData = 'No data.';
          this.error = 'Could not fetch data from server.';
        }
      },
      
     createContainer (item, launch = false) {
        if (!launch) {
          this.createDialog = true
          this.newItem = {
            name: '',
            image: item.properties.description,
            image_fingerprint: item.fingerprint,
            profile: ['default'],
            ephemeral: false,
            remote: this.activeRemote
          };
        } else {
          if (this.$refs.form.validate() && this.valid) {

            if (!this.loggedUser) {
              this.$router.replace('/servers')
            }
            
            //
            this.snackbar = true;
            this.snackbarColor = 'green';
            this.snackbarText = 'Container queued for creation.';
              
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
            axios.post(this.loggedUser.sub + '/api/lxd/containers', this.newItem).then(response => {
              if (response.data.code === 200) {
                //
                this.snackbar = true;
                this.snackbarText = 'Container created.';
              } else {
                //
                this.snackbar = true;
                this.snackbarColor = 'red';
                this.snackbarText = response.data.error;
              }
            }).catch(error => {
              this.error = 'Could not create container.'
            })
            this.createDialog = false
          }
        }
      },
      
      safe_name() {
        this.newItem.name = this.newItem.name.replace(".", "-");
      },

      // create or edit item
      editItem (item) {
        this.editingIndex = this.items.indexOf(item)
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
          const response = await axios.delete(this.loggedUser.sub + '/api/routes/port-forwards', { data: item })
          //
          this.snackbar = true;
          this.snackbarText = 'Port forward successfully deleted.';
          
        } catch (error) {
          this.error = 'Could not delete port forward from server.';
        }
      },

      // save item
      async save () {
        if (this.$refs.form.validate()) {
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
            const response = await axios.post(this.loggedUser.sub + '/api/routes/port-forwards', this.editingItem)
            //
            this.snackbar = true;
            this.snackbarText = 'Port forward successfully saved.';
          } catch (error) {
            this.error = 'Could not save port forward to server.';
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
      },
      
      formatBytes (bytes, decimals) {
        if(bytes == 0) return '0 Bytes';
        var k = 1024,
            dm = decimals || 2,
            sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
            i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
      },
      
      ucfirst(str) {
          return String(str).charAt(0).toUpperCase() + String(str).slice(1);
      }

    }
  }
</script>

<style>

</style>
