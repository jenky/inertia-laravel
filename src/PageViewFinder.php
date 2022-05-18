<?php

namespace Inertia;

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\FileViewFinder;
use InvalidArgumentException;

class PageViewFinder extends FileViewFinder
{
    /**
     * The component base symlink path.
     *
     * @var string
     */
    public static $symlinkBaseDirectory = 'vendor';

    public function __construct(Filesystem $files)
    {
        return parent::__construct($files, [], [
            'js',
            'jsx',
            'svelte',
            'ts',
            'tsx',
            'vue',
        ]);
    }

    /**
     * Get the path to a template with a named path.
     *
     * @param  string  $name
     * @return string
     */
    protected function findNamespacedView($name)
    {
        [$namespace, $view] = $this->parseNamespaceSegments($name);

        foreach ($this->hints[$namespace] as $path) {
            foreach ($this->getPossibleViewFiles($view) as $file) {
                if ($this->files->exists($path.'/'.$file)) {
                    return static::$symlinkBaseDirectory.'/'.$namespace.'/'.$view;
                }
            }
        }

        throw new InvalidArgumentException("View [{$name}] not found.");
    }

    /**
     * Find the given view in the list of paths.
     *
     * @param  string  $name
     * @param  array  $paths
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function findInPaths($name, $paths)
    {
        return $name;
    }
}
