const bodyParser = require('body-parser')

/* nuxt.config.js */
// only add `router.base = '/<repository-name>/'` if `DEPLOY_ENV` is `GH_PAGES`
const router = process.env.DEPLOY_ENV === 'GH_PAGES' ? {
  middleware: 'check-auth',
  base: '/LXD-Web-Control-Panel/'
} : {
  middleware: 'check-auth'
}

const publicPath = process.env.DEPLOY_ENV === 'GH_PAGES' ? '/dist/' : '/_nuxt/'

/**
 * @see: node_modules/lib/common/options.js
 */
module.exports = {
  head: {
    title: '',
    meta: [
      { charset: 'utf-8' },
      { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      { hid: 'description', name: 'description', content: '' }
    ],
    link: [
      { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }
    ]
  },
  loading: { color: '#3B8070' },
  serverMiddleware: [
    bodyParser.json(),
    '~/api'
  ],
  router: {
    ...router
  },
  plugins: [],
  modules: [
    '@nuxtjs/font-awesome',
    '@nuxtjs/vuetify'
  ],
  vuetify: {
    // Vuetify options
    // theme: { }
  },
  css: [
    'xterm/dist/xterm.css'
  ],
  generate: {
    minify: {
      collapseBooleanAttributes: true,
      collapseWhitespace: false,
      decodeEntities: true,
      minifyCSS: true,
      minifyJS: true,
      processConditionalComments: true,
      removeAttributeQuotes: false,
      removeComments: true,
      removeEmptyAttributes: true,
      removeOptionalTags: true,
      removeRedundantAttributes: true,
      removeScriptTypeAttributes: false,
      removeStyleLinkTypeAttributes: false,
      removeTagWhitespace: false,
      sortAttributes: true,
      sortClassName: false,
      trimCustomFragments: true,
      useShortDoctype: true
    }
  },
  build: {
    extractCSS: true,
    publicPath: publicPath,
    vendor: [
      'axios',
    ],
    postcss: {
      plugins: {
        // prevent compile warnings from bulma
        'postcss-custom-properties': false
      }
    },
    /*
    ** Run ESLint on save
    */
    extend (config, { isDev, isClient }) {
      if (isDev && isClient) {
        config.module.rules.push({
          enforce: 'pre',
          test: /\.(js|vue)$/,
          loader: 'eslint-loader',
          exclude: /(node_modules)/
        })
      }
    }
  },
  messages: {
    error_404: 'Page could not be found',
    server_error: 'Server error',
    nuxtjs: 'Nuxt.js',
    back_to_home: 'Go back to the home page',
    server_error_details: 'An error occurred in the application and your page could not be served.',
    client_error: 'Error',
    client_error_details: 'An error occurred while rendering the page.'
  }
}
