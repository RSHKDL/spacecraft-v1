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
##   Project
## ----------------------------------------------------------------------------
##

install: ## Install PHP dependencies (works even before containers are started)
	$(COMPOSE) run --rm app composer install

.PHONY: install

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