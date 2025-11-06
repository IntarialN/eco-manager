.PHONY: build up install migrate serve stop shell

docker?=docker compose

build:
	$(docker) build yii-app

up:
	$(docker) up -d

install:
	$(docker) run --rm yii-app composer install

migrate:
	$(docker) run --rm yii-app php yii migrate --interactive=0

serve:
	$(docker) up yii-app

stop:
	$(docker) down

shell:
	$(docker) exec -it yii-app bash
