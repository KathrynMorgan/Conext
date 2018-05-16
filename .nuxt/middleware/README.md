# MIDDLEWARE

This directory contains your Application Middleware.
The middleware lets you define custom function to be ran before rendering a page or a group of pages (layouts).

More information about the usage of this directory in the documentation:
https://nuxtjs.org/guide/routing#middleware

## Contents

 - anonymous.js - Checks store that current user is not authenticated.
 - authenticated.js - Checks store that current user is authenticated or redirects to /auth/sign-in
 - check-auth.js - Checks jwt token, pulls from localstorage if client, pulls from cookie if server.
 