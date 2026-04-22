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
    icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        gd \
        bcmath \
        pcntl \
        exif \
        opcache \
        intl

# Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# ============================================
# STAGE 2 — Node (compila assets para produção)
# ============================================
FROM node:22-alpine AS node-builder

WORKDIR /var/www

COPY src/package.json src/package-lock.json* ./
RUN npm ci

COPY src/ .
RUN npm run build

# ============================================
# STAGE 3 — Production (imagem final leve)
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
    icu-libs \
    && rm -rf /var/cache/apk/*

# Utilizador não root (UID/GID configurável para evitar mismatch com o host)
ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} appuser && \
    adduser -u ${UID} -s /bin/sh -D -G appuser appuser

# Directório da aplicação
WORKDIR /var/www

# Permissões correctas
RUN chown -R appuser:appuser /var/www

# Directório de logs do Laravel
RUN mkdir -p /var/www/storage/logs && \
    chown -R appuser:appuser /var/www/storage

# Copiar assets compilados pelo Node (JS/CSS minificados)
COPY --from=node-builder --chown=appuser:appuser /var/www/public/build /var/www/public/build

# Mudar para utilizador não root
USER appuser

EXPOSE 9000

# ============================================
# STAGE 4 — Development (ferramentas extra)
# ============================================
FROM production AS development

USER root

# bash e ferramentas úteis em desenvolvimento
RUN apk add --no-cache bash

# Chromium + Node.js + Puppeteer para spatie/browsershot (PDF generation)
RUN apk add --no-cache \
    chromium \
    nodejs \
    npm \
    && npm install -g puppeteer --unsafe-perm \
    && ln -sf /usr/bin/chromium-browser /usr/bin/chromium

ENV CHROME_PATH=/usr/bin/chromium
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true

USER appuser