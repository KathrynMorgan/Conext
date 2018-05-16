# LXD - Web Control Panel

WIP/PoC!

The idea behind this project is to create a **standalone** web interface to control LXD servers which can be **hosted anywhere**, it can be deployed with the server, in an electron app, on localhost or on static hosting like GitHub!

Instead of directly talking to the LXD server, it will connect to an API which you install on each of the servers, the API is powered by PHP and FatFree Framework so its lightweight and straightforward to install, as I will deliver it as a composer project.

**Features will include:**

 - Server Information
 - NGINX reverse proxy configuration
 - LetsEncrypt Certificates
 - Host port forwarding
 - Maintenance Tasks
 - Host Management
 - And many other cool features!
 
**Screen**

As you can see, far from finished but the following shows adding a server and connecting to one of the containers terminals. Using the [GitHub](https://lcherone.github.io/LXD-Web-Control-Panel/) hosted version, I'm able to manage my local LXD without creating any certificates! Simplez..

![Screenshot](https://i.imgur.com/zxoWFYW.gif)

## Hosted Versions

 - [GitHub](https://lcherone.github.io/LXD-Web-Control-Panel/) [![Build Status](https://travis-ci.org/lcherone/LXD-Web-Control-Panel.svg?branch=master)](https://travis-ci.org/lcherone/LXD-Web-Control-Panel)

## Build Setup

``` bash
# install dependencies
$ npm install # Or yarn install

# serve with hot reload at localhost:3000
$ npm run dev

# build for production and launch server
$ npm run build
$ npm start

# generate static project
$ npm run generate
```

## The API

No yet public as im building it.

## Support / Sponsor

If you enjoy using this app, would like to see more development time or you just want to show your appreciation then,
please make a donation [https://www.paypal.me/lcherone](https://www.paypal.me/lcherone), thanks.