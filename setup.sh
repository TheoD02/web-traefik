#!/usr/bin/env bash

sudo apt install mkcert libnss3-tools
mkcert -install
mkcert -cert-file certs/local-cert.pem -key-file certs/local-key.pem "web.localhost" "*.web.localhost" "db.localhost" "*.db.localhost"

isWsl=$(uname -a | grep -q Microsoft && echo "true" || echo "false")

if [ $isWsl = "true" ]; then
    certutil.exe -addstore -user "Root" "$HOME/.local/share/mkcert/rootCA.pem"
else
    sudo cp "$HOME/.local/share/mkcert/rootCA.pem" /usr/local/share/ca-certificates/
    sudo update-ca-certificates
fi
