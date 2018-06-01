<template>
  <div>
    <v-navigation-drawer dark fixed :clipped="$vuetify.breakpoint.lgAndUp" app v-model="drawer">
      <v-list dense>
        <template v-for="item in items">
          <v-list-group v-if="item.children" v-model="item.model" :key="item.text" :prepend-icon="item.model ? item.icon : item['icon-alt']" append-icon="">
            <v-list-tile slot="activator">
              <v-list-tile-content>
                <v-list-tile-title>
                  {{ item.text }}
                </v-list-tile-title>
              </v-list-tile-content>
            </v-list-tile>
            <v-list-tile v-for="(child, i) in item.children" :key="i" @click="navigate(child.route)">
              <v-list-tile-action v-if="child.icon">
                <v-icon>{{ child.icon }}</v-icon>
              </v-list-tile-action>
              <v-list-tile-content>
                <v-list-tile-title>
                  {{ child.text }}
                </v-list-tile-title>
              </v-list-tile-content>
            </v-list-tile>
          </v-list-group>
          <v-list-tile v-else @click="navigate(item.route)" :key="item.text">
            <v-list-tile-action>
              <v-icon>{{ item.icon }}</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
              <v-list-tile-title>
                {{ item.text }}
              </v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
        </template>
      </v-list>
    </v-navigation-drawer>
    <v-toolbar color="light-blue darken-4" dark app :clipped-left="$vuetify.breakpoint.lgAndUp" fixed>
      <v-toolbar-title style="width: 300px" class="ml-0 pl-3">
        <v-toolbar-side-icon @click.stop="drawer = !drawer"></v-toolbar-side-icon>
        <span class="hidden-sm-and-down">{{ loggedUser && loggedUser.lab ? loggedUser.lab : 'Conext' }}</span>
      </v-toolbar-title>
      <v-spacer></v-spacer>
      <span class="mr-3">{{ loggedUser && loggedUser.sub }}</span>
    </v-toolbar>
  </div>
</template>

<script>
  import { mapGetters, mapMutations } from 'vuex'

  export default {
    middleware: [],
    computed: {
      ...mapGetters({
        isAuthenticated: 'auth/isAuthenticated',
        loggedUser: 'auth/loggedUser',
        loggedToken: 'auth/loggedToken'
      }),
      items () {
        //
        var modules = [];
        var items = [
          { icon: 'chevron_right', text: 'My Servers', route: '/servers' }
        ];

        // if not logged in
        if (!this.loggedUser || !this.loggedUser.sub) {
          //items.push({ icon: 'chevron_right', text: 'About', route: '/about' });
          return items
        }

        // server
        if (this.loggedUser.mod.server.constructor === Array) {
          // add child/module menu links
          modules.push({ icon: 'chevron_right', text: 'Overview', route: '/' })
          //
          if (this.loggedUser.mod.server.includes('network-connections')) {
            modules.push({ icon: 'chevron_right', text: 'Network Connections', route: '/server/network-connections' })
          }
          //
          if (this.loggedUser.mod.server.includes('processes')) {
            modules.push({ icon: 'chevron_right', text: 'Processes', route: '/server/processes' })
          }
          //
          if (this.loggedUser.mod.server.includes('logins')) {
            modules.push({ icon: 'chevron_right', text: 'Logins', route: '/server/logins' })
          }

          items.push({
            icon: 'keyboard_arrow_up',
            'icon-alt': 'keyboard_arrow_down',
            text: 'Server',
            model: false,
            children: modules
          });
          
          modules = [];
        }

        // api
        if (this.loggedUser.mod.api.constructor === Array) {
          // add child/module menu links
          if (this.loggedUser.mod.api.includes('data')) {
            modules.push({ icon: 'chevron_right', text: 'Data', route: '/api/data' })
          }
          //
          if (this.loggedUser.mod.api.includes('email')) {
            modules.push({ icon: 'chevron_right', text: 'Email', route: '/api/email' })
          }
          
          items.push({
            icon: 'keyboard_arrow_up',
            'icon-alt': 'keyboard_arrow_down',
            text: 'API',
            model: false,
            children: modules
          })
          
          modules = [];
        }

        // lxd
        if (this.loggedUser.mod.lxd.constructor === Array) {
          // add child/module menu links
          if (this.loggedUser.mod.lxd.includes('containers')) {
            modules.push({ icon: 'chevron_right', text: 'Containers', route: '/lxd/containers' })
          }
          //
          if (this.loggedUser.mod.lxd.includes('certificates')) {
            modules.push({ icon: 'chevron_right', text: 'Certificates', route: '/lxd/certificates' })
          }
          //
          if (this.loggedUser.mod.lxd.includes('images')) {
            modules.push({ icon: 'chevron_right', text: 'Images', route: '/lxd/images' })
          }
          //
          if (this.loggedUser.mod.lxd.includes('profiles')) {
            modules.push({ icon: 'chevron_right', text: 'Profiles', route: '/lxd/profiles' })
          }
          //
          if (this.loggedUser.mod.lxd.includes('networks')) {
            modules.push({ icon: 'chevron_right', text: 'Networks', route: '/lxd/networks' })
          }
          //
          if (this.loggedUser.mod.lxd.includes('files')) {
            modules.push({ icon: 'chevron_right', text: 'Files', route: '/lxd/files' })
          }
          //
          if (this.loggedUser.mod.lxd.includes('logs')) {
            modules.push({ icon: 'chevron_right', text: 'Logs', route: '/lxd/logs' })
          }
          //
          if (this.loggedUser.mod.lxd.includes('metadata')) {
            modules.push({ icon: 'chevron_right', text: 'Metadata', route: '/lxd/metadata' })
          }
          //
          if (this.loggedUser.mod.lxd.includes('operations')) {
            modules.push({ icon: 'chevron_right', text: 'Operations', route: '/lxd/operations' })
          }
          //
          if (this.loggedUser.mod.lxd.includes('backups')) {
            modules.push({ icon: 'chevron_right', text: 'Backups', route: '/lxd/backups' })
          }
          //
          if (this.loggedUser.mod.lxd.includes('cluster')) {
            modules.push({ icon: 'chevron_right', text: 'Cluster', route: '/lxd/cluster' })
          }

          items.push({
            icon: 'keyboard_arrow_up',
            'icon-alt': 'keyboard_arrow_down',
            text: 'LXD',
            model: false,
            children: modules
          })
          
          modules = [];
        }

        // routes
        if (this.loggedUser.mod.routes.constructor === Array) {
          // add child/module menu links
          if (this.loggedUser.mod.routes.includes('web')) {
            modules.push({ icon: 'chevron_right', text: 'Web Forwards', route: '/routes/web-forwards' })
          }
          //
          if (this.loggedUser.mod.routes.includes('port')) {
            modules.push({ icon: 'chevron_right', text: 'Port Forwards', route: '/routes/port-forwards' })
          }
          
          items.push({
            icon: 'keyboard_arrow_up',
            'icon-alt': 'keyboard_arrow_down',
            text: 'Routes',
            model: false,
            children: modules
          })
          
          modules = [];
        }

        // tasks
        if (this.loggedUser.mod.tasks.constructor === Array) {
          items.push({ icon: 'chevron_right', text: 'Tasks', route: '/tasks' })
        }
        
        items.push({ icon: 'exit_to_app', text: 'Sign Out', route: '/auth/sign-out' });
        
        return items
      }
    },
    components: { },
    data: () => ({
      drawer: null
    }),
    methods: {
      navigate(route) {
        this.$router.push(route)
      }
    }
  }
</script>

<style>

</style>
