<?php

namespace Phreepio;

use Phreepio\Translator\Translator;

class Configuration
{
    private $config = array();

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function __get($key)
    {
        return $this->config->$key;
    }

    public function __isset($key)
    {
        return isset($this->config->$key);
    }

    public function getTranslator()
    {
        $className = $this->config->translator->service;
        if (is_subclass_of($className, 'Phreepio\Translator\Adapter')) {
            $config = isset($this->config->translator->config) ? $this->config->translator->config : array();
            return new Translator(new $className($config), $this); 
        }
        throw new Exception('Provided service does not implement Phreepio\Translator\Adapter');
    }

    private function validate()
    {

    }
}