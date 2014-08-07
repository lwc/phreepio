<?php

namespace Phreepio\Translator;

class DownloadIterator implements \Iterator
{
    private $translator;
    private $remotePaths;
    private $skeletonPaths;
    private $locales;
    private $destinationPattern;

    public function __construct($translator, $remotePaths, $skeletonPaths, $locales, $destinationPattern)
    {
        $this->translator = $translator;
        $this->remotePaths = new \ArrayIterator($remotePaths);
        $this->locales = new \ArrayIterator($locales);
        $this->skeletonPaths = new \ArrayIterator($skeletonPaths);
        $this->destinationPattern = $destinationPattern;
    }

    public function current()
    {
        $remotePath = $this->remotePaths->current();
        $locale = $this->locales->current();
        $localPath = $this->getLocalPath($remotePath, $locale);

        try {
            $this->downloadTranslation($remotePath, $localPath, $locale);
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
        $this->skeletonPaths->next();
    }

    public function rewind()
    {
        $this->index = 0;
        $this->locales->rewind();
        $this->remotePaths->rewind();
        $this->skeletonPaths->rewind();
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

    /**
     * Download translations if cache doesn't exist
     * @param  string $remotePath   Remote path to translation
     * @param  string $localPath    Path to local translation
     * @param  string $locale       translation locale
     * @return null
     */
    private function downloadTranslation($remotePath, $localPath, $locale) {
        $skeletonPath = $this->skeletonPaths->current();

        if ($this->translator->enableCache() && file_exists($skeletonPath)) {
            $cachePath = $this->getCachePath($skeletonPath, $localPath);

            if (!file_exists($cachePath)) {
                $this->makeCacheDir($cachePath);

                $this->translator->download($remotePath, $cachePath, $locale);
            }

            copy($cachePath, $localPath);
        }

        $this->translator->download($remotePath, $localPath, $locale);
    }

    private function makeCacheDir($cachePath)
    {
        $cacheDir = dirname($cachePath);
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
    }

    /**
     * Get the cache path of the translation file
     * @param  string $skeletonPath Smartling's skeleton file path
     * @param  string $localPath    Local translation file path
     * @return string               Path to the cached translation
     */
    private function getCachePath($skeletonPath, $localPath)
    {
        $pathInfo = pathInfo($localPath);
        $hash = md5_file($skeletonPath);

        return sprintf(
            "%s/.cache/%s.%s/%s",
            $pathInfo['dirname'],
            pathinfo($skeletonPath, PATHINFO_FILENAME),
            $hash,
            $pathInfo['basename']
        );
    }
}
