.DEFAULT_GOAL := help

COMPOSE=docker compose

##
## ----------------------------------------------------------------------------
##   Docker setup
## ----------------------------------------------------------------------------
##

build: ## Build images and start the project (detached)
	$(COMPOSE) up --build -d

start: ## Start the project without recreating containers
	$(COMPOSE) up -d --no-recreate

stop: ## Stop the containers (keeps them)
	$(COMPOSE) stop

down: ## Stop and remove containers
	$(COMPOSE) down --remove-orphans

destroy: ## Remove containers, local images and volumes (wipes the database!)
	$(COMPOSE) down --rmi local --volumes --remove-orphans

.PHONY: build start stop down destroy

##
## ----------------------------------------------------------------------------
##   Shell access
## ----------------------------------------------------------------------------
##

bash: ## Open a shell inside the running app container
	$(COMPOSE) exec app bash

.PHONY: bash

##
## ----------------------------------------------------------------------------
##   Project
## ----------------------------------------------------------------------------
##

install: ## Install PHP dependencies (works even before containers are started)
	$(COMPOSE) run --rm app composer install

.PHONY: install

##
## ----------------------------------------------------------------------------
##   Tests
## ----------------------------------------------------------------------------
##

test: ## Execute the test suite
	$(COMPOSE) exec app vendor/bin/phpunit --testdox

test-watch: ## Execute the test suite in watch mode
	$(COMPOSE) exec app vendor/bin/phpunit-watcher watch --testdox

.PHONY: test test-watch

##
## ----------------------------------------------------------------------------
##   Makefile info
## ----------------------------------------------------------------------------
##

.PHONY: help
help: ## This help message
	@egrep -h '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' \
		| sed -e 's/\[32m##/[33m/'
