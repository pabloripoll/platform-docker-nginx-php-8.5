# This Makefile requires GNU Make.
MAKEFLAGS += --silent

# Settings
ifeq ($(strip $(OS)),Windows_NT) # is Windows_NT on XP, 2000, 7, Vista, 10...
    DETECTED_OS := Windows
	C_BLU=''
	C_GRN=''
	C_RED=''
	C_YEL=''
	C_END=''
else
    DETECTED_OS := $(shell uname) # same as "uname -s"
	C_BLU='\033[0;34m'
	C_GRN='\033[0;32m'
	C_RED='\033[0;31m'
	C_YEL='\033[0;33m'
	C_END='\033[0m'
endif

include .env

ROOT_DIR=$(patsubst %/,%,$(dir $(realpath $(firstword $(MAKEFILE_LIST)))))
DIR_BASENAME=$(shell basename $(ROOT_DIR))

# -------------------------------------------------------------------------------------------------
#  Help
# -------------------------------------------------------------------------------------------------
.PHONY: help

help: ## shows this Makefile help message
	echo "Usage: $$ make "${C_GRN}"[target]"${C_END}
	echo ${C_GRN}"Targets:"${C_END}
	awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z0-9_-]+:.*?## / {printf "$$ make \033[0;33m%-30s\033[0m %s\n", $$1, $$2}' ${MAKEFILE_LIST} | column -t -c 2 -s ':#'

# -------------------------------------------------------------------------------------------------
#  System
# -------------------------------------------------------------------------------------------------
.PHONY: local-info local-ownership local-ownership-set services-set services-create services-info services-destroy

local_ip ?= $(word 1,$(shell hostname -I))
local-info: ## shows local machine ip and container ports set
	echo ${C_BLU}"Local IP / Hostname:"${C_END} ${C_YEL}"$(local_ip)"${C_END}

user ?= ${USER}
group ?= root
local-ownership: ## shows local ownership
	echo $(user):$(group)

local-ownership-set: ## sets recursively local root directory ownership
	$(SUDO) chown -R ${user}:${group} $(ROOT_DIR)/

services-set: ## sets all container services
	$(MAKE) apirest-set mailer-set db-set broker-set mongodb-set redis-set

services-create: ## builds and starts up all container services
	$(MAKE) apirest-create mailer-create db-create broker-create mongodb-create redis-create

services-info: ## shows all container services information
	$(MAKE) apirest-info mailer-info db-info broker-info mongodb-info redis-info

services-destroy: ## destroys all container services
	$(MAKE) apirest-destroy mailer-destroy db-destroy broker-destroy mongodb-destroy redis-destroy

# -------------------------------------------------------------------------------------------------
#  Backend API Service
# -------------------------------------------------------------------------------------------------
.PHONY: apirest-hostcheck apirest-info apirest-set apirest-create apirest-network apirest-ssh apirest-start apirest-stop apirest-destroy

apirest-hostcheck: ## shows this project ports availability on local machine for apirest container
	cd platform/$(APIREST_PLTF) && $(MAKE) port-check

apirest-info: ## shows the apirest docker related information
	cd platform/$(APIREST_PLTF) && $(MAKE) info

apirest-set: ## sets the apirest enviroment file to build the container
	cd platform/$(APIREST_PLTF) && $(MAKE) env-set

apirest-create: ## creates the apirest container from Docker image
	cd platform/$(APIREST_PLTF) && $(MAKE) build up

apirest-network: ## creates the apirest container network - execute this recipe first before others
	$(MAKE) apirest-stop
	cd platform/$(APIREST_PLTF) && $(DOCKER_COMPOSE) -f docker-compose.yml -f docker-compose.network.yml up -d

apirest-ssh: ## enters the apirest container shell
	cd platform/$(APIREST_PLTF) && $(MAKE) ssh

apirest-start: ## starts the apirest container running
	cd platform/$(APIREST_PLTF) && $(MAKE) start

apirest-stop: ## stops the apirest container but its assets will not be destroyed
	cd platform/$(APIREST_PLTF) && $(MAKE) stop

apirest-restart: ## restarts the running apirest container
	cd platform/$(APIREST_PLTF) && $(MAKE) restart

