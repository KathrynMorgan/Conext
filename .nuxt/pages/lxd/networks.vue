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
                LXD - Networks
                <v-btn color="success" @click="dialog = true" style="float:right">New Network</v-btn>
              </v-flex>
              <v-flex>
                <v-alert type="error" :value="error.global">
                  {{ error.global }}
                </v-alert>
                <v-data-table :headers="tableHeaders" :items="networks" hide-actions class="elevation-1" :loading="tableLoading">
                  <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                  <template slot="items" slot-scope="props">
                    <tr>
                      <td>
                        <span v-if="!props.item.managed">{{ props.item.name }}</span>
                        <span v-else><a href="javascript:void(0)" @click.stop="editItem(props.item)">{{ props.item.name }}</a></span>
                      </td>
                      <td>{{ props.item.description ? ucfirst(props.item.description) : '-' }}</td>
                      <td>
                        <span v-if="props.item.config['ipv4.address'] && props.item.config['ipv4.address'] !== 'none'">{{ props.item.config['ipv4.address'] }}</span>
                        <span v-else>-</span>
                      </td>
                      <td>
                        <span v-if="props.item.config['ipv6.address'] && props.item.config['ipv6.address'] !== 'none'">{{ props.item.config['ipv6.address'] }}</span>
                        <span v-else>-</span>
                      </td>
                      <td>{{ props.item.type ? ucfirst(props.item.type) : '-' }}</td>
                      <td>{{ props.item.managed ? ucfirst(props.item.managed) : '-' }}</td>
                      <td>
                        <v-btn icon class="mx-0" style="float:right" @click.stop="deleteItem(props.item)" :disabled="!props.item.managed">
                          <v-icon color="pink">delete</v-icon>
                        </v-btn>
                      </td>
                    </tr>
                  </template>
                  <template slot="no-data">
                    {{ tableLoading ? 'Fetching data, please wait...' : 'There are currently no networks.' }}
                  </template>
                </v-data-table>
              </v-flex>
            </v-layout>
          </v-flex>
        </v-layout>
      </v-container>

      <!-- Add/Edit Dialog -->
      <v-dialog v-model="dialog" max-width="650px" scrollable>
        <v-card tile>
          <v-toolbar card dark color="light-blue darken-3">
            <v-btn icon @click.native="dialog = false" dark>
              <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>{{ editingIndex === -1 ? 'New' : 'Edit' }} Network</v-toolbar-title>
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
                  <v-text-field v-model="editingItem.name" :rules="nameRule" label="Name:" placeholder="" required hint="Enter a name for the network."></v-text-field>
                  <v-text-field v-model="editingItem.description" label="Description:" placeholder="" hint="Enter a description for the network."></v-text-field>

                  <h2>Bridge</h2>
                  <v-select :items="['native','openvswitch']" v-model="editingItem.config['bridge.driver']" label="Driver:"></v-select>
                  <v-text-field v-model="editingItem.config['bridge.external_interfaces']" label="External Interfaces:" placeholder="" hint="Comma separate list of unconfigured network interfaces to include in the bridge."></v-text-field>
                  <v-select :items="['standard','fan']" v-model="editingItem.config['bridge.mode']" label="Mode:"></v-select>
                  <v-text-field v-model="editingItem.config['bridge.mtu']" label="MTU:" placeholder="" hint="Bridge MTU (default varies if tunnel or fan setup)"></v-text-field>

                  <div v-show="editingItem.config['bridge.mode'] == 'standard'">
                    <h2>IPv4 <v-switch color="success" v-model="state.ip4" style="margin-left:60px;margin-right:-60px;margin-top:-30px;width:50%;"></v-switch></h2>
                    <div v-if="state.ip4" style="margin-top:-20px">
                      <v-text-field v-model="editingItem.config['ipv4.address']" label="IPv4 address:" required placeholder="" hint="IPv4 address for the bridge (CIDR notation). Enter &quot;auto&quot; to generate a new one."></v-text-field>
                      <v-text-field v-model="editingItem.config['ipv4.routes']" label="IPv4 routes:" placeholder="" hint="Comma separated list of additional IPv4 CIDR subnets to route to the bridge."></v-text-field>
                      <v-layout row wrap style="margin-top:10px">
                        <v-flex xs4>
                          <h4>NAT</h4>
                          <v-switch :label="`${editingItem.config['ipv4.nat'] === 'true' ? 'Yes' : 'No'}`" true-value="true" false-value="false" color="success" v-model="editingItem.config['ipv4.nat']" persistent-hint hint="Whether to NAT."></v-switch>
                        </v-flex>
                        <v-flex xs4>
                          <h4>Firewall</h4>
                          <v-switch :label="`${editingItem.config['ipv4.firewall'] === 'true' ? 'Yes' : 'No'}`" true-value="true" false-value="false" color="success" v-model="editingItem.config['ipv4.firewall']" persistent-hint hint="Generate filtering firewall rules."></v-switch>
                        </v-flex>
                        <v-flex xs4>
                          <h4>Routing</h4>
                          <v-switch :label="`${editingItem.config['ipv4.routing'] === 'true' ? 'Yes' : 'No'}`" true-value="true" false-value="false" color="success" v-model="editingItem.config['ipv4.routing']" persistent-hint hint="Route traffic in and out of the bridge."></v-switch>
                        </v-flex>
                      </v-layout>
                      <h3 style="margin-top:20px">IPv4 DHCP <v-switch color="success" v-model="editingItem.config['ipv4.dhcp']" true-value="true" false-value="false" style="margin-left:100px;margin-right:-60px;margin-top:-28px;width:50%;"></v-switch></h3>
                      <div v-if="editingItem.config['ipv4.dhcp'] === 'true'" style="margin-top:-20px">
                        <v-text-field v-model="editingItem.config['ipv4.dhcp.expiry']" label="IPv4 DHCP expiry:" placeholder="" hint="When to expire DHCP leases."></v-text-field>
                        <v-text-field v-model="editingItem.config['ipv4.dhcp.gateway']" label="IPv4 gateway address:" placeholder="" hint="Address of the gateway for the subnet."></v-text-field>
                        <v-text-field v-model="editingItem.config['ipv4.dhcp.ranges']" label="IPv4 DHCP ranges:" placeholder="" :hint="`Comma separated list of IP ranges to use for DHCP (e.g: ${editingItem.config['ipv4.address'].substring(0, editingItem.config['ipv4.address'].lastIndexOf('.') + 2)}.2-${editingItem.config['ipv4.address'].substring(0, editingItem.config['ipv4.address'].lastIndexOf('.') + 2)}.255)`"></v-text-field>
                      </div>
                    </div>
  
                    <h2>IPv6 <v-switch color="success" v-model="state.ip6" style="margin-left:60px;margin-right:-60px;margin-top:-30px;width:50%;"></v-switch></h2>
                    <div v-if="state.ip6" style="margin-top:-20px">
                      <v-text-field v-model="editingItem.config['ipv6.address']" label="IPv6 address:" required placeholder="" hint="IPv6 address for the bridge (CIDR notation). Enter &quot;auto&quot; to generate a new one."></v-text-field>
                      <v-text-field v-model="editingItem.config['ipv6.routes']" label="IPv6 routes:" placeholder="" hint="Comma separated list of additional IPv6 CIDR subnets to route to the bridge."></v-text-field>
                      <v-layout row wrap style="margin-top:10px">
                        <v-flex xs4>
                          <h4>NAT</h4>
                          <v-switch :label="`${editingItem.config['ipv6.nat'] === 'true' ? 'Yes' : 'No'}`" true-value="true" false-value="false" color="success" v-model="editingItem.config['ipv6.nat']" persistent-hint hint="Whether to NAT."></v-switch>
                        </v-flex>
                        <v-flex xs4>
                          <h4>Firewall</h4>
                          <v-switch :label="`${editingItem.config['ipv6.firewall'] === 'true' ? 'Yes' : 'No'}`" true-value="true" false-value="false" color="success" v-model="editingItem.config['ipv6.firewall']" persistent-hint hint="Generate filtering firewall rules."></v-switch>
                        </v-flex>
                        <v-flex xs4>
                          <h4>Routing</h4>
                          <v-switch :label="`${editingItem.config['ipv6.routing'] === 'true' ? 'Yes' : 'No'}`" true-value="true" false-value="false" color="success" v-model="editingItem.config['ipv6.routing']" persistent-hint hint="Route traffic in and out of the bridge."></v-switch>
                        </v-flex>
                      </v-layout>
                      <h3 style="margin-top:20px">IPv6 DHCP <v-switch color="success" v-model="editingItem.config['ipv6.dhcp']" true-value="true" false-value="false" style="margin-left:100px;margin-right:-60px;margin-top:-28px;width:50%;"></v-switch></h3>
                      <div v-if="editingItem.config['ipv6.dhcp'] === 'true'" style="margin-top:-20px">
                        <v-text-field v-model="editingItem.config['ipv6.dhcp.expiry']" label="IPv6 DHCP expiry:" placeholder="" hint="When to expire DHCP leases."></v-text-field>
                        <h4>Stateful</h4>
                        <v-switch :label="`${editingItem.config['ipv6.dhcp.stateful'] === 'true' ? 'Yes' : 'No'}`" true-value="true" false-value="false" color="success" v-model="editingItem.config['ipv6.dhcp.stateful']" persistent-hint hint="Whether to allocate addresses using DHCP."></v-switch>
                        <v-text-field style="margin-top:10px" v-model="editingItem.config['ipv6.dhcp.ranges']" label="IPv6 DHCP ranges:" placeholder="" :hint="`Comma separated list of IP ranges to use for DHCP (e.g: ${editingItem.config['ipv6.address'].substring(0, editingItem.config['ipv6.address'].lastIndexOf(':') + 1)}2-${editingItem.config['ipv6.address'].substring(0, editingItem.config['ipv6.address'].lastIndexOf(':') + 1)}255)`"></v-text-field>
                      </div>
                    </div>
                  </div>
                  <div v-show="editingItem.config['bridge.mode'] == 'fan'">
                    <h2>Fan</h2>
                    <v-text-field v-model="editingItem.config['fan.overlay_subnet']" label="Overlay Subnet:" placeholder="" hint="Subnet to use as the overlay for the FAN (CIDR notation)."></v-text-field>
                    <v-text-field v-model="editingItem.config['fan.underlay_subnet']" label="Underlay Subnet:" placeholder="" hint="Subnet to use as the underlay for the FAN (CIDR notation)."></v-text-field>
                    <v-select :items="['vxlan','ipip']" v-model="editingItem.config['fan.type']" label="Type:"></v-select>
                  </div>
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
      networks: function () {
        return this.items.filter(item => {
          return item.managed === true;
        })
      }
    },
    data: () => ({
      dialog: false,
      valid: true,

      // error alerts
      error: {global:false, editing: false},

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
        { text: 'IPv4 Address', value: 'ipv4' },
        { text: 'IPv6 Address', value: 'ipv6' },
        { text: 'Type', value: 'type' },
        { text: 'Managed', value: 'managed' },
        { text: 'Actions', value: 'action', sortable: false, align: 'right' }
      ],

      // determines the state of the configuration
      state: {
        ip4: true,
        ip6: true,
        dhcp: false,
        bridge: false,
        dns: false,
        fan: false,
        raw: false,
        tunnel: false,
      },

      editingIndex: -1,
      editingItem: {
        config: {
          "bridge.driver": "native",
          "bridge.external_interfaces": "",
          "bridge.mode": "standard",
          "bridge.mtu": "",
          "ipv4.address": "",
          "ipv4.nat": "",
          "ipv6.address": "",
          "ipv6.nat": ""
        },
        description: "",
        locations: [],
        managed: true,
        name: "",
        status: "",
        type: "",
        used_by: []
      },
      defaultItem: {
        config: {
          "bridge.driver": "native",
          "bridge.external_interfaces": "",
          "bridge.mode": "standard",
          "bridge.mtu": "",
          "ipv4.address": "",
          "ipv4.nat": "",
          "ipv6.address": "",
          "ipv6.nat": ""
        },
        description: "",
        locations: [],
        managed: true,
        name: "",
        status: "",
        type: "",
        used_by: []
      },

      nameRule: [
        v => !!v || 'Name is required',
        v => (v && v.length <= 15) || 'Name is too long (maximum 15 characters)'
      ]
    }),
    beforeDestroy: function() {},
    mounted: function () {
      axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
      this.initialize()
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
          const response = await axios.get(this.loggedUser.sub + '/api/lxd/networks')
          this.items = response.data.data
        } catch (error) {
          this.items = []
          this.tableNoData = 'No data.'
          this.error.global = 'Could not fetch data from server.'
        }
        this.tableLoading = false
      },

      // create or edit item
      editItem (item) {
        this.editingIndex = this.items.indexOf(item)
        this.editingItem = Object.assign({}, item)
        
        // apply toggle states
        this.state.ip4 = this.editingItem.config['ipv4.address'] !== 'none'
        this.state.ip6 = this.editingItem.config['ipv6.address'] !== 'none'
        
        // setup mode if not defined
        if (!this.editingItem.config['bridge.mode']) {
          this.editingItem = Object.assign({}, this.editingItem.config, {
            'bridge.driver': "native",
            'bridge.external_interfaces': "",
            'bridge.mode': "standard",
            'bridge.mtu': "",
          })
        }

        // apply change to the model
        if (this.editingItem.config['ipv4.address'] === 'none') {
          this.editingItem.config['ipv4.address'] = ''
          this.editingItem.config['ipv4.nat'] = 'false'
          this.editingItem.config['ipv4.routes'] = ''
          this.editingItem.config['ipv4.firewall'] = 'false'
          this.editingItem.config['ipv4.routing'] = 'false'
          this.editingItem.config['ipv4.dhcp'] = 'false'
          this.editingItem.config['ipv4.dhcp.expiry'] = ''
          this.editingItem.config['ipv4.dhcp.gateway'] = ''
          this.editingItem.config['ipv4.dhcp.ranges'] = ''
        }
        
        if (this.editingItem.config['ipv6.address'] === 'none') {
          this.editingItem.config['ipv6.address'] = ''
          this.editingItem.config['ipv6.nat'] = 'false'
          this.editingItem.config['ipv6.routes'] = ''
          this.editingItem.config['ipv6.firewall'] = 'false'
          this.editingItem.config['ipv6.routing'] = 'false'
          this.editingItem.config['ipv6.dhcp'] = 'false'
          this.editingItem.config['ipv6.dhcp.expiry'] = ''
          this.editingItem.config['ipv6.dhcp.stateful'] = 'false'
          this.editingItem.config['ipv6.dhcp.ranges'] = ''
        }

        this.dialog = true
      },

      // save
      async save () {
        if (this.$refs.form.validate()) {
          // remote
          try {
            if (!this.loggedUser) {
              this.$router.replace('/servers')
            }

            // remove IPv4 is turned off
            if (!this.state.ip4) {
              this.editingItem.config['ipv4.address'] = 'none'
              this.editingItem.config['ipv4.nat'] = 'false'
            }
            
            // remove IPv6 is turned off
            if (!this.state.ip6) {
              this.editingItem.config['ipv6.address'] = 'none'
              this.editingItem.config['ipv6.nat'] = 'false'
            }
            
            // workaround as cant remove keys
            if (this.editingItem.config['bridge.mtu'] === '') {
              this.$delete(this.editingItem.config, 'bridge.mtu')
            }
            
            // workaround as cant remove keys
            if (this.editingItem.config['ipv4.routes'] === '') {
              this.$delete(this.editingItem.config, 'ipv4.routes')
            }
            
            if (this.editingItem.config['ipv4.dhcp.ranges'] === '') {
              this.$delete(this.editingItem.config, 'ipv4.dhcp.ranges')
            }
            
            // workaround as cant remove keys
            if (this.editingItem.config['ipv6.routes'] === '') {
              this.$delete(this.editingItem.config, 'ipv6.routes')
            }
            
            if (this.editingItem.config['ipv6.dhcp.ranges'] === '') {
              this.$delete(this.editingItem.config, 'ipv6.dhcp.ranges')
            }
            
            // remove bridge mode
            if (this.editingItem.config['bridge.mode'] === 'fan') {
              // ip4
              this.$delete(this.editingItem.config, 'ipv4.address')
              this.$delete(this.editingItem.config, 'ipv4.nat')
              this.$delete(this.editingItem.config, 'ipv4.routes')
              this.$delete(this.editingItem.config, 'ipv4.firewall')
              this.$delete(this.editingItem.config, 'ipv4.routing')
              this.$delete(this.editingItem.config, 'ipv4.dhcp')
              this.$delete(this.editingItem.config, 'ipv4.dhcp.expiry')
              this.$delete(this.editingItem.config, 'ipv4.dhcp.stateful')
              this.$delete(this.editingItem.config, 'ipv4.dhcp.ranges')

              // ip6
              this.$delete(this.editingItem.config, 'ipv6.address')
              this.$delete(this.editingItem.config, 'ipv6.nat')
              this.$delete(this.editingItem.config, 'ipv6.routes')
              this.$delete(this.editingItem.config, 'ipv6.firewall')
              this.$delete(this.editingItem.config, 'ipv6.routing')
              this.$delete(this.editingItem.config, 'ipv6.dhcp')
              this.$delete(this.editingItem.config, 'ipv6.dhcp.expiry')
              this.$delete(this.editingItem.config, 'ipv6.dhcp.stateful')
              this.$delete(this.editingItem.config, 'ipv6.dhcp.ranges')

            } else if (this.editingItem.config['bridge.mode'] === 'standard') {
              // fan
              this.$delete(this.editingItem.config, 'fan.overlay_subnet')
              this.$delete(this.editingItem.config, 'fan.underlay_subnet')
              this.$delete(this.editingItem.config, 'fan.type')
            }

            // edit
            if (this.editingIndex > -1) {
              var response = await axios.put(this.loggedUser.sub + '/api/lxd/networks/'+this.editingItem.name, {
                config: this.editingItem.config,
                description: this.editingItem.description,
                name: this.editingItem.name,
                type: this.editingItem.type
              })
            } 
            // add
            else {
              var response = await axios.post(this.loggedUser.sub + '/api/lxd/networks', {
                name: this.editingItem.name,
                config: this.editingItem.config,
                description: this.editingItem.description,
                name: this.editingItem.name,
                type: this.editingItem.type
              })
            }
            
            // check errors
            if (response.data.code === 422) {
              this.error.editing = response.data.error
            } else {
              this.error.editing = false
              // local
              if (this.editingIndex > -1) {
                Object.assign(this.items[this.editingIndex], this.editingItem)
              } else {
                this.items.push(Object.assign({}, this.editingItem))
              }
              //
              this.snackbar = true
              this.snackbarText = 'Network successfully saved.'
            }
          } catch (error) {
            this.error.global = 'Could not save network to server.'
          }

          if (!this.error.editing && this.editingIndex === -1) {
            this.close()
          }
          
          if (!this.error.editing) {
            this.initialize()
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
          title: 'Delete network?',
          text: 'Are you sure you want to delete the <b>'+item.name+'</b> network?',
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
                  const response = await axios.delete(this.loggedUser.sub + '/api/lxd/networks/'+item.name)

                  //
                  this.snackbar = true
                  this.snackbarColor = 'green'
                  this.snackbarText = 'Network deleted.'
                } catch (error) {
                  //
                  this.error.global = 'Failed to delete network.'
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
          this.error.editing = false
        }, 300)
      },

      ucfirst(str) {
        return String(str).charAt(0).toUpperCase() + String(str).slice(1)
      }
    }
  }
</script>

<style>

</style>
