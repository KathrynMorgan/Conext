<template>
  <v-app>
    <nuxt-child ref="childComponent"/>
  </v-app>
</template>

<script>
  import { mapGetters } from 'vuex'

  export default {
    middleware: 'authenticated',
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
    mounted: function () {},
    data () {
      return {}
    },
    methods: {}
  }
</script>

<style>

</style>
