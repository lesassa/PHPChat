<?php
namespace MyApp;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use Cake\ORM\TableRegistry;

class Pusher implements WampServerInterface {

	/**
	 * A lookup of all the topics clients have subscribed to
	 */
	protected $subscribedTopics = array();

	protected $clients;

	public function __construct() {
		$this->clients = new \SplObjectStorage;

	}

	// トピック一覧
	private $topics = array();

	public function onSubscribe(ConnectionInterface $conn, $topic) {
		echo '********** '.__FUNCTION__.' begin **********'.PHP_EOL;
		echo '$topic : '.$topic.PHP_EOL;
		echo '********** '.__FUNCTION__.' end **********'.PHP_EOL;

		// 不正なトピック
// 		if( ! in_array($topic, array('topic_1', 'topic_2', 'topic_3')))
// 		{
// 			return;
// 		}

		// トピック一覧にトピックを追加
		if (!array_key_exists($topic->getId(), $this->topics))
		{
			$this->topics[$topic->getId()] = $topic;
		}

		//入室通知


	}
	public function onUnSubscribe(ConnectionInterface $conn, $topic) {
		echo '********** '.__FUNCTION__.' begin **********'.PHP_EOL;
		echo '$topic : '.$topic.PHP_EOL;
		echo '********** '.__FUNCTION__.' end **********'.PHP_EOL;

		// 不正なトピック
		if( ! in_array($topic, array('topic_1', 'topic_2', 'topic_3')))
		{
			return;
		}

		// トピックからコネクションを削除
		$topic->remove($conn);

		// トピックの購読者が存在しない場合、トピック一覧からトピックを削除
		if ($topic->count() == 0)
		{
			unset($this->topics[$topic->getId()]);
		}
	}
	public function onOpen(ConnectionInterface $conn) {

		// Store the new connection to send messages to later
		$this->clients->attach($conn);

		echo "New connection! ({$conn->resourceId})\n";

	}
	public function onClose(ConnectionInterface $conn) {
		// 全てのトピックを購読解除
		foreach ($this->topics as $topic)
		{
			$this->onUnSubscribe($conn, $topic);
		}

		$exec = dirname(dirname(dirname(__FILE__))).'\bin\cake chat logout '.$conn->resourceId;
		$return = exec($exec);
		$msg["logoutId"] = true;
		$msg["memberId"] = $return;
		$msg= json_encode($msg);
		$topic = $this->topics["9999"];
		$topic->broadcast($msg);
		echo "disconnection! ({$conn->resourceId})\n".date("Y-M-d H:i");
		echo $return;

	}
	public function onCall(ConnectionInterface $conn, $id, $fn, array $params) {
		if ($fn == 'ping') {
			return;
		}

		echo '********** '.__FUNCTION__.' begin **********'.date("Y-M-d H:i").PHP_EOL;
		echo '$id : '.$id.PHP_EOL;
		echo '$fn : '.$fn.PHP_EOL;
		echo '$params : '.print_r($params, true).PHP_EOL;
		echo '********** '.__FUNCTION__.' end *********'.PHP_EOL;
		switch ($fn) {
			//ピン
			case 'ping':
				return;
				break;
				//ログイン
			case 'login':
				$exec = dirname(dirname(dirname(__FILE__))).'\bin\cake chat login '.$conn->resourceId." ".$params[0];
				$return = exec($exec);
				return $conn->callResult($id, [$return]);
				break;

			// 購読しているトピック一覧を取得
			case 'get_subscribing_topics':
				$subscribing_topics = array();

// 				Log::debug('********** Topics begin **********');

				foreach ($this->topics as $topic)
				{
// 					Log::debug('$topic : '.$topic);
// 					Log::debug('$topic->count() : '.$topic->count());

					$topic->has($conn) and $subscribing_topics[] = $topic;
				}

// 				Log::debug('********** Topics end **********');

				return $conn->callResult($id, $subscribing_topics);
				break;

				// エラー処理
			default:
				$errorUri = 'errorUri';
				$desc = 'desc';
				$details = 'details';

				/**
				 * \Ratchet\Wamp\WampConnection
				 *
				 * callError($id, $errorUri, $desc = '', $details = null)
				 */
				return $conn->callError($id, $errorUri, $desc, $details);
			break;
		}
	}

	public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
// 		Log::debug('********** '.__FUNCTION__.' begin **********');
// 		Log::debug('$topic : '.$topic);
// 		Log::debug('$event : '.$event);
// 		Log::debug('$exclude : '.print_r($exclude, true));
// 		Log::debug('$eligible : '.print_r($eligible, true));
// 		Log::debug('********** '.__FUNCTION__.' end **********');
		// 不正なトピック
		if( ! in_array($topic, array('topic_1', 'topic_2', 'topic_3')))
		{
			return;
		}

		$json = json_decode($event);

		// トピックに対する購読者が存在する場合、配信
		if (array_key_exists($topic->getId(), $this->topics))
		{
			echo " ({$json->input})\n";
			$topic->broadcast($json->input);
		}
	}

	public function onError(ConnectionInterface $conn, \Exception $e) {
		print_r($e->getMessage());
	}

	/**
	 * ZeroMQ経由でコールされる
	 *
	 * @param  string $msg
	 */
	public function zmqCallback($msg) {
		echo '********** '.__FUNCTION__.' begin **********'.PHP_EOL;
		echo '$json_string : '.$msg.PHP_EOL;
		echo '********** '.__FUNCTION__.' end **********'.PHP_EOL;

		$json = json_decode($msg);

// 		if( ! isset($json->roomId))
// 		{
// 			return;
// 		}

		//ルーム作成またはログイン
		if(isset($json->roomCreate) || isset($json->loginId) || isset($json->unsubscribeId)){
			$topic = $this->topics["9999"];
			$topic->broadcast($msg);
			return;
		}

		foreach ($this->topics as $topic)
		{
			if ($json->roomId == $topic->getId())
			{
				// 配信
				echo "({$json->chatText})\n";
				$topic->broadcast($msg);
				break;
			}
		}


	}
}