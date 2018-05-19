<template>
  <v-app>
    <v-content>
      <v-container fluid tag="section" id="grid">
        <v-layout row wrap>
          
          <v-flex tag="h1" class="display-1 mb-3">
                Server - Memory
          </v-flex>
          <v-flex>
            <v-alert type="error" :value="error">
              {{ error }}
            </v-alert>
                
           
            <pre>app rendered {{ rendered }} side</pre>
            <pre>{{ loggedUser }}</pre>
            <pre>{{ loggedToken }}</pre>
            <pre>isAuthenticated = {{ isAuthenticated }}</pre>
            <pre>{{ result }}</pre>

          </v-flex>
        </v-layout>
      </v-container>
    </v-content>
  </v-app>
</template>

<script>
  import { mapGetters } from 'vuex'
  import axios from 'axios'
  
  export default {
    middleware: 'authenticated',
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
      this.getApi()
    },
    /*
    async asyncData() {
      const { data } = await axios.get('https://jsonplaceholder.typicode.com/users')
      return { users: data }
    },
    */
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
        
        // set
        this.$storage.set("xxx", {foo:'bar'})
        
        // get
        console.log(this.$storage.get("xxx"));
        console.log(this.$storage.isset("xxx"));
        
        // remove
        this.$storage.remove("xxx")
        
        this.$storage.clear()
        
        
        //
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
        //
        const response = await axios.get(this.loggedUser.sub + '/api/server/information/memory')
        this.items = response.data.data
      },
      //
      async getApi() {
        // set jwt into request header
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
        //
        const response = await axios.get(this.loggedUser.sub + '/api')
        this.result = response.data.data
      },
      //
      async remove(id) {
        // set jwt into request header
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
        // delete (server)
        const response = await axios.delete('https://fatfree-base-rest-cloned-lcherone.c9users.io/servers/' + id)
        this.getServers()
      }
    }
  }
</script>

<style>
</style>
