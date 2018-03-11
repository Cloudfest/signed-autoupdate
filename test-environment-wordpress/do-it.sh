#!/bin/sh
docker-compose down
#rm -rf WordPress
#rm -rf signed-autoupdate

git clone https://github.com/WordPress/WordPress.git
#git clone https://github.com/Cloudfest/signed-autoupdate.git -b wordpress-plugin
git clone https://github.com/Cloudfest/signed-autoupdate.git signed-autoupdate
docker-compose up -d

#do wp magic to install wordpress
echo "sleeping 10s to wait for containers boot up"
sleep 10
docker exec -it wordpress-server sh -c "cd /var/www/html;wp --allow-root config create --dbname=wordpressdb --dbuser=wp --dbpass=wp --dbhost=db"
docker exec -it wordpress-server sh -c "cd /var/www/html;wp --allow-root core install --url=http://localhost:8091 --title=WordPressAutoSignPluginDemo --admin_user=test123 --admin_email=test123@127.0.0.1 --admin_password=test123"
docker exec -it wordpress-server sh -c "cd /var/www/html;wp --allow-root plugin activate signed-autoupdate"
