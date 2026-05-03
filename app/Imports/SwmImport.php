<?php

namespace App\Imports;

use App\Models\SwmPaymentInfo\SwmPayment;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithUpsertColumns;
use Maatwebsite\Excel\Concerns\RemembersChunkOffset; 
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class SwmImport implements ToModel, WithHeadingRow, WithChunkReading,WithValidation, SkipsOnError, WithColumnFormatting, WithMapping, WithBatchInserts, SkipsOnFailure, ShouldQueue 
{
    use Importable;
    use RemembersChunkOffset;
    use SkipsFailures;

    public function model(array $row)
    { 
        $chunkOffset = $this->getChunkOffset();
        return new SwmPayment([
            'swm_customer_id' => $row['swm_customer_id'],
            'customer_name' => $row['customer_name'] ? $row['customer_name'] : null,
            'customer_contact' => $row['customer_contact'] ? $row['customer_contact'] : null,
            'last_payment_date' => ($row['last_payment_date']),
            'receipt_no' => isset($row['receipt_no']) && $row['receipt_no'] !== '' ? (string) $row['receipt_no'] : null,
        ]);
    }
      
   public function chunkSize(): int
    {
        return 1000;
    }
    public function batchSize(): int
    {
        return 1000;
    }
    /**
    * @return array
    */
    public function rules(): array {
       
         return [
            'swm_customer_id' => [
                'required',
                'string',
                //'unique:pgsql.taxpayment_info.tax_payments,swm_customer_id',
            ],
            'customer_name' => [
                'nullable',
                'string',
            ],
            'customer_contact' => [
                'nullable',
                'integer',
            ],
            'last_payment_date' => [
                'required',
                'date_format:Y-m-d',
            ],
            'receipt_no' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
    public function map($row): array
    {
        return [
            'swm_customer_id' => $row['swm_customer_id'],
            'customer_name' => $row['customer_name'],
            'customer_contact' => $row['customer_contact'],
            'last_payment_date' => date('Y-m-d', strtotime($row['last_payment_date'])),
            'receipt_no' => isset($row['receipt_no']) && $row['receipt_no'] !== '' ? (string) $row['receipt_no'] : null,
        ];
    }
     /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'last_payment_date' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }


    public function onError(\Throwable $e) {
        
    }
       
}