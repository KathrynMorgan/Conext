<template>
  <v-content>
    <v-container fluid tag="section" id="grid">
      <v-layout row wrap>
        <v-flex d-flex xs12 order-xs5>
          <v-layout column>
            <v-flex tag="h1" class="display mb-2">
              About
            </v-flex>
            <v-flex>
              <v-alert type="error" :value="error">
                {{ error }}
              </v-alert>

              This project is a decentralized LXD server control panel, so you only need a single instance which can be hosted anywhere.

              <h2 class="mt-1">Features</h2>
              
              <p>Here are some of the features of the panel:</p>
              
              <h3>SPA control panel</h3>
              <p>
                The control panel and the rest server agent can be installed together on a single server, or on its own if you have multiple servers. 
                You are not tied to logging into multiple panels on different servers as all communications between the panel and the server are done 
                over rest with JWT authentication. The panel can be served as a static SPA (Single Page App) on staic host, or with SSR (Server Side Rendering) through nuxt.
              </p>

              <h3>Server Information:</h3>
              <p>Access server information like CPU, memory, disk, processes, network connections and logins.</p>
              
              <h3>Server Management:</h3>
              <p>Manage the server from rebooting, clearing memory cache and much more, extendable to suit your needs with custom tasks.</p>
              
              <h3>APIs:</h3>
              <p>Build custom restful API endpoints, which can be used by your applications with full access to the underlying system and the fat-free framework.</p>
              
              <h3>LXD:</h3>
              <p>Easily manage all aspects of the LXD server, including containers, certificates, images, profiles, networks, files, logs, metadata and operations.</p>
              
              <h3>Routes:</h3>
              <p>You can also manage web and port forwarding, simply add a domain and set where it should be reverse proxied to, including multiple upstream's and Let's Encrypt Certificates.</p>
              
              <h3>Tasks:</h3>
              <p>Tasks allow you to run custom or predefined system tasks on the server once or at specific intervals. Which is great for maintenance tasks or deployment triggers like git pulling.</p>

            </v-flex>
          </v-layout>
        </v-flex>
      </v-layout>
    </v-container>
  </v-content>
</template>

<script>
  import { mapGetters } from 'vuex'

  export default {
    computed: {
      ...mapGetters({
        isAuthenticated: 'auth/isAuthenticated',
        loggedUser: 'auth/loggedUser',
        loggedToken: 'auth/loggedToken',
      })
    },
    components: {},
    mounted: function() {
      this.initialize()
    },
    data: () => ({
      error: ''
    }),
    methods: {
      initialize() {}
    }
  }
</script>

<style>
</style>
