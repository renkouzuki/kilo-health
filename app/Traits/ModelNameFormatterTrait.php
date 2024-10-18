<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait ModelNameFormatterTrait
{
    protected function formatModelName(string $modelName): string
    {
        $parts = explode('Models', str_replace('\\', '', $modelName));
        if (count($parts) == 2) {
            return 'App\\Models\\' . ucfirst($parts[1]);
        }
        return 'App\\' . str_replace('App', 'Models', $modelName);
    }

    protected function decodeAndFormatChanges($changes)
    {
        Log::info('Original changes value: ' . var_export($changes, true));

        if ($changes === null) {
            Log::info('Changes is null');
            return $changes;
        }

        if (is_string($changes)) {
            // Use json_decode with the JSON_UNESCAPED_SLASHES option
            $decoded = json_decode($changes, true, 512, JSON_UNESCAPED_SLASHES);
            if (json_last_error() === JSON_ERROR_NONE) {
                $changes = $decoded;
                Log::info('Successfully decoded changes: ' . var_export($changes, true));
            } else {
                Log::error('JSON decoding error: ' . json_last_error_msg());
                // If decoding fails, try to clean the string and decode again
                $cleanedChanges = stripslashes($changes);
                $decoded = json_decode($cleanedChanges, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $changes = $decoded;
                    Log::info('Successfully decoded changes after cleaning: ' . var_export($changes, true));
                } else {
                    Log::error('JSON decoding error after cleaning: ' . json_last_error_msg());
                    // If it still fails, return the original string
                    return $changes;
                }
            }
        }

        if (is_array($changes)) {
            if (isset($changes['model'])) {
                $changes['model'] = $this->formatModelName($changes['model']);
                Log::info('Formatted model name: ' . $changes['model']);
            } elseif (isset($changes['model_type'])) {
                $changes['model_type'] = $this->formatModelName($changes['model_type']);
                Log::info('Formatted model_type: ' . $changes['model_type']);
            }
        }

        Log::info('Final changes value: ' . var_export($changes, true));
        return $changes;
    }
}