<?php


namespace App\Command;
use App\Websocket\MessageHandler;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
require dirname(__DIR__) . '/../vendor/autoload.php';

class WebsocketServerCommand extends Command

{
    protected static $defaultName = "run:websocket-server";
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $port = 3001;
        $output->writeln('Server start in port'.$port);
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new MessageHandler()
                )
            ),$port
        );
        $server->run();
        return 0;
    }
}
