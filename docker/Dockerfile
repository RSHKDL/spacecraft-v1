# Base image : FrankenPHP (web server + PHP 8.4) on Debian Bookworm.
FROM dunglas/frankenphp:1-php8.4-bookworm

# Arguments defined in docker-compose.yml
ARG UID=1000
ARG GID=1000

# Minimum PHP extensions we need:
#   pdo_pgsql -> talk to PostgreSQL through Doctrine
#   intl      -> i18n / formatting (recommended by Symfony)
#   opcache   -> performance (bytecode cache)
#   zip       -> required by Composer to unpack packages
# install-php-extensions is a helper shipped in the FrankenPHP image.
RUN install-php-extensions \
    pdo_pgsql \
    intl \
    opcache \
    zip

# Copy the official Composer binary from its dedicated image.
COPY --from=composer/composer:2-bin /composer /usr/bin/composer

# Grant the FrankenPHP binary the capability to bind privileged ports (80/443)
# while running as a non-root user. Without this, only root could open <1024.
# libcap2-bin provides "setcap".
RUN apt-get update \
    && apt-get install -y --no-install-recommends libcap2-bin \
    && rm -rf /var/lib/apt/lists/* \
    && setcap cap_net_bind_service=+ep /usr/local/bin/frankenphp

# Non-root user mapped to the host uid/gid so files created in the /app volume
# belong to the host user (not root). /data and /config are FrankenPHP/Caddy
# state directories that must be writable by this user.
RUN groupadd -g ${GID} app \
    && useradd -u ${UID} -g ${GID} -m app \
    && chown -R app:app /data /config

# Working directory (FrankenPHP serves /app/public by default).
WORKDIR /app
USER app
