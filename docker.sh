#!/bin/bash

set -e

# Default values
export run_tests=0
export LOCAL_PORT=""
export DB_HOST="localhost"
export DB_USER="poster_generator"
export DB_PASS="password"
export DB_PORT=3800
export RESET_DB=${RESET_DB:-false}
export DB_NAME="poster_generator"

# Help message
help_message() {
	echo "Usage: display_mongodb_gui.sh [OPTIONS]"
	echo "Options:"
	echo "  --local-port       Local port to bind for the GUI"
	echo "  --reset_db         Reset DB"
	echo "  --run_tests        Run tests before starting"
	echo "  --help             Show this help message"
}

# Parse command-line arguments
while [[ "$#" -gt 0 ]]; do
	case $1 in
		--reset_db)
			RESET_DB="true"
			;;
		--run_tests)
			run_tests=1
			shift
			;;
		--local-port)
			LOCAL_PORT="$2"
			shift
			;;
		--help)
			help_message
			exit 0
			;;
		*)
			echo "Error: Unknown option '$1'. Use --help for usage."
			exit 1
			;;
	esac
	shift
done

# Check for required parameters
if [[ -z $LOCAL_PORT ]]; then
	echo "Error: Missing required parameter --local-port. Use --help for usage."
	exit 1
fi


is_package_installed() {
	dpkg-query -W -f='${Status}' "$1" 2>/dev/null | grep -c "ok installed"
}

UPDATED_PACKAGES=0

if ! command -v curl &>/dev/null; then
	if ! command -v apt &>/dev/null; then
		echo "curl is not installed and the system is not debian-based. Can only auto-install on debian-based systems with apt available."
	fi
	# Update package lists
	if [[ $UPDATED_PACKAGES == 0 ]]; then
		sudo apt update || {
			echo "apt-get update failed. Are you online?"
			exit 3
		}

		UPDATED_PACKAGES=1
	fi

	sudo apt-get install -y curl || {
		echo "sudo apt install -y curl failed"
		exit 3
	}
fi

# Check if Docker is installed
if ! command -v docker &>/dev/null; then
	echo "Docker not found. Installing Docker..."

	curl -fsSL https://get.docker.com | bash
fi



export LOCAL_PORT

# Write environment variables to .env file
echo "#!/bin/bash" > .env
echo "LOCAL_PORT=$LOCAL_PORT" >> .env

echo "=== Current git hash before auto-pulling ==="
git rev-parse HEAD
echo "=== Current git hash before auto-pulling ==="

git pull

function die {
	echo $1
	exit 1
}

if command -v php 2>/dev/null >/dev/null; then
	SYNTAX_ERRORS=0
	for i in $(ls *.php); do
		if ! php -l $i 2>&1; then
			SYNTAX_ERRORS=1;
		fi ;
	done

	if [[ "$SYNTAX_ERRORS" -ne "0" ]]; then
		echo "Tests failed";
		exit 1
	fi

	if [[ "$run_tests" -eq "1" ]]; then
		php testing.php && echo "Syntax checks for PHP Ok" || die "Syntax Checks for PHP failed"
	fi
fi

CURRENT_USER=$(whoami)

if [ "$RESET_DB" = true ]; then
    echo "Resetting DB container and volume..."
    if groups "$CURRENT_USER" | grep -q "\bdocker\b"; then
        docker-compose down -v --remove-orphans
    else
        sudo docker-compose down -v --remove-orphans
    fi
fi

if groups "$CURRENT_USER" | grep -q "\bdocker\b"; then
    docker-compose build && docker-compose up -d || { echo "Failed to build container"; exit 1; }
else
    sudo docker-compose build && sudo docker-compose up -d || { echo "Failed to build container"; exit 1; }
fi

function maria_db_exec {
	docker-compose exec dockerdb mariadb -uroot -ppassword -e "$1"
}

echo "⏳ Waiting for MariaDB to be ready..."

while ! docker-compose exec dockerdb mariadb -uroot -ppassword -e "SELECT 1;" > /dev/null 2>&1; do
	echo "⏳ MariaDB not ready yet... waiting 1s"
	sleep 1
