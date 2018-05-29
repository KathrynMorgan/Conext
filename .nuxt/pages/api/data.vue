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
                API - Data
                <v-btn color="success" @click="dialog = true" style="float:right">New Endpoint</v-btn>
              </v-flex>
              <v-flex>
                <v-alert type="error" :value="error">
                  {{ error }}
                </v-alert>
                <p>The data API allows you to create custom data endpoints on the server.</p>
                <v-data-table :headers="tableHeaders" :items="items" hide-actions class="elevation-1">
                  <template slot="items" slot-scope="props">
                    <td><a href="javascript:void(0)" @click.stop="editItem(props.item)">{{ props.item.module }}</a></td>
                    <td>{{ props.item.version }}</td>
                    <td>
                      <v-btn icon class="mx-0" style="float:right" @click="deleteItem(props.item)">
                        <v-icon color="pink">delete</v-icon>
                      </v-btn>
                    </td>
                  </template>
                  <template slot="no-data">
                    You have not added any API endpoints.
                  </template>
                </v-data-table>
              </v-flex>
            </v-layout>
          </v-flex>
        </v-layout>
      </v-container>
      
      <!-- Fullscreen Dialog -->
      <v-dialog v-model="dialog" fullscreen hide-overlay transition="dialog-bottom-transition" scrollable>
        <v-card tile>
          <v-toolbar card dark color="light-blue darken-3">
            <v-btn icon @click.native="dialog = false" dark>
              <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>{{ editingIndex === -1 ? 'New' : 'Edit' }} Endpoint</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-toolbar-items>
              <v-btn dark flat @click.native="save()">Save</v-btn>
            </v-toolbar-items>
          </v-toolbar>
          <v-card-text style="padding: 0px;">
            <v-card flat>
              <v-card-text>
                <v-alert :value="true" outline color="info" icon="info" style="margin-bottom: 10px;">
                  <strong>Endpoint:</strong> {{loggedUser.sub}}/{{editingItem.version}}/{{editingItem.module}} [GET|POST|PUT|DELETE]
                </v-alert>
                <v-form ref="form" v-model="valid" lazy-validation>
                  <v-layout row wrap>
                    <v-flex xs6>
                      <v-text-field v-model="editingItem.module" :rules="moduleRules" label="Name:" placeholder="" required hint="Enter the name of the endpoint."></v-text-field>
                    </v-flex>
                    <v-flex xs6>
                      <v-text-field v-model="editingItem.version" :rules="versionRules" label="Version:" placeholder="e.g: 1.0" required hint="Enter the version of the endpoint."></v-text-field>
                    </v-flex>
                  </v-layout> 
                  <v-layout row wrap>
                    <v-flex xs6>
                      <v-select :items="['None', 'JWT']" v-model="editingItem.auth" label="Authentication:"></v-select>
                      <p v-if="editingItem.auth === 'JWT'" style="margin-top:-20px;color:rgba(0,0,0,0.54);font-size: 12px;">To obtain bearer token, authenticate using POST to {{loggedUser.sub}}/auth/jwt</p>
                    </v-flex>
                    <v-flex xs6>
                      <v-select :items="['None', 'JSON', 'HTML', 'TEXT', 'JS', 'XML']" v-model="editingItem.header" label="Response Content Type:" hint="Select response content-type."></v-select>
                    </v-flex>
                  </v-layout> 
                  <h3>Source (PHP)</h3>
                  <no-ssr placeholder="Loading...">
                    <codemirror v-model="editingItem.source" :options="cmOption"></codemirror>
                  </no-ssr>
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

      // code mirror options
      cmOption: {
        smartIndent: false,
        indentWithTabs: true,
        tabSize: 4,
        indentUnit:4,
        foldGutter: true,
        styleActiveLine: true,
        lineNumbers: true,
        line: true,
        keyMap: "sublime",
        mode: 'text/x-php'
      },
        
      // snackbar (notification)
      snackbar: false,
      snackbarColor: 'green',
      snackbarText: '',
      snackbarTimeout: 5000,

      // table & items
      items: [],
      
      tableHeaders: [
        { text: 'Name', value: 'module' },
        { text: 'Version', value: 'version' },
        { text: 'Actions', value: 'module', sortable: false, align: 'right' }
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
        module: "",
        version: "1.0",
        source: "",
        headers: "",
        config: null,
        auth: null
      },
      defaultItem: {
        id: -1,
        module: "",
        version: "1.0",
        source: "",
        headers: "",
        config: null,
        auth: null
      },
      
      // item form & validation
      valid: true,
      moduleRules: [
        v => !!v || 'Module name is required',
        v => (v && v.length <= 100) || 'Module name must be less than 100 characters'
      ],
      versionRules: [
        v => !!v || 'Version is required',
        v => (v && !isNaN(v)) || 'Version must be a number'
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
          const response = await axios.get(this.loggedUser.sub + '/api/ams/data')
          this.items = response.data.data
        } catch (error) {
          this.error = 'Could not fetch data from server.';
        }
      },

      // create or edit item
      editItem (item) {
        this.editingIndex = this.items.indexOf(item)
        this.editingItem = Object.assign({}, item)
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
          title: 'Delete endpoint?',
          text: 'Are you sure you want to delete the <b>'+item.module+'</b> endpoint?',
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
        
                  axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
                  //
                  const response = await axios.delete(this.loggedUser.sub + '/api/ams/data', { data: item })
                  //
                  this.snackbar = true;
                  this.snackbarText = 'Endpoint successfully deleted.';
                  
                } catch (error) {
                  this.error = 'Could not delete endpoint from server.';
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
            const response = await axios.post(this.loggedUser.sub + '/api/ams/data', this.editingItem)
            //
            this.snackbar = true;
            this.snackbarText = 'Endpoint successfully saved.';
          } catch (error) {
            this.error = 'Could not save endpoint to server.';
          }
  
          if (this.editingIndex === -1) {
            this.close()
          }
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
  .CodeMirror {
    border: 1px solid #eee;
    min-height:calc(100vh - 350px);
    height: auto;
    font-family: Ubuntu Mono, Menlo, Consolas, monospace;
    font-size: 13px;
    line-height:1.1em;
  }
  .CodeMirror-scroll{
    min-height:calc(100vh - 350px);
  }
  .CodeMirror-gutters {
    left: 0px!important;
  }
</style>
