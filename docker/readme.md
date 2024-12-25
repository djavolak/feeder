# Step 1
install docker https://docs.docker.com/get-docker/
# Step 2
create .env file and enter your variables and setup your nginx.conf if needeed
# Step 3
add extensions you need to be instaled inside PHP.Dockerfile
# Step 4
in the same folder where docker-compose.yml is enter command docker compose up -d, if evrything works http://127.0.0.1/ server should be running on localhost

To turn off container use docker compose down command

If you need to use composer.phar inside of project via linux machine you can enter shell of the machine using
command docker exec -it <name of the container> bash, php container is named using project name (projectName-php) but sometimes docker can add numbers to it
(projectName-php-1). To check names of runnig containers you can use docker ps --format "{{.Names}}"
you can execute composer using docker exec -it <container_name_or_id> sh -c "cd /path/to/your/app && composer install" 