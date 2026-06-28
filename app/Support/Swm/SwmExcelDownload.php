<?php

namespace App\Support\Swm;

use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\WriterInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SwmExcelDownload
{
    /**
     * Build an XLSX export as a proper Laravel download response.
     *
     * Box\Spout's openToBrowser() echoes the file to php://output and sets raw
     * header() calls, but a controller returning through Laravel's response
     * lifecycle then re-sends its own headers (Content-Type: text/html,
     * Content-Length: 0) on top of the binary body. The browser receives a
     * malformed response and fails with ERR_INVALID_RESPONSE. Writing to a real
     * temp file and returning a BinaryFileResponse lets Laravel send correct
     * headers, an accurate Content-Length, and stream the body cleanly.
     *
     * @param  callable(WriterInterface): void  $writeRows  receives the open writer; add rows here
     */
    public static function xlsx(string $filename, callable $writeRows): BinaryFileResponse
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'swm_xlsx_');

        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($tmpPath);
        $writeRows($writer);
        $writer->close();

        return response()
            ->download($tmpPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }
}
