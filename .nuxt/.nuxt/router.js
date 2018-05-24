import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

const _980f151c = () => import('../pages/tasks.vue' /* webpackChunkName: "pages/tasks" */).then(m => m.default || m)
const _1cc8d8e2 = () => import('../pages/server.vue' /* webpackChunkName: "pages/server" */).then(m => m.default || m)
const _f2b73846 = () => import('../pages/server/processes.vue' /* webpackChunkName: "pages/server/processes" */).then(m => m.default || m)
const _424dc85a = () => import('../pages/server/logins.vue' /* webpackChunkName: "pages/server/logins" */).then(m => m.default || m)
const _4038d394 = () => import('../pages/server/network-connections.vue' /* webpackChunkName: "pages/server/network-connections" */).then(m => m.default || m)
const _3b316788 = () => import('../pages/lxd.vue' /* webpackChunkName: "pages/lxd" */).then(m => m.default || m)
const _5fa62362 = () => import('../pages/lxd/operations.vue' /* webpackChunkName: "pages/lxd/operations" */).then(m => m.default || m)
const _5896d775 = () => import('../pages/lxd/containers.vue' /* webpackChunkName: "pages/lxd/containers" */).then(m => m.default || m)
const _04ba105b = () => import('../pages/lxd/images.vue' /* webpackChunkName: "pages/lxd/images" */).then(m => m.default || m)
const _584b05f1 = () => import('../pages/about.vue' /* webpackChunkName: "pages/about" */).then(m => m.default || m)
const _e1bb1e04 = () => import('../pages/api.vue' /* webpackChunkName: "pages/api" */).then(m => m.default || m)
const _ac69206a = () => import('../pages/api/data.vue' /* webpackChunkName: "pages/api/data" */).then(m => m.default || m)
const _d438bfd4 = () => import('../pages/routes.vue' /* webpackChunkName: "pages/routes" */).then(m => m.default || m)
const _26613530 = () => import('../pages/routes/web-forwards.vue' /* webpackChunkName: "pages/routes/web-forwards" */).then(m => m.default || m)
const _451bc461 = () => import('../pages/routes/port-forwards.vue' /* webpackChunkName: "pages/routes/port-forwards" */).then(m => m.default || m)
const _45827f74 = () => import('../pages/servers.vue' /* webpackChunkName: "pages/servers" */).then(m => m.default || m)
const _40f2c212 = () => import('../pages/servers/index.vue' /* webpackChunkName: "pages/servers/index" */).then(m => m.default || m)
const _38c793ae = () => import('../pages/auth/sign-out.vue' /* webpackChunkName: "pages/auth/sign-out" */).then(m => m.default || m)
const _7802e0b6 = () => import('../pages/index.vue' /* webpackChunkName: "pages/index" */).then(m => m.default || m)



if (process.client) {
  window.history.scrollRestoration = 'manual'
}
const scrollBehavior = function (to, from, savedPosition) {
  // if the returned position is falsy or an empty object,
  // will retain current scroll position.
  let position = false

  // if no children detected
  if (to.matched.length < 2) {
    // scroll to the top of the page
    position = { x: 0, y: 0 }
  } else if (to.matched.some((r) => r.components.default.options.scrollToTop)) {
    // if one of the children has scrollToTop option set to true
    position = { x: 0, y: 0 }
  }

  // savedPosition is only available for popstate navigations (back button)
  if (savedPosition) {
    position = savedPosition
  }

  return new Promise(resolve => {
    // wait for the out transition to complete (if necessary)
    window.$nuxt.$once('triggerScroll', () => {
      // coords will be used if no selector is provided,
      // or if the selector didn't match any element.
      if (to.hash) {
        let hash = to.hash
        // CSS.escape() is not supported with IE and Edge.
        if (typeof window.CSS !== 'undefined' && typeof window.CSS.escape !== 'undefined') {
          hash = '#' + window.CSS.escape(hash.substr(1))
        }
        try {
          if (document.querySelector(hash)) {
            // scroll to anchor by returning the selector
            position = { selector: hash }
          }
        } catch (e) {
          console.warn('Failed to save scroll position. Please add CSS.escape() polyfill (https://github.com/mathiasbynens/CSS.escape).')
        }
      }
      resolve(position)
    })
  })
}


export function createRouter () {
  return new Router({
    mode: 'history',
    base: '/',
    linkActiveClass: 'nuxt-link-active',
    linkExactActiveClass: 'nuxt-link-exact-active',
    scrollBehavior,
    routes: [
		{
			path: "/tasks",
			component: _980f151c,
			name: "tasks"
		},
		{
			path: "/server",
			component: _1cc8d8e2,
			name: "server",
			children: [
				{
					path: "processes",
					component: _f2b73846,
					name: "server-processes"
				},
				{
					path: "logins",
					component: _424dc85a,
					name: "server-logins"
				},
				{
					path: "network-connections",
					component: _4038d394,
					name: "server-network-connections"
				}
			]
		},
		{
			path: "/lxd",
			component: _3b316788,
			name: "lxd",
			children: [
				{
					path: "operations",
					component: _5fa62362,
					name: "lxd-operations"
				},
				{
					path: "containers",
					component: _5896d775,
					name: "lxd-containers"
				},
				{
					path: "images",
					component: _04ba105b,
					name: "lxd-images"
				}
			]
		},
		{
			path: "/about",
			component: _584b05f1,
			name: "about"
		},
		{
			path: "/api",
			component: _e1bb1e04,
			name: "api",
			children: [
				{
					path: "data",
					component: _ac69206a,
					name: "api-data"
				}
			]
		},
		{
			path: "/routes",
			component: _d438bfd4,
			name: "routes",
			children: [
				{
					path: "web-forwards",
					component: _26613530,
					name: "routes-web-forwards"
				},
				{
					path: "port-forwards",
					component: _451bc461,
					name: "routes-port-forwards"
				}
			]
		},
		{
			path: "/servers",
			component: _45827f74,
			children: [
				{
					path: "",
					component: _40f2c212,
					name: "servers"
				}
			]
		},
		{
			path: "/auth/sign-out",
			component: _38c793ae,
			name: "auth-sign-out"
		},
		{
			path: "/",
			component: _7802e0b6,
			name: "index"
		}
    ],
    
    
    fallback: false
  })
}
