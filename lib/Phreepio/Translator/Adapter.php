<?php

namespace Phreepio\Translator;

interface Adapter
{
    public function download($remotePath, $localPath, $locale);

    public function upload($localPath, $remotePath, $type);

    public function status($remotePath, $locale);
}