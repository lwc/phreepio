<?php

namespace Phreepio\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Phreepio\Exception;
use Phreepio\Configuration;

class PhreepioCommand extends Command
{
    private $phreepioConfig;
    private $translator;

    protected function getPhreepioConfig()
    {
        if (is_null($this->phreepioConfig)) {
            $config = json_decode(file_get_contents(getcwd().'/phreepio.json'));
            if (is_null($config)) {
                throw new Exception("Cannot load phreepio.json in current directory");
            }
            $this->phreepioConfig = new Configuration($config);
        }
        return $this->phreepioConfig;
    }

    protected function getTranslator()
    {
        if (!$this->translator) {
            $this->translator = $this->getPhreepioConfig()->getTranslator();
        }

        return $this->translator;
    }
}