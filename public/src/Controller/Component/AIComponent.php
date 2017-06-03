<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use PHPExcel_IOFactory;

class AIComponent  extends Component
{



	const FILE = "C:/Users/nowko/Dropbox/WorkSpace/CakePHP/doc/test.xlsx";


	public $components = ['Log', 'Docomo'];

	/**
	 * DocomoAPIとの対話<br/>
	 * https://dev.smt.docomo.ne.jp/?p=docs.api.page&api_docs_id=5<br/>
	 *
	 * @param string $text 送信メッセージ
	 * @return string 返信メッセージ
	 */
	function talkAI($text) {

		if ($text == "excel") {
			$obj = PHPExcel_IOFactory::createReader('Excel2007');
			$book = $obj->load(self::FILE);

			//シートを設定する
			$book->setActiveSheetIndex(0);//一番最初のシートを選択
			$sheet = $book->getActiveSheet();//選択シートにアクセスを開始
			$cell = $sheet->getCell('A1');
			$reply = $cell->getValue();
			return $reply;
		}

		//DOCOMOAI
		$reply = $this->Docomo->talk($text);

		return $reply;
	}

}
?>