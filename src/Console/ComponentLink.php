<?php

namespace Inertia\Console;

use Illuminate\Console\Command;
use Inertia\PageViewFinder;

class ComponentLink extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'inertia:component-link
                {--relative : Create the symbolic link using relative paths}
                {--force : Recreate existing symbolic links}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the symbolic links configured for loading the package components';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->ensureComponentBaseDirectoryExists();
        $fs = $this->laravel->make('files');

        foreach ($this->hints() as $namespace => $links) {
            foreach ($links as $target) {
                $target = realpath($target);

                if (! $fs->exists($target)) {
                    continue;
                }

                $link = $this->componentPath($namespace);

                if (file_exists($link) && ! $this->isRemovableSymlink($link, $this->option('force'))) {
                    $this->error("The [$link] link already exists.");
                    continue;
                }

                if (is_link($link)) {
                    $fs->delete($link);
                }

                if ($this->option('relative')) {
                    $fs->relativeLink($target, $link);
                } else {
                    $fs->link($target, $link);
                }

                $this->info("The [$link] link has been connected to [$target].");
            }
        }
    }

    protected function componentPath(?string $path = null): string
    {
        $basePath = 'js/Pages/'.PageViewFinder::$symlinkBaseDirectory;

        return resource_path($path ? $basePath.'/'.$path : $basePath);
    }

    protected function ensureComponentBaseDirectoryExists()
    {
        $path = $this->componentPath();
        $fs = $this->laravel->make('files');

        if ($fs->missing($path) || ! $fs->isDirectory($path)) {
            $fs->makeDirectory($path);
        }
    }

    /**
     * Get the symbolic links that are configured from all packages.
     *
     * @return array
     */
    protected function hints()
    {
        return $this->laravel[PageViewFinder::class]->getHints();
    }

    /**
     * Determine if the provided path is a symlink that can be removed.
     *
     * @param  string  $link
     * @param  bool  $force
     * @return bool
     */
    protected function isRemovableSymlink(string $link, bool $force): bool
    {
        return is_link($link) && $force;
    }
}
