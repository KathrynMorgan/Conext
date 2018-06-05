<template>
  <div>
    <v-alert type="error" :value="attachError">
      {{ attachError }}
    </v-alert>
    <v-data-table :headers="tableHeaders" :items="items" hide-actions :loading="tableLoading">
      <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
      <template slot="items" slot-scope="props">
        <tr>
          <td>
            <span v-if="linkedItem.devices">{{ props.item.name }}</span>
            <span v-else><a href="javascript:void(0)" @click.stop="editItem(props.item)">{{ props.item.name }}</a></span>
          </td>
          <td>{{ props.item.dict.source ? props.item.dict.source : '-' }}</td>
          <td>{{ props.item.dict.path ? props.item.dict.path : '-' }}</td>
          <td>{{ props.item.dict.size ? props.item.dict.size : '-' }}</td>
          <td v-if="!linkedItem.devices">{{ (props.item.dict['limits.read'] ? props.item.dict['limits.read'] : '-') + '/' + (props.item.dict['limits.write'] ? props.item.dict['limits.write'] : '-') }}</td>
          <td>
            <span v-if="linkedItem.devices">
              <v-btn depressed small @click="attachItem(props.item)" v-if="!linkedItem.devices[props.item.name]">Attach</v-btn>
              <v-btn dark depressed small color="red" @click="detachItem(props.item)" v-if="linkedItem.devices[props.item.name]">Detach</v-btn>
            </span>
            <v-tooltip left v-else>
              <v-btn slot="activator" icon class="mx-0" style="float:right" @click.stop="deleteItem(props.item)">
                <v-icon color="pink">delete</v-icon>
              </v-btn>
              <span>Delete</span>
            </v-tooltip>
          </td>
        </tr>
      </template>
      <template slot="no-data">
        {{ tableLoading ? 'Fetching data, please wait...' : 'There are currently no disk devices.' }}
      </template>
    </v-data-table>

    <!-- Dialog -->
    <v-dialog v-model="dialog" max-width="700px" scrollable :hide-overlay="linkedItem !== null">
      <v-card flat style="overflow-x:hidden; overflow-y: auto; height:calc(100vh - 104px);">
        <v-toolbar card dark color="light-blue darken-3">
          <v-btn icon @click.native="close('preview')" dark>
            <v-icon>close</v-icon>
          </v-btn>
          <v-toolbar-title>{{ editingIndex === -1 ? 'New' : 'Edit' }} Disk</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-toolbar-items>
            <v-btn dark flat @click.native="saveItem()">Save</v-btn>
          </v-toolbar-items>
        </v-toolbar>
        <v-card-text>
          <v-form ref="form" v-model="valid" lazy-validation>
            <v-alert type="error" :value="error">
              {{ error }}
            </v-alert>
            <h3>General</h3>
            
            <v-text-field v-model="editingItem.name" :rules="nameRule" label="Name:" placeholder="" required hint="Enter a name for the disk device."></v-text-field>
            
            <v-text-field v-model="editingItem.dict['path']" label="Path:" placeholder="" required hint="Path inside the container where the disk will be mounted."></v-text-field>
            <v-text-field v-model="editingItem.dict['source']" label="Source:" placeholder="" required hint="Path on the host, either to a file/directory or to a block device."></v-text-field>

            <h3>Limits</h3>
            <v-layout row wrap>
              <v-flex xs6>
                <v-text-field v-model="editingItem.dict['limits.read']" label="Read:" placeholder="" hint="I/O limit in byte/s (supports kB, MB, GB, TB, PB and EB suffixes) or in iops (must be suffixed with 'iops')."></v-text-field>
              </v-flex>
              <v-flex xs6>
                <v-text-field v-model="editingItem.dict['limits.write']" label="Write:" placeholder="" hint="I/O limit in byte/s (supports kB, MB, GB, TB, PB and EB suffixes) or in iops (must be suffixed with 'iops')."></v-text-field>
              </v-flex>
            </v-layout>
            
            <v-text-field v-model="editingItem.dict['size']" label="Size:" placeholder="" hint="Disk size in bytes (supports kB, MB, GB, TB, PB and EB suffixes). This is only supported for the rootfs (/)."></v-text-field>
            
            <v-layout row wrap>
              <v-flex xs4>
                <h4>Optional</h4>
                <v-switch color="success" v-model="editingItem.dict['optional']" persistent-hint hint="Controls whether to fail if the source doesn't exist."></v-switch>
              </v-flex>
              <v-flex xs4>
                <h4>Readonly</h4>
                <v-switch color="success" v-model="editingItem.dict['readonly']" persistent-hint hint="Controls whether to make the mount read-only."></v-switch>
              </v-flex>
              <v-flex xs4>
                <h4>Recursive</h4>
                <v-switch color="success" v-model="editingItem.dict['recursive']" persistent-hint hint="Whether or not to recursively mount the source path."></v-switch>
              </v-flex>
            </v-layout>

            <v-select :items="['None','default']" v-model="editingItem.dict.pool" label="Pool:" persistent-hint hint="Storage pool the disk device belongs to. This is only applicable for storage volumes managed by LXD."></v-select>
            <v-select :items="['None','private','shared','slave','unbindable','rshared','rslave','runbindable','rprivate']" v-model="editingItem.dict.propagation" label="Propagation:" persistent-hint hint="Controls how a bind-mount is shared between the container and the host."></v-select>

          </v-form>
        </v-card-text>
        <div style="flex: 1 1 auto;"></div>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
  import { mapGetters } from 'vuex'
  import axios from 'axios'
  
  const container = require('~/components/lxd/container')

  export default {
    components: {},
    props: [
      'linked'
    ],
    computed: {
      ...mapGetters({
        loggedUser: 'auth/loggedUser',
        loggedToken: 'auth/loggedToken'
      }),
      tableHeaders: function () {
        if (this.linked) {
          return [
            { text: 'Name', value: 'name' },
            { text: 'Source', value: 'source' },
            { text: 'Path', value: 'path' },
            { text: 'Size', value: 'size' },
            { text: 'Actions', value: 'name', sortable: false, align: 'center', width:'100px' }
          ]
        } else {
          return [
            { text: 'Name', value: 'name' },
            { text: 'Source', value: 'source' },
            { text: 'Path', value: 'path' },
            { text: 'Size', value: 'size' },
            { text: 'Limits (Read/Write)', value: 'limits' },
            { text: 'Actions', value: 'name', sortable: false, align: 'center', width:'100px' }
          ]
        }
      }
    },
    data: () => ({
      error: false,
      attachError: false,
      valid: true,
      dialog: false,

      tableLoading: true,

      networks: [],
      items: [],
      editingIndex: -1,
      editingItem: {
        id: -1,
        type: "disk",
        name: "",
        dict: {
          "limits.read": "",
          "limits.write": "",
          "path": "",
          "source": "",
          "readonly": false,
          "size": "",
          "recursive": false,
          "pool": "None",
          "propagation": "None"
        }
      },
      defaultItem: {
        id: -1,
        type: "disk",
        name: "",
        dict: {
          "limits.read": "",
          "limits.write": "",
          "path": "",
          "source": "",
          "readonly": false,
          "size": "",
          "recursive": false,
          "pool": "None",
          "propagation": "None"
        }
      },

      // container/profile
      linkedItem: {},

      // rules
      nameRule: [
        v => !!v || 'Name is required',
        v => (v && v.length <= 100) || 'Name must be less than 100 characters'
      ]
    }),
    beforeDestroy: function() {},
    mounted: function () {
      axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken

      this.linkedItem = Object.assign({}, this.linked)

      this.initialize()
    },
    watch: {
      dialog (val) {
        val || this.close()
      }
    },
    methods: {
      async initialize () {
        try {
          //
          const response = await axios.get(this.loggedUser.sub + '/api/lxd/devices/disk')
          this.items = response.data.data
          
          this.getNetworks()
        } catch (error) {
          this.error = 'Could not fetch data from server.';
        }
        this.tableLoading = false
      },

      async attachItem(item) {
        this.linkedItem = Object.assign({}, container.outfix(this.linkedItem))
        this.linkedItem.devices  = Object.assign({}, this.linkedItem.devices)
        
        if (item.dict['propagation'] === 'None') {
          delete item.dict['propagation']
        }
        
        if (item.dict['pool'] === 'None') {
          delete item.dict['pool']
        }
        
        this.$set(this.linkedItem.devices, item.name, {
          "type": item.type,
          ...item.dict
        })
        
        // attach root of host to root of container (else it be nobody)
        //this.$set(this.linkedItem.config, "raw.idmap", "both 0 1000")

        //
        const response = await axios.patch(this.loggedUser.sub + '/api/lxd/containers/' + this.linkedItem.name, {
          // config: this.linkedItem.config,
          devices: this.linkedItem.devices
        })
        
        if (response.data.error) {
          this.attachError = response.data.error
        }
      },

      async detachItem(item) {
        this.attachError = false;
        
        // remove from linked item
        this.$delete(this.linkedItem.devices, item.name)
        
        this.linkedItem = Object.assign({}, container.outfix(this.linkedItem))
        
        // remove root of host to root of container
        delete this.linkedItem.config["raw.idmap"];
        
        //
        const response = await axios.put(this.loggedUser.sub + '/api/lxd/containers/' + this.linkedItem.name, {
          config: this.linkedItem.config,
          devices: this.linkedItem.devices,
          ephemeral: this.linkedItem.ephemeral,
          stateful: this.linkedItem.stateful,
          profiles: this.linkedItem.profiles
        })
        
        if (response.data.error) {
          this.attachError = response.data.error
        }
      },
      
      async getNetworks () {
        //
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          //
          const response = await axios.get(this.loggedUser.sub + '/api/lxd/networks')
          
          this.networks = []
          response.data.data.forEach(item => {
            if (item.managed) {
              this.networks.push(item.name);
            }
          });
        } catch (error) {
          this.networks = [];
        }
      },

      // create or edit item
      editItem (item) {
        this.editingIndex = this.items.indexOf(item)
        this.editingItem = Object.assign({}, this.defaultItem, item)

        this.dialog = true
      },

      // save
      async saveItem () {
        if (this.$refs.form.validate()) {

          // remote
          try {

            // edit
            if (this.editingIndex > -1) {
              var response = await axios.put(this.loggedUser.sub + '/api/lxd/devices/disk/'+this.editingItem.id, this.editingItem)
            } 
            // add
            else {
              var response = await axios.post(this.loggedUser.sub + '/api/lxd/devices/disk', this.editingItem)
            }

            if (response.data.error) {
              if (response.data.error.path) {
                this.error = response.data.error.path
              }
              if (response.data.error.source) {
                this.error = response.data.error.source
              }
            } else {
              //
              this.$emit('snackbar', 'Device successfully saved.')
              
              // local
              if (this.editingIndex > -1) {
                Object.assign(this.items[this.editingIndex], this.editingItem)
              } else {
                this.items.push(Object.assign({}, this.editingItem))
              }
    
              if (this.editingIndex === -1) {
                this.close()
              }
    
              this.initialize()
            }
          } catch (error) {
            this.error = 'Could not save device to server.';
          }
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
          title: 'Delete device?',
          text: 'Are you sure you want to delete the <b>'+item.name+'</b> device?',
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
                  const response = await axios.delete(this.loggedUser.sub + '/api/lxd/devices/disk/'+item.id)

                  //
                  this.$emit('snackbar', 'Device successfully deleted.')
                } catch (error) {
                  //
                  this.error = 'Failed to delete device.';
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

      openDialog(){
        this.dialog = true
      },

      // close item dialog, and reset to default item
      close () {
        this.dialog = false
        setTimeout(() => {
          this.editingItem = Object.assign({}, this.defaultItem)
          this.editingIndex = -1
        }, 300)
      },
            
      ucfirst(str) {
          return String(str).charAt(0).toUpperCase() + String(str).slice(1);
      }
    }
  }
</script>

<style>

</style>
