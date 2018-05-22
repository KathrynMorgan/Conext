<template>
  <v-content>
    <v-container fluid tag="section" id="grid">
      <v-layout row wrap>
        <v-flex d-flex xs12 order-xs5>
          <v-layout column>
            <v-flex tag="h1" class="display mb-2">
              Server - Processes
            </v-flex>
            <v-flex>
              <v-alert type="error" :value="error">
                {{ error }}
              </v-alert>

              <v-data-table :headers="headers" :items="items.top" hide-actions class="elevation-1">
                <template slot="items" slot-scope="props">
                  <td>{{ props.item['PID'] }}</td>
                  <td>{{ props.item['USER'] }}</td>
                  <td>{{ props.item['PR'] }}</td>
                  <td>{{ props.item['NI'] }}</td>
                  <td>{{ props.item['VIRT'] }}</td>
                  <td>{{ props.item['RES'] }}</td>
                  <td>{{ props.item['SHR'] }}</td>
                  <td>{{ props.item['S'] }}</td>
                  <td>{{ props.item['%CPU'] }}</td>
                  <td>{{ props.item['%MEM'] }}</td>
                  <td>{{ props.item['TIME+'] }}</td>
                  <td>{{ props.item['COMMAND'] }}</td>
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
        { text: 'PID', value: 'PID' },
        { text: 'USER', value: 'USER' },
        { text: 'PR', value: 'PR' },
        { text: 'NI', value: 'NI' },
        { text: 'VIRT', value: 'VIRT' },
        { text: 'RES', value: 'RES' },
        { text: 'SHR', value: 'SHR' },
        { text: 'S', value: 'S' },
        { text: '%CPU', value: '%CPU' },
        { text: '%MEM', value: '%MEM' },
        { text: 'TIME+', value: 'TIME+' },
        { text: 'COMMAND', value: 'COMMAND' }
      ],
      items: {
        top: []
      }
    }),
    mounted: function () {
      this.initialize()
    },
    methods: {
      async initialize () {
        //
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
        //
        const response = await axios.get(this.loggedUser.sub + '/api/server/information/top')
        this.items = response.data.data
      }
    }
  }
</script>

<style>

</style>
