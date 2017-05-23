<?php
namespace App\Controller\Component;

use Cake\Controller\Component;

class DocomoComponent  extends Component
{

	const API_KEY = '57594a4f6537617377586247376c6561474e6774436a6c4839794d6e472f3364366b67726d6a6e54566f2f';


	public $components = ['Log'];

	/**
	 * DocomoAPIとの対話<br/>
	 * https://dev.smt.docomo.ne.jp/?p=docs.api.page&api_docs_id=5<br/>
	 *
	 * @param string $text 送信メッセージ
	 * @return string 返信メッセージ
	 */
	function talk($text) {

		//ログ出力
		$this->Log->outputLog("TALK START");

		$context_file = dirname(__FILE__).'/.docomoapi.context';
		$api_key = self::API_KEY;
		$api_url = sprintf('https://api.apigw.smt.docomo.ne.jp/dialogue/v1/dialogue?APIKEY=%s', $api_key);
		$req_body = array('utt' => $text);
		if ( file_exists($context_file) ) {
			$req_body['context'] = file_get_contents($context_file);
		}
		$headers = array(
				'Content-Type: application/json; charset=UTF-8',
		);
		$options = array(
				'http'=>array(
						'method'  => 'POST',
						'header'  => implode( "\r\n", $headers ),
						'content' => json_encode($req_body),
				)
		);
		$stream = stream_context_create( $options );
		$res = json_decode(file_get_contents($api_url, false, $stream));
		if (isset($res->context)) {
			file_put_contents($context_file, $res->context);
		}

		//ログ出力
		$this->Log->outputLog($res);

		return isset($res->utt) ? $res->utt : '';
	}

}
?>