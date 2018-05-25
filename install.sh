#!/bin/bash

# Set envioroment
set -e
export DEBIAN_FRONTEND=noninteractive
export PATH='/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/snap/bin'
export HOME='/root'

# upon launch wait for internet connection
echo "Waiting for network connection."
while [ 1 ]; do
  if ping -q -c 1 -W 1 8.8.8.8 > /dev/null 2>&1; then
    break;
  fi
  sleep 1
done

# Set working vars, change these to suit
#
# Application Name
appname="Deval"
webroot="/var/www/html"
timezone="Europe/London"

# Setup & Install Dependencys
setup_system() {
    #
    # set timezone
    # echo $timezone > /etc/timezone
    # dpkg-reconfigure -f noninteractive tzdata >/dev/null 2>/dev/null
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

# Install composer (globally)
install_composer() {
    #
    # Install composer
    sudo curl -sS https://getcomposer.org/installer | sudo php
    sudo mv composer.phar /usr/local/bin/composer
    sudo ln -s /usr/local/bin/composer /usr/bin/composer
}

# Setup LXD
#perge_lxd() {
    ## check lxd version is good should be v3.0
#}

# Genaral webroot setup - initial nginx part, specific configuration to be added
install_web() {
    #
    # Install Nginx
    sudo apt-get -yq install nginx
    #
    # Empty the webroot
    #sudo rm $webroot/* -rf
    
    #
    # setup initial nginx config
    echo -e "
server {
    listen 88 default_server;
    listen [::]:88 default_server;
    root $webroot/public;
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
    #composer create-project lcherone/deval .
    #
    cd .nuxt
    npm install --unsafe-perm
    npm run generate
    #
    cd $webroot
    #
    # change ownership
    chown www-data:www-data ./ -Rf
    #
    # make .plinker folder writeable by all
    chmod 0777 .plinker -Rf
}

# Main 
#
main() {
    #
    #ask_ssh_vcs
    #
    setup_system
    #
    install_php
    #
    #install_composer
    #
    install_web
    #
    #install_nodejs
    #
    install_project
    #
    echo "Project installed."
}

main
