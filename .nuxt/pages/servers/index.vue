<template>
  <v-app>
    <v-content>
      <v-container fluid tag="section" id="grid">
        <v-layout row wrap>
          <v-flex d-flex xs12 order-xs5>
            <v-layout column>
              <v-flex tag="h1" class="display-1 mb-3">
                My Servers
                <v-btn color="success" @click="dialog = true" style="float:right">Add Server</v-btn>
              </v-flex>
              <v-flex>
                <v-dialog v-model="dialog" max-width="500px">
                  <v-card>
                    <v-card-title>
                      <span class="headline">{{ formTitle }}</span>
                    </v-card-title>
                    <v-card-text>
                      <v-alert outline color="info" icon="info" :value="true">
                        Servers are securely stored in your browser for easy selection.
                      </v-alert>
                      <v-container grid-list-md>
                        <v-text-field label="Label" v-model="editedItem.label"></v-text-field>
                        <v-layout wrap>
                          <v-flex xs12 sm6 md6>
                            <v-text-field label="Host" v-model="editedItem.host"></v-text-field>
                          </v-flex>
                          <v-flex xs12 sm6 md6>
                            <v-text-field label="Secret" v-model="editedItem.secret"></v-text-field>
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
                    <td><a href="javascript:void(0)" @click="authItem(props.item)">{{ props.item.label }}</a></td>
                    <td><a :href="props.item.host" target="_blank" rel="noopener">{{ props.item.host }}</a></td>
                    <td>{{ props.item.secret }}</td>
                    <td class="justify-center layout px-0">
                      <v-btn icon class="mx-0" @click="editItem(props.item)">
                        <v-icon color="teal">edit</v-icon>
                      </v-btn>
                      <v-btn icon class="mx-0" @click="deleteItem(props.item)">
                        <v-icon color="pink">delete</v-icon>
                      </v-btn>
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
      </v-container>
    </v-content>
  </v-app>
</template>

<script>
  import { mapGetters, mapMutations } from 'vuex'
  import { setToken } from '~/utils/auth'
  import axios from 'axios'

  export default {
    middleware: [
      // 'authenticated'
    ],
    components: {},
    computed: {
      ...mapGetters({
        isAuthenticated: 'auth/isAuthenticated',
        loggedUser: 'auth/loggedUser',
        loggedToken: 'auth/loggedToken'
      }),
      formTitle () {
        return this.editedIndex === -1 ? 'Add Server' : 'Edit Server'
      }
    },
    data: () => ({
      error: '',
      dialog: false,
      headers: [
        { text: 'Label', align: 'left', value: 'host' },
        { text: 'Host', value: 'host' },
        { text: 'Secret', value: 'secret' },
        { text: 'Actions', value: 'host', sortable: false }
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
      }
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
      initialize () {
        this.items = this.$storage.get("servers") || []
      },

      authItem (item) {
        this.editedIndex = this.items.indexOf(item)
        this.editedItem = Object.assign({}, item)

        axios.post(item.host + '/auth', {
          label:  item.label,
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
        
        this.$storage.set("servers", this.items)

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
