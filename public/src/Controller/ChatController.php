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

    /**
     * Displays a view
     *
     * @param string ...$path Path segments.
     * @return void|\Cake\Network\Response
     * @throws \Cake\Network\Exception\ForbiddenException When a directory traversal attempt.
     * @throws \Cake\Network\Exception\NotFoundException When the view file could not
     *   be found or \Cake\View\Exception\MissingTemplateException in debug mode.
     */
    public function index()
    {
    	$RoomsDBI = TableRegistry::get('Rooms');
    	$rooms = $RoomsDBI->find('all');

    	$this->set('rooms', $rooms);
    }

    public function chat($roomId)
    {

    }

    public function addChat()
    {
    	$this->autoRender = FALSE;
    	if($this->request->is('ajax')) {
	    	$ChatsDBI = TableRegistry::get('Chats');
	    	$chat = $ChatsDBI->newEntity();
	    	$chat->roomId = 1;
	    	$query = $ChatsDBI->find();
	    	$ret = $query->select(['max_id' => $query->func()->max('chatNumber')])->where(["roomId =" => $chat->roomId])->first();
	    	$chat->chatNumber = $ret->max_id + 1;
	    	$chat->memberId = 1;
	    	$chat->chatText = $this->request->data["chatText"];
	    	$ChatsDBI->save($chat);

	    	$msg["roomId"] = $chat->roomId;
	    	$msg["chatNumber"] = $chat->chatNumber;
	    	$msg["chatText"] = $chat->chatText;
	    	$msg["memberId"] = $chat->memberId;
	    	$msg["memberName"] = "匿名さん";
	    	echo json_encode($msg);
    	}
    }
}
