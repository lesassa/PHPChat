<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use PHPExcel_IOFactory;

class AIComponent  extends Component
{


	//「今日の勤怠」用
	const FILE = "//P2fsvt01/2170/2G/個人用/2Gプロパー/TIS303761岩浪/勤怠.xlsx";//ファイルパスは/で記載すること
	const ROW_OFFSET = "4"; //日付の行の手前行
	const NAME_ROW = "3"; //名前の行
	const NAME_COLUMN = "3"; //名前の開始列（列は0始まり）
	const NAME_INDENT = "3"; //名前の列の間隔

	public $components = ['Log', 'Docomo'];

	/**
	 * DocomoAPIとの対話<br/>
	 * https://dev.smt.docomo.ne.jp/?p=docs.api.page&api_docs_id=5<br/>
	 *
	 * @param string $text 送信メッセージ
	 * @return string 返信メッセージ
	 */
	function talkAI($text) {

		if ($text == "今日の勤怠") {
			$obj = PHPExcel_IOFactory::createReader('Excel2007');
			$book = $obj->load(self::FILE);


			$month = date ("n");
			$day = date ("j");


			//シートを設定する
			$sheetName = $month."月";
			$book->setActiveSheetIndexByName($sheetName);//一番最初のシートを選択
			$sheet = $book->getActiveSheet();//選択シートにアクセスを開始
			$todayRow = self::ROW_OFFSET + $day;
			$today = $sheet->getCellByColumnAndRow(1, $todayRow)->getFormattedValue();
			$reply = $today."の勤怠は";


			$i = 0;
			$memberColumn = self::NAME_COLUMN  + (self::NAME_INDENT * $i);
			$memberName = $sheet->getCellByColumnAndRow($memberColumn, self::NAME_ROW)->getFormattedValue();
			while ($memberName != "") {
				$reply .= PHP_EOL;
				$startTime = $sheet->getCellByColumnAndRow($memberColumn, $todayRow)->getFormattedValue();

				if ($startTime == "") {
					$service = $sheet->getCellByColumnAndRow($memberColumn + 2, $todayRow)->getFormattedValue();
				} else {

					$endTime = $sheet->getCellByColumnAndRow($memberColumn + 1, $todayRow)->getFormattedValue();
					$service = $startTime."-".$endTime;
				}
				$reply .= $memberName."さんは".$service."だって";

				$i++;
				$memberColumn = self::NAME_COLUMN  + (self::NAME_INDENT * $i);
				$memberName = $sheet->getCellByColumnAndRow($memberColumn, self::NAME_ROW)->getFormattedValue();
			}


			//ログ出力
			$this->Log->outputLog($reply);

			return $reply;
		}

		//DOCOMOAI
		$reply = $this->Docomo->talk($text);

		return $reply;
	}

}
?>