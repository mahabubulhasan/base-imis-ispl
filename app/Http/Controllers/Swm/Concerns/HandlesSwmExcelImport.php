<?php

namespace App\Http\Controllers\Swm\Concerns;

use App\Support\Swm\SwmImportRowHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

trait HandlesSwmExcelImport
{
    protected function swmImportFormView(string $pageTitle, string $backRoute, string $storeRoute)
    {
        return view('swm.partials.import-excel', [
            'page_title' => $pageTitle,
            'backRoute' => $backRoute,
            'storeRoute' => $storeRoute,
        ]);
    }

    /**
     * @param  class-string  $importClass
     * @param  array<int, string>  $requiredHeaders
     */
    protected function swmImportStore(
        Request $request,
        string $importClass,
        array $requiredHeaders,
        string $indexRoute,
        string $disk,
        string $filenamePrefix,
        string $entityName
    ) {
        Validator::extend('swm_excel_import_ext', function ($attribute, $value) {
            return strtolower((string) $value->getClientOriginalExtension()) === 'xlsx';
        }, __('File must be XLSX format.'));

        $this->validate($request, [
            'import_file' => 'required|file|swm_excel_import_ext',
        ], [
            'import_file.required' => __('The import file is required.'),
        ]);

        $filename = $filenamePrefix.'.xlsx';
        if (Storage::disk($disk)->exists($filename)) {
            Storage::disk($disk)->delete($filename);
        }
        $stored = $request->file('import_file')->storeAs('/', $filename, $disk);
        if (! $stored) {
            return back()->with('error', __('Could not store the uploaded file.'));
        }
        $fullPath = Storage::disk($disk)->path($filename);

        $headings = (new HeadingRowImport)->toArray($fullPath);
        $headingRow = isset($headings[0][0])
            ? array_map(fn ($h) => SwmImportRowHelper::slugifyHeader((string) $h), $headings[0][0])
            : [];
        $headingErrors = [];
        foreach ($requiredHeaders as $col) {
            $slug = SwmImportRowHelper::slugifyHeader($col);
            if (! in_array($slug, $headingRow, true)) {
                $headingErrors[$col] = __('Heading row is missing required column: :col', ['col' => $col]);
            }
        }
        if (count($headingErrors) > 0) {
            return back()->withErrors($headingErrors);
        }

        $import = new $importClass((int) Auth::id());
        Excel::import($import, $fullPath);

        $message = __('Successfully :n Records Imported For :entity From Excel.', [
            'n' => $import->successCount,
            'entity' => $entityName,
        ]);
        if (count($import->errors) > 0) {
            return redirect()->route($indexRoute)
                ->with('success', $message)
                ->with('import_errors', $import->errors);
        }

        return redirect()->route($indexRoute)->with('success', $message);
    }
}
