<?php
namespace NotificationBundle\Command;


use NotificationBundle\Manager\Cache\Cache;
use NotificationBundle\Manager\Cache\NotificationCache;
use NotificationBundle\Server\CacheAlpha;
use NotificationBundle\Server\NotificationServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerCommand extends ContainerAwareCommand
{

    /**
     * Configure a new Command Line
     */
    protected function configure()
    {
        $this
            ->setName('Project:notification:server')
            ->setDescription('Start the notification server.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var NotificationCache $cache */
        $cache = $this->getContainer()->get('notification.cache');
        $messageFactory = $this->getContainer()->get('notification.message');
        $notificationCenter = $this->getContainer()->get('notification.center');


        $server = IoServer::factory(new HttpServer(
            new WsServer(
                new NotificationServer($this->getContainer(), 
                                        $cache,
                                        $messageFactory, 
                                        $notificationCenter)
            )
        ), 8081);

        $server->run();

    }

}
