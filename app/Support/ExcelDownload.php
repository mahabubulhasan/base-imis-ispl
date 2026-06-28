<?php

namespace App\Support;

use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\WriterInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExcelDownload
{
    /**
     * Build an XLSX export as a proper Laravel download response.
     *
     * @param  callable(WriterInterface): void  $writeRows  receives the open writer; add rows here
     */
    public static function xlsx(string $filename, callable $writeRows): BinaryFileResponse
    {
        return self::stream(Type::XLSX, $filename, $writeRows);
    }

    /**
     * Build a CSV export as a proper Laravel download response.
     *
     * @param  callable(WriterInterface): void  $writeRows  receives the open writer; add rows here
     */
    public static function csv(string $filename, callable $writeRows): BinaryFileResponse
    {
        return self::stream(Type::CSV, $filename, $writeRows);
    }

    /**
     * Box\Spout's openToBrowser() echoes the file to php://output and sets raw
     * header() calls, but a controller returning through Laravel's response
     * lifecycle then re-sends its own headers (Content-Type: text/html,
     * Content-Length: 0) on top of the binary body. The browser receives a
     * malformed response and fails with ERR_INVALID_RESPONSE. Writing to a real
     * temp file and returning a BinaryFileResponse lets Laravel send correct
     * headers, an accurate Content-Length, and stream the body cleanly.
     *
     * @param  callable(WriterInterface): void  $writeRows
     */
    protected static function stream(string $type, string $filename, callable $writeRows): BinaryFileResponse
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'export_');

        // box/spout 2.x ships a Singleton trait whose __wakeup()/__clone() are
        // declared private; PHP 8.0+ raises an E_WARNING when that trait is first
        // loaded (during the XLSX writer's openWriter()). Laravel's error handler
        // promotes the warning to a fatal ErrorException, which breaks the export
        // on production. Silence just that warning while the writer initialises;
        // the row-building closure below runs with normal error reporting.
        $previousReporting = error_reporting();
        error_reporting($previousReporting & ~E_WARNING & ~E_DEPRECATED);
        try {
            $writer = WriterFactory::create($type);
            $writer->openToFile($tmpPath);
        } finally {
            error_reporting($previousReporting);
        }

        $writeRows($writer);
        $writer->close();

        $contentType = $type === Type::CSV
            ? 'text/csv'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        return response()
            ->download($tmpPath, $filename, ['Content-Type' => $contentType])
            ->deleteFileAfterSend(true);
    }
}