done

echo "✅ MariaDB is ready."

maria_db_exec "CREATE DATABASE IF NOT EXISTS poster_generator;"
maria_db_exec "GRANT ALL PRIVILEGES ON poster_generator.* TO 'poster_generator'@'%' IDENTIFIED BY 'password'; FLUSH PRIVILEGES;"

docker-compose exec -T dockerdb mariadb -uroot -ppassword poster_generator < ./tests/test_config2.sql
docker-compose exec -T dockerdb mariadb -uroot -ppassword poster_generator < ./tests/test_img.sql

export inDocker="true"

container_id=$(docker ps | grep scientific_poster_generator-poster_generator-1 | sed 's/^\([^ ]*\).*/\1/')

echo "#########################"
sed -n -E "s/<VirtualHost \*:[0-9]{4}>/<VirtualHost \*:${LOCAL_PORT}>/p" custom-000-default.conf
docker exec $container_id cp custom-000-default.conf /etc/apache2/sites-enabled/000-default.conf
sed -n -E "s/Listen [0-9]{4}/Listen ${LOCAL_PORT}/p" custom-ports.conf
docker exec $container_id cp custom-ports.conf /etc/apache2/ports.conf

# echo "export DB_HOST=localhost" | sudo tee -a /etc/apache2/envvars
# echo "export DB_USER=poster_generator" | sudo tee -a /etc/apache2/envvars
# echo "export DB_PASS=password" | sudo tee -a /etc/apache2/envvars
# echo "export DB_NAME=poster_generator" | sudo tee -a /etc/apache2/envvars
# echo "export DB_PORT=3800" | sudo tee -a /etc/apache2/envvars

sudo a2enmod rewrite
sudo systemctl restart apache2
echo "#########################"

# sudo netstat -tuln

# docker exec $(docker ps | grep scientific_poster_generator-poster_generator-1 | sed 's/^\([^ ]*\).*/\1/') cat /etc/apache2/sites-enabled/000-default.conf
# docker exec $container_id cat /etc/apache2/sites-enabled/000-default.conf

curl http://localhost:${LOCAL_PORT}/login.php | grep title
sleep 1
curl http://localhost:${LOCAL_PORT}/pages/login.php | grep title


echo Running Backend-Tests...

echo Run tests on Test-DB: ${DB_NAME}

docker exec $container_id php testing.php $DB_NAME
CODE=$?

echo "-----------------"
sudo cat /var/log/apache2/error.log | grep servername

# docker-compose exec dockerdb mariadb -uroot -ppassword poster_generator > ./tests/results_backend_test.sql

maria_db_exec "DROP DATABASE IF EXISTS poster_generator;"
maria_db_exec "CREATE DATABASE IF NOT EXISTS poster_generator;"

docker-compose exec -T dockerdb mariadb -uroot -ppassword poster_generator < ./tests/test_config2.sql
docker-compose exec -T dockerdb mariadb -uroot -ppassword poster_generator < ./tests/test_img.sql

maria_db_exec "GRANT ALL PRIVILEGES ON poster_generator.* TO 'poster_generator'@'%' IDENTIFIED BY 'password'; FLUSH PRIVILEGES;"

php -r 'foreach(get_defined_functions()["internal"] as $i) {echo $i . "\n";};' > ./tests/php_build_in_func

for i in __halt_compiler abstract and array as break callable case catch class clone const continue declare default die do echo else elseif empty enddeclare endfor endforeach endif endswitch endwhile eval exit extends final for foreach function global goto if implements include include_once instanceof insteadof interface isset list namespace new or print private protected public require require_once return static switch throw trait try unset use var while xor
do
 echo $i >> ./tests/php_build_in_func
done

cd tests
./run_tests $*
CODE=$?
cd ..

echo --- Backend Coverage ---
# python3 ./tests/coverage.py ./testing.php 2 0 0

echo --- Frontend Coverage ---
echo -

sudo cat /var/log/apache2/error.log | grep servername

exit $CODE
