.PHONY: help start stop restart clean setup-certs status logs

# Default target
.DEFAULT_GOAL := start

# Colors for output
GREEN := \033[0;32m
YELLOW := \033[0;33m
RED := \033[0;31m
NC := \033[0m # No Color

help: ## Show this help message
	@echo "$(GREEN)Web-Traefik Local Development Setup$(NC)"
	@echo "$(YELLOW)Available commands:$(NC)"
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  $(GREEN)%-12s$(NC) %s\n", $$1, $$2}' $(MAKEFILE_LIST)
	@echo ""
	@echo "$(YELLOW)Alternative: Use Castor instead of Make$(NC)"
	@echo "  $(GREEN)castor start$(NC)  # Same as 'make start'"
	@echo "  $(GREEN)castor help$(NC)   # Show Castor commands"

setup-certs: ## Generate SSL certificates using mkcert
	@echo "$(YELLOW)Setting up SSL certificates...$(NC)"
	@mkdir -p certs
	@if command -v mkcert >/dev/null 2>&1; then \
		echo "$(GREEN)mkcert found, generating certificates...$(NC)"; \
		mkcert -cert-file certs/local-cert.pem -key-file certs/local-key.pem \
			"web.localhost" "*.web.localhost" \
			"api.localhost" "*.api.localhost" \
			"db.localhost" "*.db.localhost" \
			"docs.localhost" "*.docs.localhost"; \
		echo "$(GREEN)Certificates generated successfully!$(NC)"; \
	else \
		echo "$(YELLOW)mkcert not found. Installing...$(NC)"; \
		if command -v apt >/dev/null 2>&1; then \
			sudo apt update && sudo apt install -y mkcert libnss3-tools; \
		elif command -v brew >/dev/null 2>&1; then \
			brew install mkcert; \
		elif command -v choco >/dev/null 2>&1; then \
			choco install mkcert; \
		else \
			echo "$(RED)Please install mkcert manually: https://github.com/FiloSottile/mkcert$(NC)"; \
			exit 1; \
		fi; \
		mkcert -install; \
		mkcert -cert-file certs/local-cert.pem -key-file certs/local-key.pem \
			"web.localhost" "*.web.localhost" \
			"api.localhost" "*.api.localhost" \
			"db.localhost" "*.db.localhost" \
			"docs.localhost" "*.docs.localhost"; \
		echo "$(GREEN)Certificates generated successfully!$(NC)"; \
	fi

start: setup-certs ## Start Traefik (generates certs if needed)
	@echo "$(YELLOW)Starting Traefik...$(NC)"
	@docker compose up -d
	@echo "$(GREEN)Traefik is running!$(NC)"
	@echo "$(YELLOW)Dashboard: https://traefik.web.localhost$(NC)"
	@echo "$(YELLOW)Network: traefik$(NC)"

stop: ## Stop Traefik
	@echo "$(YELLOW)Stopping Traefik...$(NC)"
	@docker compose down
	@echo "$(GREEN)Traefik stopped!$(NC)"

restart: stop start ## Restart Traefik

clean: stop ## Stop Traefik and remove certificates
	@echo "$(YELLOW)Cleaning up...$(NC)"
	@rm -rf certs/*.pem
	@docker network rm traefik 2>/dev/null || true
	@echo "$(GREEN)Cleanup complete!$(NC)"

status: ## Show Traefik status
	@echo "$(YELLOW)Traefik Status:$(NC)"
	@docker compose ps

logs: ## Show Traefik logs
	@docker compose logs -f traefik

# Quick development commands
dev: start ## Alias for start (for quick development)

# Show network info
network: ## Show Docker network information
	@echo "$(YELLOW)Docker Networks:$(NC)"
	@docker network ls | grep traefik || echo "$(RED)Traefik network not found$(NC)"
