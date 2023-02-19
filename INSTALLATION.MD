## Server requirements
- php >= 8.1
- MariaDB 10.2+ / MySQL 5.7+
- redis
- Extensions:
  - Mbstring PHP Extension
  - OpenSSL PHP Extension
  - PDO PHP Extension
  - Fileinfo PHP Extension
  - JSON PHP Extension
  - Tokenizer PHP Extension
  - GD PHP Extension
  - zip PHP Extension
  - bcmath PHP Extension
  - gmp PHP Extension
  - sodium PHP Extension

## IP2Proxy
- testing : {your-project-path}/src/database/ip2location/IP2PROXY.testing.BIN
- production : {your-project-path}/src/database/ip2location/IP2PROXY.BIN

## Deploy scenario by manual
- cd /var/www/{your-project-path}
- git checkout {branchName}
- git pull origin {branchName}
- cd src/
- touch .testing (.testing, .staging) (default this file is not exist => use Production env)
- composer install --no-dev --no-scripts --optimize-autoloader
- php artisan migrate
- php artisan cache:clear
- php artisan swagger-lume:publish
- php artisan swagger-lume:generate
- Set permission step:
  + sudo chmod -R 775 bootstrap/cache
  + sudo chmod -R 775 storage


**- Seeder for first deploy Production**
  + php artisan db:seed --class=MediaCategorySeeder --database=web_kr
  + php artisan db:seed --class=PotentialReference --database=web_kr
  + php artisan db:seed --class=MediaCategorySeeder --database=web_tw
  + php artisan db:seed --class=PotentialReference --database=web_tw


## Deploy scenario by script
- cd /var/www/{your-project-path}
- chmod 777 deploy-production
- ./deploy-production --seeder

**References:** 
- Set Permissions step: (Ref: https://www.itsolutionstuff.com/post/how-to-setup-file-permissions-correctly-in-laravelexample.html):