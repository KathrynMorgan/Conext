<template>
  <v-app>
    <v-content>
      <v-container fluid tag="section" id="grid">
        <v-layout row wrap>
          <v-flex d-flex xs12 order-xs5>
            <v-layout column>
              <v-flex tag="h1" class="display mb-2">
                My Servers
                <v-btn color="success" @click="dialog = true" style="float:right">Add Server</v-btn>
              </v-flex>
              <v-flex>
                <v-alert type="error" :value="error">
                  {{ error }}
                </v-alert>
                <v-data-table :headers="headers" :items="items" hide-actions class="elevation-1">
                  <template slot="items" slot-scope="props">
                    <td><a href="javascript:void(0)" @click="authItem(props.item)">{{ props.item.label }}</a></td>
                    <td>{{ props.item.host }}</td>
                    <td>{{ props.item.secret }}</td>
                    <td>
                      <span left v-if="props.item.status">
                        <v-icon color="green">done</v-icon>
                        <span>Connectable</span>
                      </span>
                      <span left v-if="!props.item.status">
                        <v-icon color="red">error</v-icon>
                        <span>Error</span>
                      </span>
                    </td>
                    <td class="justify-center layout px-0">
                      <v-tooltip left>
                        <v-btn slot="activator" icon class="mx-0" @click="editItem(props.item)">
                          <v-icon color="teal">edit</v-icon>
                        </v-btn>
                        <span>Edit</span>
                      </v-tooltip>
                      <v-tooltip left>
                        <v-btn slot="activator" icon class="mx-0" @click="deleteItem(props.item)">
                          <v-icon color="pink">delete</v-icon>
                        </v-btn>
                        <span>Delete</span>
                      </v-tooltip>
                    </td>
                  </template>
                  <template slot="no-data">
                    You have not added any servers, add a new server to continue.
                  </template>
                </v-data-table>
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
                        <v-form ref="form" v-model="valid" lazy-validation>
                          <v-text-field label="Label" :rules="labelRule" v-model="editedItem.label" required></v-text-field>
                          <v-layout wrap>
                            <v-flex xs12 sm6 md6>
                              <v-text-field label="Host" :rules="hostRule" v-model="editedItem.host" required></v-text-field>
                            </v-flex>
                            <v-flex xs12 sm6 md6>
                              <v-text-field label="Secret" :rules="secretRule" v-model="editedItem.secret" required></v-text-field>
                            </v-flex>
                          </v-layout>
                        </v-form>
                      </v-container>
                    </v-card-text>
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn color="blue darken-1" flat @click.native="close">Cancel</v-btn>
                      <v-btn color="blue darken-1" flat @click.native="save">Save</v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
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
        { text: 'Label', align: 'left', value: 'label' },
        { text: 'Host', value: 'host' },
        { text: 'Secret', value: 'secret' },
        { text: 'Status', value: 'status' },
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
      },
      
      // item form & validation
      valid: true,
      labelRule: [
        v => !!v || 'Label is required'
      ],
      hostRule: [
        v => !!v || 'Host is required',
        v => (v && /^https?:\/\/.+:\d+/i.test(v)) || 'Host must be in the following format: '+window.location.protocol+'//'+window.location.hostname+':'+window.location.port
      ],
      secretRule: [
        v => !!v || 'Secret is required'
      ]
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
        
        // check status
        this.items.forEach(item => {
          this.status(item)
        });
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
          //axios.defaults.headers.common['Authorization'] = 'Bearer ' + response.data['token']
          //axios.post(item.host + '/sync', this.items)
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
        this.$prompt.show({
          persistent: true,
          width: 400,
          toolbar: {
            color: 'red darken-3',
            closable: false,
          },
          title: 'Delete server?',
          text: 'Are you sure you want to delete the <b>'+item.label+'</b> server?',
          buttons: [
            {
              title: 'Yes',
              color: 'success',
              handler: () => { 
                const index = this.items.indexOf(item)
                this.items.splice(index, 1)
        
                this.$storage.set("servers", this.items)
              }
            },
            {
              title: 'No',
              color: 'error'
            }
         ]
        })
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
        
        this.status(this.editedItem)
        
        this.$storage.set("servers", this.items)

        this.close()
      },
      
      async status (item) {
        let index = this.items.indexOf(item)
        //
        try {
          const response = await axios.get(item.host + '/ping')
          this.items[index].status = response.data === 'pong'
        } catch (Error) {
          this.items[index].status = false
        }
      }
    }
  }
</script>

<style>

</style>
