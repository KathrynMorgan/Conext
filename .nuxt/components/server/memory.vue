<template>
  <div>
    <v-card height="315px">
      <v-card-title class="blue-grey lighten-2">
        <span class="headline white--text">Memory</span>
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
            <div class="control">
              <div class="tags has-addons">
                <span class="tag is-dark">Total</span>
                <span class="tag is-primary">{{ memory_total }}</span>
              </div>
            </div>
          </div>
        </div>
        <v-divider></v-divider>
        <v-card-text>
          <v-progress-circular :size="150" :width="25" :rotate="-90" :value="memory_percent" color="pink">
            <span>{{ memory_percent }}%</span><br>
            {{ memory_used }}
          </v-progress-circular>
        </v-card-text>
      </v-card-text>
    </v-card>

    <!--Details Dialog -->
    <v-dialog v-model="dialog">
      <v-card>
        <v-card-title class="headline">Disks</v-card-title>
        <v-card-text>
          <v-data-table :headers="headers" :items="items.disks" hide-actions>
            <template slot="items" slot-scope="props">
              <td>{{ props.item['Filesystem'] }}</td>
              <td>{{ props.item['Type'] }}</td>
              <td>{{ props.item['Size'] }}</td>
              <td>{{ props.item['Used'] }}</td>
              <td>{{ props.item['Avail'] }}</td>
              <td>{{ props.item['Used (%)'] }}</td>
              <td>{{ props.item['Mounted'] }}</td>
            </template>
            <template slot="no-data"></template>
          </v-data-table>
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
      memory_percent: function (){
        if (!this.items.memory_stats) {
          return 0
        }
        return 100 - this.items.memory_stats.free;
      },
      memory_total: function (){
        if (!this.items.memory_stats) {
          return '0 Bytes'
        }
        return this.formatBytes(this.items.memory_total * 1024);
      },
      memory_used: function (){
        if (!this.items.memory_stats) {
          return '0 Bytes'
        }
        return this.formatBytes((100 - this.items.memory_stats.free) / 100 * (this.items.memory_total * 1024));
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
        //{ title: 'Details', action: 'details' }
      ],
      items: []
    }),
    mounted: function () {
      this.initialize()
    },
    methods: {
      initialize () {
        //
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
        //
        axios.get(this.loggedUser.sub + '/api/server/information/memory').then(response => {
          this.items = response.data.data
        })
          .catch(error => {
          this.items = []
        })
      },
      menuAction(item) {
        if (item.action && item.action === 'refresh') {
          this.initialize()
        }
        if (item.action && item.action === 'details') {
          this.dialog = true
        }
      },
      formatBytes (bytes, decimals) {
        if(bytes == 0) return '0 Bytes';
        var k = 1024,
            dm = decimals || 2,
            sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
            i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
      }
    }
  }
</script>

<style>
  .progress-circular__info span {
    font-weight: bold;
  }
</style>
