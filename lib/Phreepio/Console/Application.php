<?php

namespace Phreepio\Console;

use Symfony\Component\Console\Application as BaseApplication;

use Phreepio\Command\StatusCommand;
use Phreepio\Command\UploadCommand;
use Phreepio\Command\DownloadCommand;

class Application extends BaseApplication
{
    private static $logo = '<comment>
            .-.
           |o,o|
        ,| _\=/_
        ||/_/_\_\
        |_/|(_)|\\\\
           \._.///
           |\_/|"`
           |_|_|
           |-|-|
           |_|_|
          /_/ \_\

</comment>';


    public function __construct()
    {
        parent::__construct('phreepio', '0.0.1');
        $this->add(new StatusCommand());
        $this->add(new UploadCommand());
        $this->add(new DownloadCommand());
    }

    public function getHelp()
    {
        return self::$logo . parent::getHelp();
    }
}
