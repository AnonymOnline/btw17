#!/bin/sh

#run registry
#docker run -d -p 127.0.0.1:5000:5000 --restart=always --name registry registry:latest

#create bridged btw17 network
docker network create -d bridge btw17

#build own-php-apache
docker build -t own-php-apache ./php-apache

#run nginx-proxy first
docker run -d -p 80:80 \
	-v /var/run/docker.sock:/tmp/docker.sock:ro \
	--network btw17 \
	--restart=unless-stopped \
	jwilder/nginx-proxy

#run mysql container
docker run --network btw17 \
        -d \
        --restart=unless-stopped \
	--name mysql \
        -v mysql_datadir:/var/lib/mysql \
        -e MYSQL_ROOT_PASSWORD=btw17 \
        -p 127.0.0.1:3306:3306 \
        mysql:latest

#3rd one is php-apache                                                                                                                                       
docker run --network btw17 \
        -d \
        -e VIRTUAL_HOST=btw.localhost \
        --restart=unless-stopped \
	--name phpApache \
        -v www_src:/var/www/html \
        -v php-apache_config:/usr/local/etc/php \
        own-php-apache:latest

#last but not least
docker run --network btw17 \
        -d \
        -e VIRTUAL_HOST=btwadmin.localhost \
	--name phpmyadmin \
        -e PMA_HOST=mysql \
        --restart=unless-stopped \
phpmyadmin/phpmyadmin 
