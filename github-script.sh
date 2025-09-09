php artisan down
git add .
git stash save
git pull -r git@github.com:bravoga/pieve_crm_back.git
git stash pop
composer install
php artisan cache:clear
php artisan queue:restart
php artisan view:clear
php artisan route:cache
php artisan config:cache
php artisan optimize
sudo chown www-data: -R public/
sudo chown www-data: -R storage/
sudo chown www-data: -R bootstrap/
sudo chmod -R 777 public/images
sudo chmod -R 755 public
sudo chmod -R 777 storage
sudo chmod -R 777 bootstrap
sudo chmod -R 777 public
php artisan up

