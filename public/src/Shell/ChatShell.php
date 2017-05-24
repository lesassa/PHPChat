<?php
namespace App\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Log\Log;
use Psy\Shell as PsyShell;
use Cake\ORM\TableRegistry;

class ChatShell extends Shell
{

    public function main()
    {
    	echo  "success";
    }

    public function logout($resourceId)
    {
    	$ParticipantsDBI = TableRegistry::get('Participants');
    	$participants = $ParticipantsDBI->find()->where(['resourceId =' => $resourceId]);
    	foreach ($participants as $participant) {
    		$ParticipantsDBI->delete($participant);
    	}


    	echo "success";
    }

    public function judgeAI()
    {



    }

}
