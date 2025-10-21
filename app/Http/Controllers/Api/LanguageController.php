<?php
// Last Modified Date: 20-03-2025
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Language\LanguageRequest;
use Illuminate\Http\Request;
use App\Models\Language\Language;
use App\Models\Language\Translate;
use App\Models\Language\Setting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cookie;
use DB;
use DataTables;
use Auth;


use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Illuminate\Support\Facades\Validator;
use App\Imports\TranslateImport;


class LanguageController extends Controller
{

    public function headerDropdown()
    {
        $activeLang = Language::where('status', 1)->pluck('code');
        return response()->json(['languages' => $activeLang]);
    }




    public function getTranslation($lang_id)
{
    $translations = DB::table('language.translates as t')
    ->join('language.translates as en_translations', function ($join) {
        $join->on('t.key', '=', 'en_translations.key')
             ->where('en_translations.name', '=', 'en');
    })
    ->where('t.name', $lang_id)
    ->where('t.platform', 'mobile')

    ->where('en_translations.platform', 'mobile') // Ensure English translations are also for mobile
    ->get([
        'en_translations.text as english_text', // English text
        DB::raw('TRIM(t.text) as translated_text') // Trimmed translated text
    ]);

    // Convert list to key-value format
    $formattedTranslations = [];
    foreach ($translations as $translation) {
        $formattedTranslations[$translation->english_text] = $translation->translated_text;
    }

    return response()->json($formattedTranslations);
}


}
