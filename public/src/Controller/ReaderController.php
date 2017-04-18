<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\TableRegistry;


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
	}

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
    }

}
