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

	    	$ChatsDBI = TableRegistry::get('Chats');
	    	$query = $ChatsDBI->find();
	    	$ret = $query->select(['max_id' => $query->func()->max('chatNumber')])->where(["roomId =" => $room->roomId])->first();
	    	$chats = $ChatsDBI->find()->where(["roomId =" => $room->roomId])->andWhere(["chatNumber >" => $ret->max_id - 10])->contain(['Members'])->order(['Chats.chatNumber' => 'DESC']);;
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

		    $RoomsDBI = TableRegistry::get('Rooms');
		    $room = $RoomsDBI->get($chat->roomId);

		    $MembersDBI = TableRegistry::get('Members');
		    $member = $MembersDBI->get($chat->memberId);

		    //チャットサーバに送信
		    $msg["roomId"] = $chat->roomId;
		    $msg["roomName"] = $room->roomName;
		    $msg["chatNumber"] = $chat->chatNumber;
		    $msg["chatText"] = $chat->chatText;
		    $msg["replyId"] = $chat->replyId;
		    $msg["memberId"] = $chat->memberId;
		    $msg["memberName"] = $member->memberName;
		    $this->sendByZMQ($msg);

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
		    	$replyMsg["roomId"] = $replyChat->roomId;
		    	$replyMsg["roomName"] = $room->roomName;
		    	$replyMsg["chatNumber"] = $replyChat->chatNumber;
		    	$replyMsg["chatText"] = $replyChat->chatText;
		    	$replyMsg["replyId"] = $replyChat->replyId;
		    	$replyMsg["memberId"] = $replyChat->memberId;
		    	$replyMsg["memberName"] = $originalChat->member->memberName;
		    	$this->sendByZMQ($replyMsg);
		    }
	    }

	    //エラーメッセージ返信
	    echo json_encode(["errors" =>$chat->errors()]);
    }



//     public function enter()
//     {
//     	//AJAX精査
//     	$this->autoRender = FALSE;
//     	if(!$this->request->is('ajax')) {
//     		return;
//     	}

//     	$SubscribesDBI = TableRegistry::get('Subscribes');
//     	$subscribes = $SubscribesDBI->find()->where(["memberId =" => $this->loginTable->memberId]);

//     	$MembersDBI = TableRegistry::get('Members');
//     	$member = $MembersDBI->get($participant->memberId);

//     	$msg = ["login" => true,];
//     	foreach ($subscribes as $subscribe) {
// 	    	$msg[] = ["roomId" => $subscribe->roomId, "memberId" => $subscribe->memberId, "memberName" => $member->memberName];
//     	}

//     	//チャットサーバに送信
//     	$this->sendByZMQ($msg);
//     	echo 0;
//     }

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
    	$members = array();
    	foreach ($participants as $participant) {

    		//ログ出力
    		$this->Log->outputLog("participant = [".print_r($participant, true)."]");

    		//自身のログイン情報を排除
    		if ($participant->memberId == $this->loginTable->memberId) {
				continue;
    		}

    		//ログイン情報に紐づく購読ルームを取得
    		foreach ($participant->subscribes as $subscribe) {
	    		$member = array();
	    		$member["memberId"] = $subscribe->memberId;
	    		$member["roomId"] = $subscribe->roomId;
	    		$member["memberName"] = $participant->member->memberName;
	    		$members[] = $member;
    		}

    		//ログイン情報と表紙を紐づけ
    		$member = array();
    		$member["memberId"] = $participant->memberId;
    		$member["roomId"] = "9999";
    		$member["memberName"] = $participant->member->memberName;
    		$members[] = $member;
		}

		$this->response->body(json_encode($members));
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
    		$msg["roomId"] = $room->roomId;
    		$msg["roomName"] = $room->roomName;
    		$msg["roomDescription"] = $room->roomDescription;
    		$msg["roomCreate"] = true;
    		$this->sendByZMQ($msg);
    	}

    	echo json_encode(["errors" =>$room->errors()]);
    }

    /**
     * AJAX購読ルームのい保存
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
    	$msg = ["loginId" => true,];
    	$msg[] = ["roomId" => $subscribe->roomId, "memberId" => $subscribe->memberId, "memberName" => $member->memberName];
    	$this->sendByZMQ($msg);

    	//既出のチャットの取得
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
    	$participants = ["loginId" => true,];
    	$msg = array();
		foreach($subscribes as $subscribe) {
			$msg[] = $subscribe->roomId;
			$participants[] = ["roomId" => $subscribe->roomId, "memberId" => $subscribe->memberId, "memberName" => $subscribe->member->memberName];
		}
		$participants[] = ["roomId" => "9999", "memberId" => $this->loginTable->memberId, "memberName" => $subscribe->member->memberName];
		$this->sendByZMQ($participants);

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
    	$this->sendByZMQ($participants);
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
}
