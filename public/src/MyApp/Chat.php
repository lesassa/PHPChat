<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;

    protected $counter;

    public function __construct() {
        $this->clients = new \SplObjectStorage;

        $counter = 0;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";

        //リソースIDを返信
        $conn->send($conn->resourceId);
    }

    public function onMessage(ConnectionInterface $from, $msg) {

    	//ピン送信の場合は何もしない
    	if ($msg == "ping") {
    		return;
    	}

        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        foreach ($this->clients as $client) {
        	//全員（自分含めて）メッセージ送信
//             if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
//             }
        }
    }

    public function onClose(ConnectionInterface $conn) {

    	$exec = dirname(dirname(dirname(__FILE__))).'\bin\cake chat logout '.$conn->resourceId;
    	$return = exec($exec);
    	$msg["logoutId"] = $conn->resourceId;

    	//参加者にログアウト情報を送信
    	foreach ($this->clients as $client) {
    		$client->send(json_encode($msg));
    	}

        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        $date = date("Y/m/d H:i:s");
        echo "Connection {$conn->resourceId} has disconnected {$return} {$date}\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
    	echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}