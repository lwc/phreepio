<?php

namespace Phreepio\Translator;

class StatusIterator implements \Iterator
{
    private $translator;
    private $remotePaths;
    private $locales;

    public function __construct(Adapter $translator, $remotePaths, $locales)
    {
        $this->translator = $translator;
        $this->remotePaths = new \ArrayIterator($remotePaths);
        $this->locales = new \ArrayIterator($locales);
    }

    public function current()
    {
        $remotePath = $this->remotePaths->current();
        $locale = $this->locales->current();


        return $this->translator->status($remotePath, $locale)->then(
            function($result) use ($remotePath, $locale) {
                return (object)array(
                    'success' => true,
                    'remotePath' => $remotePath,
                    'locale' => $locale,
                    'stringCount' => $result['stringCount'],
                    'approvedStringCount' => $result['approvedStringCount'],
                    'completedStringCount' => $result['completedStringCount'],
                );
            },
            function($error) use ($remotePath, $locale) {
                return (object)array(
                    'success' => false,
                    'errorMessage' => $error,
                    'remotePath' => $remotePath,
                    'locale' => $locale,
                );
            }
        );
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