<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
//ログ使用
use Cake\Log\Log;
use Cake\Core\Configure;

/**
 * Log component
 */
class LogComponent extends Component
{

	private $callBy;

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    public function initialize(array $config)
    {
    	parent::initialize($config);
//     	if (isset ($config[0])) {
// 	    	$this->callBy = $config[0];
//     	}
    }

	/**
	 * ログ出力
	 * @param Object $message ログメッセージ
	 */
	public function outputLog($message) {

		if (Configure::read('debug')) {

			$message = rtrim(print_r( $message, true));
			$dbg = debug_backtrace($limit = 2);
			$message = $dbg[1]["class"]."(".$dbg[1]["function"].")    ".$message;

			Log::info($message, 'your_scope');
		}
	}
}
