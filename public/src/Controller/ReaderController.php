<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Core\Configure;


/**
 * Reader Controller
 *
 * @property \App\Model\Table\ReaderTable $Reader
 */
class ReaderController extends AppController
{



	public function initialize()
	{
		parent::initialize();

		$this->Auth->allow(['login', "error", "createMember", "bot", "addbot"]);
	}

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
    }

	public function login()
    {
    	$this->viewBuilder()->layout('defaultLogin');
		//初期化
    	$errorMessage = "";

    	if ($this->request->is('post')) {
	        $user = $this->Auth->identify(); // Postされたユーザー名とパスワードをもとにデータベースを検索。ユーザー名とパスワードに該当するユーザーがreturnされる

	        if ($user) { // 該当するユーザーがいればログイン処理
	        	$this->Auth->setUser($user);

	        	$MembersDBI = TableRegistry::get('Members');
	        	$login = $MembersDBI->get($user["memberId"]);
	        	$this->Session->write("loginTable", $login);


	            return $this->redirect($this->Auth->redirectUrl());
	        } else { // 該当するユーザーがいなければエラー
	            $errorMessage = 'メールアドレスかパスワードが間違っています';

	        }
    	}


//     	$password = (new DefaultPasswordHasher)->hash("2011kagecat");
//     	print $password;
    	$this->set('errorMessage', $errorMessage);
    }

	/**
	 * ログアウト
	 * @return bool
	 */
	public function logout()
	{
		$this->Session->delete("loginTable");
	    $this->request->session()->destroy(); // セッションの破棄
	    return $this->redirect($this->Auth->logout()); // ログアウト処理
	}

	public function error($errorId)
	{
		$errorMessage = $this->Session->consume($errorId);
		$this->set('errorMessage', $errorMessage);
	}

	public function createMember()
	{
		$member = null;
		$this->viewBuilder()->layout('defaultLogin');
		if($this->request->is(['post'])) {
			$MembersDBI = TableRegistry::get('Members');
			$member = $MembersDBI->newEntity($this->request->data,['associated' => ['Login']]);
			if ($MembersDBI->save($member)) {
				return $this->redirect(['controller'=>'Reader', 'action' => 'login']);
			}
		}
		$this->set('member', $member);
	}

	public function bot()
	{
		$this->viewBuilder()->layout('defaultLogin');
	}

	public function addbot()
	{
		$this->autoRender = FALSE;
		// post.php ???
		// This all was here before  ;)
		$entryData = array(
				'topic' => "topic_2"
				, 'msg'    => "テスト"
		);

		// This is our new stuff
		$context = new \ZMQContext();
		$socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
		$socket->connect("tcp://localhost:5555");

		$socket->send(json_encode($entryData));
	}
}
