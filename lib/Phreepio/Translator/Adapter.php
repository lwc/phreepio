<?php

namespace Phreepio\Translator;

use React\Promise\PromiseInterface;

interface Adapter
{
    /**
     * @return PromiseInterface
     */
    public function download($remotePath, $localPath, $locale);

    /**
     * @return PromiseInterface
     */
    public function upload($localPath, $remotePath, $type);

    /**
     * @return PromiseInterface
     */
    public function status($remotePath, $locale);

    /**
     * Called before exit to ensure any pending requests are sent.
     */
    public function flush();
}