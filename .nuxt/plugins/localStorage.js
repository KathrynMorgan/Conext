import Vue from 'vue'

const CryptoJS = require("crypto-js");

const obfusck = '2SAQLEJuhV2LVANqgZzomZXVlPK9q8N5';

const Storage = {
  install(Vue, options) {
    Vue.prototype.$storage = {
      get(key) {
        if (process.browser){
          let value = window.localStorage.getItem(options.prefix + key);
          if (value) {
            try {
              return JSON.parse(CryptoJS.AES.decrypt(window.localStorage.getItem(options.prefix + key), obfusck).toString(CryptoJS.enc.Utf8))
            } catch (Error) {
              this.clear(true)
            }
          }
          return null
        }
      },
      set(key, value) {
        if (process.browser){
          window.localStorage.setItem(options.prefix + key, CryptoJS.AES.encrypt(JSON.stringify(value), obfusck))
        }
      },
      isset(key) {
        if (process.browser){
          return ( options.prefix + key in window.localStorage);
        }
      },
      remove(key) {
        if (process.browser){
          window.localStorage.removeItem(options.prefix + key)
        }
      },
      clear(forced) {
        if (process.browser){
          var arr = [];
          for (var i = 0; i < window.localStorage.length; i++){
            // dont remove servers
            if (!forced && window.localStorage.key(i) == 'storage_servers') {
              continue;
            }
            if (window.localStorage.key(i).substring(0, options.prefix.length) == options.prefix) {
              arr.push(window.localStorage.key(i));
            }
          }
          for (var i = 0; i < arr.length; i++) {
              window.localStorage.removeItem(arr[i]);
          }
        }
      }
    }
  }
};

Vue.use(Storage, {prefix: 'storage_'})
