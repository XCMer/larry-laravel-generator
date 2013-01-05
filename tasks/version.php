<?php

class Larry_Version_Task
{
    const version = "1.1";
    const released = "6th Jan, 2013";

    private $changes = array(
        '1.0' => array(
            'Initial release supporting models, migrations, relations, and validation'
        ),
        '1.1' => array(
            'Parser now has better error handling. It will try giving helpful error messages instead of throwing generic PHP errors',
            'Command larry::version added to get the current version of Larry'
        ),
    );

    public function run()
    {
        echo "\nYou're running Larry " . self::version . ", released on " . self::released . ".\n\n";
        echo "=====Changes=====\n\n";

        foreach (array_reverse($this->changes) as $version => $notes)
        {
            echo "#=[Version {$version}]=#\n\n";
            foreach ($notes as $note)
            {
                echo "- {$note}\n";
            }
            echo "\n";
        }
    }
}