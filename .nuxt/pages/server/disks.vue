<template>
  <v-content>
    <v-container fluid tag="section" id="grid">
      <v-layout row wrap>
        <v-flex d-flex xs12 order-xs5>
          <v-layout column>
            <v-flex tag="h1" class="display-1 mb-3">
              Server - Disks
            </v-flex>
            <v-flex>
              <v-alert type="error" :value="error">
                {{ error }}
              </v-alert>

              <p>Free Disk Space: {{ items.disk_space }}%</p>
              <p>Total Disk Space: {{ items.total_disk_space }} bytes</p>

              <v-data-table :headers="headers" :items="items.disks" hide-actions class="elevation-1">
                <template slot="items" slot-scope="props">
                  <td>{{ props.item['Filesystem'] }}</td>
                  <td>{{ props.item['Type'] }}</td>
                  <td>{{ props.item['Size'] }}</td>
                  <td>{{ props.item['Used'] }}</td>
                  <td>{{ props.item['Avail'] }}</td>
                  <td>{{ props.item['Used (%)'] }}</td>
                  <td>{{ props.item['Mounted'] }}</td>
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
        { text: 'Filesystem', value: 'Filesystem' },
        { text: 'Type', value: 'Type' },
        { text: 'Size', value: 'Size' },
        { text: 'Used', value: 'Used' },
        { text: 'Avail', value: 'Avail' },
        { text: 'Used (%)', value: 'Used (%)' },
        { text: 'Mounted', value: 'Mounted' }
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
        const response = await axios.get(this.loggedUser.sub + '/api/server/information/disks')
        this.items = response.data.data
      }
    }
  }
</script>

<style>

</style>
