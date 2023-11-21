sudo apt install mkcert
mkcert -cert-file certs/local-cert.pem -key-file certs/local-key.pem "alls.dev" "*.alls.dev"
certutil.exe -addstore -user "Root" "\home\t\.local\share\mkcert\rootCA.pem"