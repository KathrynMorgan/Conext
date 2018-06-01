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
                Tasks
                <v-btn color="success" @click="dialog = true" style="float:right">New Task</v-btn>
              </v-flex>
              <v-flex>
                <v-alert type="error" :value="error">
                  {{ error }}
                </v-alert>
                <p>Tasks allow you to run custom or predefined system tasks on the server.</p>

                <v-tabs v-model="activeType" class="elevation-1">
                  <v-tab ripple :href="`#user`">User</v-tab>
                  <v-tab ripple :href="`#system`">System</v-tab>
                  <!--
                  <v-spacer></v-spacer>
                  <div class="tabs__div" style="margin-right: 10px">
                    <v-text-field style="margin-bottom: 5px" v-model="search" append-icon="search" label="Filter" single-line hide-details></v-text-field>
                  </div>
                  -->
                  <v-tab-item :id="`user`">
                    <v-data-table hide-actions :search="search" :headers="tableHeaders" :items="items.user" class="elevation-1" :loading="tableLoading">
                      <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                      <template slot="items" slot-scope="props">
                        <tr @click.stop="tableExpand(props)">
                        <td><a href="javascript:void(0)" @click.stop="editItem('user', props.item)">{{ props.item.name.trim() }}</a></td>
                        <td>{{ props.item.description.trim() }}</td>
                        <td>{{ props.item.type.toUpperCase() }}</td>
                        <td>
                          <v-tooltip left>
                            <v-btn slot="activator" icon class="mx-0" style="float:right" @click.stop="deleteItem('user', props.item)" :disabled="is_system_task(props.item)">
                              <v-icon color="pink">delete</v-icon>
                            </v-btn>
                            <span>Delete</span>
                          </v-tooltip>
                          <v-tooltip left>
                            <v-btn slot="activator" icon class="mx-0" style="float:right" @click.stop="runTask(props.item)">
                              <v-icon color="green">play_arrow</v-icon>
                            </v-btn>
                            <span>Run</span>
                          </v-tooltip>
                        </td>
                        </tr>
                      </template>
                      <template slot="no-results" :value="true" color="error" icon="warning">
                        No items found matching "{{ search }}".
                      </template>
                      <template slot="no-data">
                        {{ tableLoading ? 'Fetching data, please wait...' : tableNoData }}
                      </template>
                      <template slot="expand" slot-scope="props">
                        <v-data-table :headers="expandedTableHeaders" :items="item" hide-actions :loading="tableLoading">
                          <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                          <template slot="items" slot-scope="props">
                            <tr @click.stop="props.expanded = !props.expanded">
                              <td>{{ props.item.repeats == 1 ? 'Yes' : 'No'}}</td>
                              <td>
                                <v-edit-dialog
                                  :return-value.sync="props.item.sleep"
                                  lazy
                                >{{ props.item.sleep }}
                                  <v-text-field
                                    slot="input"
                                    v-model="sleep"
                                    :rules="sleepRule"
                                    label="Sleep time between iterations."
                                    single-line
                                    @focus="sleep = props.item.sleep"
                                    @change="saveInstance(props.item, sleep)"
                                  ></v-text-field>
                                </v-edit-dialog>
                              </td>
                              <td>{{ props.item.run_last ? new Date(props.item.run_last).toLocaleString() : '-' }}</td>
                              <td>{{ props.item.run_next ? new Date(props.item.run_next).toLocaleString() : '-' }}</td>
                              <td>{{ props.item.completed != 0 ? new Date(props.item.completed).toLocaleString() : '-' }}</td>
                              <td>{{ props.item.run_count }}</td>
                              <td>
                                <v-tooltip left>
                                  <v-btn slot="activator" icon class="mx-0" style="float:right" @click.stop="deleteInstance(props.item)">
                                    <v-icon color="pink">delete</v-icon>
                                  </v-btn>
                                  <span>Delete</span>
                                </v-tooltip>
                                <v-tooltip left v-if="props.item.completed != 0">
                                  <v-btn slot="activator" icon class="mx-0" style="float:right" @click.stop="reloadInstance(props.item)">
                                    <v-icon color="blue">replay</v-icon>
                                  </v-btn>
                                  <span>Restart</span>
                                </v-tooltip>
                                 <v-tooltip left v-if="props.item.completed == 0">
                                  <v-btn slot="activator" icon class="mx-0" style="float:right" @click.stop="stopInstance(props.item)">
                                    <v-icon color="red">stop</v-icon>
                                  </v-btn>
                                  <span>Stop</span>
                                </v-tooltip>
                              </td>
                            </tr>
                          </template>
                          <template slot="no-data">
                            {{ tableLoading ? 'Fetching data, please wait...' : 'Task has no instances.' }}
                          </template>
                          <template slot="expand" slot-scope="props">
                            <v-card flat>
                              <v-card-text v-html="props.item.result ? '<pre style=\'font-size:10px\'>' + props.item.result + '</pre>' : 'Task has no output.'"></v-card-text>
                            </v-card>
                          </template>
                        </v-data-table>
                      </template>
                    </v-data-table>
                  </v-tab-item>
                  <v-tab-item :id="`system`">
                    <v-data-table hide-actions :search="search" :headers="tableHeaders" :items="items.system" class="elevation-1" :loading="tableLoading">
                      <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                      <template slot="items" slot-scope="props">
                        <tr @click.stop="tableExpand(props)">
                        <td><a href="javascript:void(0)" @click.stop="editItem('system', props.item)">{{ props.item.name.trim() }}</a></td>
                        <td>{{ props.item.description.trim() }}</td>
                        <td>{{ props.item.type.toUpperCase() }}</td>
                        <td>
                          <v-tooltip left>
                            <v-btn slot="activator" icon class="mx-0" style="float:right" @click.stop="runTask(props.item)">
                              <v-icon color="green">play_arrow</v-icon>
                            </v-btn>
                            <span>Run</span>
                          </v-tooltip>
                        </td>
                        </tr>
                      </template>
                      <template slot="no-results" :value="true" color="error" icon="warning">
                        No items found matching "{{ search }}".
                      </template>
                      <template slot="no-data">
                        {{ tableLoading ? 'Fetching data, please wait...' : tableNoData }}
                      </template>
                      <template slot="expand" slot-scope="props">
                        <v-data-table :headers="expandedTableHeaders" :items="item" hide-actions :loading="tableLoading">
                          <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                          <template slot="items" slot-scope="props">
                            <tr @click.stop="props.expanded = !props.expanded">
                              <td>{{ props.item.repeats == 1 ? 'Yes' : 'No'}}</td>
                              <td>
                                <v-edit-dialog
                                  :return-value.sync="props.item.sleep"
                                  lazy
                                >{{ props.item.sleep }}
                                  <v-text-field
                                    slot="input"
                                    v-model="sleep"
                                    :rules="sleepRule"
                                    label="Sleep time between iterations."
                                    single-line
                                    @focus="sleep = props.item.sleep"
                                    @change="saveInstance(props.item, sleep)"
                                  ></v-text-field>
                                </v-edit-dialog>
                              </td>
                              <td>{{ props.item.run_last ? new Date(props.item.run_last).toLocaleString() : '-' }}</td>
                              <td>{{ props.item.run_next ? new Date(props.item.run_next).toLocaleString() : '-' }}</td>
                              <td>{{ props.item.completed != 0 ? new Date(props.item.completed).toLocaleString() : '-' }}</td>
                              <td>{{ props.item.run_count }}</td>
                              <td>
                                <v-tooltip left>
                                  <v-btn slot="activator" icon class="mx-0" style="float:right" @click.stop="deleteInstance(props.item)">
                                    <v-icon color="pink">delete</v-icon>
                                  </v-btn>
                                  <span>Delete</span>
                                </v-tooltip>
                                <v-tooltip left v-if="props.item.completed != 0">
                                  <v-btn slot="activator" icon class="mx-0" style="float:right" @click.stop="reloadInstance(props.item)">
                                    <v-icon color="blue">replay</v-icon>
                                  </v-btn>
                                  <span>Restart</span>
                                </v-tooltip>
                                <v-tooltip left v-if="props.item.completed == 0">
                                  <v-btn slot="activator" icon class="mx-0" style="float:right" @click.stop="stopInstance(props.item)">
                                    <v-icon color="red">stop</v-icon>
                                  </v-btn>
                                  <span>Stop</span>
                                </v-tooltip>
                              </td>
                            </tr>
                          </template>
                          <template slot="no-data">
                            {{ tableLoading ? 'Fetching data, please wait...' : 'Task has no instances.' }}
                          </template>
                          <template slot="expand" slot-scope="props">
                            <v-card flat>
                              <v-card-text v-html="props.item.result ? '<pre style=\'font-size:10px\'>' + props.item.result + '</pre>' : 'Task has no output.'"></v-card-text>
                            </v-card>
                          </template>
                        </v-data-table>
                      </template>
                    </v-data-table>
                  </v-tab-item>
                </v-tabs>
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
            <v-toolbar-title>{{ editingIndex === -1 ? 'New' : 'Edit' }} Task</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-toolbar-items>
              <v-btn dark flat @click.native="save(editingItem.system === '1' ? 'system' : 'user')">Save</v-btn>
            </v-toolbar-items>
          </v-toolbar>
          <v-card-text>
            <v-alert :value="true" outline color="error" icon="warning" v-if="editingItem.id !== -1 && is_system_task(editingItem)">
             Changes to system tasks should be done with caution!
            </v-alert>
            <v-alert :value="true" outline color="error" icon="warning" v-if="editingItem.id === -1 && is_system_task(editingItem)">
              <strong>Error:</strong> Name is reserved for the system task!
            </v-alert>
            <v-form ref="form" v-model="valid" lazy-validation>
              <v-layout row wrap>
                <v-flex xs6>
                  <v-text-field v-model="editingItem.name" :rules="nameRule" label="Name:" placeholder="" :disabled="editingItem.id !== -1 && is_system_task(editingItem)" required hint="Enter the name of the task."></v-text-field>
                </v-flex>
                <v-flex xs6>
                  <v-text-field v-model="editingItem.description" label="Description:" placeholder="" required hint="Enter the tasks description."></v-text-field>
                </v-flex>
              </v-layout>
              <v-select :items="['PHP', 'BASH']" v-model="editingItem.type" label="Task Source Type:" hint="Select the type of code the task is written in."></v-select>
              <h3>Source ({{editingItem.type}})</h3>
              <no-ssr placeholder="Loading...">
                <codemirror v-model="editingItem.source" :options="cmOption" ref="cmInstance"></codemirror>
              </no-ssr>
            </v-form>
          </v-card-text>
          <div style="flex: 1 1 auto;"></div>
        </v-card>
      </v-dialog>
      
      <!---->
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
      codemirror() {
        return this.$refs.cmInstance.codemirror
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
      
      // table & items
      search:'',
      activeType: 'user',
      system_tasks: [],
      items: {system: [], user: []},
      item: [],
      sleep: 0,
      
      tableLoading: true,
      tableNoData: 'You have not added any tasks.',
      tableHeaders: [
        { text: 'Name', value: 'name' },
        { text: 'Description', value: 'description' },
        { text: 'Source Type', value: 'type' },
        { text: 'Actions', value: 'name', sortable: false, align: 'right' }
      ],
      expandedTableHeaders: [
        { text: 'Repeats', value: 'repeats' },
        { text: 'Sleep', value: 'sleep' },
        { text: 'Last Run', value: 'run_last' },
        { text: 'Next Run', value: 'run_next' },
        { text: 'Completed', value: 'completed' },
        { text: 'Run Count', value: 'run_count' },
        { text: 'Actions', value: 'name', sortable: false, align: 'right' }
      ],

      // dialog
      dialog: false,
      
      // item
      editingIndex: -1,
      editingItem: {
        id: -1,
        name: "",
        source: "",
        checksum: "",
        type: "PHP",
        description: "",
        params: "",
        updated: "",
        created: ""
      },
      defaultItem: {
        id: -1,
        name: "",
        source: "",
        checksum: "",
        type: "PHP",
        description: "",
        params: "",
        updated: "",
        created: ""
      },
      
      // item form & validation
      valid: true,
      nameRule: [
        v => !!v || 'Name is required',
        v => (v && v.length <= 100) || 'Name must be less than 100 characters'
      ],
      sleepRule: [
        v => !!v || 'Sleep is required',
        v => (v && !isNaN(v)) || 'Sleep must be a number'
      ],
      
      // poller id
      pollItem: 0
    }),
    mounted: function () {
      this.initialize()
    },
    beforeDestroy: function(){
      clearInterval(this.pollId);
    },
    watch: {
      dialog (val) {
        val || this.close()
      }
    },
    methods: {
      async initialize () {
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
          //
          const response = await axios.get(this.loggedUser.sub + '/api/tasks')
          //
          this.items = Object.assign({system: [], user: []}, this.items, response.data.data.tasks)
          this.system_tasks = Object.assign([], this.items, response.data.data.system_tasks)
        } catch (error) {
          this.tableNoData = 'No data.';
          this.error = 'Could not fetch data from server.';
        }
        this.tableLoading = false
      },
      
      // called when polled
      async taskItem(item) {
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }
  
          axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
          //
          const response = await axios.get(this.loggedUser.sub + '/api/tasks/' + item.id)
          this.item = response.data.data
        } catch (error) {
          this.tableNoData = 'No data.';
          this.error = 'Could not fetch data from server.';
        }
      },
      
      async tableExpand(prop) {
        this.item = [];
        clearInterval(this.pollId);
        if (!prop.expanded) {
          this.taskItem(prop.item)
          this.pollId = setInterval(function () {
            this.taskItem(prop.item)
          }.bind(this), 5000);
        }
        prop.expanded = !prop.expanded
      },
      
      async saveInstance(item, sleep) {
        const index = this.item.indexOf(item)
        try {
            if (!this.loggedUser) {
              this.$router.replace('/servers')
            }
            
            item.sleep = sleep || 0
  
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
            //
            const response = await axios.post(this.loggedUser.sub + '/api/tasks/' + item.id, item)
            
            this.item[index] = Object.assign(this.item[index], response.data.data)
            
            // check for error, reset values with response
            if (response.data.code === 422) {
              //
              this.snackbar = true;
              this.snackbarColor = 'red';
              this.snackbarText = response.data.error;
            } else {
              //
              this.snackbar = true;
              this.snackbarColor = 'green';
              this.snackbarText = 'Task instance updated.';
            }
          } catch (error) {
            this.error = 'Could not update task instance.';
          }
      },

      deleteInstance(item) {
        this.$prompt.show({
          persistent: true,
          width: 400,
          toolbar: {
            color: 'red darken-3',
            closable: false,
          },
          title: 'Delete task instance?',
          text: 'Are you sure you want to delete the <b>'+item.name+'</b> task instance?',
          buttons: [
            {
              title: 'Yes',
              color: 'success',
              handler: async () => {
                const index = this.item.indexOf(item)
                try {
                    if (!this.loggedUser) {
                      this.$router.replace('/servers')
                    }
                    
                    // delete local
                    this.item.splice(index, 1)
          
                    axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
                    //
                    const response = await axios.delete(this.loggedUser.sub + '/api/tasks/' + item.id)
        
                    // check for error, reset values with response
                    if (response.data.code === 422) {
                      //
                      this.snackbar = true;
                      this.snackbarColor = 'red';
                      this.snackbarText = response.data.error;
                    } else {
                      //
                      this.snackbar = true;
                      this.snackbarColor = 'green';
                      this.snackbarText = 'Task instance deleted.';
                    }
                  } catch (error) {
                    this.error = 'Could not deleted task instance.';
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
      
      async reloadInstance(item) {
        const index = this.item.indexOf(item)
        try {
            if (!this.loggedUser) {
              this.$router.replace('/servers')
            }

            axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
            //
            const response = await axios.put(this.loggedUser.sub + '/api/tasks/' + item.id, item)
            
            this.item[index] = Object.assign(this.item[index], response.data.data)

            // check for error, reset values with response
            if (response.data.code === 422) {
              //
              this.snackbar = true;
              this.snackbarColor = 'red';
              this.snackbarText = response.data.error;
            } else {
              //
              this.snackbar = true;
              this.snackbarColor = 'green';
              this.snackbarText = 'Task instance reloaded.';
            }
          } catch (error) {
            this.error = 'Could not reloaded task instance.';
          }
      },    
      
      async stopInstance(item) {
        const index = this.item.indexOf(item)
        try {
            if (!this.loggedUser) {
              this.$router.replace('/servers')
            }
            
            //
            // set sleep wheich will cause stop
            item.sleep = 0

            axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
            //
            const response = await axios.put(this.loggedUser.sub + '/api/tasks/' + item.id, item)
            
            this.item[index] = Object.assign(this.item[index], response.data.data)

            // check for error, reset values with response
            if (response.data.code === 422) {
              //
              this.snackbar = true;
              this.snackbarColor = 'red';
              this.snackbarText = response.data.error;
            } else {
              //
              this.snackbar = true;
              this.snackbarColor = 'green';
              this.snackbarText = 'Task instance stopped.';
            }
          } catch (error) {
            this.error = 'Could not stopped task instance.';
          }
      },
      
      async runTask(item) {
        try {
            if (!this.loggedUser) {
              this.$router.replace('/servers')
            }
  
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
            //
            const response = await axios.put(this.loggedUser.sub + '/api/tasks', item)
            
            this.snackbar = true;
            this.snackbarColor = 'green';
            this.snackbarText = 'Task run instance executed.';
            
            this.initialize()
          } catch (error) {
            this.error = 'Could not add task instance.';
          }
      },
      
      is_system_task(item) {
        if (!item.name) {
          return false
        }
        return this.system_tasks.includes(item.name) || item.system === '1'
      },

      // create or edit item
      editItem (type, item) {
        this.editingIndex = this.items[type].indexOf(item)
        this.editingItem = Object.assign({}, item)
        // mutate task source type
        this.editingItem.type = this.editingItem.type.toUpperCase()
        this.dialog = true
      },

      // delete item
      deleteItem (type, item) {
        this.$prompt.show({
          persistent: true,
          width: 400,
          toolbar: {
            color: 'red darken-3',
            closable: false,
          },
          title: 'Delete task?',
          text: 'Are you sure you want to delete the <b>'+item.name+'</b> task?',
          buttons: [
            {
              title: 'Yes',
              color: 'success',
              handler: async () => {
                const index = this.items[type].indexOf(item)
                
                // local
                this.items[type].splice(index, 1)
        
                // remote
                try {
                  if (!this.loggedUser) {
                    this.$router.replace('/servers')
                  }
        
                  axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
                  //
                  const response = await axios.delete(this.loggedUser.sub + '/api/tasks', { data: item })
                  //
                  this.snackbar = true;
                  this.snackbarText = 'Task successfully deleted.';
                  
                } catch (error) {
                  this.error = 'Could not delete task from server.';
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
      async save (type) {
        if (this.$refs.form.validate()) {
          // local
          if (this.editingIndex > -1) {
            Object.assign(this.items[type][this.editingIndex], this.editingItem)
          } else {
            // forbid new tasks with the same name as system tasks
            if (this.is_system_task(this.editingItem)) {
              return false;
            }
            this.items[type].push(Object.assign({}, this.editingItem))
          }
          
          // remote
          try {
            if (!this.loggedUser) {
              this.$router.replace('/servers')
            }
  
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
            //
            const response = await axios.post(this.loggedUser.sub + '/api/tasks', this.editingItem)
            //
            this.snackbar = true;
            this.snackbarText = 'Task successfully saved.';
          } catch (error) {
            this.error = 'Could not save task to server.';
          }
          
          // reload data
          this.initialize()
          
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
  
  .CodeMirror-scroll {
    min-height:calc(100vh - 350px);
  }

  .CodeMirror-gutters {
    left: 0px !important;
  }
</style>
