<template>
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
              <v-flex tag="h1" class="display-1">
                Servers - Add Server
                <div class="is-pulled-right">
                  <v-btn to="/servers">Back</v-btn>
                  <v-btn color="success" @click="save">Save</v-btn>
                </div>
              </v-flex>
              <v-flex>
                <v-card flat>
                  <v-card-text>
                    <div class="notification is-danger" v-show="form.error.global">
                      <button class="delete" @click="form.error.global=false"></button>
                      <strong>Error: </strong> <span v-html="form.error.global"></span>
                    </div>

                    <div class="notification is-success" v-show="form.success">
                      <button class="delete" @click="form.success=false"></button>
                      Server successfully added.
                    </div>

                    <div class="field">
                      <label class="label">Name</label>
                      <div class="control">
                        <input class="input" :class="{'is-danger': form.error.name }" type="text" v-model="form.value.name" placeholder="Enter the name of the server...">
                      </div>
                      <p class="help is-danger" v-if="form.error.name">{{ form.error.name }}</p>
                    </div>

                    <div class="field">
                      <label class="label">Customer</label>
                      <div class="control">
                        <input class="input" :class="{'is-danger': form.error.customer }" type="text" v-model="form.value.customer" placeholder="Enter the customer of the server...">
                      </div>
                      <p class="help is-danger" v-if="form.error.customer">{{ form.error.customer }}</p>
                    </div>

                    <div class="field">
                      <label class="label">IP Address</label>
                      <div class="control">
                        <input class="input" :class="{'is-danger': form.error.ip }" type="text" v-model="form.value.ip" placeholder="Enter the IP address of the server...">
                      </div>
                      <p class="help is-danger" v-if="form.error.ip">{{ form.error.ip }}</p>
                    </div>

                    <div class="field">
                      <label class="label">Details</label>
                      <div class="control">
                        <textarea :class="{'is-danger': form.error.details }" class="textarea" v-model="form.value.details" placeholder="enter any details about the server..."></textarea>
                      </div>
                      <p class="help is-danger" v-if="form.error.details">{{ form.error.details }}</p>
                    </div>

                    <div class="field is-grouped">
                      <div class="control">
                        <button class="button is-link" @click="save">Submit</button>
                      </div>
                      <div class="control">
                        <button class="button is-text">Cancel</button>
                      </div>
                    </div>
                  </v-card-text>
                </v-card>
              </v-flex>
            </v-layout>
          </v-flex>
        </v-layout>
      </v-container>
    </v-content>
  </v-app>
</template>

<script>
  import { mapGetters } from 'vuex'
  import axios from 'axios'

  const getInitialData = () => {
    return {
      form: {
        success: false,
        error: {
          global: '',
          name: '',
          customer: '',
          ip: '',
          details: ''
        },
        value: {
          name: '',
          customer: '',
          ip: '',
          details: ''
        }
      }
    }
  };

  export default {
    components: {},
    computed: {
      ...mapGetters({
        isAuthenticated: 'auth/isAuthenticated',
        loggedUser: 'auth/loggedUser',
        loggedToken: 'auth/loggedToken',
      })
    },
    /*
    async asyncData() {
      const { data } = await axios.get('https://jsonplaceholder.typicode.com/users')
      return { users: data }
    },
    */
    asyncData() {
      return {
        name: process.static ? 'static' : (process.server ? 'server' : 'client')
      }
    },
    data () {
      return getInitialData()
    },
    mounted: function () {

    },
    methods: {
      save() {
        // set jwt into request header
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
        // post (servers)
        axios.post('https://fatfree-base-rest-cloned-lcherone.c9users.io/servers', this.form.value).then(response => {
          // reset form errors
          this.form = getInitialData().form
          // has errors
          if (response.data.error) {
            for (var key in response.data.error) {
              if (response.data.error.hasOwnProperty(key)) {
                console.log(response.data.error[key])
                this.form.error[key] = response.data.error[key]
              }
            }
          } 
          // all good
          else {
            // reset form success
            this.form = getInitialData().form
            this.form.success = true;
          }
        }).catch(error => {
          if (error.response) {
            if (error.response.status === 401) {
              this.form.error.global = 'Session token expired, please refresh page.'
            } else {
              this.form.error.global = 'Unknown error whilst posting form!'
            }
          } else if (error.request) {
            this.form.error.global = 'A network error has occured, your changes have <b>not</b> been saved.'
          } else {
            this.form.error.global = error.message
          }
        })
      }
    }
  }
</script>

<style>

</style>
