# README - Traefik Proxy for Local Environments

This repository contains the configuration for Traefik version 3.4, which serves as a reverse proxy for managing different environments. Traefik is a powerful tool that simplifies routing and load balancing for your projects.

## Prerequisites

Before getting started, please make sure you have the following prerequisites installed on your system:

- **Docker**: You can download and install Docker from [Docker's official website](https://www.docker.com/get-started)
- **Make**: Usually pre-installed on Linux/macOS, or available via package managers

## Quick Start

Get up and running in one command:

```bash
make start
```

That's it! This command will:
- Install mkcert (if needed)
- Generate SSL certificates for local domains
- Start Traefik with proper configuration
- Create the required Docker network

## Available Commands

```bash
make start    # Start Traefik (default command)
make stop     # Stop Traefik
make restart  # Restart Traefik
make status   # Show Traefik status
make logs     # Show Traefik logs
make clean    # Stop and remove certificates
make help     # Show all available commands
```

## Using Traefik

Once started, Traefik is ready to route traffic to your local services. The Traefik dashboard is accessible at [https://traefik.web.localhost](https://traefik.web.localhost).

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

### Example Docker Compose

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

This example exposes a Symfony project and configures Traefik to route traffic to it based on the subdomain "php.web.localhost."

Make sure to add similar labels to your services in your Docker Compose files to leverage Traefik's routing capabilities.

## SSL Certificates

The setup automatically generates SSL certificates for the following domains:
- `*.web.localhost`
- `*.api.localhost` 
- `*.db.localhost`
- `*.docs.localhost`

Certificates are generated using [mkcert](https://github.com/FiloSottile/mkcert) and automatically trusted by your system.

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

