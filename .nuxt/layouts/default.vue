<template>
  <v-app>
    <main-nav/>
    <nuxt/>
    <prompt></prompt>
  </v-app>
</template>

<script>
  import MainNav from '~/components/MainNav.vue'
  
  export default {
    components: {
      MainNav
    }
  }
</script>

<style>
  
</style>
