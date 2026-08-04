# Spacecraft

A Sci-fi ship builder.

## Stack

- Symfony 8.1
- PHP 8.4 
- PostgreSQL 18

## Requirements

- Docker + Docker Compose
- `make`
- Host user with UID/GID `1000`. If yours differ, export `UID` / `GID`
  before building so mounted files keep the right ownership:
  `export UID=$(id -u) GID=$(id -g)`

## Installation

1. **Clone**
   ```bash
   git clone <repo-url> spacecraft && cd spacecraft
   ```

2. **Map** the dev hostnames to localhost:
   ```bash
   echo "127.0.0.1  dev.spacecraft.com www.dev.spacecraft.com admin.dev.spacecraft.com" | sudo tee -a /etc/hosts
   ```

3. **Create `.env.local`** at the project root. It is gitignored: no secret is
   ever committed, not even a dev one. Pick your own password.

   ```dotenv
   POSTGRES_PASSWORD=choose-your-own
   DATABASE_URL="postgresql://${POSTGRES_USER}:${POSTGRES_PASSWORD}@database:5432/${POSTGRES_DB}?serverVersion=18&charset=utf8"
   APP_SECRET=choose-or-generate-a-value
   ```

4. **Build & start** the containers:
   ```bash
   make build
   ```

5. **Install** PHP dependencies:
   ```bash
   make install
   ```

6. **Trust the local TLS CA** (Caddy generates its own). Skip if you accept the
   browser warning, but the site won't be green.
   ```bash
   # Extract the CA root cert
   docker cp spacecraft-app:/data/caddy/pki/authorities/local/root.crt /tmp/spacecraft-caddy-root.crt

   # Trust it system-wide (Debian/Ubuntu)
   sudo cp /tmp/spacecraft-caddy-root.crt /usr/local/share/ca-certificates/spacecraft-caddy-local.crt
   sudo update-ca-certificates
   ```

7. **Open** https://dev.spacecraft.com

## Everyday commands

```bash
make start             # start without recreating
make stop              # stop containers
make down              # stop and remove containers
make bash              # shell inside the app container
make test              # unit tests only -- no database, instant
make test-integration  # integration tests -- hits Postgres
make test-all          # both suites
make test-watch        # unit tests in watch mode
make test-db-migrate   # run migrations on the test database
```

Run `make help` to list all targets.

## Tests and the test database

Domain tests (value objects, aggregates) are pure unit tests and never touch a
database. Only the `integration` suite does.

Integration tests run against `spacecraft_test`, owned by a dedicated
`spacecraft_test` role that has no access to the development database.
See `docker/postgres/init/setup-test-role.sql`.

The role and database are created when the Postgres data directory is first
initialized. If you already had a volume before this was added, recreate it:

```bash
docker compose down
docker volume rm spacecraft_db_data   # development data is lost
make start
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
make test-db-migrate
```
