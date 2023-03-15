#!/usr/bin/env sh

RED='\033[0;3m'
PURPLE='\033[0;35m'
GREEN='\033[0;32m'
NOCOLOR="\033[0m"

BOLD='\033[1m'
UNBOLD='\033[0m'

# Check if .develop file exists
if [ -f .develop ]; then
    # Copy env.develop to .env in backend folder
    cp backend/.env.develop backend/.env
elif [ -f .test ]; then
    # Copy backend/.env.test to .env in backend folder
    cp backend/.env.test backend/.env
elif [ -f .stag ]; then
    # Copy backend/.env.stag to .env in backend folder
    cp backend/.env.stag backend/.env
elif [ -f .production ]; then
    # Copy backend/.env.production to .env in backend folder
    cp env.production backend/.env
else
    echo "No environment file found."
fi

# Create file .development for development env
DEVELOPMENT=./.development
if [ ! -f "${DEVELOPMENT}" ]; then
    touch "${DEVELOPMENT}"
    chmod 777 artisan
    chmod 777 composer
    chmod 777 stop
fi
# check docker is running
if ! docker info > /dev/null 2>&1; then
  echo "\n${RED}This script uses docker, and it isn't running - please start docker and try again!\n"
  exit 1
fi

docker-compose up -d

if [ "$1" == '--build' ]; then
  ./composer install
fi

# swagger 
# echo "\n${GREEN}${BOLD}Generating Swagger docs ...  ${UNBOLD}${NOCOLOR}"
# if [ "$1" == '--build' ]; then
#   ./artisan swagger-lume:publish
# fi
# ./artisan swagger-lume:generate

if [ "$1" == '--build' ]; then
  echo "${PURPLE}${BOLD} 
RRRRRRRRRRRRRRRRR   UUUUUUUU     UUUUUUUUNNNNNNNN        NNNNNNNNNNNNNNNN        NNNNNNNNIIIIIIIIIINNNNNNNN        NNNNNNNN        GGGGGGGGGGGGG
R::::::::::::::::R  U::::::U     U::::::UN:::::::N       N::::::NN:::::::N       N::::::NI::::::::IN:::::::N       N::::::N     GGG::::::::::::G
R::::::RRRRRR:::::R U::::::U     U::::::UN::::::::N      N::::::NN::::::::N      N::::::NI::::::::IN::::::::N      N::::::N   GG:::::::::::::::G
RR:::::R     R:::::RUU:::::U     U:::::UUN:::::::::N     N::::::NN:::::::::N     N::::::NII::::::IIN:::::::::N     N::::::N  G:::::GGGGGGGG::::G
  R::::R     R:::::R U:::::U     U:::::U N::::::::::N    N::::::NN::::::::::N    N::::::N  I::::I  N::::::::::N    N::::::N G:::::G       GGGGGG
  R::::R     R:::::R U:::::D     D:::::U N:::::::::::N   N::::::NN:::::::::::N   N::::::N  I::::I  N:::::::::::N   N::::::NG:::::G              
  R::::RRRRRR:::::R  U:::::D     D:::::U N:::::::N::::N  N::::::NN:::::::N::::N  N::::::N  I::::I  N:::::::N::::N  N::::::NG:::::G              
  R:::::::::::::RR   U:::::D     D:::::U N::::::N N::::N N::::::NN::::::N N::::N N::::::N  I::::I  N::::::N N::::N N::::::NG:::::G    GGGGGGGGGG
  R::::RRRRRR:::::R  U:::::D     D:::::U N::::::N  N::::N:::::::NN::::::N  N::::N:::::::N  I::::I  N::::::N  N::::N:::::::NG:::::G    G::::::::G
  R::::R     R:::::R U:::::D     D:::::U N::::::N   N:::::::::::NN::::::N   N:::::::::::N  I::::I  N::::::N   N:::::::::::NG:::::G    GGGGG::::G
  R::::R     R:::::R U:::::D     D:::::U N::::::N    N::::::::::NN::::::N    N::::::::::N  I::::I  N::::::N    N::::::::::NG:::::G        G::::G
  R::::R     R:::::R U::::::U   U::::::U N::::::N     N:::::::::NN::::::N     N:::::::::N  I::::I  N::::::N     N:::::::::N G:::::G       G::::G
RR:::::R     R:::::R U:::::::UUU:::::::U N::::::N      N::::::::NN::::::N      N::::::::NII::::::IIN::::::N      N::::::::N  G:::::GGGGGGGG::::G
R::::::R     R:::::R  UU:::::::::::::UU  N::::::N       N:::::::NN::::::N       N:::::::NI::::::::IN::::::N       N:::::::N   GG:::::::::::::::G
R::::::R     R:::::R    UU:::::::::UU    N::::::N        N::::::NN::::::N        N::::::NI::::::::IN::::::N        N::::::N     GGG::::::GGG:::G
RRRRRRRR     RRRRRRR      UUUUUUUUU      NNNNNNNN         NNNNNNNNNNNNNNN         NNNNNNNIIIIIIIIIINNNNNNNN         NNNNNNN        GGGGGG   GGGG"
  echo ""
fi

echo "${GREEN}${BOLD} RUN API BY URL: http://localhost/api/"
echo ""
echo "${GREEN}${BOLD} RUN FrontEnd BY URL: http://localhost/api/"
echo ""
echo "${GREEN}${BOLD} RUN SWAGGER API BY URL: http://localhost/api/documentation"
echo ""
