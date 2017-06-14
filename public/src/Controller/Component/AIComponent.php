<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use PHPExcel_IOFactory;

class AIComponent  extends Component
{


	//ファイルパスは/で記載すること
// 	const FILE = "C:/Users/nowko/Dropbox/WorkSpace/CakePHP/doc/test.xlsx";
// 	const FILE = "//192.168.90.8/filesv/00_JENIUS保守開発/20.保守開発2/2-1G/10.オーソリ/xxx.個人別/岩浪/test.xlsx";
	const FILE = "//P2fsvt01/2170/2G/個人用/2Gプロパー/TIS303761岩浪/test.xlsx";
// 	const FILE = "C:/php_WS/CatKwaidan/doc/test.xlsx";

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