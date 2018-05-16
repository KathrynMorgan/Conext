<template>
  <v-app>
    <main-nav/>
    <nuxt-child ref="childComponent"/>
  </v-app>
</template>

<script>
  import { mapGetters } from 'vuex'

  import MainNav from '~/components/MainNav.vue'

  export default {
    middleware: 'authenticated',
    components: {
      MainNav
    },
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
    mounted: function () {},
    data () {
      return {}
    },
    methods: {}
  }
</script>

<style>

</style>
