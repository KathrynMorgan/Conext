#!/bin/bash

if [[ $EUID -ne 0 ]]; then
   echo "This project must be installed run as root user, as we need to install system packages. If in doubt review the script which is located here: ./.api/setup.sh - To re-run setup run: composer setup" 
   exit 1
fi

# Set envioroment
set -e
export DEBIAN_FRONTEND=noninteractive

# Set working vars, change these to suit
#
# Application Name
appname="Deval"
webroot="/var/www/html"

# Setup & Install Dependencys
setup_system() {
    #
    # Update System
    sudo apt-get update
    sudo apt-get -yq upgrade
    #
    # Install basic system packages
    sudo apt-get -yq install curl wget
    sudo apt-get -yq install unzip
    sudo apt-get -yq install nano
    sudo apt-get -yq install htop
    sudo apt-get -yq install git
    #
    # remove apache2
    sudo apt-get -yq --purge remove apache2
}

# Install PHP
install_php() {
    sudo apt-get -yq install php-cli
    sudo apt-get -yq install php-fpm
    sudo apt-get -yq install php-mbstring
    sudo apt-get -yq install php-curl
    sudo apt-get -yq install php-json
    sudo apt-get -yq install php-xml
    sudo apt-get -yq install php-mysql
    sudo apt-get -yq install php-sqlite3
    sudo apt-get -yq install php-zip
}

# Genaral webroot setup - initial nginx part, specific configuration to be added
install_web() {
    #
    # Install Nginx
    sudo apt-get -yq install nginx
    #
    # setup initial nginx config
    echo -e "
server {
    listen 88 default_server;
    listen [::]:88 default_server;
    root /var/www/html/public;
    #
    index index.php index.html;
    #
    server_name _;
    #
    location / {
        try_files \$uri \$uri/ /index.php?\$args;
    }
    #
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:$(find /run/php/php*-fpm.sock);
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param SCRIPT_NAME     \$fastcgi_script_name;
    }
    #
    location ~ /\.api {
      deny all;
      return 403;
    }
    #
    location ~ /\. {
      deny all;
      return 403;
    }
    #
    location ~ /\.ht {
        deny all;
    }
    #
    location ~ /.*\.db {
        deny all;
    }
}
" > /etc/nginx/sites-enabled/default
    #
    nginx -s reload
}

# Install Node.js
install_nodejs() {
    #
    # Install nvm, add nvm source line to roots bashrc
    curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.33.11/install.sh | bash
    #
    export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
    #
    # install node 9
    nvm install 9
    #
    # install npm
    sudo apt -yq install npm
    # update  npm
    npm install npm@latest -g
}

# Install Application
install_project() {
    #
    #
    cd $webroot
    #
    # add basic service cron job - will change later
    crontab -l | grep -q "do cd $webroot && /usr/bin/php task.php"  && echo 'cron task exists' || crontab -l | { cat; echo -e "@reboot while sleep 1; do cd $webroot && /usr/bin/php task.php ; done >/dev/null 2>&1"; } | crontab -
    #
    cd .nuxt
    npm install --unsafe-perm
    npm run generate
}

# Main 
#
main() {
    setup_system
    #
    install_php
    #
    install_web
    #
    install_nodejs
    #
    install_project
}

main