<template>
  <div></div>
</template>

<script>
  import { unsetToken } from '~/utils/auth'
  export default {
    mounted () {
      unsetToken()
      this.$router.replace('/')
    }
  }
</script>

<style>
  html {
    background: #dddddd;
    height: 100%
  }
</style>