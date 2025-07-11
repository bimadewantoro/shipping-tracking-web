.PHONY: up down restart logs shell db-reset db-migrate db-seed

# Start all services
up:
	docker-compose up -d

# Stop all services
down:
	docker-compose down

# Restart all services
restart:
	docker-compose restart

# View logs
logs:
	docker-compose logs -f

# View PostgreSQL logs
logs-db:
	docker-compose logs -f postgres

# Access PostgreSQL shell
db-shell:
	docker-compose exec postgres psql -U root -d shipping_tracking_web

# Reset database (careful - this will delete all data)
db-reset:
	docker-compose exec postgres psql -U root -d shipping_tracking_web -c "DROP SCHEMA public CASCADE; CREATE SCHEMA public;"

# Run Laravel migrations
db-migrate:
	php artisan migrate

# Run Laravel seeders
db-seed:
	php artisan db:seed

# Full database setup (migrate + seed)
db-setup: db-migrate db-seed

# Show container status
status:
	docker-compose ps

# Remove all containers and volumes (careful - this will delete all data)
clean:
	docker-compose down -v --remove-orphans
