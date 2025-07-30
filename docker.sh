#!/bin/bash

set -e

# Default values
export run_tests=0
export LOCAL_HOST="localhost"
export LOCAL_PORT=""
export DB_HOST="localhost"
export DB_USER="poster_generator"
export DB_PASS="password"
export DB_PORT=3306
export RESET_DB=${RESET_DB:-false}
export DB_NAME="poster_generator"
export MYSQL_USERNAME="root"
export MYSQL_PASSWORD="password"

IMAGE_NAME="scientific_poster_generator_poster_generator"

# Help message
help_message() {
	echo "Usage: display_mongodb_gui.sh [OPTIONS]"
	echo "Options:"
	echo "  --local-port       Local port to bind for the GUI"
	echo "  --reset_db         Reset DB"
	echo "  --run-tests        Run tests before starting"
	echo "  --help             Show this help message"
}

echo $GITHUB_ACTIONS

# Parse command-line arguments
while [[ "$#" -gt 0 ]]; do
	case "$1" in
		--reset_db)
			RESET_DB="true"
			shift
			;;
		--run-tests)
			run_tests=1
			shift
			;;
		--local-port)
			if [[ -n "$2" && "$2" != --* ]]; then
				LOCAL_PORT="$2"
				shift 2
			else
				echo "Error: --local-port requires a value."
				exit 1
			fi
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

if ! command -v docker-compose &>/dev/null; then
	echo "docker-compose not found. Installing it..."

	sudo apt update

	sudo apt-get install ca-certificates curl gnupg
	sudo install -m 0755 -d /etc/apt/keyrings
	curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
	sudo chmod a+r /etc/apt/keyrings/docker.gpg

	# Add the repository to Apt sources:
	echo \
	"deb [arch="$(dpkg --print-architecture)" signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
	"$(. /etc/os-release && echo "$VERSION_CODENAME")" stable" | \
	sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
	sudo apt-get update

	sudo apt install -y docker-compose
fi

if ! command -v php >/dev/null; then
	sudo apt update

	sudo apt install -y php
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

# if groups "$CURRENT_USER" | grep -q "\bdocker\b"; then
#     DOCKER="docker"
#     DOCKER_COMPOSE="docker-compose"
# else
#     DOCKER="sudo docker"
#     DOCKER_COMPOSE="sudo docker-compose"
# fi

# echo "Checking if Docker image '$IMAGE_NAME' exists..."
# if ! $DOCKER image inspect "$IMAGE_NAME" > /dev/null 2>&1; then
#     echo "Image not found. Building with docker-compose..."
#     $DOCKER_COMPOSE build || { echo "❌ Failed to build container"; exit 1; }
# else
#     echo "✅ Image '$IMAGE_NAME' already exists. Skipping build."
# fi

# echo "Starting container using docker-compose..."
# $DOCKER_COMPOSE up -d || { echo "❌ Failed to start container"; exit 1; }

if groups "$CURRENT_USER" | grep -q "\bdocker\b"; then
    docker-compose build && docker-compose up -d || { echo "Failed to build container"; exit 1; }
else
    sudo docker-compose build && sudo docker-compose up -d || { echo "Failed to build container"; exit 1; }
fi

function docker_exec_env {
    local service="$1"
    shift

	if [ -n "$GITHUB_ACTIONS" ]; then
		docker-compose exec -T "$service" "$@"
	else
		docker-compose exec "$service" "$@"
	fi
}

function docker_exec {
    local service="$1"
    shift

	docker-compose exec -T "$service" "$@"
}

function maria_db_exec {
	docker_exec dockerdb mariadb -u$MYSQL_USERNAME -p$MYSQL_PASSWORD -e "$1"
}

echo "⏳ Waiting for MariaDB to be ready..."

while ! docker_exec dockerdb mariadb -u$MYSQL_USERNAME -p$MYSQL_PASSWORD -e "SELECT 1;" >/dev/null; do
	echo "⏳ MariaDB not ready yet... waiting 1s"
	sleep 1
done

echo "✅ MariaDB is ready."

