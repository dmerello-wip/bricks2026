# https://laravel.com/docs/12.x/sail

DUMP ?= ./dump.sql

SAIL = ./vendor/bin/sail

.PHONY: all
all: dev

.PHONY: init
init:
	composer install
	$(SAIL) up -d --wait
	$(SAIL) artisan key:generate
	$(SAIL) artisan migrate
	$(SAIL) artisan twill:install
	$(SAIL) artisan twill:build
	$(SAIL) npm install
	$(SAIL) artisan translation-handler:import --force --fresh

.PHONY: dev
dev:
	$(SAIL) up -d --wait
	$(SAIL) npm run dev

.PHONY: ssr
ssr:
	$(SAIL) npm run build:ssr
	$(SAIL) artisan inertia:start-ssr

# USAGE: `DUMP=dump.sql make importdb`
.PHONY: importdb
importdb:
	echo $(DUMP)

.PHONY: translations
translations:
	$(SAIL) artisan translation-handler:import --force --fresh

.PHONY: swagger
swagger:
	$(SAIL) artisan l5-swagger:generate
	$(SAIL) npm run generate-swagger-types

.PHONY: types
types:
	make translations
	make swagger