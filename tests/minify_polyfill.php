<?php

declare(strict_types=1);

/*
 * Polyfill for the MatthiasMullie\Minify package that is used by AssetHandler
 * but is not installed in the test environment. It mimics the minimal API:
 * add() collects files, minify() concatenates them into the target file.
 */

namespace MatthiasMullie\Minify;

if (! class_exists(CSS::class)) {
    abstract class AbstractMinifier
    {
        protected array $files = [];

        public function add($file, $type = null): static
        {
            $this->files[] = $file;

            return $this;
        }

        public function minify($target = null): string
        {
            $content = implode('', array_map(
                static fn($file) => (string) file_get_contents($file),
                $this->files
            ));

            if ($target !== null) {
                file_put_contents($target, $content);
            }

            return $content;
        }
    }

    class CSS extends AbstractMinifier
    {
    }

    class JS extends AbstractMinifier
    {
    }
}
