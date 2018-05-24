<template>
  <div>
    <v-alert type="error" :value="error">
      {{ error }}
    </v-alert>
    <v-data-table :headers="headers" :items="items" hide-actions :loading="tableLoading">
      <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
      <template slot="items" slot-scope="props">
        <tr>
          <td>{{ props.item.name }}</td>
          <td>{{ new Date(props.item.created_at).toLocaleString() }}</td>
          <td>
            <v-tooltip left>
              <v-btn slot="activator" icon class="mx-0" style="float:right" @click.stop="deleteSnapshot(props.item)">
                <v-icon color="pink">delete</v-icon>
              </v-btn>
              <span>Delete</span>
            </v-tooltip>
            <v-tooltip left>
              <v-btn slot="activator" icon class="mx-0" style="float:right" @click.stop="restoreSnapshot(props.item)">
                <v-icon color="blue">restore</v-icon>
              </v-btn>
              <span>Restore</span>
            </v-tooltip>
          </td>
        </tr>
      </template>
      <template slot="no-data">
        {{ tableLoading ? 'Fetching data, please wait...' : 'Currently there are no snapshots for this container.' }}
      </template>
    </v-data-table>
  </div>
</template>

<script>
  import { mapGetters } from 'vuex'
  import axios from 'axios'

  export default {
    components: {},
    props: ['item'],
    computed: {
      ...mapGetters({
        loggedUser: 'auth/loggedUser',
        loggedToken: 'auth/loggedToken'
      })
    },
    data: () => ({
      // global error
      error: '',
      container: {},
      dialog: false,
      tableLoading: true,
      headers: [
        { text: 'Name', value: 'name' },
        { text: 'Created', value: 'created' },
        { text: 'Actions', value: 'name', sortable: false, align: 'right' }
      ],
      //
      items: []
    }),
    beforeDestroy: function() {},
    mounted: function () {
      this.container = Object.assign({info:{name:''}}, this.item)
      
      this.initialize()
    },
    methods: {
      async initialize () {
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
          //
          const response = await axios.get(this.loggedUser.sub + '/api/lxd/containers/' + this.container.info.name + '/snapshots')
          this.items = response.data.data
        } catch (error) {
          this.error = 'Could not fetch data from server.';
        }
        this.tableLoading = false
      },
      
      async deleteSnapshot (item) {
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }
          
          // delete local
          const index = this.items.indexOf(item)
          this.items.splice(index, 1)

          axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
          //
          const response = await axios.delete(this.loggedUser.sub + '/api/lxd/containers/' + this.container.info.name + '/snapshots/'+item.name.substr(item.name.lastIndexOf('/') + 1))
          this.$emit('snackbar', 'Snapshot deleted.')
        } catch (error) {
          this.$emit('snackbar', 'Failed to delete snapshot.')
        }
      },

      async restoreSnapshot (item) {
        try {
          if (!this.loggedUser) {
            this.$router.replace('/servers')
          }

          axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.loggedToken
          //
          const response = await axios.put(this.loggedUser.sub + '/api/lxd/containers/' + this.container.info.name + '/snapshots', {
            name: item.name.substr(item.name.lastIndexOf('/') + 1)
          })
          this.$emit('snackbar', 'Snapshot restored.')
        } catch (error) {
          this.$emit('snackbar', 'Failed to restore snapshot.')
        }
      }
    }
  }
</script>

<style>

</style>
