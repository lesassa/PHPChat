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
use Cake\View\View;
use App\View\AjaxView;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link http://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class ChatController extends AppController
{
	public $AI;

	public function initialize()
	{
		parent::initialize();

		$this->AI = $this->loadComponent('AI');
	}

	/**
	 * 画面初期表示処理
	 */
    public function index()
    {
    	//ルームを全取得
    	$RoomsDBI = TableRegistry::get('Rooms');
    	$rooms = $RoomsDBI->find()->all();

    	//チャットを取得
    	$roomsWithChats = array();
    	foreach ($rooms as $room) {

    		//購読ルームを取得
    		$SubscribesDBI = TableRegistry::get('Subscribes');
    		$subscribe = $SubscribesDBI->find()->where(["roomId =" => $room->roomId])->andWhere(["memberId =" => $this->loginTable->memberId]);
    		if (!$subscribe->count()) {
    			$room->chats = array();
    			$roomsWithChats[$room->roomId] = $room;
    			continue;
    		}

    		$chats = $this->getChats($room->roomId);
	    	$room->chats = $chats;
	    	$roomsWithChats[$room->roomId] = $room;
    	}
    	$this->set('rooms', $roomsWithChats);
    }

    /**
     * AJAXチャット送信（保存）
     */
    public function addChat()
    {
    	//AJAX精査
    	$this->autoRender = FALSE;
    	if(!$this->request->is('ajax')) {
    		return;
    	}

    	//DB登録
	    $ChatsDBI = TableRegistry::get('Chats');
	    $chat = $ChatsDBI->newEntity($this->request->data);
	    $query = $ChatsDBI->find();
	    $ret = $query->select(['max_id' => $query->func()->max('chatNumber')])->where(["roomId =" => $chat->roomId])->first();
	    $chat->chatNumber = $ret->max_id + 1;
	    $chat->memberId = $this->loginTable->memberId;

	    //ログ出力
	    $this->Log->outputLog("chat = [".print_r($chat, true)."]");

	    if ($ChatsDBI->save($chat)) {

	    	$chat = $ChatsDBI->get([$chat->roomId, $chat->chatNumber], ["contain" => ["Members", "Rooms", "Nocares"]]);
	    	$View = new AjaxView();
		    $View->set("chat", $chat);
		    $response["status"] = "success";
		    $response["html"] = $View->render('/Element/chat', false);
		    $response["selecter"] = "#chats".$chat->roomId;
		    $response["roomId"] = $chat->room->roomId;
		    $response["chat"] = $chat->toArray();
		    $this->sendByZMQ($response);

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

		    	//チャットサーバに送信
		    	$replyChat= $ChatsDBI->get([$replyChat->roomId, $replyChat->chatNumber], ["contain" => ["Members",  "Rooms"]]);
		    	$View = new AjaxView();
		    	$View->set("chat", $replyChat);
		    	$response["status"] = "success";
		    	$response["html"] = $View->render('/Element/chat', false);
		    	$response["selecter"] = "#chats".$replyChat->roomId;
		    	$response["roomId"] = $chat->room->roomId;
		    	$response["chat"] = $replyChat->toArray();
		    	$this->sendByZMQ($response);
		    	return;
		    }
	    }

	    //エラーメッセージ返信
	    echo json_encode(["errors" =>$chat->errors()]);
    }

    /**
     * AJAXログイン情報取得
     */
    public function getParticipants()
    {
    	//AJAX精査
    	$this->autoRender = FALSE;
    	if(!$this->request->is('ajax')) {
    		return;
    	}

    	sleep(1);//前回ログアウトを待つ
    	$ParticipantsDBI = TableRegistry::get('Participants');
    	$participants = $ParticipantsDBI->find()->contain(['Members', "Subscribes"])->all();
    	$response["status"] = "loginId";
    	$response["html"][] = array();
    	foreach ($participants as $participant) {

    		//ログ出力
    		$this->Log->outputLog("participant = [".print_r($participant, true)."]");

    		//自身のログイン情報を排除
    		if ($participant->memberId == $this->loginTable->memberId) {
				continue;
    		}

    		//ログイン情報に紐づく購読ルームを取得

    		foreach($participant->subscribes as $subscribe) {
    			$View = new AjaxView();
    			$View->set("subscribe", $subscribe);
    			$View->set("memberName", $participant->member->memberName);
    			$response["html"][] = $View->render('/Element/login', false);
    		}
    		$View = new AjaxView();
    		$SubscribesDBI = TableRegistry::get('Subscribes');
    		$subscribe = $SubscribesDBI->newEntity();
    		$subscribe->memberId = $participant->memberId;
    		$subscribe->roomId= "9999";
    		$View->set("subscribe", $subscribe);
    		$View->set("memberName", $participant->member->memberName);
    		$response["html"][] = $View->render('/Element/login', false);
		}

		$this->response->body(json_encode($response));
    }


    /**
     * AJAXルーム作成
     */
    public function createRoom()
    {
    	//AJAX精査
    	$this->autoRender = FALSE;
    	if(!$this->request->is('ajax')) {
    		return;
    	}

    	//ルーム作成
    	$RoomsDBI = TableRegistry::get('Rooms');
    	$room = $RoomsDBI->newEntity($this->request->data);
    	if ($RoomsDBI->save($room)) {

    		//ログ出力
    		$this->Log->outputLog("room = [".print_r($room, true)."]");

    		//チャットサーバに送信
    		$room->chats = array();
    		$response["status"] = "roomCreate";
    		$View = new AjaxView();
    		$View->set("room", $room);
    		$response["html"]["#main form"] = $View->render('/Element/roomName', false);
    		$View = new AjaxView();
    		$View->set("room", $room);
    		$response["html"][".inner"] = $View->render('/Element/room', false);
    		$View = new AjaxView();
    		$View->set("room", $room);
    		$response["html"]["#main .room9999"] = $View->render('/Element/roomList', false);
    		$this->sendByZMQ($response);

    		return;
    	}

    	echo json_encode(["errors" =>$room->errors()]);
    }

    /**
     * AJAX購読ルームの保存
     */
    public function saveSubscribe()
    {
    	//AJAX精査
    	$this->autoRender = FALSE;
    	if(!$this->request->is('ajax')) {
    		return;
    	}

    	$SubscribesDBI = TableRegistry::get('Subscribes');
    	$subscribe = $SubscribesDBI->newEntity($this->request->data);
    	$subscribe->memberId = $this->loginTable->memberId;
    	$SubscribesDBI->save($subscribe);

    	//ログ出力
    	$this->Log->outputLog("subscribe = [".print_r($subscribe, true)."]");

    	//入室情報送信
    	//チャットサーバに送信
    	$MembersDBI = TableRegistry::get('Members');
    	$member = $MembersDBI->get($subscribe->memberId);
    	$response["status"] = "loginId";
    	$View = new AjaxView();
    	$View->set("subscribe", $subscribe);
    	$View->set("memberName", $member->memberName);
    	$response["html"][] = $View->render('/Element/login', false);
    	$this->sendByZMQ($response);

    	//既出のチャットの取得
    	$ChatsDBI = TableRegistry::get('Chats');
    	$query = $ChatsDBI->find();
    	$ret = $query->select(['max_id' => $query->func()->max('chatNumber')])->where(["roomId =" => $subscribe->roomId])->first();
    	$chats = $ChatsDBI->find()->where(["roomId =" => $subscribe->roomId])->andWhere(["chatNumber >" => $ret->max_id - 10])->contain(['Members'])->order(['Chats.chatNumber' => 'ASC'])->all();
    	$chatsArray = array();

    	//チャットサーバに送信
    	$response["status"] = "success";
    	$response["selecter"] = "#chats".$subscribe->roomId;
    	$response["memberName"] = $member->memberName;
    	$response["html"] = "";
    	foreach ($chats as $chat) {
    		$View = new AjaxView();
    		$View->set("chat", $chat);
    		$response["html"] = $View->render('/Element/chat', false).$response["html"];
    	}
    	$this->response->body(json_encode($response));
    }

    /**
     * AJAX購読ルームの取得
     */
    public function getSubscribes()
    {
    	//AJAX精査
    	sleep(1);//前回ログアウトを待つ
    	$this->autoRender = FALSE;
    	if(!$this->request->is('ajax')) {
    		return;
    	}

    	//購読ルーム取得
    	$SubscribesDBI = TableRegistry::get('Subscribes');
    	$subscribes = $SubscribesDBI->find()->where(["Subscribes.memberId =" => $this->loginTable->memberId])->contain("Members");

    	//入室情報送信
    	//チャットサーバに送信
    	$response["status"] = "loginId";
    	$msg = array();
    	foreach($subscribes as $subscribe) {
    		$msg[] = $subscribe->roomId;
	    	$View = new AjaxView();
	    	$View->set("subscribe", $subscribe);
	    	$View->set("memberName", $subscribe->member->memberName);
	    	$response["html"][] = $View->render('/Element/login', false);
    	}
    	$View = new AjaxView();
    	$SubscribesDBI = TableRegistry::get('Subscribes');
    	$subscribe = $SubscribesDBI->newEntity();
    	$subscribe->memberId = $this->loginTable->memberId;
    	$subscribe->roomId= "9999";
    	$View->set("subscribe", $subscribe);
    	$View->set("memberName", $this->loginTable->memberName);
    	$response["html"][] = $View->render('/Element/login', false);
    	$this->sendByZMQ($response);
		echo json_encode($msg);
    }

    public function unsubscribe()
    {
    	//AJAX精査
    	$this->autoRender = FALSE;
    	if(!$this->request->is('ajax')) {
    		return;
    	}

    	//購読ルーム削除
    	//チャットサーバに送信
    	$SubscribesDBI = TableRegistry::get('Subscribes');
    	$subscribe = $SubscribesDBI->get([$this->loginTable->memberId, $this->request->data["roomId"]]);
    	$SubscribesDBI->delete($subscribe);
    	$msg = ["unsubscribeId" => true, "roomId" => $this->request->data["roomId"], "memberId" =>$this->loginTable->memberId];
    	$this->sendByZMQ($msg);
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



    public function loadChat()
    {
    	//AJAX精査
    	$this->autoRender = FALSE;
    	if(!$this->request->is('ajax')) {
    		return;
    	}

    	//チャット取得
    	$chats = $this->getChats($this->request->data["roomId"], $this->request->data["chatNumber"]);
    	$response["status"] = "success";
    	$response["selecter"] = "#chats".$this->request->data["roomId"];
    	$response["html"] = "";
    	foreach ($chats as $chat) {
    		$View = new AjaxView();
    		$View->set("chat", $chat);
    		$response["html"] = $response["html"].$View->render('/Element/chat', false);
    	}
    	$this->response->body(json_encode($response));
    }

    private function getChats($roomId, $chatNumber = null)
    {
    	//チャット取得
    	$ChatsDBI = TableRegistry::get('Chats');
    	$query = $ChatsDBI->find();
    	if ($chatNumber == null) {
	    	$ret = $query->select(['max_id' => $query->func()->max('chatNumber')])->where(["roomId =" => $roomId])->first();
	    	$offset = $ret->max_id;
    	} else {
    		$offset = $chatNumber - 1;
    	}
    	$chats = $ChatsDBI->find()->where(["roomId =" => $roomId])->andWhere(["chatNumber >" => $offset - 10])->andWhere(["chatNumber <=" => $offset])->contain(['Members'])->order(['Chats.chatNumber' => 'DESC']);
    	return $chats;
    }

    public function getChat($roomId, $chatNumber)
    {
    	//AJAX精査
    	$this->autoRender = FALSE;
    	if(!$this->request->is('ajax')) {
    		return;
    	}

    	//チャット取得
    	$ChatsDBI = TableRegistry::get('Chats');
    	$chat = $ChatsDBI->get([$roomId, $chatNumber], ["contain" => ['Members', "Nocares"]]);

    	$View = new AjaxView();
    	$View->set("chat", $chat);
    	$response = $View->render('/Element/chat', false);
    	$this->response->body($response);
    }


}
