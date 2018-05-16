import { 
  getUserFromCookie,
  getUserFromLocalStorage,
  getTokenFromCookie,
  getTokenFromLocalStorage
} from '~/utils/auth'

export default function ({ store, req }) {
  // If nuxt generate, pass this middleware
  if (process.server && !req) return
  
  const loggedUser = process.server ? getUserFromCookie(req) : getUserFromLocalStorage()
  const loggedToken = process.server ? getTokenFromCookie(req) : getTokenFromLocalStorage()

  store.commit('auth/SET_USER', loggedUser)
  store.commit('auth/SET_TOKEN', loggedToken)
}