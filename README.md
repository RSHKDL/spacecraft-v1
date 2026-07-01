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

3. **Build & start** the containers:
   ```bash
   make build
   ```

4. **Install** PHP dependencies:
   ```bash
   make install
   ```

5. **Trust the local TLS CA** (Caddy generates its own). Skip if you accept the
   browser warning, but the site won't be green.
   ```bash
   # Extract the CA root cert
   docker cp spacecraft-app:/data/caddy/pki/authorities/local/root.crt /tmp/spacecraft-caddy-root.crt

   # Trust it system-wide (Debian/Ubuntu)
   sudo cp /tmp/spacecraft-caddy-root.crt /usr/local/share/ca-certificates/spacecraft-caddy-local.crt
   sudo update-ca-certificates
   ```

6. **Open** https://dev.spacecraft.com

## Everyday commands

```bash
make start        # start without recreating
make stop         # stop containers
make down         # stop and remove containers
make bash         # shell inside the app container
make test         # run the test suite
make test-watch   # run tests in watch mode
```

Run `make help` to list all targets.
