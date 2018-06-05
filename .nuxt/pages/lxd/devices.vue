<template>
  <v-app>
    <v-snackbar top :timeout="snackbarTimeout" :color="snackbarColor" v-model="snackbar">
      {{ snackbarText }}
      <v-btn dark flat @click.native="snackbar = false">Close</v-btn>
    </v-snackbar>
    <v-content>
      <v-container fluid tag="section" id="grid">
        <v-layout row wrap>
          <v-flex d-flex xs12 order-xs5>
            <v-layout column>
              <v-flex tag="h1" class="display mb-2">
                LXD - Devices
                <v-btn color="success" @click="openDialog()" style="float:right">New {{ activeTab }}</v-btn>
              </v-flex>
              <v-flex>
                <v-alert type="error" :value="error">
                  {{ error }}
                </v-alert>
                <v-tabs v-model="activeTab" show-arrows class="elevation-1">
                  <!--<v-tab ripple :href="`#blocker`">Blocker</v-tab>-->
                  <v-tab ripple :href="`#nic`">Nic</v-tab>
                  <v-tab ripple :href="`#disk`">Disk</v-tab>
                  <!--<v-tab ripple :href="`#unix-char`">Unix-char</v-tab>-->
                  <!--<v-tab ripple :href="`#unix-block`">Unix-block</v-tab>-->
                  <!--<v-tab ripple :href="`#usb`">USB</v-tab>-->
                  <!--<v-tab ripple :href="`#gpu`">GPU</v-tab>-->
                  <!--<v-tab ripple :href="`#infiniband`">InfiniBand</v-tab>-->
                  <!--<v-tab ripple :href="`#proxy`">Proxy</v-tab>-->
                  
                  <!--<v-tab-item :id="`blocker`">blocker</v-tab-item>-->
                  <v-tab-item :id="`nic`">
                    <nic @snackbar="setSnackbar" ref="nic"></nic>
                  </v-tab-item>
                  <v-tab-item :id="`disk`">
                    <disk @snackbar="setSnackbar" ref="disk"></disk>
                  </v-tab-item>
                  <!--<v-tab-item :id="`unix-char`">unix-char</v-tab-item>-->
                  <!--<v-tab-item :id="`unix-block`">unix-block</v-tab-item>-->
                  <!--<v-tab-item :id="`usb`">usb</v-tab-item>-->
                  <!--<v-tab-item :id="`gpu`">gpu</v-tab-item>-->
                  <!--<v-tab-item :id="`infiniband`">infiniband</v-tab-item>-->
                  <!--<v-tab-item :id="`proxy`">proxy</v-tab-item>-->
                </v-tabs>
              </v-flex>
            </v-layout>
          </v-flex>
        </v-layout>
      </v-container>
    </v-content>
  </v-app>
</template>

<script>
  // components
  import nic from '~/components/lxd/devices/nic.vue'
  import disk from '~/components/lxd/devices/disk.vue'

  export default {
    components: {
      nic, disk
    },
    data: () => ({
      error: '',
      activeTab: 'nic',
      snackbar: false,
      snackbarColor: 'green',
      snackbarText: '',
      snackbarTimeout: 5000
    }),
    methods: {
      // set snackbar (invoked from components)
      setSnackbar (msg) {
        this.snackbar = true
        this.snackbarTimeout = 2500
        this.snackbarText = msg
      },
      
      // set error (invoked from components)
      setError (msg) {
        this.error = msg
      },
      
      openDialog () {
        this.$refs[this.activeTab].openDialog()
      }
    }
  }
</script>

<style>

</style>
