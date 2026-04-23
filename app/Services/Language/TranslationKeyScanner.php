<?php

namespace App\Services\Language;

use Illuminate\Support\Facades\File;

class TranslationKeyScanner
{
    /**
     * @var array<string, array<int, string>>
     */
    protected array $patterns = [
        "/__\\(\\s*'((?:\\\\'|[^'])+)'\\s*[\\),]/" => [],
        '/__\\(\\s*"((?:\\\\"|[^"])+)"\\s*[\\),]/' => [],
        "/@lang\\(\\s*'((?:\\\\'|[^'])+)'\\s*[\\),]/" => [],
        '/@lang\\(\\s*"((?:\\\\"|[^"])+)"\\s*[\\),]/' => [],
        "/trans\\(\\s*'((?:\\\\'|[^'])+)'\\s*[\\),]/" => [],
        '/trans\\(\\s*"((?:\\\\"|[^"])+)"\\s*[\\),]/' => [],
        "/Lang::get\\(\\s*'((?:\\\\'|[^'])+)'\\s*[\\),]/" => [],
        '/Lang::get\\(\\s*"((?:\\\\"|[^"])+)"\\s*[\\),]/' => [],
    ];

    /**
     * Scan app files and return translation keys with source files.
     *
     * @param  array<int, string>  $directories
     * @param  array<int, string>  $extensions
     * @return array{keys: array<string, array<int, string>>, scanned_files: int}
     */
    public function scan(array $directories, array $extensions = ['php', 'blade.php', 'js', 'ts', 'vue']): array
    {
        $keys = [];
        $scannedFiles = 0;

        foreach ($directories as $directory) {
            if (!File::isDirectory($directory)) {
                continue;
            }

            foreach (File::allFiles($directory) as $file) {
                $path = $file->getPathname();
                if (! $this->hasSupportedExtension($path, $extensions)) {
                    continue;
                }

                $content = File::get($path);
                $scannedFiles++;

                foreach (array_keys($this->patterns) as $pattern) {
                    if (! preg_match_all($pattern, $content, $matches)) {
                        continue;
                    }

                    foreach ($matches[1] as $match) {
                        $key = $this->normalizeKey($match);
                        if ($key === '') {
                            continue;
                        }

                        if (! isset($keys[$key])) {
                            $keys[$key] = [];
                        }
                        $keys[$key][] = $path;
                    }
                }
            }
        }

        foreach ($keys as $key => $sources) {
            $keys[$key] = array_values(array_unique($sources));
        }

        ksort($keys);

        return [
            'keys' => $keys,
            'scanned_files' => $scannedFiles,
        ];
    }

    protected function hasSupportedExtension(string $path, array $extensions): bool
    {
        foreach ($extensions as $extension) {
            if (str_ends_with($path, '.' . $extension)) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeKey(string $key): string
    {
        $key = str_replace(['\\"', "\\'"], ['"', "'"], $key);
        return trim($key);
    }
}
