# LXD Control Panel

<img src="https://i.imgur.com/gHhwGG4.png" alt="Screen" title="Screen" align="right" />

A decentralized LXD server control panel with sugar!

## Features

Not going to bore you with stuff you probably won't read, so here is what the system can do:

 - Manage LXD containers, as if you were to use them for web hosting.
 - Create web and port forwards, to you know.. forward traffic into the containers or external upstream's.
 - Create custom tasks for maintaining containers or project deployments.
 - Create API endpoints for stuff the panel does not do.
 - Server information, so you know when to stop.

## Install

There are 2 ways to install the project, composer and git, some setup is required as shown below.

### With Composer

On a **clean** Ubuntu server run the following commands:

```
# switch into root user!
sudo su

# update package lists and packages
sudo apt update && sudo apt -y upgrade

# install deps
sudo apt -y install zip php-cli

# install composer
sudo curl -sS https://getcomposer.org/installer | sudo php
sudo mv composer.phar /usr/local/bin/composer
sudo ln -s /usr/local/bin/composer /usr/bin/composer
```

Once done, install the project:

```
# make webroot and move into it
mkdir -p /var/www/html && cd /var/www/html

# install project (ignore warning about not to use root, root is required for post-install)
composer create-project lcherone/deval .
```

## Install LXD

You must be using LXD version 3.0.0 or above, so unless your using 18.04 or above use the snap package.

```
# add www-data to lxd group
sudo usermod -a -G lxd www-data

# install snapd
sudo apt -y install snapd

# install lxd snap package
sudo snap install lxd

# initialise lxd (make sure you allow lxd over network - or the console wont work)
sudo lxd init
```

## Allow www-data LXD access

```
# add www-data to sudoers so can run lxc commands
visudo

# then amend to User privilege specification, it should look like the following:

# User privilege specification
root     ALL=(ALL:ALL) ALL
www-data ALL=(ALL:ALL) NOPASSWD: /usr/bin/lxc
```

Once complete, you will be able to go to the panel at http://IP:88, and then add the server, with the provided key during install.


### With Git

Follow the above, then install with git.

```
cd /var/www/html

git clone git@github.com:lcherone/Deval.git .

composer install

composer setup

cd .nuxt

npm install

npm run dev

// or
npm run generate
```

## Github Hosted

You can use the github hosted version of the panel which is located here: [https://lcherone.github.io/Deval](https://lcherone.github.io/Deval), did I mention its decentralized.

## Contributing

Please see [CONTRIBUTING](https://github.com/lcherone/Deval/blob/master/CONTRIBUTING.md) for details.

## Bespoke System

If you would like a bespoke system created at very competitive rates, with a turn around of a week, contact me via my website @ [https://cherone.co.uk](https://cherone.co.uk) or if you use [Upwork](https://www.upwork.com/o/profiles/users/~01ff0ec055a5895e8f/), though that.

## Developer Support

If you want to show your appreciation or you make money where this is used, please make a small donation to help encourage development [https://www.paypal.me/lcherone](https://www.paypal.me/lcherone), cheers. Also help the project get seen by staring it.. thats free just like the hours I have put into making the system.

## Credits

- [Lawrence Cherone](https://github.com/lcherone)
- [All Contributors](https://github.com/lcherone/Deval/graphs/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/lcherone/Deval/blob/master/LICENSE) for more information.

```
MIT License

Copyright (c) 2018 Lawrence Cherone

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```