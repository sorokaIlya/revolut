<?php
namespace App\Websocket;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;
class MessageHandler implements MessageComponentInterface
{
    protected $connections;
    protected $users=[];
    public function __construct()
    {
        $this->connections = new SplObjectStorage();

    }
    public function onOpen(ConnectionInterface $conn)
    {
        $this->connections->attach($conn);

    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg);
        if(property_exists($data,"userId")){
            $from->userId = $data->userId; //1 user has 1 id
            $this->users[$data->userId]=$from;
        }else{
            $this->users[$data->to]->send($msg);
        }
//            foreach ($this->connections as $connection) {
//                if ($connection->userId === $data->to) {
//                    $connection->userId->send($msg);
//                }
//            }
        }


    public function onClose(ConnectionInterface $conn)
    {
        $this->connections->detach($conn);
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        $this->connections->detach($conn);
        $conn->close();
    }
}
