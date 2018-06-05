<template>
  <div>
    <v-data-table :headers="tableHeaders" :items="items" hide-actions :loading="tableLoading">
      <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
      <template slot="items" slot-scope="props">
        <tr>
          <td>
            <span v-if="linkedItem.devices">{{ props.item.dict.name }}</span>
            <span v-else><a href="javascript:void(0)" @click.stop="editItem(props.item)">{{ props.item.dict.name }}</a></span>
          </td>
          <td>{{ ucfirst(props.item.dict.nictype) }}</td>
          <td>{{ props.item.dict.parent }}</td>
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
        {{ tableLoading ? 'Fetching data, please wait...' : 'There are currently no nic devices.' }}
      </template>
    </v-data-table>

    <!-- Dialog -->
    <v-dialog v-model="dialog" max-width="800px" scrollable :hide-overlay="linkedItem !== null">
      <v-card flat style="overflow-x:hidden; overflow-y: auto; height:calc(100vh - 104px);">
        <v-toolbar card dark color="light-blue darken-3">
          <v-btn icon @click.native="close('preview')" dark>
            <v-icon>close</v-icon>
          </v-btn>
          <v-toolbar-title>{{ editingIndex === -1 ? 'New' : 'Edit' }} Nic</v-toolbar-title>
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
            <v-text-field v-model="editingItem.dict.name" :rules="nameRule" label="Name:" placeholder="" required hint="Enter a name for the nic device."></v-text-field>
            <v-select :items="['bridged','macvlan','p2p','physical','sriov']" v-model="editingItem.dict.nictype" label="NIC Type:"></v-select>
            <div v-if="['bridged','macvlan', 'p2p', 'sriov'].includes(editingItem.dict.nictype)">
              <v-select :items="networks" v-model="editingItem.dict.parent" label="Parent:"></v-select>
              <v-text-field v-model="editingItem.dict['host_name']" label="Hostname:" placeholder="" hint="Hostname of the interface inside the host."></v-text-field>
            </div>
            <v-layout row wrap>
              <v-flex xs6>
                <v-text-field v-model="editingItem.dict.hwaddr" label="MAC address:" placeholder="" hint="MAC address of the interface."></v-text-field>
              </v-flex>
              <v-flex xs6>
                <v-text-field v-model="editingItem.dict.mtu" label="MTU:" placeholder="" hint="MTU of the interface."></v-text-field>
              </v-flex>
            </v-layout>
            <div v-if="['bridged', 'p2p'].includes(editingItem.dict.nictype)">
              <h3>Limits</h3>
              <v-layout row wrap>
                <v-flex xs6>
                  <v-text-field v-model="editingItem.dict['limits.ingress']" label="Ingress:" placeholder="" hint="I/O limit in bit/s (supports kbit, Mbit, Gbit suffixes)."></v-text-field>
                </v-flex>
                <v-flex xs6>
                  <v-text-field v-model="editingItem.dict['limits.egress']" label="Egress:" placeholder="" hint="I/O limit in bit/s (supports kbit, Mbit, Gbit suffixes)."></v-text-field>
                </v-flex>
              </v-layout>
            </div>
            <div v-if="['bridged'].includes(editingItem.dict.nictype)">
              <h3>DHCP</h3>
              <v-layout row wrap>
                <v-flex xs6>
                  <v-text-field v-model="editingItem.dict['ipv4.address']" label="IPv4 Address:" placeholder="" hint="An IPv4 address to assign to the container through DHCP."></v-text-field>
                </v-flex>
                <v-flex xs6>
                  <v-text-field v-model="editingItem.dict['ipv6.address']" label="IPv6 Address:" placeholder="" hint="An IPv6 address to assign to the container through DHCP."></v-text-field>
                </v-flex>
              </v-layout>
              <h4>MAC Filtering</h4>
              <v-switch color="success" v-model="editingItem.dict['security.mac_filtering']"></v-switch>
            </div>
            <div v-if="['macvlan','physical'].includes(editingItem.dict.nictype)">
              <h3>VLAN</h3>
              <v-text-field v-model="editingItem.dict['vlan']" label="VLAN:" placeholder="" hint="VLAN ID to attach to."></v-text-field>
            </div>
            <div v-if="['bridged','macvlan', 'p2p', 'sriov'].includes(editingItem.dict.nictype)">
              <h3>MAAS</h3>
              <v-layout row wrap>
                <v-flex xs6>
                  <v-text-field v-model="editingItem.dict['maas.subnet.ipv4']" label="MAAS IPv4:" placeholder="" hint="MAAS IPv4 subnet to register the container in."></v-text-field>
                </v-flex>
                <v-flex xs6>
                  <v-text-field v-model="editingItem.dict['maas.subnet.ipv6']" label="MAAS IPv6:" placeholder="" hint="MAAS IPv6 subnet to register the container in."></v-text-field>
                </v-flex>
              </v-layout>
            </div>
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
        if (this.attach) {
          return [
            { text: 'Name', value: 'name' },
            { text: 'Type', value: 'nictype' },
            { text: 'Parent', value: 'parent' },
            { text: 'Actions', value: 'name', sortable: false, align: 'center', width:'100px' }
          ]
        } else {
          return [
            { text: 'Name', value: 'name' },
            { text: 'Type', value: 'nictype' },
            { text: 'Parent', value: 'parent' },
            { text: 'Actions', value: 'name', sortable: false, align: 'center', width:'100px' }
          ]
        }
      }
    },
    data: () => ({
      error: '',
      valid: true,
      dialog: false,

      tableLoading: true,

      networks: [],
      items: [],
      editingIndex: -1,
      editingItem: {
        id: -1,
        type: "nic",
        name: "",
        dict: {
          "nictype": "bridged",
          "limits.ingress": "",
          "limits.egress": "",
          "limits.max": "",
          "name": "",
          "host_name": "",
          "hwaddr": "",
          "mtu": "",
          "vlan": "",
          "ipv4.address": "",
          "ipv6.address": "",
          "security.mac_filtering": "",
          "maas.subnet.ipv4": "",
          "maas.subnet.ipv6": "",
          "parent": "lxdbr0"
        }
      },
      defaultItem: {
        id: -1,
        type: "nic",
        name: "",
        dict: {
          "nictype": "bridged",
          "limits.ingress": "",
          "limits.egress": "",
          "limits.max": "",
          "name": "",
          "host_name": "",
          "hwaddr": "",
          "mtu": "",
          "vlan": "",
          "ipv4.address": "",
          "ipv6.address": "",
          "security.mac_filtering": "",
          "maas.subnet.ipv4": "",
          "maas.subnet.ipv6": "",
          "parent": "lxdbr0"
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
          const response = await axios.get(this.loggedUser.sub + '/api/lxd/devices/nic')
          this.items = response.data.data
          
          this.getNetworks()
        } catch (error) {
          this.error = 'Could not fetch data from server.';
        }
        this.tableLoading = false
      },

      async attachItem(item) {
        this.linkedItem.devices  = Object.assign({}, this.linkedItem.devices)
        this.$set(this.linkedItem.devices, item.name, {
          "type": item.type,
          ...item.dict
        })
        //
        const response = await axios.patch(this.loggedUser.sub + '/api/lxd/containers/' + this.linkedItem.name, {
          devices: this.linkedItem.devices
        })
      },

      async detachItem(item) {
        this.$delete(this.linkedItem.devices, item.name)
        
        this.linkedItem = Object.assign({}, container.outfix(this.linkedItem))
        //
        const response = await axios.put(this.loggedUser.sub + '/api/lxd/containers/' + this.linkedItem.name, {
          config: this.linkedItem.config,
          devices: this.linkedItem.devices,
          ephemeral: this.linkedItem.ephemeral,
          stateful: this.linkedItem.stateful,
          profiles: this.linkedItem.profiles
        })
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
        this.editingItem = Object.assign({}, item)

        // set name
        this.editingItem.name = this.editingItem.dict.name

        this.dialog = true
      },

      // save
      async saveItem () {
        if (this.$refs.form.validate()) {
          
          // set name
          this.editingItem.name = this.editingItem.dict.name

          // remote
          try {

            // edit
            if (this.editingIndex > -1) {
              var response = await axios.put(this.loggedUser.sub + '/api/lxd/devices/nic/'+this.editingItem.id, this.editingItem)
            } 
            // add
            else {
              var response = await axios.post(this.loggedUser.sub + '/api/lxd/devices/nic', this.editingItem)
            }

            if (response.data.error) {
              if (response.data.error.name) {
                this.error = response.data.error.name
              }
              if (response.data.error.parent) {
                this.error = response.data.error.parent
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
                  const response = await axios.delete(this.loggedUser.sub + '/api/lxd/devices/nic/'+item.id)

                  //
                  this.$emit('snackbar', 'Device successfully saved.')
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
