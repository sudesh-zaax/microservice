# Environment handling
# Read current environment from .env file if it exists, otherwise default to dev
ENV := $(shell if [ -f docker/.env ]; then grep COMPOSE_PROJECT_NAME docker/.env | sed 's/.*_\(.*\)/\1/'; else echo "dev"; fi)
ENV_FILE := docker/.env.$(ENV)

# Include environment files
ifneq ("$(wildcard $(ENV_FILE))","")
	include $(ENV_FILE)
else ifneq ("$(wildcard docker/.env)","")
	include docker/.env
endif

# Declare phony targets
.PHONY: help build build-test build-staging build-prod start start-test start-staging start-prod \
        stop stop-test stop-staging stop-prod down down-test down-staging down-prod restart \
        restart-test restart-staging restart-prod status cleanup logs validate-env use-env

# Version check
MIN_DOCKER_COMPOSE_VERSION := 2.0.0
DOCKER_COMPOSE_VERSION := $(shell docker compose version --short 2>/dev/null)

ifndef INSIDE_DOCKER_CONTAINER
	INSIDE_DOCKER_CONTAINER = 0
endif

export WWWUSER := $(shell id -u)
export WWWGROUP := $(shell id -g)
PHP_USER := -u www-data
PROJECT_NAME := -p ${COMPOSE_PROJECT_NAME}
ERROR_ONLY_FOR_HOST = @printf "\033[33mThis command for host machine\033[39m\n"
ERROR_ENV_FILE_MISSING = @printf "\033[31mError: $(ENV_FILE) file is missing\033[39m\n"

