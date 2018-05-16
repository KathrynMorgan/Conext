<template>
  <v-content>
    <v-container fluid tag="section" id="grid">
      <v-layout row wrap>
        <v-flex d-flex xs12 order-xs5>
          <v-layout column>
            <v-flex tag="h1" class="display-1 mb-3">
              Server - Logins
            </v-flex>
            <v-flex>
              <v-alert type="error" :value="error">
                {{ error }}
              </v-alert>

              <v-data-table :headers="headers" :items="items.logins" hide-actions class="elevation-1">
                <template slot="items" slot-scope="props">
                  <td>{{ props.item['User'] }}</td>
                  <td>{{ props.item['Terminal'] }}</td>
                  <td>{{ props.item['Date'] }}</td>
                  <td>{{ props.item['Disconnected'] }}</td>
                  <td>{{ props.item['Duration'] }}</td>
                </template>
                <template slot="no-data"></template>
              </v-data-table>

            </v-flex>
          </v-layout>
        </v-flex>
      </v-layout>
    </v-container>
  </v-content>
</template>

<script>
  import { mapGetters, mapMutations } from 'vuex'
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
      error: '',
      headers: [
        { text: 'User', value: 'User' },
        { text: 'Terminal', value: 'Terminal' },
        { text: 'Date', value: 'Date' },
        { text: 'Disconnected', value: 'Disconnected' },
        { text: 'Duration', value: 'Duration' }
      ],
      items: []
    }),
    mounted: function () {
      this.initialize()
    },
    methods: {
      async initialize () {
        //
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
        //
        const response = await axios.get(this.loggedUser.sub + '/api/server/information/logins')
        this.items = response.data.data
      }
    }
  }
</script>

<style>
  code {
    padding:5px
  }
  code:after, kbd:after, code:before, kbd:before {
    content: "";
    letter-spacing: -1px;
  }
</style>
