# Makefile Documentation

This document provides detailed information about all available make commands in the project.

## Environment Commands

- `make help` - Shows available commands with description
- `make use-env ENV=<env>` - Switch to a specific environment (dev|test|staging|prod)
- `make validate-env` - Validate environment setup
- `make debug-env` - Debug environment variables
- `make status` - Show status of all containers
- `make logs` - Show logs of all containers
- `make cleanup` - Remove all unused containers, networks, and images

## Build Commands

- `make build` - Build dev environment
- `make build-test` - Build test environment
- `make build-staging` - Build staging environment
- `make build-prod` - Build prod environment

## Start Commands

- `make start` - Start dev environment
- `make start-test` - Start test environment
- `make start-staging` - Start staging environment
- `make start-prod` - Start prod environment

## Stop Commands

- `make stop` - Stop dev environment containers
- `make stop-test` - Stop test environment containers
- `make stop-staging` - Stop staging environment containers
- `make stop-prod` - Stop prod environment containers

## Down Commands

- `make down` - Stop and remove dev environment containers, networks
- `make down-test` - Stop and remove test environment containers, networks
- `make down-staging` - Stop and remove staging environment containers, networks
- `make down-prod` - Stop and remove prod environment containers, networks

## Restart Commands

- `make restart` - Stop and start dev environment
- `make restart-test` - Stop and start test environment
- `make restart-staging` - Stop and start staging environment
- `make restart-prod` - Stop and start prod environment

## SSH Commands

- `make ssh-auth` - SSH into auth service container
- `make ssh-policy` - SSH into policy service container
- `make ssh-auth-db` - SSH into auth database container
- `make ssh-policy-db` - SSH into policy database container
- `make ssh-redis` - SSH into redis container
- `make ssh-mail` - SSH into mail container

## Auth Service Commands

- `make auth-install` - Install composer dependencies for auth service
- `make auth-update` - Update composer dependencies for auth service
- `make auth-migrate` - Run migrations for auth service
- `make auth-seed` - Run seeders for auth service
- `make auth-key` - Generate application key for auth service
- `make auth-cache` - Clear cache for auth service

## Policy Service Commands

- `make policy-install` - Install composer dependencies for policy service
- `make policy-update` - Update composer dependencies for policy service
- `make policy-migrate` - Run migrations for policy service
- `make policy-seed` - Run seeders for policy service
- `make policy-key` - Generate application key for policy service
- `make policy-cache` - Clear cache for policy service

## Combined Service Commands

- `make services-install` - Install dependencies for all services
- `make services-update` - Update dependencies for all services
- `make services-migrate` - Run migrations for all services
- `make services-seed` - Run seeders for all services
- `make services-cache` - Clear cache for all services

## Environment Variables

The Makefile uses environment variables from the following files:
- `docker/.env.<environment>` - Environment-specific variables
- `docker/.env` - Default environment variables

Important environment variables:
- `COMPOSE_PROJECT_NAME` - Project name used for Docker containers
- `WWWUSER` - User ID for web services
- `WWWGROUP` - Group ID for web services

## Docker Compose Files

The project uses different docker-compose files for different environments:
- `docker/docker-compose.yml` - Development environment
- `docker/docker-compose-test.yml` - Test environment
- `docker/docker-compose-staging.yml` - Staging environment
- `docker/docker-compose-prod.yml` - Production environment
