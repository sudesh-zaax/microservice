# Determine if .env file exist
ifneq ("$(wildcard .env)","")
	include .env
endif

ifndef INSIDE_DOCKER_CONTAINER
	INSIDE_DOCKER_CONTAINER = 0
endif

export WWWUSER := $(shell id -u)
export WWWGROUP := $(shell id -g)
PHP_USER := -u www-data
PROJECT_NAME := -p ${COMPOSE_PROJECT_NAME}
ERROR_ONLY_FOR_HOST = @printf "\033[33mThis command for host machine\033[39m\n"


define ENV_VARS
	$(shell if [ -f .env ]; then export $$(grep -v '^#' .env | xargs); fi)
endef

define DOCKER_BUILD
	@$(ENV_VARS) docker compose -f $1 build
endef

define DOCKER_START
	@$(ENV_VARS) docker compose -f $1 $(PROJECT_NAME) up -d
endef

define DOCKER_STOP
	@$(ENV_VARS) docker compose -f $1 $(PROJECT_NAME) stop
endef

define DOCKER_DOWN
	@$(ENV_VARS) docker compose -f $1 $(PROJECT_NAME) down
endef

help: ## Shows available commands with description
	@echo "\033[34mList of available commands:\033[39m"
	@grep -E '^[a-zA-Z-]+:.*?## .*$$' Makefile | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "[32m%-27s[0m %s\n", $$1, $$2}'

build: ## Build dev environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	$(call DOCKER_BUILD,docker-compose.yml)
else
	$(ERROR_ONLY_FOR_HOST)
endif

build-test: ## Build test or continuous integration environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	$(call DOCKER_BUILD,docker-compose-test.yml)
else
	$(ERROR_ONLY_FOR_HOST)
endif

build-staging: ## Build staging environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	$(call DOCKER_BUILD,docker-compose-staging.yml)
else
	$(ERROR_ONLY_FOR_HOST)
endif

build-prod: ## Build prod environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	$(call DOCKER_BUILD,docker-compose-prod.yml)
else
	$(ERROR_ONLY_FOR_HOST)
endif

start: ## Start dev environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	$(call DOCKER_START,docker-compose.yml)
else
	$(ERROR_ONLY_FOR_HOST)
endif

start-test: ## Start test or continuous integration environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	$(call DOCKER_START,docker-compose-test.yml)
else
	$(ERROR_ONLY_FOR_HOST)
endif

start-staging: ## Start staging environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	$(call DOCKER_START,docker-compose-staging.yml)
else
	$(ERROR_ONLY_FOR_HOST)
endif

start-prod: ## Start prod environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	$(call DOCKER_START,docker-compose-prod.yml)
else
	$(ERROR_ONLY_FOR_HOST)
endif

stop: ## Stop dev environment containers
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	$(call DOCKER_STOP,docker-compose.yml)
else
	$(ERROR_ONLY_FOR_HOST)
endif

stop-test: ## Stop test or continuous integration environment containers
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	$(call DOCKER_STOP,docker-compose-test.yml)
else
	$(ERROR_ONLY_FOR_HOST)
endif

stop-staging: ## Stop staging environment containers
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	$(call DOCKER_STOP,docker-compose-staging.yml)
else
	$(ERROR_ONLY_FOR_HOST)
endif

stop-prod: ## Stop and remove prod environment containers
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	$(call DOCKER_STOP,docker-compose-prod.yml)
else
	$(ERROR_ONLY_FOR_HOST)
endif

down: ## Stop and remove dev environment containers, networks
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	$(call DOCKER_DOWN,docker-compose.yml)
else
	$(ERROR_ONLY_FOR_HOST)
endif

down-test: ## Stop and remove test or continuous integration environment containers, networks
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	$(call DOCKER_DOWN,docker-compose-test.yml)
else
	$(ERROR_ONLY_FOR_HOST)
endif

down-staging: ## Stop and remove staging environment containers, networks
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	$(call DOCKER_DOWN,docker-compose-staging.yml)
else
	$(ERROR_ONLY_FOR_HOST)
endif

down-prod: ## Stop and remove prod environment containers, networks
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	$(call DOCKER_DOWN,docker-compose-prod.yml)
else
	$(ERROR_ONLY_FOR_HOST)
endif

restart: stop start ## Stop and start dev environment
restart-test: stop-test start-test ## Stop and start test or continuous integration environment
restart-staging: stop-staging start-staging ## Stop and start staging environment
restart-prod: stop-prod start-prod ## Stop and start prod environment
