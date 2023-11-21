# README - Traefik Proxy for Local Environments

This repository contains the configuration for Traefik version 2.10, which serves as a reverse proxy for managing different environments. Traefik is a powerful tool that simplifies routing and load balancing for your projects.

## Prerequisites

Before getting started, please make sure you have the following prerequisites installed on your system:

- Docker: You can download and install Docker from [Docker's official website](https://www.docker.com/get-started).

## Setup

1. Clone this repository to your local machine.

2. Run the following command to set up the necessary certificates and configure Traefik. This script is specifically designed for use on the Windows Subsystem for Linux (WSL).

   ```bash
   chmod u+x ./setup.sh && sh ./setup.sh
   ```

   The setup script does the following:

    - Installs `mkcert` to generate SSL certificates for your local domains.
    - Generates SSL certificates for the domain "alls.dev" and its subdomains.
    - Adds the root certificate to your Windows certificate store using `certutil.exe`.

## Using Traefik

Once you've completed the setup, run the following command :
   ```bash
   docker compose up -d
   ```
Traefik is ready to route traffic to your local services. The Traefik dashboard is accessible at [https://traefik.alls.dev](https://traefik.alls.dev).

## Project Configuration

### Traefik Configuration

Traefik is configured using a `docker-compose.yaml` file in the repository. Here's an overview of the key settings:

```yaml
version: '3'

services:
  reverse-proxy:
    image: traefik:v2.10
    container_name: traefik
    restart: unless-stopped
    security_opt:
      - no-new-privileges:true
    command: --api.insecure=true --providers.docker
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock:ro
      - ./config/traefik/traefik.yml:/etc/traefik/traefik.yml:ro
      - ./config/traefik/config.yml:/etc/traefik/config.yml:ro
      - ./certs:/etc/certs:ro
    networks:
      - reverse-proxy
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.traefik=true"
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
      - "traefik.http.routers.php.rule=Host(`php.alls.dev`)"
      - "traefik.http.routers.php.tls=true"
      - "traefik.http.services.php.loadbalancer.server.port=8000"
    networks:
      - reverse-proxy

networks:
  reverse-proxy:
    external: true
```

This example exposes a Symfony project and configures Traefik to route traffic to it based on the subdomain "php.alls.dev."

Make sure to add similar labels to your services in your Docker Compose files to leverage Traefik's routing capabilities.

## Additional Information

If you have any questions or encounter issues, please refer to the Traefik documentation at [https://doc.traefik.io/](https://doc.traefik.io/) for more detailed information and troubleshooting tips.