# Color definitions
YELLOW := \033[33m
GREEN := \033[32m
RED := \033[31m
BLUE := \033[34m
NC := \033[39m

define ENV_VARS
	$(shell if [ -f $(ENV_FILE) ]; then \
		export $$(grep -v '^#' $(ENV_FILE) | xargs); \
	elif [ -f docker/.env ]; then \
		export $$(grep -v '^#' docker/.env | xargs); \
	fi)
endef

define DOCKER_BUILD
	@$(ENV_VARS) docker compose -f docker/$1 build
endef

define DOCKER_START
	@$(ENV_VARS) docker compose -f docker/$1 $(PROJECT_NAME) up -d
endef

define DOCKER_STOP
	@$(ENV_VARS) docker compose -f docker/$1 $(PROJECT_NAME) stop
endef

define DOCKER_DOWN
	@$(ENV_VARS) docker compose -f docker/$1 $(PROJECT_NAME) down
endef

define CHECK_ENV_FILE
	@if [ ! -f $(ENV_FILE) ] && [ ! -f docker/.env ]; then \
		$(ERROR_ENV_FILE_MISSING); \
		exit 1; \
	fi
endef

debug-env: ## Debug environment variables
	@echo "$(BLUE)Environment Debug Information:$(NC)"
	@printf "================================\n"
	@echo "ENV = $(ENV)"
	@echo "ENV_FILE = $(ENV_FILE)"
	@printf "\n"
	@echo "$(BLUE)Current $(GREEN).env$(NC) contents:$(NC)"
	@printf "================================\n"
	@cat docker/.env
	@printf "\n"
	@echo "$(BLUE)Current environment file $(GREEN)$(ENV)$(NC) (using $(ENV_FILE)) contents:"
	@printf "================================\n"
	@cat docker/.env.$(ENV)

help: ## Shows available commands with description
	@echo "$(BLUE)List of available commands:$(NC)"
	@echo "$(BLUE)Current environment: $(GREEN)$(ENV)$(NC) (using $(ENV_FILE))"
	@grep -E '^[a-zA-Z-]+:.*?## .*$$' Makefile | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(GREEN)%-27s$(NC) %s\n", $$1, $$2}'

use-env: ## Switch to a specific environment (usage: make use-env ENV=dev|test|staging|prod)
	@echo "$(BLUE)Switching environment...$(NC)"
	@echo "$(BLUE)Requested environment: $(GREEN)$(ENV)$(NC)"
	@echo "$(BLUE)Looking for file: $(GREEN).env.$(ENV)$(NC)"
	@if [ -f docker/.env.$(ENV) ]; then \
		cp docker/.env.$(ENV) docker/.env; \
		echo "$(GREEN)✓ Copied docker/.env.$(ENV) to docker/.env$(NC)"; \
		echo "$(GREEN)✓ Switched to $(ENV) environment using $(ENV_FILE)$(NC)"; \
		echo "$(BLUE)Current .env contents:$(NC)"; \
		cat docker/.env | grep "COMPOSE_PROJECT_NAME"; \
	else \
		echo "$(RED)Error: .env.$(ENV) file not found$(NC)"; \
		exit 1; \
	fi

validate-env: ## Validate environment setup
	@printf "\n$(BLUE)Environment Validation Report:$(NC)\n"
	@printf "================================\n"
	@printf "$(BLUE)Active Environment:$(NC) $(GREEN)$(ENV)$(NC)\n"
	@printf "$(BLUE)Environment File:$(NC) $(GREEN)$(ENV_FILE)$(NC)\n"
	@printf "$(BLUE)Project Name:$(NC) $(GREEN)$(shell grep COMPOSE_PROJECT_NAME docker/.env | cut -d'=' -f2)$(NC)\n"
	@printf "================================\n"
	$(CHECK_ENV_FILE)
	@if [ -f $(ENV_FILE) ]; then \
		printf "$(GREEN)✓ Environment file $(ENV_FILE) exists$(NC)\n"; \
	else \
		printf "$(YELLOW)⚠ Using default .env file$(NC)\n"; \
	fi
	@if [ -z "$(DOCKER_COMPOSE_VERSION)" ]; then \
		printf "$(RED)✗ Docker Compose not found$(NC)\n"; \
		exit 1; \
	else \
		printf "$(GREEN)✓ Docker Compose version: $(DOCKER_COMPOSE_VERSION)$(NC)\n"; \
	fi
	@printf "\n$(GREEN)Environment is correctly set to: $(ENV)$(NC)\n"

status: ## Show status of all containers
	@echo "$(BLUE)Container Status ($(ENV) environment):$(NC)"
	@cd docker && docker compose ps

logs: ## Show logs of all containers
	@echo "$(BLUE)Container Logs ($(ENV) environment):$(NC)"
	@cd docker && docker compose logs --tail=100 -f

cleanup: ## Remove all unused containers, networks, and images
	@echo "$(YELLOW)Cleaning up Docker resources...$(NC)"
	@docker system prune -f
	@echo "$(GREEN)Cleanup complete$(NC)"

build: validate-env ## Build dev environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Building development environment ($(ENV))...$(NC)"
	$(call DOCKER_BUILD,docker-compose.yml)
	@echo "$(GREEN)Build complete$(NC)"
else
	$(ERROR_ONLY_FOR_HOST)
endif

build-test: validate-env ## Build test or continuous integration environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Building test environment...$(NC)"
	$(call DOCKER_BUILD,docker-compose-test.yml)
	@echo "$(GREEN)Build complete$(NC)"
else
	$(ERROR_ONLY_FOR_HOST)
endif

build-staging: validate-env ## Build staging environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Building staging environment...$(NC)"
	$(call DOCKER_BUILD,docker-compose-staging.yml)
	@echo "$(GREEN)Build complete$(NC)"
else
	$(ERROR_ONLY_FOR_HOST)
endif

build-prod: validate-env ## Build prod environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Building production environment...$(NC)"
	$(call DOCKER_BUILD,docker-compose-prod.yml)
	@echo "$(GREEN)Build complete$(NC)"
else
	$(ERROR_ONLY_FOR_HOST)
endif

start: validate-env ## Start dev environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Starting development environment ($(ENV))...$(NC)"
	$(call DOCKER_START,docker-compose.yml)
	@echo "$(GREEN)Environment started$(NC)"
else
	$(ERROR_ONLY_FOR_HOST)
endif

start-test: validate-env ## Start test or continuous integration environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Starting test environment...$(NC)"
	$(call DOCKER_START,docker-compose-test.yml)
	@echo "$(GREEN)Environment started$(NC)"
else
	$(ERROR_ONLY_FOR_HOST)
endif

start-staging: validate-env ## Start staging environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Starting staging environment...$(NC)"
	$(call DOCKER_START,docker-compose-staging.yml)
	@echo "$(GREEN)Environment started$(NC)"
else
	$(ERROR_ONLY_FOR_HOST)
endif

start-prod: validate-env ## Start prod environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Starting production environment...$(NC)"
	$(call DOCKER_START,docker-compose-prod.yml)
	@echo "$(GREEN)Environment started$(NC)"
else
	$(ERROR_ONLY_FOR_HOST)
endif

stop: ## Stop dev environment containers
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Stopping development environment ($(ENV))...$(NC)"
	$(call DOCKER_STOP,docker-compose.yml)
	@echo "$(GREEN)Environment stopped$(NC)"
else
	$(ERROR_ONLY_FOR_HOST)
endif

stop-test: ## Stop test or continuous integration environment containers
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Stopping test environment...$(NC)"
	$(call DOCKER_STOP,docker-compose-test.yml)
	@echo "$(GREEN)Environment stopped$(NC)"
else
	$(ERROR_ONLY_FOR_HOST)
endif

stop-staging: ## Stop staging environment containers
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Stopping staging environment...$(NC)"
	$(call DOCKER_STOP,docker-compose-staging.yml)
	@echo "$(GREEN)Environment stopped$(NC)"
else
	$(ERROR_ONLY_FOR_HOST)
endif

stop-prod: ## Stop and remove prod environment containers
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Stopping production environment...$(NC)"
	$(call DOCKER_STOP,docker-compose-prod.yml)
	@echo "$(GREEN)Environment stopped$(NC)"
else
	$(ERROR_ONLY_FOR_HOST)
endif

down: ## Stop and remove dev environment containers, networks
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Removing development environment ($(ENV))...$(NC)"
	$(call DOCKER_DOWN,docker-compose.yml)
	@echo "$(GREEN)Environment removed$(NC)"
else
	$(ERROR_ONLY_FOR_HOST)
endif

down-test: ## Stop and remove test or continuous integration environment containers, networks
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Removing test environment...$(NC)"
	$(call DOCKER_DOWN,docker-compose-test.yml)
	@echo "$(GREEN)Environment removed$(NC)"
else
	$(ERROR_ONLY_FOR_HOST)
endif

down-staging: ## Stop and remove staging environment containers, networks
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Removing staging environment...$(NC)"
	$(call DOCKER_DOWN,docker-compose-staging.yml)
	@echo "$(GREEN)Environment removed$(NC)"
else
	$(ERROR_ONLY_FOR_HOST)
endif

down-prod: ## Stop and remove prod environment containers, networks
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Removing production environment...$(NC)"
	$(call DOCKER_DOWN,docker-compose-prod.yml)
	@echo "$(GREEN)Environment removed$(NC)"
else
	$(ERROR_ONLY_FOR_HOST)
endif

restart: stop start ## Stop and start dev environment
restart-test: stop-test start-test ## Stop and start test or continuous integration environment
restart-staging: stop-staging start-staging ## Stop and start staging environment
restart-prod: stop-prod start-prod ## Stop and start prod environment

# Auth Microservice Commands
auth-install: ## Install composer dependencies for auth service
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Installing Auth Service Dependencies (Inside Container)...$(NC)"
	@cd services/auth && composer install
	@echo "$(GREEN)Auth Service Dependencies Installed$(NC)"
else
	@echo "$(BLUE)Installing Auth Service Dependencies (Docker)...$(NC)"
	@cd services/auth && docker compose exec -w /var/www/html auth composer install
	@echo "$(GREEN)Auth Service Dependencies Installed$(NC)"
endif

auth-update: ## Update composer dependencies for auth service
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Updating Auth Service Dependencies (Inside Container)...$(NC)"
	@cd services/auth && composer update
	@echo "$(GREEN)Auth Service Dependencies Updated$(NC)"
else
	@echo "$(BLUE)Updating Auth Service Dependencies (Docker)...$(NC)"
	@cd services/auth && docker compose exec -w /var/www/html auth composer update
	@echo "$(GREEN)Auth Service Dependencies Updated$(NC)"
endif

auth-migrate: ## Run migrations for auth service
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Running Auth Service Migrations (Inside Container)...$(NC)"
	@cd services/auth && php artisan migrate
	@echo "$(GREEN)Auth Service Migrations Completed$(NC)"
else
	@echo "$(BLUE)Running Auth Service Migrations (Docker)...$(NC)"
	@cd services/auth && docker compose exec -w /var/www/html auth php artisan migrate
	@echo "$(GREEN)Auth Service Migrations Completed$(NC)"
endif

auth-seed: ## Run seeders for auth service
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Running Auth Service Seeders (Inside Container)...$(NC)"
	@cd services/auth && php artisan db:seed
	@echo "$(GREEN)Auth Service Seeders Completed$(NC)"
else
	@echo "$(BLUE)Running Auth Service Seeders (Docker)...$(NC)"
	@cd services/auth && docker compose exec -w /var/www/html auth php artisan db:seed
	@echo "$(GREEN)Auth Service Seeders Completed$(NC)"
endif

auth-key: ## Generate application key for auth service
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Generating Auth Service Application Key (Inside Container)...$(NC)"
	@cd services/auth && php artisan key:generate
	@echo "$(GREEN)Auth Service Application Key Generated$(NC)"
else
	@echo "$(BLUE)Generating Auth Service Application Key (Docker)...$(NC)"
	@cd services/auth && docker compose exec -w /var/www/html auth php artisan key:generate
	@echo "$(GREEN)Auth Service Application Key Generated$(NC)"
endif

auth-cache: ## Clear cache for auth service
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Clearing Auth Service Cache (Inside Container)...$(NC)"
	@cd services/auth && php artisan cache:clear
	@cd services/auth && php artisan config:clear
	@cd services/auth && php artisan route:clear
	@echo "$(GREEN)Auth Service Cache Cleared$(NC)"
else
	@echo "$(BLUE)Clearing Auth Service Cache (Docker)...$(NC)"
	@cd services/auth && docker compose exec -w /var/www/html auth php artisan cache:clear
	@cd services/auth && docker compose exec -w /var/www/html auth php artisan config:clear
	@cd services/auth && docker compose exec -w /var/www/html auth php artisan route:clear
	@echo "$(GREEN)Auth Service Cache Cleared$(NC)"
endif

# Policy Microservice Commands
policy-install: ## Install composer dependencies for policy service
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Installing Policy Service Dependencies (Inside Container)...$(NC)"
	@cd services/policy && composer install
	@echo "$(GREEN)Policy Service Dependencies Installed$(NC)"
else
	@echo "$(BLUE)Installing Policy Service Dependencies (Docker)...$(NC)"
	@cd services/auth && docker compose exec -w /var/www/html policy composer install
	@echo "$(GREEN)Policy Service Dependencies Installed$(NC)"
endif

policy-update: ## Update composer dependencies for policy service
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Updating Policy Service Dependencies (Inside Container)...$(NC)"
	@cd services/policy && composer update
	@echo "$(GREEN)Policy Service Dependencies Updated$(NC)"
else
	@echo "$(BLUE)Updating Policy Service Dependencies (Docker)...$(NC)"
	@cd services/auth && docker compose exec -w /var/www/html policy composer update
	@echo "$(GREEN)Policy Service Dependencies Updated$(NC)"
endif

policy-migrate: ## Run migrations for policy service
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Running Policy Service Migrations (Inside Container)...$(NC)"
	@cd services/policy && php artisan migrate
	@echo "$(GREEN)Policy Service Migrations Completed$(NC)"
else
	@echo "$(BLUE)Running Policy Service Migrations (Docker)...$(NC)"
	@cd services/auth && docker compose exec -w /var/www/html policy php artisan migrate
	@echo "$(GREEN)Policy Service Migrations Completed$(NC)"
endif

policy-seed: ## Run seeders for policy service
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Running Policy Service Seeders (Inside Container)...$(NC)"
	@cd services/policy && php artisan db:seed
	@echo "$(GREEN)Policy Service Seeders Completed$(NC)"
else
	@echo "$(BLUE)Running Policy Service Seeders (Docker)...$(NC)"
	@cd services/auth && docker compose exec -w /var/www/html policy php artisan db:seed
	@echo "$(GREEN)Policy Service Seeders Completed$(NC)"
endif

policy-key: ## Generate application key for policy service
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Generating Policy Service Application Key (Inside Container)...$(NC)"
	@cd services/policy && php artisan key:generate
	@echo "$(GREEN)Policy Service Application Key Generated$(NC)"
else
	@echo "$(BLUE)Generating Policy Service Application Key (Docker)...$(NC)"
	@cd services/auth && docker compose exec -w /var/www/html policy php artisan key:generate
	@echo "$(GREEN)Policy Service Application Key Generated$(NC)"
endif

policy-cache: ## Clear cache for policy service
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@echo "$(BLUE)Clearing Policy Service Cache (Inside Container)...$(NC)"
	@cd services/policy && php artisan cache:clear
	@cd services/policy && php artisan config:clear
	@cd services/policy && php artisan route:clear
	@echo "$(GREEN)Policy Service Cache Cleared$(NC)"
else
	@echo "$(BLUE)Clearing Policy Service Cache (Docker)...$(NC)"
	@cd services/auth && docker compose exec -w /var/www/html policy php artisan cache:clear
	@cd services/auth && docker compose exec -w /var/www/html policy php artisan config:clear
	@cd services/auth && docker compose exec -w /var/www/html policy php artisan route:clear
	@echo "$(GREEN)Policy Service Cache Cleared$(NC)"
endif

# SSH Commands for Services
ssh-auth: ## SSH into auth service container
	@echo "$(BLUE)Connecting to Auth Service Container...$(NC)"
	@docker compose -f docker/docker-compose.yml exec auth bash

ssh-policy: ## SSH into policy service container
	@echo "$(BLUE)Connecting to Policy Service Container...$(NC)"
	@docker compose -f docker/docker-compose.yml exec policy bash

ssh-auth-db: ## SSH into auth database container
	@echo "$(BLUE)Connecting to Auth Database Container...$(NC)"
	@docker compose -f docker/docker-compose.yml exec auth-db bash

ssh-policy-db: ## SSH into policy database container
	@echo "$(BLUE)Connecting to Policy Database Container...$(NC)"
	@docker compose -f docker/docker-compose.yml exec policy-db bash

ssh-redis: ## SSH into redis container
	@echo "$(BLUE)Connecting to Redis Container...$(NC)"
	@docker compose -f docker/docker-compose.yml exec redis sh

ssh-mail: ## SSH into mail container
	@echo "$(BLUE)Connecting to Mail Container...$(NC)"
	@docker compose -f docker/docker-compose.yml exec mail sh

# Combined Service Commands
services-install: auth-install policy-install ## Install dependencies for all services
services-update: auth-update policy-update ## Update dependencies for all services
services-migrate: auth-migrate policy-migrate ## Run migrations for all services
services-seed: auth-seed policy-seed ## Run seeders for all services
services-cache: auth-cache policy-cache ## Clear cache for all services
