<template>
  <v-app>
    <nuxt-child ref="childComponent"/>
  </v-app>
</template>

<script>
  import { mapGetters } from 'vuex'

  export default {
    components: {},
    computed: {
      ...mapGetters({
        isAuthenticated: 'auth/isAuthenticated',
        loggedUser: 'auth/loggedUser',
        loggedToken: 'auth/loggedToken',
      })
    },
    mounted: function () {},
    data: () => ({}),
    methods: {}
  }
</script>

<style>

</style>
