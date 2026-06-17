<?php

use App\Models\BuildingInfo\BuildingSurvey;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const BACKFILL_ORDER = [
        'ward',
        'road_code',
        'house_number',
        'functional_use_id',
        'use_category_id',
        'water_source_id',
        'sanitation_system_id',
        'sewer_code',
        'drain_code',
        'tax_code',
        'collected_date',
        'temp_building_code',
    ];

    public function up(): void
    {
        BuildingSurvey::withTrashed()
            ->whereNotNull('payload_json')
            ->chunkById(500, function ($surveys) {
                foreach ($surveys as $survey) {
                    $payload = $this->normalizePayload($survey->payload_json);
                    if ($payload === []) {
                        continue;
                    }

                    $updates = [];
                    $payloadChanged = false;

                    foreach (self::BACKFILL_ORDER as $column) {
                        if ($this->hasValue($survey->{$column})) {
                            continue;
                        }

                        if (!array_key_exists($column, $payload) || !$this->hasValue($payload[$column])) {
                            continue;
                        }

                        $updates[$column] = $payload[$column];
                        unset($payload[$column]);
                        $payloadChanged = true;
                    }

                    if ($payloadChanged) {
                        $updates['payload_json'] = $payload ?: null;
                        $survey->forceFill($updates)->saveQuietly();
                    }
                }
            });
    }

    public function down(): void
    {
        // Data migration — not reversible without re-merging columns into payload_json.
    }

    private function normalizePayload($payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        if (isset($payload['payload_json']) && is_array($payload['payload_json'])) {
            $nested = $payload['payload_json'];
            unset($payload['payload_json']);
            $payload = array_merge($payload, $nested);
        }

        return $payload;
    }

    private function hasValue(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }
};
