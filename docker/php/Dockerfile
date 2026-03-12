# ============================================
# STAGE 1 — Builder (instala dependências)
# ============================================
FROM php:8.4-fpm-alpine AS builder

# Dependências apenas para compilação
RUN apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    autoconf \
    gcc \
    g++ \
    make \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        gd \
        bcmath \
        pcntl \
        opcache

# Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ============================================
# STAGE 2 — Production (imagem final leve)
# ============================================
FROM php:8.4-fpm-alpine AS production

# Copiar apenas as extensões compiladas do stage anterior
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --from=builder /usr/bin/composer /usr/bin/composer

# Dependências apenas de runtime
RUN apk add --no-cache \
    libpq \
    libzip \
    libpng \
    libjpeg-turbo \
    freetype \
    && rm -rf /var/cache/apk/*

# Utilizador não root
RUN addgroup -g 1000 appuser && \
    adduser -u 1000 -s /bin/sh -D -G appuser appuser

# Directório da aplicação
WORKDIR /var/www

# Permissões correctas
RUN chown -R appuser:appuser /var/www

# Directório de logs do Laravel
RUN mkdir -p /var/www/storage/logs && \
    chown -R appuser:appuser /var/www/storage

# Mudar para utilizador não root
USER appuser

EXPOSE 9000

# ============================================
# STAGE 3 — Development (ferramentas extra)
# ============================================
FROM production AS development

USER root

# bash e ferramentas úteis em desenvolvimento
RUN apk add --no-cache bash

USER appuser