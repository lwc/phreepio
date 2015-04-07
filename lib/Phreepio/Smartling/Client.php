<?php

namespace Phreepio\Smartling;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Pool;
use GuzzleHttp\Post\PostBody;
use GuzzleHttp\Post\PostFile;
use React\Promise\Deferred;

class Client
{
    const BASEURL_PROD = 'https://api.smartling.com/v1/';
    const BASEURL_SANDBOX = 'https://sandbox-api.smartling.com/v1/';

    const TYPE_ANDROID = 'android';
    const TYPE_IOS = 'ios';
    const TYPE_GETTEXT = 'gettext';
    const TYPE_HTML = 'html';
    const TYPE_JAVA = 'javaProperties';
    const TYPE_XLIFF = 'xliff';
    const TYPE_XML = 'xml';
    const TYPE_JSON = 'json';
    const TYPE_YAML = 'yaml';

    private $client;
    private $apiKey;
    private $projectId;
    private $useSandbox;
    private $requests = [];

    public function __construct($apiKey, $projectId, $useSandbox=false)
    {
        $this->client = new GuzzleClient([
            'base_url' => $useSandbox ? self::BASEURL_SANDBOX : self::BASEURL_PROD
        ]);

        $this->apiKey = $apiKey;
        $this->projectId = $projectId;
        $this->useSandbox = $useSandbox;
    }

    public function upload($source, $target, $type, $approved=true, $options=null, $callbackUrl=null)
    {

        $body = new PostBody();

        foreach ($options as $k => $v) {
            $body->setField("smartling.$k", $v);
        }

        $body->setField('apiKey', $this->apiKey);
        $body->setField('projectId', $this->projectId);
        $body->setField('fileType', $type);
        $body->setField('fileUri', $target);
        $body->setField('approved', $approved ? 'true' : 'false');

        $body->addFile(new PostFile('file', file_get_contents($source)));

        $request = $this->client->createRequest('POST', 'file/upload', [
            'body' => $body
        ]);

        return $this->queueRequest($request);
    }

    public function get($source, $target, $locale=null, $retrievalType=null)
    {
        $args = array(
            'apiKey' => $this->apiKey,
            'projectId' => $this->projectId,
            'fileUri' => $source
        );
        if (isset($locale)) {
            $args['locale'] = $locale;
        }
        if (isset($retrievalType)) {
            $args['retrievalType'] = $retrievalType;
        }

        $request = $this->client->createRequest('GET', 'file/get');
        $request->getQuery()->merge($args);

        $ret = $this->queueRequest($request, true)->then(function(CompleteEvent $e) use ($target) {
            file_put_contents($target, $e->getResponse()->getBody()->getContents());
        });

        return $ret;
    }

    public function files($searchTerms=null)
    {
        if (!is_array($searchTerms)) {
            $searchTerms = array();
        }
        $request = $this->client->createRequest('GET', 'file/list');

        $request->getQuery()->merge(array_merge($searchTerms, array(
            'apiKey' => $this->apiKey,
            'projectId' => $this->projectId,
        )));

        return $this->queueRequest($request);
    }

    public function status($fileUri, $locale)
    {
        $args = array(
            'apiKey' => $this->apiKey,
            'projectId' => $this->projectId,
            'fileUri' => $fileUri,
            'locale' => $locale
        );

        $request = $this->client->createRequest('GET', 'file/status');
        $request->getQuery()->merge($args);
        return $this->queueRequest($request);
    }

    public function rename($fileUri, $newFileUri)
    {
        $body = new PostBody();
        $body->setField('apiKey', $this->apiKey);
        $body->setField('projectId', $this->projectId);
        $body->setField('fileUri', $fileUri);
        $body->setField('newFileUri', $newFileUri);

        $request = $this->client->createRequest('POST', 'file/rename');
        $request->setBody($body);
        return $this->queueRequest($request);
    }

    public function delete($fileUri)
    {
         $args = array(
            'apiKey' => $this->apiKey,
            'projectId' => $this->projectId,
            'fileUri' => $fileUri,
        );

        $request = $this->client->createRequest('DELETE', 'file/delete');
        $request->getQuery()->merge($args);
        return $this->queueRequest($request);
    }

    /**
     * @param Request $request
     * @param bool $attachment
     *
     * @return \React\Promise\Promise that yields the response data, or a CompleteEvent if attachment = true
     */
    protected function queueRequest(Request $request, $attachment = false)
    {
        $deferred = new Deferred();

        $this->requests[] = $request;

        $request->getEmitter()->on('complete', function(CompleteEvent $e) use ($attachment, $deferred) {
            if (!$attachment) {
                $outer = $e->getResponse()->json();
                $deferred->resolve($outer['response']['data']);

            } else {
                $deferred->resolve($e);
            }
        });

        $request->getEmitter()->on('error', function(ErrorEvent $e) use ($deferred) {
            $deferred->reject($e->getException()->getMessage() . "\nBody: " . $e->getResponse()->getBody()->getContents());
        });

        return $deferred->promise();
    }

    /**
     * Sends all of the requests concurrently and fulfills the promises
     */
    public function sendAll()
    {
        $pool = new Pool($this->client, $this->requests, [
            'pool_size' => 20,
        ]);
        $pool->wait();
        $this->requests = [];
    }
}
