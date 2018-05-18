<template>
  <div>
    <v-navigation-drawer dark
                         fixed
                         :clipped="$vuetify.breakpoint.lgAndUp"
                         app
                         v-model="drawer"
                         >
      <v-list dense>
        <template v-for="item in items">

          <v-list-group
                        v-if="item.children"
                        v-model="item.model"
                        :key="item.text"
                        :prepend-icon="item.model ? item.icon : item['icon-alt']"
                        append-icon=""
                        >
            <v-list-tile slot="activator">
              <v-list-tile-content>
                <v-list-tile-title>
                  {{ item.text }}
                </v-list-tile-title>
              </v-list-tile-content>
            </v-list-tile>
            <v-list-tile
                         v-for="(child, i) in item.children"
                         :key="i"
                         @click="navigate(child.route)"
                         >
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
    <v-toolbar
               color="deep-orange accent-4"
               dark
               app
               :clipped-left="$vuetify.breakpoint.lgAndUp"
               fixed
               >
      <v-toolbar-title style="width: 300px" class="ml-0 pl-3">
        <v-toolbar-side-icon @click.stop="drawer = !drawer"></v-toolbar-side-icon>
        <span class="hidden-sm-and-down">LXDui</span>
      </v-toolbar-title>
      <!--
<v-text-field
flat
solo-inverted
prepend-icon="search"
label="Search"
class="hidden-sm-and-down"
></v-text-field>
-->
      <v-spacer></v-spacer>
      <span class="mr-3">{{ loggedUser && loggedUser.sub }}</span>

      <!--
<v-btn icon>
<v-icon>apps</v-icon>
</v-btn>
<v-btn icon>
<v-icon>notifications</v-icon>
</v-btn>
<v-btn icon large>
<v-avatar size="32px" tile>
<img
src="https://vuetifyjs.com/static/doc-images/logo.svg"
alt="Vuetify"
>
</v-avatar>
</v-btn>
-->
    </v-toolbar>
  </div>
</template>

<script>
  //
  //const store = require('../../store/store')
  import { mapGetters, mapMutations } from 'vuex'

  export default {
    middleware: [
      //'authenticated'
    ],
    computed: {
      ...mapGetters({
        isAuthenticated: 'auth/isAuthenticated',
        loggedUser: 'auth/loggedUser',
        loggedToken: 'auth/loggedToken'
      }),
      items () {
        return (this.loggedUser && this.loggedUser.sub) ? [
          { icon: 'chevron_right', text: 'My Servers', route: '/servers' },
          {
            icon: 'keyboard_arrow_up',
            'icon-alt': 'keyboard_arrow_down',
            text: 'Server',
            model: false,
            children: [
              { icon: 'chevron_right', text: 'Memory', route: '/server/memory' },
              { icon: 'chevron_right', text: 'Disks', route: '/server/disks' },
              { icon: 'chevron_right', text: 'Network Connections', route: '/server/network-connections' },
              { icon: 'chevron_right', text: 'Process Tree', route: '/server/process-tree' },
              { icon: 'chevron_right', text: 'Top', route: '/server/top' },
              { icon: 'chevron_right', text: 'Logins', route: '/server/logins' },
              { icon: 'chevron_right', text: 'CPU Information', route: '/server/cpu-information' },
            ]
          },
          {
            icon: 'keyboard_arrow_up',
            'icon-alt': 'keyboard_arrow_down',
            text: 'API',
            model: false,
            children: [
              { icon: 'chevron_right', text: 'Data', route: '/api/data' },
            ]
          },
          {
            icon: 'keyboard_arrow_up',
            'icon-alt': 'keyboard_arrow_down',
            text: 'LXD',
            model: false,
            children: [
              { icon: 'chevron_right', text: 'Containers', route: '/lxd/containers' },
              { icon: 'chevron_right', text: 'Certificates', route: '/lxd/certificates' },
              { icon: 'chevron_right', text: 'Images', route: '/lxd/images' },
              { icon: 'chevron_right', text: 'Profiles', route: '/lxd/profiles' },
              { icon: 'chevron_right', text: 'Networks', route: '/lxd/networks' },
              { icon: 'chevron_right', text: 'Files', route: '/lxd/files' },
              { icon: 'chevron_right', text: 'Logs', route: '/lxd/logs' },
              { icon: 'chevron_right', text: 'Metadata', route: '/lxd/metadata' },
              { icon: 'chevron_right', text: 'Operations', route: '/lxd/operations' },
            ]
          },
          {
            icon: 'keyboard_arrow_up',
            'icon-alt': 'keyboard_arrow_down',
            text: 'Routes',
            model: false,
            children: [
              { icon: 'chevron_right', text: 'Port Forwards', route: '/routes/port-forwards' },
              { icon: 'chevron_right', text: 'Web Forwards', route: '/routes/web-forwards' }
            ]
          },
          { icon: 'chevron_right', text: 'Tasks', route: '/tasks' },
          { icon: 'exit_to_app', text: 'Sign Out', route: '/auth/sign-out' }
          /*
          { icon: 'history', text: 'Frequently contacted' },
          { icon: 'content_copy', text: 'Duplicates' },
          {
            icon: 'keyboard_arrow_up',
            'icon-alt': 'keyboard_arrow_down',
            text: 'Labels',
            model: true,
            children: [
              { icon: 'add', text: 'Create label' }
            ]
          },
          {
            icon: 'keyboard_arrow_up',
            'icon-alt': 'keyboard_arrow_down',
            text: 'More',
            model: false,
            children: [
              { text: 'Import' },
              { text: 'Export' },
              { text: 'Print' },
              { text: 'Undo changes' },
              { text: 'Other contacts' }
            ]
          },
          { icon: 'settings', text: 'Settings' },
          { icon: 'chat_bubble', text: 'Send feedback' },
          { icon: 'help', text: 'Help' },
          { icon: 'phonelink', text: 'App downloads' },
          { icon: 'keyboard', text: 'Go to the old version' }
          */
        ] : [
          { icon: 'chevron_right', text: 'My Servers', route: '/servers' },
          { icon: 'chevron_right', text: 'About', route: '/about' },
        ]
      }
    },
    components: { },
    //props: [],
    data: () => ({
      dialog: false,
      drawer: null
    }),
    props: {
      source: String
    },
    // mounted: function () {}
    methods: {
      navigate(route) {
        this.$router.push(route)
      }
    }
  }
</script>

<style>

</style>
