# Search Api Service
- Lumen (8.3.4) (Laravel Components ^8.0)

## REQUIRED
- php >= 7.4
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
  - zip
  - bcmath 
  - gmp
  - sodium

## RUNNING LOCAL
- install docker desktop : https://docs.docker.com/desktop/install/mac-install/
- Run command line:
  - sudo chmod 777 ./start
  - sudo ./start --build
- run application by url : https://api.search-api.local/

- Stop Services :
  - ./stop

## RUNNING COMMAND LINE
- ./composer xxx
  - ./composer install
- ./artisan xxx
  - ./artisan cache:clear
  - ./artisan make:migration create_recent_search_table

