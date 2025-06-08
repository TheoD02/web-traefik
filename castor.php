<?php

use Castor\Attribute\AsTask;

use function Castor\io;
use function Castor\run;

#[AsTask(description: 'Show available commands')]
function help(): void
{
    io()->title('🌐 Web-Traefik Local Development Setup');
    io()->section('Available commands:');
    
    $commands = [
        'castor start' => 'Start Traefik (generates certs if needed)',
        'castor stop' => 'Stop Traefik',
        'castor restart' => 'Restart Traefik',
        'castor status' => 'Show Traefik status',
        'castor logs' => 'Show Traefik logs',
        'castor clean' => 'Stop and remove certificates',
        'castor setup-certs' => 'Generate SSL certificates only',
        'castor network' => 'Show Docker network information',
    ];
    
    foreach ($commands as $command => $description) {
        io()->writeln(sprintf('  <info>%-20s</info> %s', $command, $description));
    }
}

#[AsTask(description: 'Generate SSL certificates using mkcert')]
function setupCerts(): void
{
    io()->section('🔒 Setting up SSL certificates...');
    
    // Create certs directory
    run('mkdir -p certs');
    
    // Check if mkcert is available
    $mkcertAvailable = run('command -v mkcert', quiet: true)->isSuccessful();
    
    if (!$mkcertAvailable) {
        io()->warning('mkcert not found. Installing...');
        
        // Try to install mkcert based on available package managers
        $aptAvailable = run('command -v apt', quiet: true)->isSuccessful();
        $brewAvailable = run('command -v brew', quiet: true)->isSuccessful();
        $chocoAvailable = run('command -v choco', quiet: true)->isSuccessful();
        
        if ($aptAvailable) {
            run('sudo apt update && sudo apt install -y mkcert libnss3-tools');
        } elseif ($brewAvailable) {
            run('brew install mkcert');
        } elseif ($chocoAvailable) {
            run('choco install mkcert');
        } else {
            io()->error('Please install mkcert manually: https://github.com/FiloSottile/mkcert');
            return;
        }
        
        run('mkcert -install');
    } else {
        io()->success('mkcert found, generating certificates...');
    }
    
    // Generate certificates
    run([
        'mkcert',
        '-cert-file', 'certs/local-cert.pem',
        '-key-file', 'certs/local-key.pem',
        'web.localhost', '*.web.localhost',
        'api.localhost', '*.api.localhost',
        'db.localhost', '*.db.localhost',
        'docs.localhost', '*.docs.localhost'
    ]);
    
    io()->success('✅ Certificates generated successfully!');
}

#[AsTask(description: 'Start Traefik (generates certs if needed)')]
function start(): void
{
    setupCerts();
    
    io()->section('🚀 Starting Traefik...');
    run('docker compose up -d');
    
    io()->success('✅ Traefik is running!');
    io()->writeln('📊 Dashboard: <href=https://traefik.web.localhost>https://traefik.web.localhost</>');
    io()->writeln('🌐 Network: traefik');
}

#[AsTask(description: 'Stop Traefik')]
function stop(): void
{
    io()->section('🛑 Stopping Traefik...');
    run('docker compose down');
    io()->success('✅ Traefik stopped!');
}

#[AsTask(description: 'Restart Traefik')]
function restart(): void
{
    stop();
    start();
}

#[AsTask(description: 'Stop Traefik and remove certificates')]
function clean(): void
{
    stop();
    
    io()->section('🧹 Cleaning up...');
    run('rm -rf certs/*.pem', quiet: true);
    run('docker network rm traefik 2>/dev/null || true', quiet: true);
    io()->success('✅ Cleanup complete!');
}

#[AsTask(description: 'Show Traefik status')]
function status(): void
{
    io()->section('📊 Traefik Status:');
    run('docker compose ps');
}

#[AsTask(description: 'Show Traefik logs')]
function logs(): void
{
    run('docker compose logs -f traefik');
}

#[AsTask(description: 'Show Docker network information')]
function network(): void
{
    io()->section('🌐 Docker Networks:');
    $result = run('docker network ls | grep traefik || echo "Traefik network not found"', quiet: true);
    io()->writeln($result->getOutput());
}

#[AsTask(description: 'Quick development alias for start')]
function dev(): void
{
    start();
}

