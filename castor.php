<?php

use Castor\Attribute\AsTask;

use Castor\Exception\WaitFor\TimeoutReachedException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use function Castor\capture;
use function Castor\context;
use function Castor\finder;
use function Castor\fs;
use function Castor\io;
use function Castor\run;
use function Castor\wait_for_docker_container;
use function Castor\wait_for_http_response;

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
        'castor dev' => 'Alias for start (quick development)',
        'castor network' => 'Show Docker network information',
    ];
    
    foreach ($commands as $command => $description) {
        io()->writeln(sprintf('  <info>%-20s</info> %s', $command, $description));
    }
    
    io()->newLine();
    io()->section('Alternative: Use Make instead of Castor');
    io()->writeln('  <info>make start</info>     # Same as \'castor start\'');
    io()->writeln('  <info>make help</info>      # Show Make commands');
}

#[AsTask(description: 'Generate SSL certificates using mkcert')]
function setupCerts(): void
{
    io()->section('🔒 Setting up SSL certificates...');
    
    // Create certs directory
    run('mkdir -p certs');

    $quietAndAllowFailure = context()->withQuiet()->withAllowFailure();

    // Check if mkcert is available
    $mkcertAvailable = run('command -v mkcert', context: $quietAndAllowFailure)->isSuccessful();
    
    if (!$mkcertAvailable) {
        io()->warning('mkcert not found. Installing...');
        
        // Try to install mkcert based on available package managers
        $aptAvailable = run('command -v apt', context: $quietAndAllowFailure)->isSuccessful();
        $brewAvailable = run('command -v brew', context: $quietAndAllowFailure)->isSuccessful();
        $chocoAvailable = run('command -v choco', context: $quietAndAllowFailure)->isSuccessful();
        
        if ($aptAvailable) {
            run('sudo apt update && sudo apt install -y mkcert libnss3-tools');
            run(['sudo', 'apt', 'update']);
            run(['sudo', 'apt', 'install', '-y', 'mkcert', 'libnss3-tools']);
        } elseif ($brewAvailable) {
            run(['brew', 'install', 'mkcert']);
        } elseif ($chocoAvailable) {
            run(['choco', 'install', 'mkcert']);
        } else {
            io()->error('Please install mkcert manually: https://github.com/FiloSottile/mkcert');
            return;
        }
        
        run(['mkcert', '-install']);
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
    run(['docker', 'compose', 'up', '-d']);

    try {
        wait_for_docker_container(
            containerName: 'traefik',
            timeout: 60,
            containerChecker: static function () {
                $status = capture(['docker', 'inspect', '-f', '{{.State.Running}}', 'traefik']);
                return trim($status) === 'true';
            }
        );
    } catch (TimeoutReachedException $e) {
        io()->error("❌ Traefik container failed to start within the timeout period.");
        return;
    }

    io()->success('✅ Traefik is running!');
    io()->writeln('📊 Dashboard: <href=https://traefik.web.localhost>https://traefik.web.localhost</> (If you see a SSL warning, restart your browser)');
    io()->writeln('🌐 Network: traefik');
}

#[AsTask(description: 'Stop Traefik')]
function stop(): void
{
    io()->section('🛑 Stopping Traefik...');
    run(['docker', 'compose', 'down']);
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
    fs()->remove(finder()->files()->in('certs')->ignoreDotFiles(true));
    run(['docker', 'network', 'rm', 'traefik'], context: context()->withQuiet()->withAllowFailure());
    io()->success('✅ Cleanup complete!');
}

#[AsTask(description: 'Show Traefik status')]
function status(): void
{
    io()->section('📊 Traefik Status:');
    run(['docker', 'compose', 'ps']);
}

#[AsTask(description: 'Show Traefik logs')]
function logs(): void
{
    run(['docker', 'compose', 'logs', '-f', 'traefik']);
}

#[AsTask(description: 'Show Docker network information')]
function network(): void
{
    io()->section('🌐 Docker Networks:');
    run(['docker', 'network', 'ls']);
}

#[AsTask(description: 'Quick development alias for start')]
function dev(): void
{
    start();
}