apirest-destroy: ## destroys completly the apirest container
	echo ${C_RED}"Attention!"${C_END};
	echo ${C_YEL}"You're about to remove the "${C_BLU}"$(APIREST_PLTF)"${C_END}" container and delete its image resource."${C_END};
	@echo -n ${C_RED}"Are you sure to proceed? "${C_END}"[y/n]: " && read response && if [ $${response:-'n'} != 'y' ]; then \
        echo ${C_GRN}"K.O.! container has been stopped but not destroyed."${C_END}; \
    else \
		cd platform/$(APIREST_PLTF) && $(MAKE) stop clear destroy; \
		echo -n ${C_GRN}"Do you want to clear DOCKER cache? "${C_END}"[y/n]: " && read response && if [ $${response:-'n'} != 'y' ]; then \
			echo ${C_YEL}"The following command is delegated to be executed by user:"${C_END}; \
			echo "$$ $(DOCKER) system prune"; \
		else \
			$(DOCKER) system prune; \
			echo ${C_GRN}"O.K.! DOCKER cache has been cleared up."${C_END}; \
		fi \
	fi

# -------------------------------------------------------------------------------------------------
#  Mailer Service
# -------------------------------------------------------------------------------------------------
.PHONY: mailer-hostcheck mailer-info mailer-set mailer-create mailer-network mailer-ssh mailer-start mailer-stop mailer-destroy

mailer-hostcheck: ## shows this project ports availability on local machine for mailer container
	cd platform/$(MAILER_PLTF) && $(MAKE) port-check

mailer-info: ## shows the mailer docker related information
	cd platform/$(MAILER_PLTF) && $(MAKE) info

mailer-set: ## sets the mailer enviroment file to build the container
	cd platform/$(MAILER_PLTF) && $(MAKE) env-set

mailer-create: ## creates the mailer container from Docker image
	cd platform/$(MAILER_PLTF) && $(MAKE) build up

mailer-network: ## creates the mailer container network - execute this recipe first before others
	$(MAKE) mailer-stop
	cd platform/$(MAILER_PLTF) && $(DOCKER_COMPOSE) -f docker-compose.yml -f docker-compose.network.yml up -d

mailer-ssh: ## enters the mailer container shell
	cd platform/$(MAILER_PLTF) && $(MAKE) ssh

mailer-start: ## starts the mailer container running
	cd platform/$(MAILER_PLTF) && $(MAKE) start

mailer-stop: ## stops the mailer container but its assets will not be destroyed
	cd platform/$(MAILER_PLTF) && $(MAKE) stop

mailer-restart: ## restarts the running mailer container
	cd platform/$(MAILER_PLTF) && $(MAKE) restart

mailer-destroy: ## destroys completly the mailer container
	echo ${C_RED}"Attention!"${C_END};
	echo ${C_YEL}"You're about to remove the "${C_BLU}"$(MAILER_PLTF)"${C_END}" container and delete its image resource."${C_END};
	@echo -n ${C_RED}"Are you sure to proceed? "${C_END}"[y/n]: " && read response && if [ $${response:-'n'} != 'y' ]; then \
        echo ${C_GRN}"K.O.! container has been stopped but not destroyed."${C_END}; \
    else \
		cd platform/$(MAILER_PLTF) && $(MAKE) stop clear destroy; \
		echo -n ${C_GRN}"Do you want to clear DOCKER cache? "${C_END}"[y/n]: " && read response && if [ $${response:-'n'} != 'y' ]; then \
			echo ${C_YEL}"The following command is delegated to be executed by user:"${C_END}; \
			echo "$$ $(DOCKER) system prune"; \
		else \
			$(DOCKER) system prune; \
			echo ${C_GRN}"O.K.! DOCKER cache has been cleared up."${C_END}; \
		fi \
	fi

# -------------------------------------------------------------------------------------------------
#  Database Service
# -------------------------------------------------------------------------------------------------
.PHONY: db-hostcheck db-info db-set db-create db-ssh db-start db-stop db-destroy

db-hostcheck: ## shows this project ports availability on local machine for database container
	cd platform/$(DATABASE_PLTF) && $(MAKE) port-check

db-info: ## shows docker related information
	cd platform/$(DATABASE_PLTF) && $(MAKE) info

