FROM dunglas/frankenphp:1.9.0-php8.4.11

RUN apt-get update && apt-get install -y \
    curl git unzip \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

RUN install-php-extensions \
    @composer pcntl pdo_pgsql pgsql intl zip

WORKDIR /app

COPY package*.json ./

RUN npm ci

COPY . .

# Add --no-dev later, keep while using seeder in production
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN npm run build


CMD ["composer", "run", "dev"]
  