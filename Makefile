# Environment handling
# Read current environment from .env file if it exists, otherwise default to dev
ENV := $(shell if [ -f .env ]; then grep COMPOSE_PROJECT_NAME .env | sed 's/.*_\(.*\)/\1/'; else echo "dev"; fi)
ENV_FILE := .env.$(ENV)

# Include environment files
ifneq ("$(wildcard $(ENV_FILE))","")
	include $(ENV_FILE)
else ifneq ("$(wildcard .env)","")
	include .env
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
	elif [ -f .env ]; then \
		export $$(grep -v '^#' .env | xargs); \
	fi)
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

define CHECK_ENV_FILE
	@if [ ! -f $(ENV_FILE) ] && [ ! -f .env ]; then \
		$(ERROR_ENV_FILE_MISSING); \
		exit 1; \
	fi
endef

debug-env: ## Debug environment variables
	@echo "Environment Debug Information:"
	@echo "ENV = $(ENV)"
	@echo "ENV_FILE = $(ENV_FILE)"
	@echo "Current .env contents:"
	@cat .env
	@echo "\nCurrent environment file (.env.$(ENV)) contents:"
	@cat .env.$(ENV)

help: ## Shows available commands with description
	@echo "$(BLUE)List of available commands:$(NC)"
	@echo "$(BLUE)Current environment: $(GREEN)$(ENV)$(NC) (using $(ENV_FILE))"
	@grep -E '^[a-zA-Z-]+:.*?## .*$$' Makefile | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(GREEN)%-27s$(NC) %s\n", $$1, $$2}'

use-env: ## Switch to a specific environment (usage: make use-env ENV=dev|test|staging|prod)
	@echo "$(BLUE)Switching environment...$(NC)"
	@echo "$(BLUE)Requested environment: $(GREEN)$(ENV)$(NC)"
	@echo "$(BLUE)Looking for file: $(GREEN).env.$(ENV)$(NC)"
	@if [ -f .env.$(ENV) ]; then \
		cp .env.$(ENV) .env; \
		echo "$(GREEN)✓ Copied .env.$(ENV) to .env$(NC)"; \
		echo "$(GREEN)✓ Switched to $(ENV) environment using $(ENV_FILE)$(NC)"; \
		echo "$(BLUE)Current .env contents:$(NC)"; \
		cat .env | grep "COMPOSE_PROJECT_NAME"; \
	else \
		echo "$(RED)Error: .env.$(ENV) file not found$(NC)"; \
		exit 1; \
	fi

validate-env: ## Validate environment setup
	@printf "\n$(BLUE)Environment Validation Report:$(NC)\n"
	@printf "================================\n"
	@printf "$(BLUE)Active Environment:$(NC) $(GREEN)$(ENV)$(NC)\n"
	@printf "$(BLUE)Environment File:$(NC) $(GREEN)$(ENV_FILE)$(NC)\n"
	@printf "$(BLUE)Project Name:$(NC) $(GREEN)$(shell grep COMPOSE_PROJECT_NAME .env | cut -d'=' -f2)$(NC)\n"
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
	@docker compose ps

logs: ## Show logs of all containers
	@echo "$(BLUE)Container Logs ($(ENV) environment):$(NC)"
	@docker compose logs --tail=100 -f

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
