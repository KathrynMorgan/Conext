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
                
                <span v-if="state == 'templates'">
                  <v-btn color="success" @click="state = 'templates';dialog.template = true" style="float:right" v-if="state == 'templates'">New Template</v-btn>
                  <v-btn color="orange" @click="state = 'providers';" style="float:right" v-if="state == 'templates'">Providers</v-btn>
                </span>
                <span v-if="state == 'providers'">
                  <v-btn color="success" @click="state = 'providers';dialog.provider = true" style="float:right" v-if="state == 'providers'">New Provider</v-btn>
                  <v-btn color="orange" @click="state = 'templates';" style="float:right" v-if="state == 'providers'">Templates</v-btn>
                </span>
              </v-flex>
              <v-flex>
                <v-alert type="error" :value="error">
                  {{ error }}
                </v-alert>
                <p>The email API allows you to create custom email templates with multiple providers, then simply use a POST request to send the email.</p>
                
                <div v-if="state == 'templates'">
                  <v-data-table :headers="tableHeaders.template" :items="items.template" hide-actions class="elevation-1" :loading="tableLoading">
                    <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                    <template slot="items" slot-scope="props">
                      <tr @click.stop="tableExpand('template', props)">
                        <td><a href="javascript:void(0)" @click.stop="editItem('template', props.item)">{{ props.item.name }}</a></td>
                        <td><a :href="`${loggedUser.sub}/api/email/${props.item.slug}`" target="_blank">{{ props.item.slug }}</a></td>
                        <td>{{ props.item.subject }}</td>
                        <td>{{ props.item.type }}</td>
                        <td>
                          <v-tooltip left>
                            <v-btn slot="activator" icon class="mx-0" style="float:right" @click="deleteItem('template', props.item)">
                              <v-icon color="pink">delete</v-icon>
                            </v-btn>
                            <span>Delete</span>
                          </v-tooltip>
                          <v-tooltip left>
                            <v-btn slot="activator" icon class="mx-0" style="float:right" @click="previewItem('preview', props.item)">
                              <v-icon color="light-blue lighten-1">visibility</v-icon>
                            </v-btn>
                            <span>Preview</span>
                          </v-tooltip>
                          
                        </td>
                      </tr>
                    </template>
                    <template slot="no-data">
                      You have not added any email templates.
                    </template>
                  </v-data-table>
                </div>
                <div v-if="state == 'providers'">
                  <v-data-table :headers="tableHeaders.provider" :items="items.provider" hide-actions class="elevation-1" :loading="tableLoading">
                    <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                    <template slot="items" slot-scope="props">
                      <tr>
                        <td><a href="javascript:void(0)" @click.stop="editItem('provider', props.item)">{{ props.item.host }}</a></td>
                        <td>{{ props.item.limit }}</td>
                        <td>{{ props.item.limit_sent }}</td>
                        <td>
                          <v-btn icon class="mx-0" style="float:right" @click="deleteItem('provider', props.item)">
                            <v-icon color="pink">delete</v-icon>
                          </v-btn>
                          <v-btn icon class="mx-0" style="float:right" @click.stop="tableExpand('provider', props)" v-if="props.item.debug == 'Yes'">
                            <v-icon color="blue-grey darken-2">bug_report</v-icon>
                          </v-btn>
                        </td>
                      </tr>
                    </template>
                    <template slot="no-data">
                      You have not added any email providers.
                    </template>
                    <template slot="expand" slot-scope="props">
                      <v-data-table :headers="expandedTableHeaders" :items="props.item.ownAmsemaildebug" hide-actions>
                        <template slot="items" slot-scope="props">
                          <tr @click.stop="props.expanded = !props.expanded">
                            <td>{{ props.item.date }}</td>
                            <td>{{ props.item.from }}</td>
                            <td>{{ props.item.to }}</td>
                            <td>{{ props.item.subject }}</td>
                            <td>
                              <v-btn icon class="mx-0" style="float:right" @click.stop="deleteLog(props.item)">
                                <v-icon color="pink">delete</v-icon>
                              </v-btn>
                            </td>
                          </tr>
                        </template>
                        <template slot="no-data">
                          {{ tableLoading ? 'Fetching data, please wait...' : 'No debug logs have been recorded.' }}
                        </template>
                        <template slot="expand" slot-scope="props">
                          <v-card flat>
                            <v-card-text v-html="props.item.log ? '<pre>' + props.item.log.replace(/<br\s*[\/]?>/gi, '') + '</pre>' : ''"></v-card-text>
                          </v-card>
                        </template>
                      </v-data-table>
                    </template>
                  </v-data-table>
                </div>
              </v-flex>
            </v-layout>
          </v-flex>
        </v-layout>
      </v-container>
      
      <!-- Fullscreen Dialog -->
      <v-dialog v-model="dialog.provider" max-width="600px" scrollable>
        <v-card tile>
          <v-toolbar card dark color="light-blue darken-3">
            <v-btn icon @click.native="close('provider')" dark>
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
                  <v-layout row wrap>
                    <v-flex xs6>
                      <v-text-field v-model="editingItem.provider.host" label="Host:" placeholder="" required hint="Enter the SMTP connection hostname."></v-text-field>
                      <v-text-field v-model="editingItem.provider.username" label="Username:" placeholder="" required hint="Enter the SMTP connection username."></v-text-field>
                    </v-flex>
                    <v-flex xs6>
                      <v-text-field v-model="editingItem.provider.port" label="Port:" placeholder="" required hint="Enter the SMTP connection port."></v-text-field>
                      <v-text-field v-model="editingItem.provider.password" label="Password:" placeholder="" required hint="Enter the SMTP connection password."></v-text-field>
                    </v-flex>
                  </v-layout>
                  <v-select :items="['None', 'TLS', 'SSL']" v-model="editingItem.provider.encryption" label="Encryption:" hint="Choose the SMTP connection encryption type."></v-select>
                  <v-text-field v-model="editingItem.provider.limit" label="Message Limit:" placeholder="" required hint="Enter the SMTP message limit."></v-text-field>
                  <v-select :items="['Yes', 'No']" v-model="editingItem.provider.debug" label="Debug:"></v-select>
                  <p v-if="editingItem.provider.debug === 'Yes'" style="margin-top:-20px;color:rgba(0,0,0,0.54);font-size: 12px;">All emails will be logged and shown in table.</p>
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
            <v-btn icon @click.native="close('template')" dark>
              <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>{{ editingIndex.template === -1 ? 'New' : 'Edit' }} Template</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-toolbar-items>
              <v-btn dark flat @click.native="save('template')">Save</v-btn>
            </v-toolbar-items>
          </v-toolbar>
          <v-card-text style="padding: 0px;">
            <v-card flat>
              <v-card-text>
                <v-form ref="formtemplate" v-model="valid.template" lazy-validation>
                  <v-layout row wrap>
                    <v-flex xs6>
                      <v-text-field v-model="editingItem.template.name" label="Name:" placeholder="" required hint="Enter the name of the email template."></v-text-field>
                      <v-text-field v-model="editingItem.template.subject" label="Subject:" placeholder="" required hint="Enter the email subject for this template."></v-text-field>
                      <v-select :items="['HTML', 'Plain']" v-model="editingItem.template.type" label="Type:" hint="Choose the email template content type."></v-select>
                    </v-flex>
                    <v-flex xs6>
                      <v-text-field v-model="editingItem.template.from" label="From:" placeholder="" required hint="Enter the from email address."></v-text-field>
                      <v-text-field v-model="editingItem.template.replyto" label="Reply To:" placeholder="" required hint="Enter the reply to email address."></v-text-field>
                      <v-text-field v-model="editingItem.template.key" label="Send Key:" placeholder="" hint="Enter a key which is required before sending email."></v-text-field>
                    </v-flex>
                  </v-layout>
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
      
      <!-- Fullscreen Dialog -->
      <v-dialog v-model="dialog.preview" max-width="900px" scrollable>
        <v-card tile>
          <v-toolbar card dark color="light-blue darken-3">
            <v-btn icon @click.native="close('preview')" dark>
              <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>Preview Template</v-toolbar-title>
            <v-spacer></v-spacer>
          </v-toolbar>
          <v-card-text style="padding: 0px;">
            <iframe :srcdoc="editingItem.template.source" frameBorder="0" style="width:100%; height:calc(100vh - 200px)"></iframe>
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
      
      tableLoading: true,
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
      expandedTableHeaders: [
        { text: 'Date', value: 'date' },
        { text: 'From', value: 'from' },
        { text: 'To', value: 'to' },
        { text: 'Subject', value: 'subject' },
        { text: 'Actions', value: 'name', sortable: false, align: 'right' }
      ],
      
      // dialogs
      dialog: {template: false, provider: false, preview: false},

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
          key: "",
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
          key: "",
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
      'dialog.template': function (val) {
         val || this.close('template')
      },
      'dialog.provider': function (val) {
         val || this.close('provider')
      },
      'dialog.preview': function (val) {
         val || this.close('preview')
      },
    },
    methods: {
      async initialize () {
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
        this.tableLoading = false
      },

      // create or edit item
      editItem (type, item) {
        this.editingIndex[type] = this.items[type].indexOf(item)
        this.editingItem[type] = Object.assign({}, item)
        this.dialog[type] = true
      },

      // create or edit item
      previewItem (type, item) {
        this.editingIndex.template = this.items.template.indexOf(item)
        this.editingItem.template = Object.assign({}, item)
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
          
          this.initialize()
        }
      },
      
      tableExpand(type, prop) {
        prop.expanded = !prop.expanded
      },

      // close item dialog, and reset to default item
      close (type) {
        this.dialog[type] = false
        setTimeout(() => {
          this.editingItem = Object.assign({}, this.defaultItem)
          this.editingIndex = {template: -1, provider: -1}
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
