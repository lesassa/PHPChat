<?php
namespace App\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Log\Log;
use Psy\Shell as PsyShell;
use Cake\ORM\TableRegistry;
use App\View\AjaxView;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\AIComponent;

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

    public function talkRegularTime()
    {
    	//内容設定
    	$aiChat= "定時だよ！まだ帰れないの？m9(^Д^)";

    	//チャットを保存、送信
    	$this->saveChat($aiChat);
    }


    public function talkService()
    {
    	$AIComponent =new AIComponent(new ComponentRegistry());
    	//内容設定
    	$aiChat= $AIComponent->talkAI("今日の勤怠");

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

    		$chat = $ChatsDBI->get([$chat->roomId, $chat->chatNumber], ["contain" => ["Members",  "Rooms"]]);
    		$MembersDBI = TableRegistry::get('Members');
    		$member = $MembersDBI->get(AI_ID);

    		//チャットサーバに送信
    		$View = new AjaxView();
    		$View->set("chat", $chat);
    		$response["status"] = "success";
    		$response["html"] = $View->render('/Element/chat', false);
    		$response["selecter"] = "#chats".$chat->roomId;
//     		$response["roomName"] = $chat->room->roomName;
    		$response["roomId"] = $chat->room->roomId;
//     		$response["memberName"] = $member->memberName;
    		$response["chat"] = $chat->toArray();
    		$this->sendByZMQ($response);

    	}
    }

}
