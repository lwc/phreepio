<?php

namespace Phreepio\Translator;

class Translator
{
    private $adapter;
    private $config;

    public function __construct(Adapter $adapter, $config)
    {
        $this->adapter = $adapter;
        $this->config = $config;
    }

    public function getUploadIterator()
    {
        return new UploadIterator($this->adapter, $this->config->sources);
    }

    public function getDownloadIterator()
    {
        $remotePaths = array();
        foreach ($this->config->sources as $source) {
            $remotePaths[] = $source->target;
        }

        $locales = $this->config->target->locales;
        $destinationPattern = $this->config->target->destination;

        return new DownloadIterator($this->adapter, $remotePaths, $locales, $destinationPattern);
    }

    public function getStatusIterator()
    {
        $remotePaths = array();
        foreach ($this->config->sources as $source) {
            $remotePaths[] = $source->target;
        }

        $locales = $this->config->target->locales;
        return new StatusIterator($this->adapter, $remotePaths, $locales);        
    }
}