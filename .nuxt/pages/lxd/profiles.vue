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
                LXD - Profiles
                <v-btn color="success" @click="dialog = true" style="float:right">New Profile</v-btn>
              </v-flex>
              <v-flex>
                <v-alert type="error" :value="error">
                  {{ error }}
                </v-alert>
                <v-data-table :headers="tableHeaders" :items="items" hide-actions class="elevation-1" :loading="tableLoading">
                  <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                  <template slot="items" slot-scope="props">
                    <tr>
                      <td><a href="javascript:void(0)" @click.stop="editItem(props.item)">{{ props.item.name }}</a></td>
                      <td>{{ props.item.description }}</td>
                      <td>
                        <v-btn icon class="mx-0" style="float:right" @click.stop="deleteItem(props.item)">
                          <v-icon color="pink">delete</v-icon>
                        </v-btn>
                      </td>
                    </tr>
                  </template>
                  <template slot="no-data">
                    {{ tableLoading ? 'Fetching data, please wait...' : 'There are currently no profiles.' }}
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
            <v-toolbar-title>{{ editingIndex === -1 ? 'New' : 'Edit' }} Profile</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-toolbar-items>
              <v-btn dark flat @click.native="save()">Save</v-btn>
            </v-toolbar-items>
          </v-toolbar>
          <v-card-text style="padding: 0px;">
            <v-card flat>
              <v-card-text>
                <v-form ref="form" v-model="valid" lazy-validation>
                  <h2>General</h2>
                  <v-layout row wrap style="margin-top:-20px">
                    <v-flex xs6>
                      <v-card-text class="px-1">
                        <v-text-field v-model="editingItem.name" :rules="nameRule" label="Name:" placeholder="" required hint="Enter a name for the profile."></v-text-field>
                        <v-text-field v-model="editingItem.description" label="Description:" placeholder="" hint="Enter a description for the profile."></v-text-field>
                      </v-card-text>
                    </v-flex>
                    <v-flex xs6>
                      <v-card-text class="px-4">
                        <v-layout row wrap>
                          <v-flex xs12>
                            <h4>Auto Start</h4>
                            <v-switch :label="`${editingItem.config['boot.autostart'] === '1' ? 'Yes' : 'No'}`" true-value="1" false-value="0" color="success" v-model="editingItem.config['boot.autostart']"></v-switch>
                          </v-flex>
                        </v-layout>
                        <v-layout row wrap>
                          <v-flex xs6>
                            <h4>Privileged</h4>
                            <v-switch :label="`${editingItem.config['security.privileged'] === '1' ? 'Yes' : 'No'}`" true-value="1" false-value="0" color="success" v-model="editingItem.config['security.privileged']"></v-switch>
                          </v-flex>
                          <v-flex xs6>
                            <h4>Nesting</h4>
                            <v-switch :label="`${editingItem.config['security.nesting'] === '1' ? 'Yes' : 'No'}`" true-value="1" false-value="0" color="success" v-model="editingItem.config['security.nesting']"></v-switch>
                          </v-flex>
                        </v-layout>
                      </v-card-text>
                    </v-flex>
                  </v-layout>
                  <h2 style="margin-top:-15px">CPU</h2>
                  <v-layout row wrap>
                    <v-flex xs6>
                      <v-card-text class="px-1">
                        <h4 style="margin-bottom:-20px">CPU Cores ({{ editingItem.config['limits.cpu'] }})</h4>
                        <v-slider v-model="editingItem.config['limits.cpu']" thumb-label :max="max_cpu" ticks></v-slider>
                        <h4 style="margin-bottom:-20px">Max Processes ({{ editingItem.config['limits.processes'] }})</h4>
                        <v-slider v-model="editingItem.config['limits.processes']" thumb-label max="20000" step="100" ticks></v-slider>
                      </v-card-text>
                    </v-flex>
                    <v-flex xs6>
                      <v-card-text class="px-1">
                        <h4 style="margin-bottom:-20px">CPU Allowance ({{ editingItem.config['limits.cpu.allowance'] }}%)</h4>
                        <v-slider v-model="editingItem.config['limits.cpu.allowance']" thumb-label max="100" step="1" ticks></v-slider>
                        <h4 style="margin-bottom:-20px">CPU Priority ({{ editingItem.config['limits.cpu.priority'] }}/10)</h4>
                        <v-slider v-model="editingItem.config['limits.cpu.priority']" thumb-label max="10" step="1" ticks></v-slider>
                      </v-card-text>
                    </v-flex>
                  </v-layout>
                  <h2 style="margin-top:-15px">Memory</h2>
                  <v-layout row wrap>
                    <v-flex xs6>
                      <v-card-text class="px-1">
                        <h4 style="margin-bottom:-20px">Memory ({{ editingItem.config['limits.memory'] }}MB)</h4>
                        <v-slider v-model="editingItem.config['limits.memory']" :max="max_memory" thumb-label step="64" ticks></v-slider>
                        <h4 style="margin-bottom:-20px">Swap Priority ({{ editingItem.config['limits.memory.swap.priority'] }}/10)</h4>
                        <v-slider v-model="editingItem.config['limits.memory.swap.priority']" thumb-label max="10" step="1" ticks></v-slider>
                      </v-card-text>
                    </v-flex>
                    <v-flex xs6>
                      <v-card-text class="px-1">
                        <h4>Enforce</h4>
                        <v-switch :label="`${editingItem.config['limits.memory.enforce'] === 'hard' ? 'Hard' : 'Soft'}`" true-value="hard" false-value="soft" color="success" v-model="editingItem.config['limits.memory.enforce']"></v-switch>
                        <h4>Swap</h4>
                        <v-switch :label="`${editingItem.config['limits.memory.swap'] === '1' ? 'Yes' : 'No'}`" true-value="1" false-value="0" color="success" v-model="editingItem.config['limits.memory.swap']"></v-switch>
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
                        <h4 style="margin-bottom:-20px">Priority ({{ editingItem.config['limits.disk.priority'] }}/10)</h4>
                        <v-slider v-model="editingItem.config['limits.disk.priority']" thumb-label max="10" step="1" ticks></v-slider>
                      </v-card-text>
                    </v-flex>
                    <v-flex xs6>
                      <v-card-text class="px-1">
                        <h4 style="margin-bottom:-20px">Priority ({{ editingItem.config['limits.network.priority'] }}/10)</h4>
                        <v-slider v-model="editingItem.config['limits.network.priority']" thumb-label max="10" step="1" ticks></v-slider>
                      </v-card-text>
                    </v-flex>
                  </v-layout>
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
  import helpers from '~/utils/helpers'

  const profile = require('~/components/lxd/profile')

  export default {
    mixins: [helpers],
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
      empty_profile: function () {
        return profile.empty()
      },
      max_memory: function () {
        return this.resources.memory.total / 1000 / 1000
      },
      max_cpu: function () {
        return Number(this.resources.cpu.total)
      }
    },
    data: () => ({
      dialog: false,
      valid: true,

      // global error
      error: '',

      // snackbar (notification)
      snackbar: false,
      snackbarColor: 'green',
      snackbarText: '',
      snackbarTimeout: 5000,

      // table & items
      items: [],
      resources: {
        cpu: {
          total: 0
        },
        memory: {
          total: 0
        }
      },

      tableLoading: true,
      tableHeaders: [
        { text: 'Name', value: 'name' },
        { text: 'Description', value: 'description' },
        { text: 'Actions', value: 'id', sortable: false, align: 'right' }
      ],

      editingIndex: -1,
      editingItem: profile.empty(),
      defaultItem: profile.empty(),

      nameRule: [
        v => !!v || 'Name is required',
        v => (v && v.length <= 100) || 'Name must be less than 100 characters'
      ]
    }),
    beforeDestroy: function() {},
    mounted: function () {
      axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
      this.initialize()
      this.getResources()
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

          //
          const response = await axios.get(this.loggedUser.sub + '/api/lxd/profiles')
          this.items = response.data.data
        } catch (error) {
          this.items = [];
          this.tableNoData = 'No data.';
          this.error = 'Could not fetch data from server.';
        }
        this.tableLoading = false
      },
      
      async getResources () {
        //
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          //
          const response = await axios.get(this.loggedUser.sub + '/api/lxd/resources')
          
          this.resources = response.data.data

        } catch (error) {
          this.resources = {};
        }
      },

      // create or edit item
      editItem (item) {
        this.editingIndex = this.items.indexOf(item)

        // convoluted - add each of the items props to editingItem
        this.editingItem = Object.assign({}, this.empty_profile, item)
        this.editingItem.devices = Object.assign({}, this.empty_profile.devices, item.devices)
        this.editingItem.config = Object.assign({}, this.empty_profile.config, item.config)
        // set defaults if not set
        this.editingItem = profile.infix(this.editingItem)

        this.dialog = true
      },

      // save
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
            
            this.editingItem = profile.outfix(this.editingItem);

            // edit
            if (this.editingIndex > -1) {
              var response = await axios.post(this.loggedUser.sub + '/api/lxd/profiles/'+this.editingItem.name, {
                "config": this.editingItem.config,
                "description": this.editingItem.description,
                "devices": this.editingItem.deviced
              })
            } 
            // add
            else {
              var response = await axios.post(this.loggedUser.sub + '/api/lxd/profiles', {
                "config": this.editingItem.config,
                "description": this.editingItem.description,
                "devices": this.editingItem.deviced,
                "name": this.editingItem.name
              })
            }
            
            this.editingItem = profile.infix(this.editingItem);

            //
            this.snackbar = true;
            this.snackbarText = 'Profile successfully saved.';
          } catch (error) {
            this.error = 'Could not save profile to server.';
          }
  
          if (this.editingIndex === -1) {
            this.close()
          }
          
          this.initialize()
        }
      },

      async deleteItem (item) {
        this.$prompt.show({
          persistent: true,
          width: 400,
          toolbar: {
            color: 'red darken-3',
            closable: false,
          },
          title: 'Delete profile?',
          text: 'Are you sure you want to delete the <b>'+item.name+'</b> profile?',
          buttons: [
            {
              title: 'Yes',
              color: 'success',
              handler: async () => { 
                // local
                const index = this.items.indexOf(item)
                this.items.splice(index, 1)
                
                // remote
                try {
                  //
                  const response = await axios.delete(this.loggedUser.sub + '/api/lxd/profiles/'+item.name)

                  //
                  this.snackbar = true;
                  this.snackbarColor = 'green';
                  this.snackbarText = 'Profile deleted.';
                } catch (error) {
                  //
                  this.error = 'Failed to delete profile.';
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
