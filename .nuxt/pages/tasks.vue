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
                <v-data-table :headers="tableHeaders" :items="items" hide-actions class="elevation-1" :loading="tableLoading">
                  <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                  <template slot="items" slot-scope="props">
                    <tr @click.stop="tableExpand(props)">
                    <td><a href="javascript:void(0)" @click.stop="editItem(props.item)">{{ props.item.name.trim() }}</a></td>
                    <td>{{ props.item.description.trim() }}</td>
                    <td>{{ props.item.type.toUpperCase() }}</td>
                    <td>
                      <v-btn icon class="mx-0" style="float:right" @click.stop="deleteItem(props.item)" :disabled="is_system_task(props.item)">
                        <v-icon color="pink">delete</v-icon>
                      </v-btn>
                      <v-btn icon class="mx-0" style="float:right" @click.stop="runTask(props.item)">
                        <v-icon color="green">play_arrow</v-icon>
                      </v-btn>
                    </td>
                    </tr>
                  </template>
                  <template slot="no-data">
                    {{ tableLoading ? 'Fetching data, please wait...' : tableNoData }}
                  </template>
                  <template slot="expand" slot-scope="props">
                    <v-data-table :headers="expandedTableHeaders" :items="item" hide-actions :loading="tableLoading">
                      <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                      <template slot="items" slot-scope="props">
                        <tr @click.stop="props.expanded = !props.expanded">
                          <!--<td>{{ props.item.id }}</td>-->
                          <!--<td>{{ props.item.name }}</td>-->
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
                          <td>{{ new Date(props.item.run_last).toLocaleString() }}</td>
                          <td>{{ new Date(props.item.run_next).toLocaleString() }}</td>
                          <td>{{ props.item.completed != 0 ? new Date(props.item.completed).toLocaleString() : '-' }}</td>
                          <td>{{ props.item.run_count }}</td>
                          <td>
                            <v-btn icon class="mx-0" style="float:right" @click.stop="deleteInstance(props.item)">
                              <v-icon color="pink">delete</v-icon>
                            </v-btn>
                            <v-btn v-if="props.item.completed != 0" icon class="mx-0" style="float:right" @click.stop="reloadInstance(props.item)">
                              <v-icon color="blue">replay</v-icon>
                            </v-btn>
                          </td>
                        </tr>
                      </template>
                      <template slot="no-data">
                        {{ tableLoading ? 'Fetching data, please wait...' : 'Task ('+props.item.name+') has no instances.' }}
                      </template>
                      <template slot="expand" slot-scope="props">
                        <v-card flat>
                          <v-card-text v-html="props.item.result ? '<pre>' + props.item.result + '</pre>' : 'Task ('+props.item.name+') has no result value.'"></v-card-text>
                        </v-card>
                      </template>
                    </v-data-table>
                  </template>
                </v-data-table>
              </v-flex>
            </v-layout>
          </v-flex>
        </v-layout>
      </v-container>
      
      <!-- Fullscreen Dialog -->
      <v-dialog v-model="dialog" fullscreen hide-overlay scrollable>
        <v-card tile>
          <v-toolbar card dark color="light-blue darken-3">
            <v-btn icon @click.native="dialog = false" dark>
              <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>{{ editingIndex === -1 ? 'New' : 'Edit' }} Task</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-toolbar-items>
              <v-btn dark flat @click.native="save()">Save</v-btn>
            </v-toolbar-items>
            <v-menu bottom right offset-y>
              <v-btn slot="activator" dark icon>
                <v-icon>more_vert</v-icon>
              </v-btn>
            </v-menu>
          </v-toolbar>
          <v-card-text style="padding: 0px;">
            <v-card flat>
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
      system_tasks: ['iptables.setup', 'iptables.build', 'iptables.auto_update', 'nginx.build', 'nginx.auto_update', 'nginx.reconcile', 'nginx.reload', 'nginx.setup', 'tasks.auto_update', 'VACUUM;'],
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
      items: [],
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
        // { text: 'ID', value: 'id' },
        // { text: 'Name', value: 'name' },
        { text: 'Repeats', value: 'repeats' },
        { text: 'Sleep', value: 'sleep' },
        { text: 'Last Run', value: 'run_last' },
        { text: 'Next Run', value: 'run_next' },
        { text: 'Completed', value: 'completed' },
        { text: 'Run Count', value: 'run_count' },
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
          this.items = Object.assign([], this.items, response.data.data)
          this.$set(this.items, response.data.data)
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
          }.bind(this), 2500);
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

      async deleteInstance(item) {
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
        return this.system_tasks.includes(item.name)
      },

      // create or edit item
      editItem (item) {
        this.editingIndex = this.items.indexOf(item)
        this.editingItem = Object.assign({}, item)
        // mutate task source type
        this.editingItem.type = this.editingItem.type.toUpperCase()
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
          const response = await axios.delete(this.loggedUser.sub + '/api/tasks', { data: item })
          //
          this.snackbar = true;
          this.snackbarText = 'Task successfully deleted.';
          
        } catch (error) {
          this.error = 'Could not delete task from server.';
        }
      },

      // save item
      async save () {
        if (this.$refs.form.validate()) {
          // local
          if (this.editingIndex > -1) {
            Object.assign(this.items[this.editingIndex], this.editingItem)
          } else {
            // forbid new tasks with the same name as system tasks
            if (this.is_system_task(this.editingItem)) {
              return false;
            }
            this.items.push(Object.assign({}, this.editingItem))
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
  .CodeMirror {
    border: 1px solid #eee;
    min-height:calc(100vh - 270px);
    height: auto;
    font-family: Ubuntu Mono, Menlo, Consolas, monospace;
    font-size: 13px;
    line-height:1.1em;
  }
  .CodeMirror-scroll{
    min-height:calc(100vh - 270px);
  }
  .CodeMirror-gutters {
    left: 0px!important;
  }
</style>
