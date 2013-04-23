<?php

namespace Phreepio\Translator;

class UploadIterator implements \Iterator
{
    private $translator;
    private $sources;

    public function __construct($translator, $sources)
    {
        $this->translator = $translator;
        $this->sources = new \ArrayIterator($sources);
    }

    public function current()
    {
        $source = $this->sources->key();
        $config = $this->sources->current();

        try {
            $result = $this->translator->upload($source, $config->target, $config->type);
            return (object)array(
                'success' => true,
                'remotePath' => $config->target,
                'overWritten' => $result->overWritten,
                'stringCount' => $result->stringCount,
            );            
        }
        catch (\Exception $e) {
            return (object)array(
                'success' => false,
                'errorMessage' => $e->getMessage(),
                'errorType' => get_class($e),
                'remotePath' => $config->target,               
            );
        }        
    }

    public function key()
    {
        return $this->sources->key();
    }

    public function next()
    {
        return $this->sources->next();
    }

    public function rewind()
    {
        return $this->sources->rewind();
    }

    public function valid()
    {
        return $this->sources->valid();
    }
}