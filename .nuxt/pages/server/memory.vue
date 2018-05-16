<template>
  <v-content>
    <v-container fluid tag="section" id="grid">
      <v-layout row wrap>
        <v-flex d-flex xs12 order-xs5>
          <v-layout column>
            <v-flex tag="h1" class="display-1 mb-3">
              Server - Memory
            </v-flex>
            <v-flex>
              <v-alert type="error" :value="error">
                {{ error }}
              </v-alert>
              
              <p>
                Used: {{ items.memory_stats && items.memory_stats.used }}%
                Cache: {{ items.memory_stats && items.memory_stats.cache }}%
                Free: {{ items.memory_stats &&  items.memory_stats.free }}%
              </p>
              
              <p>Total: {{ items.memory_total }}</p>

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
        const response = await axios.get(this.loggedUser.sub + '/api/server/information/memory')
        this.items = response.data.data
      }
    }
  }
</script>

<style>

</style>
