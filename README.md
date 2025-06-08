# README - Traefik Proxy for Local Environments

This repository contains the configuration for Traefik version 3.4, which serves as a reverse proxy for managing different environments. Traefik is a powerful tool that simplifies routing and load balancing for your projects.

## Prerequisites

Before getting started, please make sure you have the following prerequisites installed on your system:

- **Docker**: You can download and install Docker from [Docker's official website](https://www.docker.com/get-started)
- **Make**: Usually pre-installed on Linux/macOS, or available via package managers
- **Castor** (optional): Alternative to Make - install from [jolicode/castor](https://github.com/jolicode/castor)

## Quick Start

Get up and running in one command:

### Using Make (recommended)
```bash
make start
```

### Using Castor (alternative)
```bash
castor start
```

That's it! This command will:
- Install mkcert (if needed)
- Generate SSL certificates for local domains
- Start Traefik with proper configuration
- Create the required Docker network

## Available Commands

### Make Commands
```bash
make start    # Start Traefik (generates certs if needed)
make stop     # Stop Traefik
make restart  # Restart Traefik
make status   # Show Traefik status
make logs     # Show Traefik logs
make clean    # Stop and remove certificates
make setup-certs # Generate SSL certificates only
make dev      # Alias for start (quick development)
make network  # Show Docker network information
make help     # Show all available commands
```

### Castor Commands
```bash
castor start       # Start Traefik (generates certs if needed)
castor stop        # Stop Traefik
castor restart     # Restart Traefik
castor status      # Show Traefik status
castor logs        # Show Traefik logs
castor clean       # Stop and remove certificates
castor setup-certs # Generate SSL certificates only
castor dev         # Alias for start (quick development)
castor network     # Show Docker network information
castor help        # Show all available commands
```

## Using Traefik

Once started, Traefik is ready to route traffic to your local services. The Traefik dashboard is accessible at [https://traefik.web.localhost](https://traefik.web.localhost).

Both Make and Castor provide identical functionality - choose the tool that fits your workflow best!

## Project Configuration

### Traefik Configuration

Traefik is configured using the `compose.yaml` file in the repository. Here's an overview of the key settings:

```yaml
version: '3.9'

services:
  traefik:
    image: traefik:v3.4
    container_name: traefik
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    # ... additional configuration
    networks:
      - traefik

networks:
  traefik:
    name: traefik
```

This configuration sets up Traefik to route traffic based on labels you add to your other services' Docker Compose files.

### Example Docker Compose (HTTPS)

Here's an example of how to expose a Symfony project using a Docker Compose file:

```yaml
version: '3.9'

services:
  php:
    image: devilbox/php-fpm:8.2-work-0.151
    command: symfony serve
    volumes:
      - ./:/shared/httpd
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.php.rule=Host(`php.web.localhost`)"
      - "traefik.http.routers.php.tls=true"
      - "traefik.http.services.php.loadbalancer.server.port=8000"
    networks:
      - traefik

networks:
  traefik:
    external: true
```

### Example Docker Compose (HTTP-only)

For services that need to stay on HTTP without HTTPS redirect:

```yaml
version: '3.9'

services:
  legacy-app:
    image: nginx:alpine
    volumes:
      - ./public:/usr/share/nginx/html:ro
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.legacy-http.rule=Host(`legacy.web.localhost`)"
      - "traefik.http.routers.legacy-http.entrypoints=http"
      - "traefik.http.routers.legacy-http.middlewares=no-https-redirect"
      - "traefik.http.services.legacy-http.loadbalancer.server.port=80"
    networks:
      - traefik

networks:
  traefik:
    external: true
```

This example exposes a legacy application that stays on HTTP without being redirected to HTTPS.

Make sure to add similar labels to your services in your Docker Compose files to leverage Traefik's routing capabilities.

## SSL Certificates

The setup automatically generates SSL certificates for the following domains:
- `*.web.localhost`
- `*.api.localhost` 
- `*.db.localhost`
- `*.docs.localhost`

Certificates are generated using [mkcert](https://github.com/FiloSottile/mkcert) and automatically trusted by your system.

## HTTP vs HTTPS Configuration

### HTTPS (Default - with automatic redirect)
Most services should use HTTPS with automatic redirect from HTTP:

```yaml
labels:
  - "traefik.http.routers.myapp.rule=Host(`myapp.web.localhost`)"
  - "traefik.http.routers.myapp.tls=true"
```

### HTTP-only (No redirect)
For services that must stay on HTTP (legacy apps, development APIs, etc.):

```yaml
labels:
  - "traefik.http.routers.myapp.rule=Host(`myapp.web.localhost`)"
  - "traefik.http.routers.myapp.entrypoints=http"
  - "traefik.http.routers.myapp.middlewares=no-https-redirect"
```

### Dual HTTP/HTTPS
For services that support both protocols:

```yaml
labels:
  # HTTP router (no redirect)
  - "traefik.http.routers.myapp-http.rule=Host(`myapp.web.localhost`)"
  - "traefik.http.routers.myapp-http.entrypoints=http"
  - "traefik.http.routers.myapp-http.middlewares=no-https-redirect"
  # HTTPS router
  - "traefik.http.routers.myapp-https.rule=Host(`myapp.web.localhost`)"
  - "traefik.http.routers.myapp-https.entrypoints=https"
  - "traefik.http.routers.myapp-https.tls=true"
```

## Troubleshooting

### Common Issues

**Port conflicts**: If ports 80 or 443 are already in use:
```bash
make stop
# Stop other services using these ports
make start
```

**Certificate issues**: Regenerate certificates:
```bash
make clean
make start
```

**Network issues**: Check Docker networks:
```bash
make network
```

## Additional Information

If you have any questions or encounter issues, please refer to the Traefik documentation at [https://doc.traefik.io/](https://doc.traefik.io/) for more detailed information and troubleshooting tips.
