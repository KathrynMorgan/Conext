<template>
  <v-layout row justify-center>
    <v-dialog v-model="dialog" :persistent="options.persistent || false" :max-width="options.width || '350'" scrollable>
      <v-card tile>
        <v-toolbar v-if="options.toolbar" card dark :color="options.toolbar.color || ''">
          <v-btn icon dark @click.native="dialog = false" v-if="options.toolbar.closable || false">
            <v-icon>close</v-icon>
          </v-btn>
          <v-toolbar-title v-if="options.title" v-html="options.title || ''"></v-toolbar-title>
          <v-spacer></v-spacer>
        </v-toolbar>
        <v-card-title v-if="!options.toolbar && options.title" v-html="options.title || ''" class="headline"></v-card-title>
        <v-card-text v-html="options.text"></v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn v-for="(button, i) in buttons" :key="i" :color="button.color || ''" @click.stop="click(i, $event)">{{ button.title }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-layout>
</template>

<script>
  import prompt from '~/plugins/prompt/prompt'

  export default {
    computed: {
      buttons () {
        return this.options.buttons || this.defaultButtons
      }
    },
    data: () => ({
      options: {},
      dialog: false,
      defaultButtons: [{ title: 'Close' }]
    }),
    beforeMount () {
      prompt.event.$on('open', (options, state) => {
        this.options = options
        this.dialog = state
      })
      prompt.event.$on('close', () => {
        this.dialog = false
      })
    },
    methods: {
      click (i, event, source = 'click') {
        const button = this.buttons[i]
        if (button && typeof button.handler === 'function') {
          button.handler(i, event, { source })
        }
        this.dialog = false
      }
    }
  }
</script>

<style>

</style>
