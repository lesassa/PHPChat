<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\ORM\TableRegistry;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link http://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class ChatController extends AppController
{
	public $Docomo;

	public function initialize()
	{
		parent::initialize();

		$this->AI = $this->loadComponent('AI');
	}

    public function index()
    {

    	$RoomsDBI = TableRegistry::get('Rooms');
    	$rooms = $RoomsDBI->find('all');

    	$roomsWithChats = array();
    	foreach ($rooms as $room) {

    		$SubscribesDBI = TableRegistry::get('Subscribes');
    		$subscribe = $SubscribesDBI->find()->where(["roomId =" => $room->roomId])->andWhere(["memberId =" => $this->loginTable->memberId]);
    		if (!$subscribe->count()) {
    			$room->chats = [];
    			$roomsWithChats[$room->roomId] = $room;
				continue;
			}

	    	$ChatsDBI = TableRegistry::get('Chats');
	    	$query = $ChatsDBI->find();
	    	$ret = $query->select(['max_id' => $query->func()->max('chatNumber')])->where(["roomId =" => $room->roomId])->first();
	    	$chats = $ChatsDBI->find()->where(["roomId =" => $room->roomId])->andWhere(["chatNumber >" => $ret->max_id - 10])->contain(['Members'])->order(['Chats.chatNumber' => 'DESC']);;
	    	$room->chats = $chats;
	    	$roomsWithChats[$room->roomId] = $room;

    	}
    	$this->set('rooms', $roomsWithChats);
    	$this->set('loginTable', $this->loginTable);


    }

    public function addChat()
    {
    	$this->autoRender = FALSE;
    	if($this->request->is('ajax')) {

    		//DB登録
	    	$ChatsDBI = TableRegistry::get('Chats');
	    	$chat = $ChatsDBI->newEntity();
	    	$chat->roomId = $this->request->data["roomId"];
	    	$query = $ChatsDBI->find();
	    	$ret = $query->select(['max_id' => $query->func()->max('chatNumber')])->where(["roomId =" => $chat->roomId])->first();
	    	$chat->chatNumber = $ret->max_id + 1;
	    	$ChatsDBI->patchEntity($chat, $this->request->data);
	    	$chat->memberId = $this->loginTable->memberId;

	    	if ($ChatsDBI->save($chat)) {

		    	//ログ出力
		    	$this->Log->outputLog("chat = [".print_r($chat, true)."]");

	    		$chat = $ChatsDBI->get([$chat->roomId, $chat->chatNumber], ["contain" => ['Members']]);

		    	$RoomsDBI = TableRegistry::get('Rooms');
		    	$room = $RoomsDBI->get($chat->roomId);

		    	$MembersDBI = TableRegistry::get('Members');
		    	$member = $MembersDBI->get($chat->memberId);

		    	$msg["roomId"] = $chat->roomId;
		    	$msg["roomName"] = $room->roomName;
		    	$msg["chatNumber"] = $chat->chatNumber;
		    	$msg["chatText"] = $chat->chatText;
		    	$msg["replyId"] = $chat->replyId;
		    	$msg["memberId"] = $chat->memberId;
		    	$msg["memberName"] = $member->memberName;

		    	//チャットサーバに送信
		    	$context = new \ZMQContext();
		    	$socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
		    	$socket->connect("tcp://localhost:5555");
		    	$socket->send(json_encode($msg));
		    	echo json_encode([]);

		    	//AIへの返信の場合
		    	//AI判定
		    	if($chat->memberId == AI_ID) {
		    		return;
		    	}
		    	if ($chat->replyId == "" || $chat->replyId == 0) {
		    		return;
		    	}
		    	$originalChat = $ChatsDBI->get([$chat->roomId, $chat->replyId], ["contain" => ["Members"]]);
		    	if ($originalChat->memberId != AI_ID) {
					return;
		    	}

		    	//ログ出力
		    	$this->Log->outputLog("AI TALK START");

		    	//AI対話
		    	$reply =  $this->AI->talkAI($chat->chatText);

		    	//DB登録
		    	$member = $MembersDBI->get(AI_ID);
		    	$replyChat = $ChatsDBI->newEntity();
		    	$replyChat->roomId = $chat->roomId;
		    	$query = $ChatsDBI->find();
		    	$ret = $query->select(['max_id' => $query->func()->max('chatNumber')])->where(["roomId =" => $chat->roomId])->first();
		    	$replyChat->chatNumber = $ret->max_id + 1;
		    	$replyChat->chatText = $reply;
		    	$replyChat->replyId = $chat->chatNumber;
		    	$replyChat->memberId = AI_ID;

		    	if ($ChatsDBI->save($replyChat)) {

		    		//ログ出力
		    		$this->Log->outputLog("AI reply = [".print_r($replyChat, true)."]");

			    	$replyMsg["roomId"] = $replyChat->roomId;
			    	$replyMsg["roomName"] = $room->roomName;
			    	$replyMsg["chatNumber"] = $replyChat->chatNumber;
			    	$replyMsg["chatText"] = $replyChat->chatText;
			    	$replyMsg["replyId"] = $replyChat->replyId;
			    	$replyMsg["memberId"] = $replyChat->memberId;
			    	$replyMsg["memberName"] = $originalChat->member->memberName;

			    	//チャットサーバに送信
			    	$context = new \ZMQContext();
			    	$socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
			    	$socket->connect("tcp://localhost:5555");
			    	$socket->send(json_encode($replyMsg));

		    	} else {

		    		//ログ出力
		    		$this->Log->outputLog($chat->errors());

		    	}

	    	} else {

	    		//ログ出力
	    		$this->Log->outputLog($chat->errors());

	    		echo json_encode(["errors" =>$chat->errors()]);
	    	}
    	}
    }


    public function enter()
    {
    	$this->autoRender = FALSE;
    	if($this->request->is('ajax')) {
    		$SubscribesDBI = TableRegistry::get('Subscribes');
    		$subscribes = $SubscribesDBI->find()->where(["memberId =" => $this->loginTable->memberId]);

    		$MembersDBI = TableRegistry::get('Members');
    		$member = $MembersDBI->get($participant->memberId);

    		$msg = ["login" => true,];
    		foreach ($subscribes as $subscribe) {
	    		$msg[] = ["roomId" => $subscribe->roomId, "memberId" => $subscribe->memberId, "memberName" => $member->memberName];
    		}

    		//チャットサーバに送信
    		$context = new \ZMQContext();
    		$socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
    		$socket->connect("tcp://localhost:5555");
    		$socket->send(json_encode($msg));
    		echo 0;
    	}
    }

    public function getParticipants()
    {
    	$this->autoRender = FALSE;
    	if($this->request->is('ajax')) {
    		sleep(1);//前回ログアウトを待つ
    		$ParticipantsDBI = TableRegistry::get('Participants');
    		$participants = $ParticipantsDBI->find("all")->contain(['Members']);
    		$SubscribesDBI = TableRegistry::get('Subscribes');
    		$members = array();
    		foreach ($participants as $participant) {

    			if ($participant->memberId == $this->loginTable->memberId) {
    				//ログ出力
    				$this->Log->outputLog($participant->memberId);
    				$this->Log->outputLog($this->loginTable->memberId);
					continue;
    			}

    			$subscribes = $SubscribesDBI->find()->where(["memberId =" => $this->loginTable->memberId]);

    			foreach ($subscribes as $subscribe) {
	    			$member = array();
	    			$member["memberId"] = $subscribe->memberId;
	    			$member["roomId"] = $subscribe->roomId;
	    			$member["memberName"] = $participant->member->memberName;
	    			$members[] = $member;
    			}

    			$member = array();
    			$member["memberId"] = $participant->memberId;
    			$member["roomId"] = "9999";
    			$member["memberName"] = $participant->member->memberName;
    			$members[] = $member;

			}
			$this->response->body(json_encode($members));
    	}
    }


    public function createRoom()
    {
    	$this->autoRender = FALSE;
    	if($this->request->is('ajax')) {
    		$RoomsDBI = TableRegistry::get('Rooms');
    		$room = $RoomsDBI->newEntity($this->request->data);
    		if ($RoomsDBI->save($room)) {

    			$msg["roomId"] = $room->roomId;
    			$msg["roomName"] = $room->roomName;
    			$msg["roomDescription"] = $room->roomDescription;
    			$msg["roomCreate"] = true;

    			//チャットサーバに送信
    			$context = new \ZMQContext();
    			$socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
    			$socket->connect("tcp://localhost:5555");
    			$socket->send(json_encode($msg));
    			echo 0;

    		} else {
    			//ログ出力
    			$this->Log->outputLog($room->errors());

    			echo json_encode(["errors" =>$room->errors()]);
    		}

    	}
    }

    public function saveSubscribe()
    {
    	$this->autoRender = FALSE;
    	if($this->request->is('ajax')) {
    		$SubscribesDBI = TableRegistry::get('Subscribes');
    		$subscribe = $SubscribesDBI->newEntity($this->request->data);
    		$subscribe->memberId = $this->loginTable->memberId;
    		$SubscribesDBI->save($subscribe);

    		$MembersDBI = TableRegistry::get('Members');
    		$member = $MembersDBI->get($subscribe->memberId);

    		$msg = ["loginId" => true,];
    		$msg[] = ["roomId" => $subscribe->roomId, "memberId" => $subscribe->memberId, "memberName" => $member->memberName];

    		//チャットサーバに送信
    		$context = new \ZMQContext();
    		$socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
    		$socket->connect("tcp://localhost:5555");
    		$socket->send(json_encode($msg));


    		//ログ出力
    		$this->Log->outputLog($subscribe->errors());

    		$ChatsDBI = TableRegistry::get('Chats');
    		$query = $ChatsDBI->find();
    		$ret = $query->select(['max_id' => $query->func()->max('chatNumber')])->where(["roomId =" => $subscribe->roomId])->first();
    		$chats = $ChatsDBI->find()->where(["roomId =" => $subscribe->roomId])->andWhere(["chatNumber >" => $ret->max_id - 10])->contain(['Members'])->order(['Chats.chatNumber' => 'ASC']);
    		$chatsArray = array();
    		foreach ($chats as $chat) {
    			$chatArray = array();
    			$chatArray["roomId"] = $chat->roomId;
    			$chatArray["replyId"] = $chat->replyId;
    			$chatArray["chatNumber"] = $chat->chatNumber;
    			$chatArray["memberName"] = $chat->member->memberName;
    			$chatArray["chatText"] = $chat->chatText;
    			$chatsArray[] = $chatArray;
    		}
    		echo json_encode($chatsArray);
    	}
    }

    public function getSubscribes()
    {
    	$this->autoRender = FALSE;
    	if($this->request->is('ajax')) {
    		$SubscribesDBI = TableRegistry::get('Subscribes');
    		$subscribes = $SubscribesDBI->find()->where(["memberId =" => $this->loginTable->memberId]);


    		$participants = ["loginId" => true,];
    		$msg = array();

    		$MembersDBI = TableRegistry::get('Members');
    		$member = $MembersDBI->get($this->loginTable->memberId);

			foreach($subscribes as $subscribe) {
				$msg[] = $subscribe->roomId;
				$participants[] = ["roomId" => $subscribe->roomId, "memberId" => $subscribe->memberId, "memberName" => $member->memberName];
			}
			$participants[] = ["roomId" => "9999", "memberId" => $this->loginTable->memberId, "memberName" => $member->memberName];

			//チャットサーバに送信
			$context = new \ZMQContext();
			$socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
			$socket->connect("tcp://localhost:5555");
			$socket->send(json_encode($participants));

			echo json_encode($msg);
    	}
    }

    public function unsubscribe()
    {
    	$this->autoRender = FALSE;
    	if($this->request->is('ajax')) {
    		$SubscribesDBI = TableRegistry::get('Subscribes');
    		$subscribe = $SubscribesDBI->get([$this->loginTable->memberId, $this->request->data["roomId"]]);
    		$SubscribesDBI->delete($subscribe);

    		$msg = ["unsubscribeId" => true, "roomId" => $this->request->data["roomId"], "memberId" =>$this->loginTable->memberId];

    		//チャットサーバに送信
    		$context = new \ZMQContext();
    		$socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
    		$socket->connect("tcp://localhost:5555");
    		$socket->send(json_encode($msg));

    	}
    }


}
