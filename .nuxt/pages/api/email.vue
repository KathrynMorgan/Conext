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
                API - Email
                
                <v-btn color="success" @click="state = 'templates';dialog.template = true" style="float:right" v-if="state == 'templates'">New Template</v-btn>
                <v-btn color="success" @click="state = 'providers';dialog.provider = true" style="float:right" v-if="state == 'providers'">New Provider</v-btn>
                
                <v-btn color="orange" @click="state = 'providers';" style="float:right" v-if="state == 'templates'">Providers</v-btn>
                <v-btn color="orange" @click="state = 'templates';" style="float:right" v-if="state == 'providers'">Templates</v-btn>
              </v-flex>
              <v-flex>
                <v-alert type="error" :value="error">
                  {{ error }}
                </v-alert>
                <p>The email API allows you to create custom email templates with multiple providers, then simply use a POST request to send the email.</p>
                
                <div v-if="state == 'templates'">
                  <v-data-table :headers="tableHeaders.template" :items="items.template" hide-actions class="elevation-1">
                    <template slot="items" slot-scope="props">
                      <td><a href="javascript:void(0)" @click.stop="editItem('template', props.item)">{{ props.item.name }}</a></td>
                      <td>{{ props.item.slug }}</td>
                      <td>{{ props.item.subject }}</td>
                      <td>{{ props.item.type }}</td>
                      <td>
                        <v-btn icon class="mx-0" style="float:right" @click="deleteItem('template', props.item)">
                          <v-icon color="pink">delete</v-icon>
                        </v-btn>
                      </td>
                    </template>
                    <template slot="no-data">
                      You have not added any email templates.
                    </template>
                  </v-data-table>
                </div>
                <div v-if="state == 'providers'">
                  <v-data-table :headers="tableHeaders.provider" :items="items.provider" hide-actions class="elevation-1">
                    <template slot="items" slot-scope="props">
                      <td><a href="javascript:void(0)" @click.stop="editItem('provider', props.item)">{{ props.item.host }}</a></td>
                      <td>{{ props.item.limit }}</td>
                      <td>{{ props.item.limit_sent }}</td>
                      <td>
                        <v-btn icon class="mx-0" style="float:right" @click="deleteItem('provider', props.item)">
                          <v-icon color="pink">delete</v-icon>
                        </v-btn>
                      </td>
                    </template>
                    <template slot="no-data">
                      You have not added any email providers.
                    </template>
                  </v-data-table>
                </div>
              </v-flex>
            </v-layout>
          </v-flex>
        </v-layout>
      </v-container>
      
      <!-- Fullscreen Dialog -->
      <v-dialog v-model="dialog.provider" fullscreen hide-overlay transition="dialog-bottom-transition" scrollable>
        <v-card tile>
          <v-toolbar card dark color="light-blue darken-3">
            <v-btn icon @click.native="dialog.provider = false" dark>
              <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>{{ editingIndex.provider === -1 ? 'New' : 'Edit' }} Provider</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-toolbar-items>
              <v-btn dark flat @click.native="save('provider')">Save</v-btn>
            </v-toolbar-items>
          </v-toolbar>
          <v-card-text style="padding: 0px;">
            <v-card flat>
              <v-card-text>
                <v-form ref="formprovider" v-model="valid.provider" lazy-validation>
                  
                  <v-text-field v-model="editingItem.provider.host" label="Host:" placeholder="" required hint="Enter the SMTP connection hostname."></v-text-field>
                  <v-text-field v-model="editingItem.provider.username" label="Username:" placeholder="" required hint="Enter the SMTP connection username."></v-text-field>
                  <v-text-field v-model="editingItem.provider.password" label="Password:" placeholder="" required hint="Enter the SMTP connection password."></v-text-field>
                  <v-select :items="['None', 'TLS', 'SSL']" v-model="editingItem.provider.encryption" label="Encryption:" hint="Choose the SMTP connection encryption type."></v-select>
                  <v-text-field v-model="editingItem.provider.port" label="Port:" placeholder="" required hint="Enter the SMTP connection port."></v-text-field>
                  <v-text-field v-model="editingItem.provider.limit" label="Limit:" placeholder="" required hint="Enter the SMTP message limit."></v-text-field>

                  <v-select :items="['Yes', 'No']" v-model="editingItem.provider.debug" label="Debug:"></v-select>
                  <p v-if="editingItem.provider.debug === 'Yes'" style="margin-top:-20px;color:rgba(0,0,0,0.54);font-size: 12px;">Debug messages will be displayed.</p>
 
                </v-form>
              </v-card-text>
            </v-card>
          </v-card-text>
          <div style="flex: 1 1 auto;"></div>
        </v-card>
      </v-dialog>
      
      <!-- Fullscreen Dialog -->
      <v-dialog v-model="dialog.template" fullscreen hide-overlay transition="dialog-bottom-transition" scrollable>
        <v-card tile>
          <v-toolbar card dark color="light-blue darken-3">
            <v-btn icon @click.native="dialog.template = false" dark>
              <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>{{ editingIndex === -1 ? 'New' : 'Edit' }} Template</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-toolbar-items>
              <v-btn dark flat @click.native="save('template')">Save</v-btn>
            </v-toolbar-items>
          </v-toolbar>
          <v-card-text style="padding: 0px;">
            <v-card flat>
              <v-card-text>
                <v-form ref="formtemplate" v-model="valid.template" lazy-validation>
                
                  <v-text-field v-model="editingItem.template.name" label="Name:" placeholder="" required hint="Enter the name of the email template."></v-text-field>
                  <v-select :items="['HTML', 'Plain']" v-model="editingItem.template.type" label="Type:" hint="Choose the email template content type."></v-select>
                  <v-text-field v-model="editingItem.template.subject" label="Subject:" placeholder="" required hint="Enter the email subject for this template."></v-text-field>
                  <v-text-field v-model="editingItem.template.from" label="From:" placeholder="" required hint="Enter the from email address."></v-text-field>
                  <v-text-field v-model="editingItem.template.replyto" label="Reply To:" placeholder="" required hint="Enter the reply to email address."></v-text-field>

                  <h3>Template Source</h3>
                  <no-ssr placeholder="Loading...">
                    <codemirror v-model="editingItem.template.source" :options="cmOption"></codemirror>
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
      state: 'templates',
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
        mode: 'text/html'
      },
        
      // snackbar (notification)
      snackbar: false,
      snackbarColor: 'green',
      snackbarText: '',
      snackbarTimeout: 5000,

      // table & items
      items: {template: [], provider: []},
      
      tableHeaders: {
        template: [
          { text: 'Name', value: 'name' },
          { text: 'Slug', value: 'slug' },
          { text: 'Subject', value: 'subject' },
          { text: 'Type', value: 'type' },
          { text: 'Actions', value: 'name', sortable: false, align: 'right' }
        ],
        provider: [
          { text: 'Host', value: 'host' },
          { text: 'Limit', value: 'limit' },
          { text: 'Sent', value: 'limit_sent' },
          { text: 'Actions', value: 'host', sortable: false, align: 'right' }
        ]
      },
      
      // dialogs
      dialog: {template: false, provider: false},

      // item
      editingIndex: {template: -1, provider: -1},
      editingItem: {
        template: {
          id: -1,
          name: "",
          type: "HTML",
          subject: "",
          from: "",
          replyto: "",
          source: ""
        }, 
        provider: {
          id: -1,
          host: "",
          username: "",
          password: "",
          encryption: "None",
          port: "",
          limit: "",
          debug: "No"
        }
      },
      defaultItem: {
        template: {
          id: -1,
          name: "",
          type: "HTML",
          subject: "",
          from: "",
          replyto: "",
          source: ""
        }, 
        provider: {
          id: -1,
          host: "",
          username: "",
          password: "",
          encryption: "None",
          port: "",
          limit: "",
          debug: "No"
        }
      },
      
      // item form & validation
      valid: {template: true, provider: true},
      
      moduleRules: [
        v => !!v || 'Module name is required',
        v => (v && v.length <= 100) || 'Module name must be less than 100 characters'
      ],
      versionRules: [
        v => !!v || 'Version is required',
        v => (v && Number(v)) || 'Version must be a number'
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

          var response;
          axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
          //
          response = await axios.get(this.loggedUser.sub + '/api/ams/email/template')
          this.items.template = response.data.data
          
          //
          response = await axios.get(this.loggedUser.sub + '/api/ams/email/provider')
          this.items.provider = response.data.data
        } catch (error) {
          this.error = 'Could not fetch data from server.';
        }
      },

      // create or edit item
      editItem (type, item) {
        this.editingIndex[type] = this.items[type].indexOf(item)
        this.editingItem[type] = Object.assign({}, item)
        this.dialog[type] = true
      },

      // delete item
      async deleteItem (type, item) {
        const index = this.items[type].indexOf(item)
        
        // local
        //if (confirm('Are you sure you want to delete this item?')){
          this.items[type].splice(index, 1)
        //}

        // remote
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
          //
          const response = await axios.delete(this.loggedUser.sub + '/api/ams/email/' + type, { data: item })
          //
          this.snackbar = true;
          this.snackbarText = 'Email '+type+' successfully deleted.';
          
        } catch (error) {
          this.error = 'Could not delete email '+type+' from server.';
        }
      },

      // save
      async save (type) {
        var ref = 'form'+type
        if (this.$refs[ref].validate()) {
          // local
          if (this.editingIndex[type] > -1) {
            Object.assign(this.items[type][this.editingIndex[type]], this.editingItem[type])
          } else {
            this.items[type].push(Object.assign({}, this.editingItem[type]))
          }
          
          // remote
          try {
            if (!this.loggedUser) {
              this.$router.replace('/servers')
            }
  
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
            //
            const response = await axios.post(this.loggedUser.sub + '/api/ams/email/' + type, this.editingItem[type])
            //
            this.snackbar = true;
            this.snackbarText = 'Email '+type+' successfully saved.';
          } catch (error) {
            this.error = 'Could not save email '+type+' to server.';
          }
  
          if (this.editingIndex[type] === -1) {
            this.close(type)
          }
        }
      },

      // close item dialog, and reset to default item
      close (type) {
        this.dialog[type] = false
        setTimeout(() => {
          this.editingItem[type] = Object.assign({}, this.defaultItem[type])
          this.editingIndex[type] = -1
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