db-set: ## sets the database enviroment file to build the container
	cd platform/$(DATABASE_PLTF) && $(MAKE) env-set

db-create: ## creates the database container from Docker image
	cd platform/$(DATABASE_PLTF) && $(MAKE) build up

db-network: ## creates the database container external network
	$(MAKE) apirest-stop
	cd platform/$(DATABASE_PLTF) && $(DOCKER_COMPOSE) -f docker-compose.yml -f docker-compose.network.yml up -d

db-ssh: ## enters the apirest container shell
	cd platform/$(DATABASE_PLTF) && $(MAKE) ssh

db-start: ## starts the database container running
	cd platform/$(DATABASE_PLTF) && $(MAKE) start

db-stop: ## stops the database container but its assets will not be destroyed
	cd platform/$(DATABASE_PLTF) && $(MAKE) stop

db-restart: ## restarts the running database container
	cd platform/$(DATABASE_PLTF) && $(MAKE) restart

db-destroy: ## destroys completly the database container with its data
	echo ${C_RED}"Attention!"${C_END};
	echo ${C_YEL}"You're about to remove the "${C_BLU}"$(DATABASE_PLTF)"${C_END}" container and delete its image resource and persistance data."${C_END};
	@echo -n ${C_RED}"Are you sure to proceed? "${C_END}"[y/n]: " && read response && if [ $${response:-'n'} != 'y' ]; then \
        echo ${C_GRN}"K.O.! container has been stopped but not destroyed."${C_END}; \
    else \
		cd platform/$(DATABASE_PLTF) && $(MAKE) clear destroy; \
		echo -n ${C_GRN}"Do you want to clear DOCKER cache? "${C_END}"[y/n]: " && read response && if [ $${response:-'n'} != 'y' ]; then \
			echo ${C_YEL}"The following commands are delegated to be executed by user:"${C_END}; \
			echo "$$ $(DOCKER) system prune"; \
			echo "$$ $(DOCKER) volume prune"; \
		else \
			$(DOCKER) system prune; \
			$(DOCKER) volume prune; \
			echo ${C_GRN}"O.K.! DOCKER cache has been cleared up."${C_END}; \
		fi \
	fi

.PHONY: db-test-up db-test-down

db-test-up: ## creates a side database for testing porpuses
	cd platform/$(DATABASE_PLTF) && $(MAKE) test-up

db-test-down: ## drops the side testing database
	cd platform/$(DATABASE_PLTF) && $(MAKE) test-down

.PHONY: db-sql-install db-sql-replace db-sql-backup db-sql-remote db-copy-remote

db-sql-install: ## migrates sql file with schema / data into the container main database to init a project
	$(MAKE) local-ownership-set;
	cd platform/$(DATABASE_PLTF) && $(MAKE) sql-install

db-sql-replace: ## replaces the container main database with the latest database .sql backup file
	$(MAKE) local-ownership-set;
	cd platform/$(DATABASE_PLTF) && $(MAKE) sql-replace

db-sql-backup: ## copies the container main database as backup into a .sql file
	$(MAKE) local-ownership-set;
	cd platform/$(DATABASE_PLTF) && $(MAKE) sql-backup

db-sql-drop: ## drops the container main database but recreates the database without schema as a reset action
	$(MAKE) local-ownership-set;
	cd platform/$(DATABASE_PLTF) && $(MAKE) sql-drop

# -------------------------------------------------------------------------------------------------
#  MongoDB Service
# -------------------------------------------------------------------------------------------------
.PHONY: mongodb-hostcheck mongodb-info mongodb-set mongodb-create mongodb-ssh mongodb-start mongodb-stop mongodb-destroy

mongodb-hostcheck: ## shows this project ports availability on local machine for database container
	cd platform/$(MONGODB_PLTF) && $(MAKE) port-check

mongodb-info: ## shows docker related information
	cd platform/$(MONGODB_PLTF) && $(MAKE) info

mongodb-set: ## sets the database enviroment file to build the container
	cd platform/$(MONGODB_PLTF) && $(MAKE) env-set

mongodb-create: ## creates the database container from Docker image
	cd platform/$(MONGODB_PLTF) && $(MAKE) build up

