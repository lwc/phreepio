<?php

namespace Phreepio\Translator;

class StatusIterator implements \Iterator
{
    private $translator;
    private $remotePaths;
    private $locales;

    public function __construct($translator, $remotePaths, $locales)
    {
        $this->translator = $translator;
        $this->remotePaths = new \ArrayIterator($remotePaths);
        $this->locales = new \ArrayIterator($locales);
    }

    public function current()
    {
        $remotePath = $this->remotePaths->current();
        $locale = $this->locales->current();

        try {
            $result = $this->translator->status($remotePath, $locale);
            return (object)array(
                'success' => true,
                'remotePath' => $remotePath,
                'locale' => $locale,
                'stringCount' => $result->stringCount,
                'approvedStringCount' => $result->approvedStringCount,
                'completedStringCount' => $result->completedStringCount,
            );            
        }
        catch (\Exception $e) {
            return (object)array(
                'success' => false,
                'errorMessage' => $e->getMessage(),
                'errorType' => get_class($e),
                'remotePath' => $remotePath,               
                'locale' => $locale,
            );
        }
    }

    public function key()
    {
        return $this->remotePaths->current().':'.$this->locales->current();
    }

    public function next()
    {
        $this->index++;

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
}