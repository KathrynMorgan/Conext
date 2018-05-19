import jwtDecode from 'jwt-decode'
import Cookie from 'js-cookie'

/**
 * Set user and token
 */
export const setToken = (token) => {
  if (process.server) return

  window.localStorage.setItem('token', token)
  window.localStorage.setItem('user', JSON.stringify(jwtDecode(token)))
  Cookie.set('jwt', token)
}

/**
 * Unset user and token
 */
export const unsetToken = () => {
  if (process.server) return

  window.localStorage.removeItem('token')
  window.localStorage.removeItem('user')
  window.localStorage.removeItem('secret')
  Cookie.remove('jwt')
}

/**
 * Get user from cookie
 */
export const getUserFromCookie = (req) => {
  if (!req.headers.cookie) return

  const jwtCookie = req.headers.cookie.split(';').find(c => c.trim().startsWith('jwt='))
  if (!jwtCookie) return

  const jwt = jwtCookie.split('=')[1]
  return jwtDecode(jwt)
}

/**
 * Get user from localstorage
 */
export const getUserFromLocalStorage = () => {
  const json = window.localStorage.user
  return json ? JSON.parse(json) : undefined
}

/**
 * Get raw jwt token from cookie
 */
export const getTokenFromCookie = (req) => {
  if (!req.headers.cookie) return

  const jwtCookie = req.headers.cookie.split(';').find(c => c.trim().startsWith('jwt='))
  if (!jwtCookie) return

  const jwt = jwtCookie.split('=')[1]
  return jwt
}

/**
 * Get raw jwt token from localstorage
 */
export const getTokenFromLocalStorage = () => {
  const token = window.localStorage.token
  return token ? token : undefined
}
