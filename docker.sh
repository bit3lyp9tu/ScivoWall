#!/bin/bash

# Default values
run_tests=0
LOCAL_PORT=""

# Help message
help_message() {
	echo "Usage: display_mongodb_gui.sh [OPTIONS]"
	echo "Options:"
	echo "  --local-port       Local port to bind for the GUI"
	echo "  --run_tests        Run tests before starting"
	echo "  --help             Show this help message"
}

# Parse command-line arguments
while [[ "$#" -gt 0 ]]; do
	case $1 in
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

#sudo mkdir -p /poster_generator_json/

CURRENT_USER=$(whoami)

if groups "$CURRENT_USER" | grep -q "\bdocker\b"; then
	docker-compose build && docker-compose up -d || echo "Failed to build container"
else
	sudo docker-compose build && sudo docker-compose up -d || echo "Failed to build container"
fi

#    mariadb -h 127.0.0.1 -P 3800 -u poster_generator -ppassword poster_generator < ./tests/test_config2.sql

function maria_db_exec {
	docker-compose exec dockerdb mariadb -uroot -ppassword -e "$1"
}

maria_db_exec "GRANT ALL PRIVILEGES ON poster_generator.* TO 'poster_generator'@'%' IDENTIFIED BY 'password'; FLUSH PRIVILEGES;"
maria_db_exec "CREATE DATABASE IF NOT EXISTS poster_generator;"
