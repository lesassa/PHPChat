<?php
namespace App\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Log\Log;
use Psy\Shell as PsyShell;
use Cake\ORM\TableRegistry;

class BotShell extends Shell
{

    public function main()
    {
    	echo  "success";
    }

    public function talkTime()
    {
    	//内容設定
    	$aiChat= date("H時だよ！");

    	//チャットを保存、送信
    	$this->saveChat($aiChat);
    }

    /**
     * ZMQでチャットサーバーに送信
     * @param array $msg 送信メッセージ（項目名と値の連想配列）
     */
    private function sendByZMQ(array $msg)
    {
    	//チャットサーバに送信
    	$context = new \ZMQContext();
    	$socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
    	$socket->connect("tcp://localhost:5555");
    	$socket->send(json_encode($msg));
    }

    /**
     * チャットを保存、送信
     * @param String $msg 送信メッセージ
     */
    private function saveChat($aiChat)
    {
    	//DB登録
    	$ChatsDBI = TableRegistry::get('Chats');
    	$chat = $ChatsDBI->newEntity();
    	$query = $ChatsDBI->find();
    	$ret = $query->select(['max_id' => $query->func()->max('chatNumber')])->where(["roomId =" => BOT_ROOM])->first();
    	$chat->chatNumber = $ret->max_id + 1;
    	$chat->memberId = AI_ID;
    	$chat->roomId = BOT_ROOM;
    	$chat->chatText = $aiChat;

    	if ($ChatsDBI->save($chat)) {

    		$MembersDBI = TableRegistry::get('Members');
    		$member = $MembersDBI->get(AI_ID);

    		//チャットサーバに送信
    		$msg["roomId"] = BOT_ROOM;
    		$msg["roomName"] = BOT_ROOMNAME;
    		$msg["chatNumber"] = $chat->chatNumber;
    		$msg["chatText"] = $chat->chatText;
    		$msg["memberId"] = AI_ID;
    		$msg["memberName"] = $member->memberName;
    		$this->sendByZMQ($msg);
    	}
    }

}
