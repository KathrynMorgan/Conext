const fs = require('fs')
const express = require('express')

// Create express router
const router = express.Router()

// Transform req & res to have the same API as express
// So we can use res.status() & res.json()
var app = express()
router.use((req, res, next) => {
  Object.setPrototypeOf(req, app.request)
  Object.setPrototypeOf(res, app.response)
  req.res = res
  res.req = req
  next()
})

// Add GET - /
router.get('/', (req, res) => {
  res.status(200).json({
    'status': 'it works!'
  })
})

/*
// Add GET - /debug
router.get('/debug/:id', (req, res) => {
  let homedir = process.env[(process.platform === 'win32') ? 'USERPROFILE' : 'HOME']
  res.status(200).json({
    'homedir': homedir,
    'body': req.body,
    'params': req.params,
    'query': req.query
  })
})

// Add GET - /api
router.get('/', (req, res) => {
  let homedir = process.env[(process.platform === 'win32') ? 'USERPROFILE' : 'HOME']
  var output = []
  fs.readdir(homedir, (err, files) => {
    files.forEach(file => {
      output.push({
        filename: file,
        filepath: homedir + '/',
        folder: fs.lstatSync(homedir + '/' + file).isDirectory(),
        stats: fs.statSync(homedir + '/' + file)
      })
    })
    res.status(200).json(output)
  })
})

// Add POST - /api/login
router.post('/login', (req, res) => {
  if (req.body.username === 'demo' && req.body.password === 'demo') {
    req.session.authUser = { username: 'demo' }
    return res.json({ username: 'demo' })
  }
  res.status(401).json({ message: 'Bad credentials' })
})

// Add POST - /api/logout
router.post('/logout', (req, res) => {
  delete req.session.authUser
  res.json({ ok: true })
})
*/

// Export the server middleware
module.exports = {
  path: '/api',
  handler: router
}