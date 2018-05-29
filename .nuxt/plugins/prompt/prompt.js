import prompt from '~/components/prompt.vue'

const Plugin = {
  install(Vue, options = {}) {
    /**
     * Makes sure that plugin can be installed only once
     */
    if (this.installed) {
      return
    }
    this.installed = true
    
    /**
     * Create event bus
     */ 
    this.event = new Vue()

    /**
     * Plugin methods
     */
    Vue.prototype.$prompt = {
      show (options = {}) {
        // open dialog
        Plugin.event.$emit('open', options, true)
      }
    }
    
    /**
     * Registration of <prompt/> component
     */
    Vue.component('prompt', prompt)
  }
};

export default Plugin
