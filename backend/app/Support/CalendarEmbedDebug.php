<?php

namespace App\Support;

class CalendarEmbedDebug
{
    private const LOG_PATH = '/Users/kevinhitaj/Desktop/Projects/DIMOCENTER/.cursor/debug-66744b.log';

    /**
     * @param  array<string, mixed>  $data
     */
    public static function log(string $location, string $message, array $data = [], string $hypothesisId = ''): void
    {
        $entry = json_encode([
            'sessionId' => '66744b',
            'timestamp' => (int) round(microtime(true) * 1000),
            'location' => $location,
            'message' => $message,
            'data' => $data,
            'hypothesisId' => $hypothesisId,
        ], JSON_UNESCAPED_SLASHES);

        if (! is_string($entry)) {
            return;
        }

        $line = $entry."\n";

        // #region agent log
        @file_put_contents(self::LOG_PATH, $line, FILE_APPEND | LOCK_EX);
        @file_put_contents(storage_path('logs/debug-66744b.log'), $line, FILE_APPEND | LOCK_EX);
        // #endregion
    }
}
