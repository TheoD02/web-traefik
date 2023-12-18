sudo apt install mkcert
mkcert -cert-file certs/local-cert.pem -key-file certs/local-key.pem "web.localhost" "*.web.localhost" "db.localhost" "*.db.localhost"
certutil.exe -addstore -user "Root" "\home\t\.local\share\mkcert\rootCA.pem"
