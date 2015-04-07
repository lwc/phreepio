<?php

namespace Phreepio\Translator;

use React\Promise\PromiseInterface;

class Translator
{
    private $adapter;
    private $config;

    public function __construct(Adapter $adapter, $config)
    {
        $this->adapter = $adapter;
        $this->config = $config;
    }

    /**
     * @return UploadIterator|PromiseInterface[]
     */
    public function getUploadIterator()
    {
        return new UploadIterator($this->adapter, $this->config->sources);
    }

    /**
     * @return DownloadIterator|PromiseInterface[]
     */
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

    /**
     * @return StatusIterator|PromiseInterface[]
     */
    public function getStatusIterator()
    {
        $remotePaths = array();
        foreach ($this->config->sources as $source) {
            $remotePaths[] = $source->target;
        }

        $locales = $this->config->target->locales;
        return new StatusIterator($this->adapter, $remotePaths, $locales);        
    }

    public function flush()
    {
        $this->adapter->flush();
    }
}