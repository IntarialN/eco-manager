.PHONY: build up install migrate serve stop shell

docker?=docker compose

build:
	$(docker) build yii-app

up:
	$(docker) up -d

install:
	$(docker) run --rm -v $(PWD)/yii-app:/var/www/html -w /var/www/html php:8.2-cli bash -c "composer install"

migrate:
	$(docker) run --rm -v $(PWD)/yii-app:/var/www/html -w /var/www/html php:8.2-cli php yii migrate --interactive=0

serve:
	$(docker) up yii-app

stop:
	$(docker) down

shell:
	$(docker) exec -it yii-app bash
