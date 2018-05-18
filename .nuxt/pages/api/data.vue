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
              <v-flex tag="h1" class="display-1 mb-3">
                API - Data
                <v-btn color="success" @click="dialog = true" style="float:right">New Endpoint</v-btn>
              </v-flex>
              <v-flex>
                <v-alert type="error" :value="error">
                  {{ error }}
                </v-alert>
                <v-data-table :headers="tableHeaders" :items="items" hide-actions class="elevation-1">
                  <template slot="items" slot-scope="props">
                    <td><a href="javascript:void(0)" @click.stop="manageItem(props.item)">{{ props.item.module }}</a></td>
                    <td>{{ props.item.version }}</td>
                    <td class="justify-center layout px-0">
                      <!--
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
                      -->
                      <v-btn icon class="mx-0" @click="deleteItem(props.item)">
                        <v-icon color="pink">delete</v-icon>
                      </v-btn>
                    </td>
                  </template>
                  <template slot="no-data">
                    You have not added any items.
                  </template>
                </v-data-table>
              </v-flex>
            </v-layout>
          </v-flex>
        </v-layout>
      </v-container>
      
      <!-- Fullscreen Dialog -->
      <v-dialog v-model="dialog" fullscreen hide-overlay transition="dialog-bottom-transition" scrollable>
        <v-card tile>
          <v-toolbar card dark color="deep-orange accent-4">
            <v-btn icon @click.native="dialog = false" dark>
              <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>{{ editingIndex === -1 ? 'New' : 'Edit' }} Endpoint</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-toolbar-items>
              <v-btn dark flat @click.native="dialog = false">Save</v-btn>
            </v-toolbar-items>
            <v-menu bottom right offset-y>
              <v-btn slot="activator" dark icon>
                <v-icon>more_vert</v-icon>
              </v-btn>
              <!--
              <v-list>
              <v-list-tile v-for="(item, i) in items" :key="i">
              <v-list-tile-title>{{ item.title }}</v-list-tile-title>
              </v-list-tile>
              </v-list>
              -->
            </v-menu>
          </v-toolbar>
          <v-card-text style="padding: 0px;">
            <!--
            <v-tabs v-model="active">
              <v-tab v-for="n in ['Information', 'Configuration', 'Console']" :key="n" ripple>
                {{ n }}
              </v-tab>
              <v-tab-item v-for="n in ['Information', 'Configuration', 'Console']" :key="n">
                <v-card flat>
                  <div id="terminal"></div>
                  <v-card-text>{{ text }}</v-card-text>
                </v-card>
              </v-tab-item>
            </v-tabs>
            -->
            <v-card flat>
              <v-card-text>
                <v-text-field v-model="editingItem.module" label="Name" placeholder="" required></v-text-field>

                
                Define the endpoint use {var_name}, to interpolate into route paramitors.
                <v-text-field v-model="editingItem.endpoint" label="Endpoint" placeholder="e.g: /customers/{id}" required></v-text-field>
                <v-text-field v-model="editingItem.version" label="Version" placeholder="e.g: 1.0" required></v-text-field>
              
                <v-select :items="['None', 'JWT']" v-model="editingItem.auth" label="Authentication" single-line></v-select>
                <v-select :items="['None', 'JSON', 'HTML', 'TEXT', 'JS', 'XML', 'JPG', 'PNG', 'GIF']" v-model="editingItem.header" label="Response Content Type" single-line></v-select>

                <no-ssr placeholder="Codemirror Loading...">
                  <codemirror v-model="editingItem.source" 
                              :options="cmOption"
                              @cursorActivity="onCmCursorActivity"
                              @ready="onCmReady"
                              @focus="onCmFocus"
                              @blur="onCmBlur">
                  </codemirror>
                </no-ssr>
              </v-card-text>
            </v-card>
            
            <pre>{{ editingItem }}</pre>
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
      }),
      formTitle () {
        return this.editedIndex === -1 ? 'New Container' : 'Edit Container'
      }
    },
    data: () => ({
      error: '',
      
      // code mirror
        cmOption: {
          tabSize: 4,
          foldGutter: true,
          styleActiveLine: true,
          lineNumbers: true,
          line: true,
          keyMap: "sublime",
          mode: 'text/x-vue',
          theme: 'base16-dark',
          extraKeys: {
            'F11'(cm) {
              cm.setOption("fullScreen", !cm.getOption("fullScreen"))
            },
            'Esc'(cm) {
              if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false)
            }
          }
        },
        
      // snackbar
      snackbar: false,
      snackbarColor: 'green',
      snackbarText: 'foobar',
      snackbarTimeout: 5000,

      // tabs
      active: null,
      text: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',

      // table & items
      items: [],
      
      tableHeaders: [
        { text: 'Name', value: 'module' },
        { text: 'Status', value: 'version' },
        { text: 'Actions', value: 'module', sortable: false }
      ],
      itemActions: [
        { title: 'Start' },
        { title: 'Stop' },
        { title: 'Delete' }
      ],

      // dialog
      dialog: false,
      
      editingIndex: -1,
      editingItem: {
        id: -1,
        module: "",
        version: 1.0,
        source: "",
        headers: "",
        config: null,
        auth: null
      },
      defaultItem: {
        id: -1,
        module: "",
        version: 1.0,
        source: "",
        headers: "",
        config: null,
        auth: null
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
      onCmCursorActivity(codemirror) {
        console.log('onCmCursorActivity', codemirror)
      },
      onCmReady(codemirror) {
        console.log('onCmReady', codemirror)
      },
      onCmFocus(codemirror) {
        console.log('onCmFocus', codemirror)
      },
      onCmBlur(codemirror) {
        console.log('onCmBlur', codemirror)
      },

      async initialize () {
        //
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
          //
          const response = await axios.get(this.loggedUser.sub + '/api/ams/data')
          this.items = response.data.data
        } catch (error) {
          console.error(error);
        }
      },

      manageItem (item) {
        this.editingIndex = this.items.indexOf(item)
        this.editingItem = Object.assign({}, item)
        this.dialog = true
      },

      async deleteItem (item) {
        const index = this.items.indexOf(item)
        
        confirm('Are you sure you want to delete this item?') && this.items.splice(index, 1)

        //
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
          //
          const response = await axios.delete(this.loggedUser.sub + '/api/ams/data', { data: item })
          //this.items = response.data.data
        } catch (error) {
          console.error(error);
        }
      },

      close () {
        this.dialog = false
        setTimeout(() => {
          this.editingItem = Object.assign({}, this.defaultItem)
          this.editingIndex = -1
        }, 300)
      },

      save () {
        if (this.editingIndex > -1) {
          Object.assign(this.items[this.editingIndex], this.editingItem)
        } else {
          this.items.push(this.editingItem)
        }

        //window.localStorage.setItem('servers', JSON.stringify(this.items))

        this.close()
      }

    }
  }
</script>

<style>

</style>
