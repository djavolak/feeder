#!/bin/bash

#cd /vagrant/backend/public
cd /home/zeleni/webapps/feeder/current/backend/public
php cli.php parse $1
php cli.php update $1
php cli.php create $1
