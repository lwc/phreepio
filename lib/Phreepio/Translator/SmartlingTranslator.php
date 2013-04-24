<?php

namespace Phreepio\Translator;

use Smartling\Client;

class SmartlingTranslator implements Adapter
{
    private $client;
    private $autoApprove;
    private $placeholderFormat;

    public function __construct($config)
    {
        $useSandbox = isset($config->useSandbox) ? $config->useSandbox : true;
        $this->placeholderFormat = isset($config->placeholderFormat) ? $config->placeholderFormat : null;

        $this->autoApprove = isset($config->autoApprove) ? $config->autoApprove : false ;
        $this->client = new Client($config->apiKey, $config->projectId, $useSandbox);
    }

    public function download($remotePath, $localPath, $locale)
    {
        $this->client->get($remotePath, $localPath, $locale);
    }

    public function upload($localPath, $remotePath, $type)
    {
        $options = array();
        if ($this->placeholderFormat) {
            $options['placeholder_format_custom'] = $this->placeholderFormat;
        }
        return (object)$this->client->upload($localPath, $remotePath, $type, $this->autoApprove, $options);
    }

    public function status($remotePath, $locale)
    {
        return (object)$this->client->status($remotePath, $locale);
    }
}