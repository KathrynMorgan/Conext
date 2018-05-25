<template>
  <v-content>
    <v-container fluid tag="section" id="grid">
      <v-layout row wrap>
        <v-flex d-flex xs12 order-xs5>
          <v-layout column>
            <v-flex tag="h1" class="display mb-2">
              Server - Network Connections
            </v-flex>
            <v-flex>
              <v-alert type="error" :value="error">
                {{ error }}
              </v-alert>
              <v-data-table :headers="headers" :items="items" hide-actions class="elevation-1">
                <template slot="items" slot-scope="props">
                  <td>{{ props.item['Proto'] }}</td>
                  <td>{{ props.item['Recv-Q'] }}</td>
                  <td>{{ props.item['Send-Q'] }}</td>
                  <td>{{ props.item['Local Address'] }}</td>
                  <td>{{ props.item['Foreign Address'] }}</td>
                  <td>{{ props.item['State'] }}</td>
                  <td>{{ props.item['PID/Program'] }}</td>
                  <td>{{ props.item['Process Name'] }}</td>
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
      dialog: false,
      headers: [
        { text: 'Proto', align: 'left', value: 'Proto' },
        { text: 'Recv-Q', value: 'Recv-Q' },
        { text: 'Send-Q', value: 'Send-Q' },
        { text: 'Local Address', value: 'Local Address' },
        { text: 'Foreign Address', value: 'Foreign Address' },
        { text: 'State', value: 'State' },
        { text: 'PID/Program', value: 'PID/Program' },
        { text: 'Process Name', value: 'Process Name' }
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
      pollItem: 0
    }),
    beforeDestroy: function() {
      clearInterval(this.pollId);
    },
    mounted: function () {
      this.initialize()
      
      clearInterval(this.pollId);
      this.pollId = setInterval(function () {
        this.initialize()
      }.bind(this), 10000);
    },
    methods: {
      async initialize () {
        // set jwt into request header
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
        //
        const response = await axios.get(this.loggedUser.sub + '/api/server/information/network-connections')
        this.items = response.data.data
      }
    }
  }
</script>

<style>

</style>
