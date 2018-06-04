<template>
  <div>
    <v-tabs v-model="activeTab" grow show-arrows>
      <v-tab ripple :href="`#tab-blocker`">Blocker</v-tab>
      <v-tab ripple :href="`#tab-network`">Network</v-tab>
      <v-tab ripple :href="`#tab-disk`">Disk</v-tab>
      <v-tab ripple :href="`#tab-unix-char`">Unix-char</v-tab>
      <v-tab ripple :href="`#tab-unix-block`">Unix-block</v-tab>
      <v-tab ripple :href="`#tab-usb`">USB</v-tab>
      <v-tab ripple :href="`#tab-gpu`">GPU</v-tab>
      <v-tab ripple :href="`#tab-infiniband`">InfiniBand</v-tab>
      <v-tab ripple :href="`#tab-proxy`">Proxy</v-tab>
      
      <v-tab-item :id="`tab-blocker`">blocker</v-tab-item>
      <v-tab-item :id="`tab-network`">Network</v-tab-item>
      <v-tab-item :id="`tab-disk`">disk</v-tab-item>
      <v-tab-item :id="`tab-unix-char`">unix-char</v-tab-item>
      <v-tab-item :id="`tab-unix-block`">unix-block</v-tab-item>
      <v-tab-item :id="`tab-usb`">usb</v-tab-item>
      <v-tab-item :id="`tab-gpu`">gpu</v-tab-item>
      <v-tab-item :id="`tab-infiniband`">infiniband</v-tab-item>
      <v-tab-item :id="`tab-proxy`">proxy</v-tab-item>
    </v-tabs>
    
    <pre>{{ container }}</pre>
    
    <v-btn color="success" @click="save()" style="float:right">Save</v-btn>
    
    <v-alert type="error" :value="error">
      {{ error }}
    </v-alert>
    <v-data-table :headers="headers" :items="items" hide-actions :loading="tableLoading">
      <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
      <template slot="items" slot-scope="props">
        <tr>
          <td>{{ props.item.name }}</td>
          <td>{{ new Date(props.item.created_at).toLocaleString() }}</td>
          <td>
            <v-tooltip left>
              <v-btn slot="activator" icon class="mx-0" style="float:right" @click.stop="deleteSnapshot(props.item)">
                <v-icon color="pink">delete</v-icon>
              </v-btn>
              <span>Delete</span>
            </v-tooltip>
            <v-tooltip left>
              <v-btn slot="activator" icon class="mx-0" style="float:right" @click.stop="restoreSnapshot(props.item)">
                <v-icon color="blue">restore</v-icon>
              </v-btn>
              <span>Restore</span>
            </v-tooltip>
            <v-tooltip left>
              <v-btn slot="activator" icon class="mx-0" style="float:right" @click.stop="imageSnapshot(props.item)">
                <v-icon color="green">save</v-icon>
              </v-btn>
              <span>Image</span>
            </v-tooltip>
          </td>
        </tr>
      </template>
      <template slot="no-data">
        {{ tableLoading ? 'Fetching data, please wait...' : 'There are currently no snapshots for this container.' }}
      </template>
    </v-data-table>
    
          
      <!-- Fullscreen Dialog -->
      <v-dialog v-model="dialog" max-width="900px" scrollable>
        <v-card tile>
          <v-toolbar card dark color="light-blue darken-3">
            <v-btn icon @click.native="close('preview')" dark>
              <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>...</v-toolbar-title>
            <v-spacer></v-spacer>
          </v-toolbar>
          <v-card-text style="padding: 0px;">
