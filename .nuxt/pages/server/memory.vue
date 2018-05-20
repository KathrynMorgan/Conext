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
              
              <v-container grid-list-md text-xs-center>
                <v-layout row wrap>
                  <v-flex xs4>
                    <server-memory></server-memory>
                  </v-flex>
                  <v-flex xs4>
                    <server-memory></server-memory>
                  </v-flex>
                  <v-flex xs4>
                    <server-memory></server-memory>
                  </v-flex>
                </v-layout>
              </v-container>
              
            </v-flex>
          </v-layout>
        </v-flex>
      </v-layout>
    </v-container>
  </v-content>
</template>

<script>
  import { mapGetters, mapMutations } from 'vuex'
  import serverMemory from '~/components/server/memory.vue'
  import axios from 'axios'

  export default {
    middleware: [
      'authenticated'
    ],
    components: {
      serverMemory
    },
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
      
    },
    methods: {
      
    }
  }
</script>

<style>

</style>
