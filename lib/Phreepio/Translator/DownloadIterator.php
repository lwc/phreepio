<?php

namespace Phreepio\Translator;

class DownloadIterator implements \Iterator
{
    private $translator;
    private $remotePaths;
    private $locales;
    private $destinationPattern;

    public function __construct($translator, $remotePaths, $locales, $destinationPattern)
    {
        $this->translator = $translator;
        $this->remotePaths = new \ArrayIterator($remotePaths);
        $this->locales = new \ArrayIterator($locales);
        $this->destinationPattern = $destinationPattern;
    }

    public function current()
    {
        $remotePath = $this->remotePaths->current();
        $locale = $this->locales->current();
        $localPath = $this->getLocalPath($remotePath, $locale);
        try {
            $this->translator->download($remotePath, $localPath, $locale);
        }
        catch (\Exception $e) {
            return (object)array(
                'success' => false,
                'errorMessage' => $e->getMessage(),
                'errorType' => get_class($e),
                'localPath' => $localPath,
                'locale' => $locale,
                'remotePath' => $remotePath                
            );
        }
        return (object)array(
            'success' => true,
            'localPath' => $localPath,
            'locale' => $locale,
            'remotePath' => $remotePath
        );
    }

    public function key()
    {
        return $this->remotePaths->key().':'.$this->locales->key();
    }

    public function next()
    {
        $this->locales->next();
        if ($this->locales->valid())
            return;

        $this->locales->rewind();

        $this->remotePaths->next();
    }

    public function rewind()
    {
        $this->index = 0;
        $this->locales->rewind();
        $this->remotePaths->rewind();
    }

    public function valid()
    {
        return $this->remotePaths->valid();
    }

    private function getLocalPath($remotePath, $locale)
    {
        $targetParts = array(
            '%name%' => pathinfo($remotePath, PATHINFO_FILENAME),
            '%ext%' => pathinfo($remotePath, PATHINFO_EXTENSION),
            '%locale%' => $locale,
        );

        return str_replace(
            array_keys($targetParts),
            array_values($targetParts),
            $this->destinationPattern
        );
    }
}