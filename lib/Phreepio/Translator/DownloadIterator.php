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

        $skeletonPath = $this->skeletonPaths->current();

        if ($this->translator->enableCache() && file_exists($skeletonPath)) {
            $cachePath = $this->getCachePath($skeletonPath, $localPath);
            if (!file_exists($cachePath) || $this->hasCacheExpired($cachePath)) {
                $this->makeCacheDir($cachePath);
                $result = $this->downloadTranslation($remotePath, $cachePath, $locale);
            } else {
                $result = (object)array(
                    'success' => true,
                    'usedCache' => true,
                    'localPath' => $localPath,
                    'locale' => $locale,
                    'remotePath' => $remotePath
                );
            }

            copy($cachePath, $localPath);

            return $result;
        } else {
            return $this->downloadTranslation($remotePath, $localPath, $locale);
        }
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

    private function downloadTranslation($remotePath, $localPath, $locale) {
        try {
            $tmpPath = $this->getTmpPath($localPath);
            $this->translator->download($remotePath, $tmpPath, $locale);
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

        rename($tmpPath, $localPath);

        return (object)array(
            'success' => true,
            'usedCache' => false,
            'localPath' => $localPath,
            'locale' => $locale,
            'remotePath' => $remotePath
        );
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
            "%s/.cache/%s.%s.%s/%s",
            $pathInfo['dirname'],
            pathinfo($skeletonPath, PATHINFO_FILENAME),
            $hash,
            $this->translator->cacheVersion(),
            $pathInfo['basename']
        );
    }

    /**
     * Checks the file path against the specified cache age
     * @param  string  $filepath translation
     * @return boolean
     */
    private function hasCacheExpired($filepath)
    {
        return (filemtime($filepath) + $this->translator->cacheMaxAge()) < time();
    }

    /**
     * Get the temporary path for downloading.
     * After download is complete the temp path will get rename to the permanent path.
     * @param  string $filePath
     * @return string
     */
    private function getTmpPath($filePath)
    {
        $filename = pathinfo($filePath, PATHINFO_FILENAME);

        return str_replace(
            $filename,
            'tmp-' . $filename,
            $filePath
        );
    }
}
