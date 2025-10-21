<?php

namespace App\Imports;

use App\Models\Language\Translate;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\HeadingRowImport;

class TranslateImport implements ToModel, WithStartRow

{


    public function rules(): array {

        return [
           'key' => [
               'required',
               'string',
           ],
           'text' => [
               'required',
               'string',
           ],
           'translated_text' => [
               'nullable',
               'string',
           ]

       ];
    }
    public function startRow(): int
    {
        return 2; // Skip the first row
    }
    public function customValidationMessages()
{
    return [
        // '0.required' => 'XXXXXXXX',
    ];
}
 /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $headings = (new HeadingRowImport)->toArray($request->file('csvfile'));
        $i = 0;
        foreach($headings[0][0] as $head)
        {
            if($head == 'key')
            {
                $key_no = $i;
            }
            elseif($head == 'text')
            {
                $text_no = $i;
            }
            elseif($translated_text == 'text')
            {
                $translated_no = $i;
            }
            $i++;
        }
        $val= new Translate([
             'key'            => $row[$key_no],
             'text'          => $row[$text_no],
             'translated_text'           => $row[$translated_no],

         ]);
    }

}
