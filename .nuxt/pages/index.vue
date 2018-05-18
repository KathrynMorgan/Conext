<template>
  <div>
    <v-app>
      <v-content>
        <v-container
                     fluid
                     grid-list-sm
                     tag="section"
                     id="grid"
                     >
          <v-layout row wrap>
            <v-flex d-flex xs12 order-xs5>
              <v-layout column>
                <v-flex tag="h1" class="display-1">Hello World</v-flex>
                <v-flex>
                  <v-card flat>
                    <v-card-text>
                      <pre>app rendered {{ rendered }} side</pre>
                      <pre>{{ loggedUser }}</pre>
                      <pre>{{ loggedToken }}</pre>
                      <pre>isAuthenticated = {{ isAuthenticated }}</pre>
                      <pre>{{ result }}</pre>
                    </v-card-text>
                    
                    <pre>{{ foo }}</pre>

                  </v-card>
                </v-flex>
              </v-layout>
            </v-flex>
          </v-layout>

        </v-container>
      </v-content>
    </v-app>
  </div>
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
    mounted: function () {
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
      result: []
    }),
    methods: {
      async getApi () {
        // set jwt into request header
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
        //
        const response = await axios.get(this.loggedUser.sub + '/api')
        this.result = response.data.data
      },
      async remove (id) {
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
