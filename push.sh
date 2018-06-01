#!/bin/bash

build_gh_pages() {
    npm run generate:gh-pages
}

push_gh_pages() {
    git subtree split --prefix .nuxt/dist -b gh-pages
    
    git checkout gh-pages
    
    git add -A ./
    
    git commit -a -m "."

    git push -f origin gh-pages:gh-pages
    
    git checkout master
    
    #git branch -D gh-pages
}

build_ui () {
    npm run generate
}

push_changes () {
    git add -A ./

    git commit -a -m '.'
    
    git push origin master
}

main () {
    cd .nuxt/
    build_ui
    
    cd ../
    push_changes
    
    cd .nuxt/
    build_gh_pages
    
    cd ../
    push_gh_pages
}

main