maria_db_exec "CREATE DATABASE IF NOT EXISTS poster_generator;"
maria_db_exec "GRANT ALL PRIVILEGES ON poster_generator.* TO 'poster_generator'@'%' IDENTIFIED BY 'password'; FLUSH PRIVILEGES;"

docker_exec dockerdb mariadb -u$MYSQL_USERNAME -p$MYSQL_PASSWORD poster_generator < ./tests/test_config2.sql
docker_exec dockerdb mariadb -u$MYSQL_USERNAME -p$MYSQL_PASSWORD poster_generator < ./tests/test_img.sql

docker_exec poster_generator sed -i -E "s|<VirtualHost \*:[0-9]+>|<VirtualHost *:${LOCAL_PORT}>|" custom-000-default.conf
docker_exec poster_generator sed -i -E "s|Listen [0-9]+|Listen ${LOCAL_PORT}|" custom-ports.conf

docker_exec poster_generator cp custom-000-default.conf /etc/apache2/sites-enabled/000-default.conf
docker_exec poster_generator cp custom-ports.conf /etc/apache2/ports.conf

docker_exec poster_generator bash -c "echo 'ServerName localhost' >> /etc/apache2/apache2.conf"

docker_exec poster_generator apachectl graceful

echo "Apache2 is ready."
echo "Running on Port:${LOCAL_PORT}"

if [[ "$run_tests" -eq "1" ]]; then
	echo Running Test Sequence...

	docker_exec poster_generator apachectl configtest

	docker_exec poster_generator curl http://${LOCAL_HOST}:${LOCAL_PORT}/login.php | grep title
	sleep 1
	docker_exec poster_generator curl http://${LOCAL_HOST}:${LOCAL_PORT}/pages/login.php | grep title

	echo Running Backend-Tests...

	echo Run tests on Test-DB: ${DB_NAME}

	docker_exec poster_generator php testing.php $DB_NAME $*
	CODE=$?

	echo -----------------
	# sudo cat /var/log/apache2/error.log | grep servername

	# docker-compose exec dockerdb mariadb -u$MYSQL_USERNAME -p$MYSQL_PASSWORD poster_generator > ./tests/results_backend_test.sql

	maria_db_exec "DROP DATABASE IF EXISTS poster_generator;"
	maria_db_exec "CREATE DATABASE IF NOT EXISTS poster_generator;"

	docker_exec dockerdb mariadb -u$MYSQL_USERNAME -p$MYSQL_PASSWORD poster_generator < ./tests/test_config2.sql
	docker_exec dockerdb mariadb -u$MYSQL_USERNAME -p$MYSQL_PASSWORD poster_generator < ./tests/test_img.sql

	maria_db_exec "GRANT ALL PRIVILEGES ON poster_generator.* TO 'poster_generator'@'%' IDENTIFIED BY 'password'; FLUSH PRIVILEGES;"

	# php -r 'foreach(get_defined_functions()["internal"] as $i) {echo $i . "\n";};' > ./tests/php_build_in_func

	# for i in __halt_compiler abstract and array as break callable case catch class clone const continue declare default die do echo else elseif empty enddeclare endfor endforeach endif endswitch endwhile eval exit extends final for foreach function global goto if implements include include_once instanceof insteadof interface isset list namespace new or print private protected public require require_once return static switch throw trait try unset use var while xor
	# do
	# echo $i >> ./tests/php_build_in_func
	# done

	base_dir="./"
	requirements="${base_dir}requirements.txt"

	docker_run="docker.sh"

	if [[ ! -e $docker_run ]]; then
		docker_run="../docker.sh"
	fi

	if [[ ! -e $requirements ]]; then
		base_dir="tests/"
		requirements="${base_dir}requirements.txt"
	fi

	if ! [[ -e $requirements ]]; then
		echo "$requirements not found"
		exit 1
	fi

	docker_exec_env -e LOCAL_HOST=${LOCAL_HOST} -e LOCAL_PORT=${LOCAL_PORT} poster_generator /venv/bin/python /var/www/html/tests/poster_tests.py
	CODE=$?

	echo --- Backend Coverage ---
	# python3 ./tests/coverage.py ./testing.php 2 0 0

	echo --- Frontend Coverage ---
	echo -

	echo Finish Testing
fi

exit $CODE
