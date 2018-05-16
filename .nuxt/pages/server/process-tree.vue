<template>
  <v-content>
    <v-container fluid tag="section" id="grid">
      <v-layout row wrap>
        <v-flex d-flex xs12 order-xs5>
          <v-layout column>
            <v-flex tag="h1" class="display-1 mb-3">
              Server - Process Tree
            </v-flex>
            <v-flex>
              <v-alert type="error" :value="error">
                {{ error }}
              </v-alert>

              <pre><code>{{ items.pstree }}</code></pre>

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
      items: {
        cpuinfo: '',
        server_cpu_usage: 0
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
        const response = await axios.get(this.loggedUser.sub + '/api/server/information/process-tree')
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
