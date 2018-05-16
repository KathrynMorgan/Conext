<template>
  <v-app>
    <v-snackbar
                top
                :timeout="snackbarTimeout"
                :color="snackbarColor"
                v-model="snackbar"
                >
      {{ snackbarText }}
      <v-btn dark flat @click.native="snackbar = false">Close</v-btn>
    </v-snackbar>

    <v-content>
      
      <v-container fluid tag="section" id="grid">
        <v-layout row wrap>
          <v-flex d-flex xs12 order-xs5>
            <v-layout column>
              <v-flex tag="h1" class="display-1 mb-3">
                Routes - Web Forwards
                <v-btn color="success" @click="dialog = true" style="float:right">New</v-btn>
              </v-flex>
              <v-flex>
                <v-dialog v-model="dialog" max-width="500px">
                  <v-card>
                    <v-card-title>
                      <span class="headline">{{ formTitle }}</span>
                    </v-card-title>
                    <v-card-text>
                      <v-alert outline color="info" icon="info" :value="true"></v-alert>
                      <v-container grid-list-md>
                        <v-layout wrap>
                          <v-flex xs12 sm6 md6>
                            <v-text-field label="Host" v-model="editedItem.name"></v-text-field>
                          </v-flex>
                          <v-flex xs12 sm6 md6>
                            <v-text-field label="Secret" v-model="editedItem.status"></v-text-field>
                          </v-flex>
                        </v-layout>
                      </v-container>
                    </v-card-text>
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn color="blue darken-1" flat @click.native="close">Cancel</v-btn>
                      <v-btn color="blue darken-1" flat @click.native="save">Save</v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>

                <v-alert type="error" :value="error">
                  {{ error }}
                </v-alert>

                <v-data-table :headers="headers" :items="items" hide-actions class="elevation-1">
                  <template slot="items" slot-scope="props">
                    <td><a href="javascript:void(0)" @click.stop="viewContainer(props.item)">{{ props.item.label }}</a></td>
                    <td>{{ props.item.ip }}</td>
                    <td>{{ props.item.port }}</td>
                    <td><ul><li style="list-style-type: none;" v-for="domain in props.item.ownDomain" v-bind:key="domain.name">{{ domain.name }}</li></ul></td>
                    <td>{{ props.item.ssl_type }}</td>
                    <td>{{ props.item.has_change }}</td>
                    <td class="justify-center layout px-0">
                      <v-menu offset-y>
                        <v-btn icon class="mx-0" slot="activator">
                          <v-icon color="blue-grey lighten-3">view_headline</v-icon>
                        </v-btn>
                        <v-list>
                          <v-list-tile v-for="item in containerActions" :key="item.title" @click="actionContainer(item.title.toLowerCase(), props.item.name)">
                            <v-list-tile-title>{{ item.title }}</v-list-tile-title>
                          </v-list-tile>
                        </v-list>
                      </v-menu>

                      <v-btn icon class="mx-0" @click="editItem(props.item)">
                        <v-icon color="teal">edit</v-icon>
                      </v-btn>
                      <!--
