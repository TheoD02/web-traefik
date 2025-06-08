# Project Templates

This directory contains ready-to-use Docker Compose templates for common project types. Simply copy the appropriate template to your project directory and customize as needed.

## Available Templates

### 📁 `php-symfony.yml`
Template for PHP/Symfony projects with optional MySQL database.
- **URL**: `https://myapp.web.localhost`
- **Database**: `https://db.web.localhost` (optional)

### 📁 `nodejs.yml`
Template for Node.js applications with optional Redis.
- **URL**: `https://nodeapp.web.localhost`
- **Redis**: `https://redis.web.localhost` (optional)

### 📁 `static-site.yml`
Template for static websites using Nginx.
- **URL**: `https://staticsite.web.localhost`

## Usage

1. Copy the template to your project:
   ```bash
   cp templates/php-symfony.yml my-project/docker-compose.yml
   ```

2. Edit the copied file to customize:
   - Change service names
   - Update hostnames (e.g., `myapp.web.localhost` → `yourapp.web.localhost`)
   - Modify ports if needed
   - Add/remove services

3. Start your project:
   ```bash
   cd my-project
   docker compose up -d
   ```

## Customization Tips

- **Hostnames**: Use the pattern `{service}.web.localhost` for consistency
- **Ports**: Make sure the `loadbalancer.server.port` matches your application's port
- **Networks**: Always use the external `traefik` network
- **SSL**: TLS is enabled by default for all services

## Need Help?

Check the main README for troubleshooting tips or refer to the [Traefik documentation](https://doc.traefik.io/).