mongodb-network: ## creates the database container external network
	$(MAKE) apirest-stop
	cd platform/$(MONGODB_PLTF) && $(DOCKER_COMPOSE) -f docker-compose.yml -f docker-compose.network.yml up -d

mongodb-ssh: ## enters the apirest container shell
	cd platform/$(MONGODB_PLTF) && $(MAKE) ssh

mongodb-start: ## starts the database container running
	cd platform/$(MONGODB_PLTF) && $(MAKE) start

mongodb-stop: ## stops the database container but its assets will not be destroyed
	cd platform/$(MONGODB_PLTF) && $(MAKE) stop

mongodb-restart: ## restarts the running database container
	cd platform/$(MONGODB_PLTF) && $(MAKE) restart

mongodb-destroy: ## destroys completly the database container with its data
	echo ${C_RED}"Attention!"${C_END};
	echo ${C_YEL}"You're about to remove the "${C_BLU}"$(MONGODB_PLTF)"${C_END}" container and delete its image resource and persistance data."${C_END};
	@echo -n ${C_RED}"Are you sure to proceed? "${C_END}"[y/n]: " && read response && if [ $${response:-'n'} != 'y' ]; then \
        echo ${C_GRN}"K.O.! container has been stopped but not destroyed."${C_END}; \
    else \
		cd platform/$(MONGODB_PLTF) && $(MAKE) stop clear destroy; \
		echo -n ${C_GRN}"Do you want to clear DOCKER cache? "${C_END}"[y/n]: " && read response && if [ $${response:-'n'} != 'y' ]; then \
			echo ${C_YEL}"The following commands are delegated to be executed by user:"${C_END}; \
			echo "$$ $(DOCKER) system prune"; \
			echo "$$ $(DOCKER) volume prune"; \
		else \
			$(DOCKER) system prune; \
			$(DOCKER) volume prune; \
			echo ${C_GRN}"O.K.! DOCKER cache has been cleared up."${C_END}; \
		fi \
	fi

# -------------------------------------------------------------------------------------------------
#  Redis Service
# -------------------------------------------------------------------------------------------------
.PHONY: redis-hostcheck redis-info redis-set redis-create redis-ssh redis-start redis-stop redis-destroy

redis-hostcheck: ## shows this project ports availability on local machine for database container
	cd platform/$(REDIS_PLTF) && $(MAKE) port-check

redis-info: ## shows docker related information
	cd platform/$(REDIS_PLTF) && $(MAKE) info

redis-set: ## sets the database enviroment file to build the container
	cd platform/$(REDIS_PLTF) && $(MAKE) env-set

redis-create: ## creates the database container from Docker image
	cd platform/$(REDIS_PLTF) && $(MAKE) build up

redis-network: ## creates the database container external network
	$(MAKE) apirest-stop
	cd platform/$(REDIS_PLTF) && $(DOCKER_COMPOSE) -f docker-compose.yml -f docker-compose.network.yml up -d

redis-ssh: ## enters the apirest container shell
	cd platform/$(REDIS_PLTF) && $(MAKE) ssh

redis-start: ## starts the database container running
	cd platform/$(REDIS_PLTF) && $(MAKE) start

redis-stop: ## stops the database container but its assets will not be destroyed
	cd platform/$(REDIS_PLTF) && $(MAKE) stop

redis-restart: ## restarts the running database container
	cd platform/$(REDIS_PLTF) && $(MAKE) restart

redis-destroy: ## destroys completly the database container with its data
	echo ${C_RED}"Attention!"${C_END};
	echo ${C_YEL}"You're about to remove the "${C_BLU}"$(REDIS_PLTF)"${C_END}" container and delete its image resource and persistance data."${C_END};
	@echo -n ${C_RED}"Are you sure to proceed? "${C_END}"[y/n]: " && read response && if [ $${response:-'n'} != 'y' ]; then \
        echo ${C_GRN}"K.O.! container has been stopped but not destroyed."${C_END}; \
    else \
		cd platform/$(REDIS_PLTF) && $(MAKE) stop clear destroy; \
		echo -n ${C_GRN}"Do you want to clear DOCKER cache? "${C_END}"[y/n]: " && read response && if [ $${response:-'n'} != 'y' ]; then \
			echo ${C_YEL}"The following commands are delegated to be executed by user:"${C_END}; \
			echo "$$ $(DOCKER) system prune"; \
			echo "$$ $(DOCKER) volume prune"; \
		else \
			$(DOCKER) system prune; \
			$(DOCKER) volume prune; \
			echo ${C_GRN}"O.K.! DOCKER cache has been cleared up."${C_END}; \
		fi \
	fi

