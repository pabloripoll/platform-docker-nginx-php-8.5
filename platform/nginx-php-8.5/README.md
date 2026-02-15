<div id="top-header" style="with:100%;height:auto;text-align:right;"></div>

# NGINX + PHP 8.5

- [./back](../../README.md)
- [Container Installation](#container)
- [Container Management](#management)
<br>

## <a id="container"></a>Container Installation

Before building the container:

- If no GO app is on `./apirest` *(Or your custom binded directory)*, It would be better to copy the example on it.
- set the required configuration files by copinf and updating them depending on your project in:

### Dockerfile

You might be using this repository with different databases connection. Copy from sample and set the correct packages and modules required by commenting them out, and others not required is recommended to be commented. In this way, the container will be built only with the neccessary settings an in less time

- `./docker/Dockerfile.sample` -> `./docker/Dockerfile`

### NGINX

The default example is a server block for a REST API, but it can be use for webapps too

- `./docker/config/nginx/conf.d-sample/default.conf` -> `./docker/config/nginx/conf.d/default.conf`

### PHP

These are the default PHP settings. Copy them all with the proper configuration required for your project

- `./docker/config/nginx/conf.d-sample/fpm-pool.conf` -> `./docker/config/nginx/conf.d/fpm-pool.conf`
- `./docker/config/php/conf.d-sample/php.ini` -> `./docker/config/php/conf.d/php.ini`

```bash
# Memory limit
memory_limit = 128M # must be equal or less than container max memory usage
```

To automatically run the PHP application, create the Supervisord service that runs the application. You can choose the dev or production version, and set it according to your project requirements

- `./docker/config/supervisor/conf.d-sample/php-fpm.conf` -> `./docker/config/supervisor/conf.d/php-fpm.conf`

```bash
[program:php-fpm]
command=php-fpm85 -F
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autostart=true
autorestart=true
startretries=0
```
<br>

## <a id="management"></a>Container Management

To manage the container, run the GNU Make recipes
```bash
$ make help
Usage: $ make [target]
Targets:
$ make help                           shows this Makefile help message
$ make port-check                     shows this project port availability on local machine
$ make env                            checks if docker .env file exists
$ make env-set                        sets docker .env file
$ make info                           shows container information
$ make ssh                            enters the container shell
$ make build                          builds the container from Dockerfile
$ make up                             attaches to containers for a service and also starts any linked services
$ make start                          starts the container and put on running
$ make stop                           stops the running container but data will not be destroyed
$ make restart                        restarts the running container
$ make clear                          removes container from Docker running containers
$ make destroy                        delete container image from Docker cache
$ make dev                            sets a development enviroment
$ make supervisord-conf               lists supervisord services set on the running container
$ make supervisord-update             updates supervisord services without the need of stoping or rebuilding the container
$ make nginx-conf                     shows nginx configuration set on the running container
$ make nginx-update                   updates nginx configuration without the need of stoping or rebuilding the container
$ make nginx-default-conf             shows nginx default server block set on the running container
$ make nginx-default-update           updates nginx default server block without the need of stoping or rebuilding the container
$ make php-conf                       shows php configuration set on the running container
$ make php-conf-update                updates php configuration without the need of stoping or rebuilding the container
$ make php-fpm-conf                   shows PHP-FPM configuration set on the running container
$ make php-fpm-update                 updates PHP-FPM configuration without the need of stoping or rebuilding the container
```
<br>

## Contributing

Contributions are very welcome! Please open issues or submit PRs for improvements, new features, or bug fixes.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/YourFeature`)
3. Commit your changes (`git commit -am 'feat: Add new feature'`)
4. Push to the branch (`git push origin feature/YourFeature`)
5. Create a new Pull Request
<br><br>

## License

This project is open-sourced under the [MIT license](LICENSE).

<!-- FOOTER -->
<br>

---

<br>

- [GO TOP ⮙](#top-header)

