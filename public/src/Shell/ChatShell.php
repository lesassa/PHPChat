<?php
namespace App\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Log\Log;
use Psy\Shell as PsyShell;

class ChatShell extends Shell
{

    public function main()
    {
    	echo  "success";
    }

    public function heyThere($name)
    {
    	echo 'Hey there ' . $name;
    }

}