# -------------------------------------------------------------------------------------------------
#  Broker Service
# -------------------------------------------------------------------------------------------------
.PHONY: broker-hostcheck broker-info broker-set broker-create broker-network broker-ssh broker-start broker-stop broker-destroy

broker-hostcheck: ## shows this project ports availability on local machine for broker container
	cd platform/$(BROKER_PLTF) && $(MAKE) port-check

broker-info: ## shows the broker docker related information
	cd platform/$(BROKER_PLTF) && $(MAKE) info

broker-set: ## sets the broker enviroment file to build the container
	cd platform/$(BROKER_PLTF) && $(MAKE) env-set

broker-create: ## creates the broker container from Docker image
	cd platform/$(BROKER_PLTF) && $(MAKE) build up

broker-network: ## creates the broker container network - execute this recipe first before others
	$(MAKE) broker-stop
	cd platform/$(BROKER_PLTF) && $(DOCKER_COMPOSE) -f docker-compose.yml -f docker-compose.network.yml up -d

broker-ssh: ## enters the broker container shell
	cd platform/$(BROKER_PLTF) && $(MAKE) ssh

broker-start: ## starts the broker container running
	cd platform/$(BROKER_PLTF) && $(MAKE) start

broker-stop: ## stops the broker container but its assets will not be destroyed
	cd platform/$(BROKER_PLTF) && $(MAKE) stop

broker-restart: ## restarts the running broker container
	cd platform/$(BROKER_PLTF) && $(MAKE) restart

broker-destroy: ## destroys completly the broker container
	echo ${C_RED}"Attention!"${C_END};
	echo ${C_YEL}"You're about to remove the "${C_BLU}"$(BROKER_PLTF)"${C_END}" container and delete its image resource."${C_END};
	@echo -n ${C_RED}"Are you sure to proceed? "${C_END}"[y/n]: " && read response && if [ $${response:-'n'} != 'y' ]; then \
        echo ${C_GRN}"K.O.! container has been stopped but not destroyed."${C_END}; \
    else \
		cd platform/$(BROKER_PLTF) && $(MAKE) stop clear destroy; \
		echo -n ${C_GRN}"Do you want to clear DOCKER cache? "${C_END}"[y/n]: " && read response && if [ $${response:-'n'} != 'y' ]; then \
			echo ${C_YEL}"The following command is delegated to be executed by user:"${C_END}; \
			echo "$$ $(DOCKER) system prune"; \
		else \
			$(DOCKER) system prune; \
			echo ${C_GRN}"O.K.! DOCKER cache has been cleared up."${C_END}; \
		fi \
	fi

# -------------------------------------------------------------------------------------------------
#  Repository Helper
# -------------------------------------------------------------------------------------------------
.PHONY: repo-flush repo-commit

repo-flush: ## echoes clearing commands for git repository cache on local IDE and sub-repository tracking remove
	echo ${C_YEL}"Clear repository for untracked files:"${C_END}
	echo ${C_YEL}"$$"${C_END}" git rm -rf --cached .; git add .; git commit -m \"maint: cache cleared for untracked files\""
	echo ""
	echo ${C_YEL}"Platform repository against REST API repository:"${C_END}
	echo ${C_YEL}"$$"${C_END}" git rm -r --cached -- \"apirest/*\" \":(exclude)apirest/.gitkeep\""

repo-commit: ## echoes common git commands
	echo ${C_YEL}"Common commiting commands:"${C_END}
	echo ${C_YEL}"$$"${C_END}" git add . && git commit -m \"feat: ... \""
	echo ""
	echo ${C_YEL}"For fixing pushed commit comment:"${C_END}
	echo ${C_YEL}"$$"${C_END}" git commit --amend"
	echo ${C_YEL}"$$"${C_END}" git push --force origin [branch]"