<v-btn icon class="mx-0" @click="deleteItem(props.item)">
<v-icon color="pink">delete</v-icon>
</v-btn>
-->
                    </td>
                  </template>
                  <template slot="no-data">
                    You have not added any servers, add a new server to continue.
                  </template>
                </v-data-table>
              </v-flex>
            </v-layout>
          </v-flex>
        </v-layout>
        
        <pre>{{items}}</pre>
        
      </v-container>
      
      
      <v-dialog
                v-model="containerDialog"
                fullscreen
                hide-overlay
                transition="dialog-bottom-transition"
                scrollable
                >
        <v-card tile>
          <v-toolbar card dark color="deep-orange accent-4">
            <v-btn icon @click.native="containerDialog = false" dark>
              <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>Container: {{ container.name }}</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-toolbar-items>
              <v-btn dark flat @click.native="containerDialog = false">Save</v-btn>
            </v-toolbar-items>
            <v-menu bottom right offset-y>
              <v-btn slot="activator" dark icon>
                <v-icon>more_vert</v-icon>
              </v-btn>
            </v-menu>
          </v-toolbar>
          <v-card-text>

            <pre>{{ container }}</pre>

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
  import { Terminal } from 'xterm'
  import * as fit from 'xterm/lib/addons/fit/fit'

  import MainNav from '~/components/MainNav.vue'

  export default {
    middleware: [
      'authenticated'
    ],
    components: {
      MainNav
    },
    computed: {
     
      ...mapGetters({
        isAuthenticated: 'auth/isAuthenticated',
        loggedUser: 'auth/loggedUser',
        loggedToken: 'auth/loggedToken'
      }),

      formTitle () {
        return this.editedIndex === -1 ? 'New Container' : 'Edit Container'
      }
    },
    data: () => ({
      // snackbar
      snackbar: false,
      snackbarColor: 'green',
      snackbarText: 'foobar',
      snackbarTimeout: 5000,

      active: null,
      text: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',

      error: '',
      dialog: false,
      containerDialog: false,
      containerActions: [
        { title: 'Start' },
        { title: 'Stop' },
        { title: 'Delete' }
      ],
      headers: [
        { text: 'Name', value: 'name' },
        { text: 'IP', value: 'ip' },
        { text: 'Port', value: 'port' },
        { text: 'Domains', value: 'ownDomain' },
        { text: 'SSL', value: 'ssl_type' },
        { text: 'Has Change', value: 'has_change' },
        { text: 'Actions', value: 'name', sortable: false }
      ],
      items: [],
      editedIndex: -1,
      editedItem: {
        host: '',
        secret: ''
      },
      defaultItem: {
        host: '',
        secret: ''
      },
      container: {}
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
        //
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
          //
          const response = await axios.get(this.loggedUser.sub + '/api/routes/web-forwards')
          this.items = response.data.data
        } catch (error) {
          console.error(error);
        }
      },


      viewContainer (container) {
        this.container = container
        this.containerDialog = true
        this.console()
      },

      async actionContainer (action, item) {
        //
        try {
          if (!this.loggedUser) {
            throw Error();
          }

          //
          axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
          //
          const response = await axios.get(this.loggedUser.sub + '/api/lxd/containers/' + item + '/' + action)
          setTimeout(this.initialize(), 1000)
        } catch (error) {
          console.error(error);
        }
      },

      /*
      authItem (item) {
        this.editedIndex = this.items.indexOf(item)
        this.editedItem = Object.assign({}, item)

        axios.post(item.host + '/auth', {
          server: item.host,
          secret: item.secret
        }).then(response => {
          setToken(response.data['token'])
          this.$router.replace('/')
        }).catch(error => {
          if (error.response) {
            if (error.response.status === 401) {
              this.error = 'Incorrect secret!'
            } else {
              this.error = 'Failed to connect to host, check details.'
            }
          } else if (error.request) {
            this.error = 'Failed to connect to host, check details.'
          } else {
            this.error = error.message
          }
        })
      },
*/
      editItem (item) {
        this.editedIndex = this.items.indexOf(item)
        this.editedItem = Object.assign({}, item)
        this.dialog = true
      },

      deleteItem (item) {
        const index = this.items.indexOf(item)
        confirm('Are you sure you want to delete this item?') && this.items.splice(index, 1)

        window.localStorage.setItem('servers', JSON.stringify(this.items))
      },

      close () {
        this.dialog = false
        setTimeout(() => {
          this.editedItem = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        }, 300)
      },

      save () {
        if (this.editedIndex > -1) {
          Object.assign(this.items[this.editedIndex], this.editedItem)
        } else {
          this.items.push(this.editedItem)
        }

        window.localStorage.setItem('servers', JSON.stringify(this.items))

        this.close()
      }

      /*
      async getServers () {
        // set jwt into request header
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
        // get (servers)
        const response = await axios.get('https://fatfree-base-rest-cloned-lcherone.c9users.io/servers')
        this.servers = response.data.data
      },
      async remove (id) {
        // set jwt into request header
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
        // delete (server)
        const response = await axios.delete('https://fatfree-base-rest-cloned-lcherone.c9users.io/servers/' + id)
        this.getServers()
      }*/
    }
  }
</script>

<style>

</style>