...
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
    props: ['item'],
    computed: {
      ...mapGetters({
        loggedUser: 'auth/loggedUser',
        loggedToken: 'auth/loggedToken'
      })
    },
    data: () => ({
      // global error
      error: '',
      activeTab: 'tab-blocker',
      dialog: false,
      tableLoading: true,
      headers: [
        { text: 'Name', value: 'name' },
        { text: 'Created', value: 'created' },
        { text: 'Actions', value: 'name', sortable: false, align: 'right' }
      ],
      //
      items: []
    }),
    beforeDestroy: function() {},
    mounted: function () {
      axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
      
      //this.container = Object.assign({info:{name:''}}, this.item)
      
      // none device
      //this.container.info.devices.push({none: 'eth0'});
      
      //this.container.info.devices = Object.assign({}, this.container.info.devices)
      
      // network
      /*
      this.$set(this.container.info.devices, 'test', {
        "type": "nic",
        "name": "eth1",
        "nictype": "bridged",
        "parent": "test"
      });
      */
      
      //this.initialize()
    },
    methods: {
      async initialize () {
        if (!this.container.info || !this.container.info.name) {
          return
        }
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          //
          const response = await axios.get(this.loggedUser.sub + '/api/lxd/containers/' + this.container.info.name + '/snapshots')
          this.items = response.data.data
        } catch (error) {
          this.error = 'Could not fetch data from server.';
        }
        this.tableLoading = false
      },
      
      openDialog(){
        this.dialog = true
      },
      
      /*
      async saveContainer () {
        // remote
          try {
            if (!this.loggedUser) {
              this.$router.replace('/servers')
            }

            //
            const response = await axios.patch(this.loggedUser.sub + '/api/lxd/containers/' + this.container.info.name, {
              devices: this.container.info.devices
            })
            
            this.$emit('snackbar', 'Containers device configuration saved.')
          } catch (error) {
            this.error = 'Could not save container device configuration.';
          }
      },
      */
      
      /*
      deleteSnapshot (item) {
        this.$prompt.show({
          persistent: true,
          width: 400,
          toolbar: {
            color: 'red darken-3',
            closable: false,
          },
          title: 'Delete snapshot?',
          text: 'Are you sure you want to delete snapshot:<br><b>'+item.name.substr(item.name.lastIndexOf('/') + 1)+'</b>',
          buttons: [
            {
              title: 'Yes',
              color: 'success',
              handler: async () => { 
                try {
                  if (!this.loggedUser) {
                    this.$router.replace('/servers')
                  }
                  
                  // delete local
                  const index = this.items.indexOf(item)
                  this.items.splice(index, 1)
        
                  // remote
                  const response = await axios.delete(this.loggedUser.sub + '/api/lxd/containers/' + this.container.info.name + '/snapshots/'+item.name.substr(item.name.lastIndexOf('/') + 1))
                  this.$emit('snackbar', 'Snapshot deleted.')
                } catch (error) {
                  this.$emit('snackbar', 'Failed to delete snapshot.')
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
      */

/*
      restoreSnapshot (item) {
        this.$prompt.show({
          persistent: true,
          width: 400,
          toolbar: {
            color: 'orange darken-3',
            closable: false,
          },
          title: 'Restore snapshot?',
          text: 'Are you sure you want to restore container from snapshot:<br><b>'+item.name.substr(item.name.lastIndexOf('/') + 1)+'</b><p class="text-md-center red--text"><br><b>Current container state will be overwritten!</b></p>',
          buttons: [
            {
              title: 'Yes',
              color: 'success',
              handler: async () => { 
                try {
                  if (!this.loggedUser) {
                    this.$router.replace('/servers')
                  }

                  //
                  const response = await axios.put(this.loggedUser.sub + '/api/lxd/containers/' + this.container.info.name + '/snapshots', {
                    name: item.name.substr(item.name.lastIndexOf('/') + 1)
                  })
                  this.$emit('snackbar', 'Snapshot restored.')
                } catch (error) {
                  this.$emit('snackbar', 'Failed to restore snapshot.')
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
      */
      
      /*
      async imageSnapshot (item) {
        //
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          //
          const response = await axios.post(this.loggedUser.sub + '/api/lxd/images', {
            source: {
              type: 'snapshot',
              name: item.name
            },
            public: false,
            properties: {
              // image name: `container-name (01/01/2018, 01:23:45)`
              description: item.name.split('/')[0] + ' (' + new Date(item.name.substr(item.name.lastIndexOf('/') + 1)).toLocaleString()+')',
              label: (item.config['image.label'] ? item.config['image.label'] : ''),
              architecture: (item.config['image.architecture'] ? item.config['image.architecture'] : ''),
              build: new Date(),
              distribution: (item.config['image.distribution'] ? item.config['image.distribution'] : ''),
              os: (item.config['image.os'] ? item.config['image.os'] : ''),
              release: (item.config['image.release'] ? item.config['image.release'] : ''),
              version: (item.config['image.version'] ? item.config['image.version'] : '')
            },
            auto_update: false
          })
          
          this.$emit('snackbar', 'Imaging snapshot.')

        } catch (error) {
          this.$emit('snackbar', 'Failed to imaging snapshot.')
        }
      }
      */
    }
  }
</script>

<style>

</style>
