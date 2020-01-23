<?php
/**
 * Created by PhpStorm.
 * User: versus
 * Date: 25/03/18
 * Time: 17:02
 */

namespace Tests\NotificationBundle\Server;

use NotificationBundle\Exception\Server\BadArgumentException;
use NotificationBundle\Exception\Server\InvalidArgumentException;
use NotificationBundle\Exception\Server\InvalidMessageException;
use NotificationBundle\Factory\Message;
use Tests\Framework\WebTestCase;

class NotificationServerTest extends WebTestCase
{
    /** @var  Helper $helper */
    private $helper;
    /** @var  \SplObjectStorage $clients */
    private $clients;
    /** @var  Message $messageFactory */
    private $messageFactory;


    public function setUp()
    {
        $this->helper = new \Tests\NotificationBundle\Server\Helper();
        $this->clients = new \SplObjectStorage;
        $this->messageFactory = new Message();
    }

    /**
     * @test
     */
    public function onOpen()
    {
        $conn = $this->dataTest();
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        $msg = $this->messageFactory->monitoringConnexion();

        $this->assertEquals($msg['type'], 'initialisation');
        $this->assertEquals($msg['msg'], "Client connected to server ok");
        $this->assertEquals($msg['data']['is_handler'], true);

        $msg = json_encode($msg);

        $this->assertTrue($this->clients->contains($conn));

    }

    /**
     * @test
     */
    public function onMessage()
    {
        $from = new \stdClass();
        $from->id = 10;
        $msg = $this->helper->msgClientConnexion();

        $this->clients->attach($from);

        $checker = $this->messageChecker($msg);
        $this->assertEquals($checker,'msg_valid');

        //$this->router($from, $msg);

        $this->assertEquals($msg->type, 'connexion');
        $this->assertEquals(gettype($msg->type), 'string');
        $this->assertEquals(gettype($msg->data->room), 'integer');
        $this->assertEquals(gettype($msg->data->user->id), 'integer');
        $this->assertTrue(property_exists($msg, 'data'));

    }

    private function dataTest()
    {
        $msg = new \stdClass();
        $data = new \stdClass();
        $resources = $this->helper->resourcesClients('jean', 'kyc', null);

        $data->room = 3;
        $data->is_handler = false;
        $data->user = $resources->user;
        $data->room = $resources->room;

        $msg->type = 'connexion';
        $msg->msg = 'has just connected';
        $msg->data = $data;

        return $msg;
    }

    /**
     * TODO : debuger ce script pour tester l'exception catché
     */
    public function canNotConnectWithoutAConnection()
    {
        $this->expectException(BadArgumentException::class);
        $conn = [
            'room' => 'madison squer garden',
            'user' => 'toto'
        ];
        $this->clients->attach($conn);

        $conn = 'room : madison squer garden,  user : toto';
        $this->expectException(BadArgumentException::class);
        $this->clients->attach($conn);

        $conn = 176434;
        $this->expectException(BadArgumentException::class);
        $this->clients->attach($conn);

        $conn = new \stdClass();
        $conn->type = 'connexion';
        $conn->message = 'connexion duu user';
        $this->expectException(InvalidArgumentException::class);
        $this->clients->attach($conn);
    }

    public function canNotHandlerConnectionWithoutValidMessage()
    {
        $msg = new \stdClass();
        $msg->type = 'connexion';
        $msg->msg = 'has just connected';

        $this->expectException(InvalidArgumentException::class);


    }

    private function messageChecker($msg)
    {
        if(gettype($msg) != 'object' || !property_exists($msg, 'data'))
        {
            throw new InvalidMessageException();
        }

        return 'msg_valid';
    }

    public function router($from, $msg)
    {
        if ($msg->type == 'connexion')
        {
            $this->initialize($from, $msg);
        }
    }

    private function initialize($from, $msg)
    {
        $group = $this->getGroup($this->clients[$from]);
        // existe il dans le cache ?
        //      oui : mise a jour de la valeur conn dans l'objet client du server
        //      non : $this->connect()
        $resourcesBackupData = $this->dataFormater($msg);
        var_dump($resourcesBackupData);die();
        $checker = $this->cache->isExist();
        // Retour
        //      les clients connectes
        //      boolean
    }

    private function getGroup($member)
    {
        $tab = [];
        foreach ($this->clients as $client)
        {
            if($client->data->user != $member)
            {
                $tab[] = $client;
            }
        }
        return $tab;
    }
}