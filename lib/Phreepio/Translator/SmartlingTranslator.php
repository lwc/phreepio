<?php

namespace Phreepio\Translator;

use Smartling\Client;

class SmartlingTranslator implements Adapter
{
    private $client;
    private $autoApprove;

    public function __construct($config)
    {
        $this->autoApprove = isset($config->autoApprove) ? $config->autoApprove : false ;
        $this->client = new Client($config->apiKey, $config->projectId, $config->useSandbox);
    }

    public function download($remotePath, $localPath, $locale)
    {
        $this->client->get($remotePath, $localPath, $locale);
    }

    public function upload($localPath, $remotePath, $type)
    {
        return (object)$this->client->upload($localPath, $remotePath, $type, $this->autoApprove);
    }

    public function status($remotePath, $locale)
    {
        return (object)$this->client->status($remotePath, $locale);
    }
}