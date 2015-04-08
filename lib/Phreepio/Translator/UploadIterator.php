<?php

namespace Phreepio\Translator;

class UploadIterator implements \Iterator
{
    private $translator;
    private $sources;

    public function __construct(Adapter $translator, $sources)
    {
        $this->translator = $translator;
        $this->sources = new \ArrayIterator($sources);
    }

    public function current()
    {
        $source = $this->sources->key();
        $config = $this->sources->current();


        return $this->translator->upload($source, $config->target, $config->type)->then(
            function ($result) use ($config) {
                return (object)array(
                    'success' => true,
                    'remotePath' => $config->target,
                    'overWritten' => $result['overWritten'],
                    'stringCount' => $result['stringCount'],
                );
            },

            function ($error) use ($config) {
                return (object)array(
                    'success' => false,
                    'errorMessage' => $error,
                    'remotePath' => $config->target,
                );
            }
        );
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