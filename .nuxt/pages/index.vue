<template>
  <v-content>
    <v-container fluid tag="section" id="grid">
      <v-layout row wrap>
        <v-flex d-flex xs12 order-xs5>
          <v-layout column>
            <v-flex tag="h1" class="display mb-2">
              Server - Overview
            </v-flex>
            <v-flex>
              <v-alert type="error" :value="error">
                {{ error }}
              </v-alert>
              <v-container grid-list-md text-xs-center>
                <v-layout row wrap>
                  <v-flex md4>
                    <server-cpu></server-cpu>
                  </v-flex>
                  <v-flex md4>
                    <server-memory></server-memory>
                  </v-flex>
                  <v-flex md4>
                    <server-disk></server-disk>
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
  import { mapGetters } from 'vuex'
  import axios from 'axios'
  
  import serverCpu from '~/components/server/cpu.vue'
  import serverMemory from '~/components/server/memory.vue'
  import serverDisk from '~/components/server/disk.vue'
  
  export default {
    middleware: 'authenticated',
    computed: {
      ...mapGetters({
        isAuthenticated: 'auth/isAuthenticated',
        loggedUser: 'auth/loggedUser',
        loggedToken: 'auth/loggedToken',
      })
    },
    components: {
      serverCpu, serverMemory, serverDisk
    },
    mounted: function() {},
    data: () => ({
      error: ''
    }),
    methods: {}
  }
</script>

<style>
</style>
