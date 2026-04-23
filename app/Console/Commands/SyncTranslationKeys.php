<?php

namespace App\Console\Commands;

use App\Models\Language\Translate;
use App\Services\Language\TranslationKeyScanner;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncTranslationKeys extends Command
{
    protected $signature = 'translations:sync-keys
                            {--scope=all : Scan scope: all|swm}
                            {--apply : Persist missing keys to database}
                            {--show-missing=20 : Number of missing keys to preview}';

    protected $description = 'Scan codebase translation helpers and sync missing english base keys to language.translates';

    public function handle(TranslationKeyScanner $scanner): int
    {
        $scope = strtolower((string) $this->option('scope'));
        if (! in_array($scope, ['all', 'swm'], true)) {
            $this->error("Invalid scope [{$scope}]. Allowed values: all, swm.");
            return self::INVALID;
        }

        $scanDirectories = $this->directoriesForScope($scope);
        $scanResult = $scanner->scan($scanDirectories);
        $keysWithSources = $scanResult['keys'];
        $allKeys = array_keys($keysWithSources);

        $existingEnKeys = Translate::where('name', 'en')->pluck('key')->all();
        $existingLookup = array_fill_keys($existingEnKeys, true);

        $missingKeys = [];
        foreach ($allKeys as $key) {
            if (! isset($existingLookup[$key])) {
                $missingKeys[] = $key;
            }
        }

        sort($missingKeys);
        $showMissing = max(0, (int) $this->option('show-missing'));

        $this->info('Translation key scan completed.');
        $this->line('Scope: ' . $scope);
        $this->line('Scanned files: ' . $scanResult['scanned_files']);
        $this->line('Extracted unique keys: ' . count($allKeys));
        $this->line('Existing EN keys: ' . count($existingEnKeys));
        $this->line('Missing keys: ' . count($missingKeys));

        if ($showMissing > 0 && count($missingKeys) > 0) {
            $this->newLine();
            $this->info('Missing keys preview:');
            foreach (array_slice($missingKeys, 0, $showMissing) as $key) {
                $source = $keysWithSources[$key][0] ?? '';
                $relativeSource = $source !== '' ? str_replace(base_path() . DIRECTORY_SEPARATOR, '', $source) : 'n/a';
                $this->line('- ' . $key . ' [' . $relativeSource . ']');
            }
        }

        if (! $this->option('apply')) {
            $this->newLine();
            $this->warn('Dry run mode. Re-run with --apply to insert missing EN keys.');
            return self::SUCCESS;
        }

        if (empty($missingKeys)) {
            $this->newLine();
            $this->info('No missing keys to insert.');
            return self::SUCCESS;
        }

        $defaults = $this->defaultMeta();
        $now = now();
        $rows = [];
        foreach ($missingKeys as $key) {
            $rows[] = [
                'key' => $key,
                'name' => 'en',
                'text' => $key,
                'pages' => $this->inferPage($key, $keysWithSources[$key][0] ?? null, $defaults['pages']),
                'group' => $defaults['group'],
                'platform' => $defaults['platform'],
                'load' => $defaults['load'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            Translate::insert($chunk);
        }

        $this->newLine();
        $this->info('Inserted missing EN keys: ' . count($rows));
        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    protected function directoriesForScope(string $scope): array
    {
        if ($scope === 'swm') {
            return [
                resource_path('views/swm'),
                app_path('Http/Controllers/Swm'),
                app_path('Services/Swm'),
                app_path('Http/Requests/Swm'),
            ];
        }

        return [
            resource_path('views'),
            app_path(),
        ];
    }

    /**
     * @return array{pages: string, group: string, platform: string, load: int}
     */
    protected function defaultMeta(): array
    {
        $sample = Translate::where('name', 'en')->first();

        return [
            'pages' => (string) ($sample?->pages ?? 'general'),
            'group' => (string) ($sample?->group ?? 'general'),
            'platform' => (string) ($sample?->platform ?? 'web'),
            'load' => (int) ($sample?->load ?? 0),
        ];
    }

    protected function inferPage(string $key, ?string $sourcePath, string $fallback): string
    {
        if (Str::contains($key, '.')) {
            return Str::before($key, '.');
        }

        if ($sourcePath !== null) {
            $normalized = str_replace('\\', '/', $sourcePath);

            if (Str::contains($normalized, '/views/swm/')) {
                return 'swm';
            }
            if (Str::contains($normalized, '/Controllers/Swm/')) {
                return 'swm';
            }
            if (Str::contains($normalized, '/Services/Swm/')) {
                return 'swm';
            }
        }

        return $fallback !== '' ? $fallback : 'general';
    }
}
