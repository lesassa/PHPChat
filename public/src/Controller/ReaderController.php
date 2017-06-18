<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;
use RuntimeException;
use App\Form\Reader\IconForm;
use Cake\View\View;

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

	public function edit($memberId)
	{
		$this->viewBuilder()->layout('defaultLogin');
		$MembersDBI = TableRegistry::get('Members');
		$member = $MembersDBI->get($memberId, ["contain" => ["Login"]]);
		if($this->request->is(['post'])) {
			$member = $MembersDBI->patchEntity($member, $this->request->data);

			if ($MembersDBI->save($member)) {

				return $this->redirect(['controller'=>'Reader', 'action' => 'login']);
			}
		}
		$this->set('member', $member);
	}

	public function uploadIcon()
	{
		//AJAX精査
		$this->autoRender = FALSE;
		$this->response->type('json');
		if(!$this->request->is('ajax')) {
			return;
		}

		//精査
		$iconForm = new IconForm();
		if (!$iconForm->execute($this->request->data)) {
			$response["status"] = "error";
			foreach($iconForm->errors() as $key => $errorMessages) {
				$View = new View(); // Viewを生成。Controllerの状態が引き継がれる
				$View->set("errorMessages", $errorMessages); // $View->viewPath = 'Viewのフォルダ名';
				$response["html"]["[name=".$key."]"] = $View->render('/Element/error', false);
			}
			echo json_encode($response);
			return;
		}

		//ログ出力
		$this->Log->outputLog($this->request->data);

		try {

			$dir = realpath(WWW_ROOT . "/icon");
			$file = $this->request->data['icon'];

			// 未定義、複数ファイル、破損攻撃のいずれかの場合は無効処理
			if (!isset($file['error']) || is_array($file['error'])){
				throw new RuntimeException('Invalid parameters.');
			}

			// エラーのチェック
			switch ($file['error']) {
				case 0:
					break;
				case UPLOAD_ERR_OK:
					break;
				case UPLOAD_ERR_NO_FILE:
					throw new RuntimeException('No file sent.');
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					throw new RuntimeException('Exceeded filesize limit.');
				default:
					throw new RuntimeException('Unknown errors.');
			}

			// ファイルタイプのチェックし、拡張子を取得
			if (false === $ext = array_search($file["type"], ['jpg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif',], true)){
						throw new RuntimeException('Invalid file format.');
			}

			$imgName = date("YmdHis");
			if (!@move_uploaded_file($file["tmp_name"], $dir . "/" . $imgName.".".$ext)){
				throw new RuntimeException('Failed to move uploaded file.');
			}

			//画像名登録
			$MembersDBI = TableRegistry::get('Members');
			$member = $MembersDBI->get($this->loginTable->memberId);
			$member->icon = $imgName.".".$ext;
			$MembersDBI->save($member);

			//ログ出力
			$this->Log->outputLog("Member = [".print_r($member, true)."]");


			$View = new View(); // Viewを生成。Controllerの状態が引き継がれる
			$View->set("member", $member); // $View->viewPath = 'Viewのフォルダ名';
			$response["status"] = "success";
			$response["html"][".icon"] = $View->render('/Element/icon', false);
			echo json_encode($response);

		} catch (RuntimeException $e){

			//ログ出力
			$this->Log->outputLog("RuntimeException = [".print_r($e->getMessage(), true)."]");
		}
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
