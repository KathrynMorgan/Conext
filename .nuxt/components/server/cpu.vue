<template>
  <div>

    <v-card height="315px">
      <v-card-title class="blue-grey lighten-2">
        <span class="headline white--text">CPU</span>
        <span class="ml-2 grey--text text--darken-4"></span>
        <v-spacer></v-spacer>
        <v-menu bottom left>
          <v-btn slot="activator" icon dark>
            <v-icon>more_vert</v-icon>
          </v-btn>
          <v-list>
            <v-list-tile v-for="(item, i) in actions" :key="i" @click="menuAction(item)">
              <v-list-tile-title>{{ item.title }}</v-list-tile-title>
            </v-list-tile>
          </v-list>
        </v-menu>
      </v-card-title>
      <v-card-text>
        <div class="field is-grouped is-grouped-multiline" style="display:flex">
          <div class="control">
            <strong>Load</strong>
          </div>
          <div class="control">
            <div class="tags has-addons">
              <span class="tag is-dark">1m</span>
              <span class="tag is-primary">{{ items.load['1m'] }}</span>
            </div>
          </div>
          <div class="control">
            <div class="tags has-addons">
              <span class="tag is-dark">5m</span>
              <span class="tag is-primary">{{ items.load['5m'] }}</span>
            </div>
          </div>
          <div class="control">
            <div class="tags has-addons">
              <span class="tag is-dark">15m</span>
              <span class="tag is-primary">{{ items.load['15m'] }}</span>
            </div>
          </div>
        </div>
        <v-divider></v-divider>
        <v-card-text>
          <v-progress-circular :size="150" :width="25" :rotate="-90" :value="cpu_usage" color="pink">
            <span>{{ cpu_usage }}%</span>
          </v-progress-circular>
        </v-card-text>
      </v-card-text>
    </v-card>
    <!--Details Dialog -->
    <v-dialog v-model="dialog" max-width="550px">
      <v-card>
        <v-card-title class="headline">CPU Information</v-card-title>
        <v-card-text>
          <table class="table">
            <tbody>
              <tr v-for="(value, key) in items.cpu_info" :key="key">
                <td style="width:40%"><strong>{{ key }}</strong></td>
                <td>{{ value }}</td>
              </tr>
            </tbody>
          </table>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="green darken-1" flat="flat" @click.native="dialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
  import { mapGetters } from 'vuex'
  import axios from 'axios'

  export default {
    components: {},
    props: {},
    computed: {
      ...mapGetters({
        loggedUser: 'auth/loggedUser',
        loggedToken: 'auth/loggedToken'
      }),
      cpu_usage: function (){
        if (!this.items.cpu_usage) {
          return 0
        }
        return Number(this.items.cpu_usage);
      }
    },
    data: () => ({
      dialog: false,
      headers: [
        { text: 'Filesystem', value: 'Filesystem' },
        { text: 'Type', value: 'Type' },
        { text: 'Size', value: 'Size' },
        { text: 'Used', value: 'Used' },
        { text: 'Avail', value: 'Avail' },
        { text: 'Used (%)', value: 'Used (%)' },
        { text: 'Mounted', value: 'Mounted' }
      ],
      //
      actions: [
        { title: 'Refresh', action: 'refresh' },
        { title: 'Details', action: 'details' }
      ],
      items: {
        load : {},
        cpu_info: '',
        cpu_usage: 0
      }
    }),
    mounted: function () {
      this.initialize()
    },
    methods: {
      initialize () {
        //
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
        //
        axios.get(this.loggedUser.sub + '/api/server/information/cpu').then(response => {
          this.items = response.data.data
        })
          .catch(error => {
          this.items = {
            load : {},
            cpu_info: '',
            cpu_usage: 0
          }
        })
      },
      menuAction(item) {
        if (item.action && item.action === 'refresh') {
          this.initialize()
        }
        if (item.action && item.action === 'details') {
          this.dialog = true
        }
      }
    }
  }
</script>

<style>
  .progress-circular__info span {
    font-weight: bold;
  }

  .progress-circular__info {
    text-align:center;
  }

  code {
    padding:5px
  }

  code:after, kbd:after, code:before, kbd:before {
    content: "";
    letter-spacing: -1px;
  }

  .field.is-grouped.is-grouped-multiline:last-child {
    margin-bottom: -.75rem;
  }

  .field.is-grouped.is-grouped-multiline {
    flex-wrap: wrap;
  }
  .field.is-grouped {
    display: flex;
    justify-content: flex-start;
  }

  .field.is-grouped.is-grouped-multiline>.control:last-child, .field.is-grouped.is-grouped-multiline>.control:not(:last-child) {
    margin-bottom: .75rem;
  }

  .field.is-grouped>.control:not(:last-child) {
    margin-bottom: 0;
    margin-right: .75rem;
  }
  .field.is-grouped>.control {
    flex-shrink: 0;
  }
  .control {
    font-size: 1rem;
    position: relative;
    text-align: left;
  }

  .tags:last-child {
    margin-bottom: -.5rem;
  }

  .tags {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start;
  }

  .tags.has-addons .tag:not(:last-child) {
    border-bottom-right-radius: 0;
    border-top-right-radius: 0;
  }

  .tags.has-addons .tag {
    margin-right: 0;
  }

  .tags .tag:not(:last-child) {
    margin-right: .5rem;
  }

  .tag:not(body).is-dark {
    background-color: #363636;
    color: #f5f5f5;
  }

  .tag:not(body).is-info {
    background-color: #209cee;
    color: #fff;
  }

  .tag:not(body).is-light {
    background-color: #f5f5f5;
    color: #363636;
  }

  .tag:not(body).is-white {
    background-color: #fff;
    color: #0a0a0a;
  }

  .tag:not(body).is-link {
    background-color: #3273dc;
    color: #fff;
  }

  .tag:not(body).is-info {
    background-color: #209cee;
    color: #fff;
  }

  .tag:not(body).is-success {
    background-color: #23d160;
    color: #fff;
  }

  .tag:not(body).is-warning {
    background-color: #ffdd57;
    color: rgba(0,0,0,.7);
  }

  .tag:not(body).is-danger {
    background-color: #ff3860;
    color: #fff;
  }

  .tags .tag {
    margin-bottom: .5rem;
  }

  .tags.has-addons .tag:not(:first-child) {
    border-bottom-left-radius: 0;
    border-top-left-radius: 0;
  }

  .tags.has-addons .tag {
    margin-right: 0;
  }

  .tag:not(body) {
    align-items: center;
    background-color: #f5f5f5;
    border-radius: 4px;
    color: #4a4a4a;
    display: inline-flex;
    font-size: .75rem;
    height: 2em;
    justify-content: center;
    line-height: 1.5;
    padding-left: .75em;
    padding-right: .75em;
    white-space: nowrap;
  }

</style>
