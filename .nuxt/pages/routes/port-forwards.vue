<template>
  <v-app>
    <!-- Snackbar Alert -->
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
                Routes - Port Forwards
                <v-btn color="success" @click="dialog = true" style="float:right">New Forward</v-btn>
              </v-flex>
              <v-flex>
                <v-alert type="error" :value="error.global">
                  {{ error.global }}
                </v-alert>
                <p>Port forwards allow you to route TCP/UDP traffic into containers.</p>
                <v-data-table :headers="tableHeaders" :items="items" hide-actions class="elevation-1" :loading="tableLoading">
                  <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                  <template slot="items" slot-scope="props">
                    <td><a href="javascript:void(0)" @click.stop="editItem(props.item)">{{ props.item.label }}</a></td>
                    <td>{{ props.item.ip }}</td>
                    <td>{{ props.item.port }}</td>
                    <td>{{ props.item.srv_port }}</td>
                    <td>
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
      <!-- Add/Edit Dialog -->
      <v-dialog v-model="dialog" max-width="600px" scrollable>
        <v-card tile>
          <v-toolbar card dark color="light-blue darken-3">
            <v-btn icon @click.native="dialog = false" dark>
              <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>{{ editingIndex === -1 ? 'New' : 'Edit' }} Port Forward</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-toolbar-items>
              <v-btn dark flat @click.native="save()">Save</v-btn>
            </v-toolbar-items>
          </v-toolbar>
          <v-card-text style="padding: 0px;">
            <v-card flat>
              <v-card-text>
                <v-alert type="error" :value="error.editing">
                  {{ error.editing }}
                </v-alert>
                <v-form ref="form" v-model="valid" lazy-validation>
                  <v-text-field v-model="editingItem.label" :rules="labelRule" label="Label:" placeholder="" required hint="Enter a label for the port forward."></v-text-field>
                  <v-text-field v-model="editingItem.ip" :rules="ipRule" label="IP:" placeholder="" required hint="Enter the containers IP address for the port forward."></v-text-field>
                  <v-text-field v-model="editingItem.port" :rules="portRule" :error-messages="portCheck" label="External Port:" placeholder="" required hint="Enter the external port to forward."></v-text-field>
                  <v-text-field v-model="editingItem.srv_port" :rules="srvPortRule" label="Internal Port:" placeholder="" required hint="Enter the internal port to forward."></v-text-field>
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
    data: function () {
      var self = this;
      return {
        // errors
        error: { editing: '', global: ''},
  
        // snackbar (notification)
        snackbar: false,
        snackbarColor: 'green',
        snackbarText: '',
        snackbarTimeout: 5000,
  
        // table & items
        items: [],
        
        tableLoading: true,
        tableNoData: 'You have not added any port forwards.',
        tableHeaders: [
          { text: 'Label', value: 'label' },
          { text: 'IP', value: 'ip' },
          { text: 'External Port', value: 'port' },
          { text: 'Internal Port', value: 'srv_port' },
          { text: 'Actions', value: 'name', sortable: false, align: 'right' }
        ],

        // dialog
        dialog: false,
        
        // item
        editingIndex: -1,
        editingItem: {
          id: -1,
          label: "",
          ip: "",
          port: "",
          srv_type: "",
          srv_port: ""
        },
        defaultItem: {
          id: -1,
          label: "",
          ip: "",
          port: "",
          srv_type: "",
          srv_port: ""
        },
        editingPort: '',
        
        // item form & validation
        valid: true,
        labelRule: [
          v => !!v || 'Label is required',
          v => (v && v.length <= 100) || 'Label must be less than 100 characters'
        ],
        ipRule: [
          v => !!v || 'IP address is required',
          v => (v && /^(?!0)(?!\.)((^|\.)([1-9]?\d|1\d\d|2(5[0-5]|[0-4]\d))){4}$/gm.test(v)) || 'Invalid IP address'
        ],
        portRule: [
          v => !!v || 'External port is required',
          v => (v && !isNaN(v)) || 'Invalid port number',
          v => (v && v !== 0) || 'Invalid port number',
          v => (v && v < 65535) || 'Invalid port number'
        ],
        portCheck: [], // checked by watch
        srvPortRule: [
          v => !!v || 'Internal port is required',
          v => (v && !isNaN(v)) || 'Invalid port number',
          v => (v && v !== 0) || 'Invalid port number',
          v => (v && v < 65535) || 'Invalid port number'
        ]
      }
    },
    mounted: function () {
      axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
      this.initialize()
    },
    watch: {
      dialog (val) {
        val || this.close()
      },
      'editingItem.port': function (v) {
        // sanity check its a number
        if (!isNaN(v)) {
          this.valid = false;
          // check port is in range
          this.checkAllowedPort(v).then(valid => {
            this.portCheck = !valid ? 'Port not in allowed ranges: 2200-2299, 3300-3399, 4300-4399, 8000-8099' : []
            // if in range check availability
            if (this.portCheck.length === 0) {
              this.checkPortInUse(v).then(valid => {
                this.portCheck = valid ? (this.editingIndex !== -1 && this.editingPort === v ? [] : 'Port in use') : []
                if (this.portCheck.length === 0) {
                  this.valid = true;
                }
              })
            }
          })
        }
      }
    },
    methods: {
      async initialize () {
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          //
          const response = await axios.get(this.loggedUser.sub + '/api/routes/port-forwards')
          this.items = response.data.data
        } catch (error) {
          this.tableNoData = 'No data.';
          this.error.global = 'Could not fetch data from server.';
        }
        this.tableLoading = false
      },
      
      async checkPortInUse (port) {
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          //
          const response = await axios.post(this.loggedUser.sub + '/api/routes/port-forwards/check-port-in-use', {port:port})
          
          return Boolean(response.data.data)
        } catch (error) {
          this.error.editing = 'Error checking port.';
          return false
        } 
      },
      
      async checkAllowedPort (port) {
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          //
          const response = await axios.post(this.loggedUser.sub + '/api/routes/port-forwards/check-allowed-port', {port:port})
          
          return Boolean(response.data.data)
        } catch (error) {
          this.error.editing = 'Error checking port.';
          return false
        } 
      },

      // create or edit item
      editItem (item) {
        this.editingIndex = this.items.indexOf(item)
        this.editingItem = Object.assign({}, item)
        if (this.editingItem.port) {
          this.editingPort = this.editingItem.port
        } else {
          this.editingPort = ''
        }
        this.dialog = true
      },

      // delete item
      deleteItem (item) {
        this.$prompt.show({
          persistent: true,
          width: 400,
          toolbar: {
            color: 'red darken-3',
            closable: false,
          },
          title: 'Delete port forward?',
          text: 'Are you sure you want to delete the <b>'+item.label+'</b> port forward?',
          buttons: [
            {
              title: 'Yes',
              color: 'success',
              handler: async () => { 
                const index = this.items.indexOf(item)
        
                // local
                this.items.splice(index, 1)
        
                // remote
                try {
                  if (!this.loggedUser) {
                    this.$router.replace('/servers')
                  }

                  //
                  const response = await axios.delete(this.loggedUser.sub + '/api/routes/port-forwards', { data: item })
                  //
                  this.snackbar = true;
                  this.snackbarText = 'Port forward successfully deleted.';
                  
                } catch (error) {
                  this.error.global = 'Could not delete port forward from server.';
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

      // save item
      async save () {
        if (this.$refs.form.validate() && this.valid) {
          // validate remote
          try {
            if (!this.loggedUser) {
              this.$router.replace('/servers')
            }

            //
            const response = await axios.post(this.loggedUser.sub + '/api/routes/port-forwards', this.editingItem)
            
            if (response.data.error) {
              //
              if (response.data.error.port) {
                this.error.editing = response.data.error.port;
              } else if (response.data.error.srv_port) {
                this.error.editing = response.data.error.srv_port;
              } else if (response.data.error.query) {
                this.error.editing = response.data.error.query;
              } else if (response.data.error.name) {
                this.error.editing = response.data.error.name;
              } else {
                this.error.editing = 'Something went wrong';
              }
            } else {
              // add local
              if (this.editingIndex > -1) {
                Object.assign(this.items[this.editingIndex], this.editingItem)
              } else {
                this.items.push(Object.assign({}, this.editingItem))
              }
              //
              this.snackbar = true;
              this.snackbarText = 'Port forward successfully saved.';
              // reload data
              this.initialize()
              
              this.close()
            }
          } catch (error) {
            this.error.editing = 'Could not save port forward to server.';
          }
        }
      },
      
      // close item dialog, and reset to default item
      close () {
        this.dialog = false
        setTimeout(() => {
          this.editingItem = Object.assign({}, this.defaultItem)
          this.editingIndex = -1
          this.editingPort = ''
        }, 300)
      }

    }
  }
</script>

<style>

</style>
