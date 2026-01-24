.PHONY: build up down logs shell composer migrate key install

build:
	docker-compose build

up:
	docker-compose up -d

down:
	docker-compose down

logs:
	docker-compose logs -f app

shell:
	docker-compose exec app bash

composer:
	docker-compose exec app composer install

migrate:
	docker-compose exec app php artisan migrate

key:
	docker-compose exec app php artisan key:generate

install: build up
	@echo "Waiting for services to start..."
	@sleep 5
	docker-compose exec app composer install
	docker-compose exec app php artisan key:generate || true
	docker-compose exec app php artisan migrate
	@echo "Installation complete! Visit http://localhost:8000"
