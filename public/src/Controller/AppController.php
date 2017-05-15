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

use Cake\Controller\Controller;
use Cake\Event\Event;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

	public $loginTable;
	public $Log;

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');

        /*
         * Enable the following components for recommended CakePHP security settings.
         * see http://book.cakephp.org/3.0/en/controllers/components/security.html
         */
        //$this->loadComponent('Security');
        //$this->loadComponent('Csrf');

        $this->Log = $this->loadComponent('Log',[get_class($this)]);
        $this->Session = $this->request->session();
        $this->loadComponent('Auth', [ // Authコンポーネントの読み込み
        		'authenticate' => [
        				'Form' => [ // 認証の種類を指定。Form,Basic,Digestが使える。デフォルトはForm
        						'userModel' => 'Login',
        						'fields' => [ // ユーザー名とパスワードに使うカラムの指定。省略した場合はusernameとpasswordになる
        								'username' => 'loginId', // ユーザー名のカラムを指定
        								'password' => 'password' //パスワードに使うカラムを指定
        						],

        				]
        		],
        		'loginAction' => [
        				'controller' => 'Reader',
        				'action' => 'login'
        		],
        		'loginRedirect' => [ // ログイン後に遷移するアクションを指定
        				'controller' => 'Chat',
        				'action' => 'index',
        		],
        		'logoutRedirect' => [ // ログアウト後に遷移するアクションを指定
        				'controller' => 'Reader',
        				'action' => 'login',
        		],
        		'authError' => 'ログインできませんでした。ログインしてください。', // ログインに失敗したときのFlashメッセージを指定(省略可)
        ]);

        $this->loginTable = new \StdClass();
        if ($this->Session->check("loginTable")){
        	$this->loginTable = $this->Session->read("loginTable");
        } else {
        	$this->loginTable->memberName = "ゲスト";
        	$this->loginTable->memberId = GUEST_ID;
        }
        $this->set('loginTable', $this->loginTable);

//         $loginTable->memberName = "";
//         $this->set('loginTable', $loginTable);
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return \Cake\Network\Response|null|void
     */
    public function beforeRender(Event $event)
    {
        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->type(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }
    }
}
