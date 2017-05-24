<?php
namespace App\Controller\Component;

use Cake\Controller\Component;

class AIComponent  extends Component
{

	const API_KEY = '57594a4f6537617377586247376c6561474e6774436a6c4839794d6e472f3364366b67726d6a6e54566f2f';


	public $components = ['Log', 'Docomo'];

	/**
	 * DocomoAPIとの対話<br/>
	 * https://dev.smt.docomo.ne.jp/?p=docs.api.page&api_docs_id=5<br/>
	 *
	 * @param string $text 送信メッセージ
	 * @return string 返信メッセージ
	 */
	function talkAI($text) {



		//DOCOMOAI
		$reply = $this->Docomo->talk($text);

		return $reply;
	}

}
?>