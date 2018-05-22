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
              <!--
                  <pre>app rendered {{ rendered }} side</pre>
              <pre>{{ loggedUser }}</pre>
              <pre>{{ loggedToken }}</pre>
              <pre>isAuthenticated = {{ isAuthenticated }}</pre>
              <pre>{{ result }}</pre>
              -->
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
    mounted: function() {
      this.initialize()
    },
    asyncData() {
      return {
        rendered: process.static ? 'static' : (process.server ? 'server' : 'client')
      }
    },
    data: () => ({
      error: '',
      result: []
    }),
    methods: {
      //
      async initialize() {
        /*
        // set
        this.$storage.set("xxx", {foo:'bar'})
        
        // get
        console.log(this.$storage.get("xxx"));
        console.log(this.$storage.isset("xxx"));
        
        // remove
        this.$storage.remove("xxx")
        
        this.$storage.clear()
        */
      }
    }
  }
</script>

<style>
</style>
