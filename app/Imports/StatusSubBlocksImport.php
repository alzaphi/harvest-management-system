<?php

namespace App\Imports;

use App\Models\StatusSubBlock;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class StatusSubBlocksImport implements ToModel, WithHeadingRow, WithValidation
{
    private $rowCount = 0;
    private $processedCount = 0;
    
    /**
     * Get the number of processed rows
     *
     * @return int
     */
    public function getProcessedCount()
    {
        return $this->processedCount;
    }

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    /**
     * Normalize header names to handle different cases and spaces
     */
    private function normalizeHeader($header)
    {
        $header = strtolower(trim($header));
        return str_replace([' ', '_', '-'], '', $header);
    }

    /**
     * Process a single row of the import
     */
    public function model(array $row)
    {
        $this->rowCount++;
        
        // Skip the header row (first row)
        if ($this->rowCount === 1) {
            return null;
        }
        
        // Skip empty rows
        if (empty(array_filter($row, function($value) {
            return $value !== null && $value !== '';
        }))) {
            return null;
        }
        
        // Map the row data to model attributes
        $kodePetak = trim($row['kode_petak'] ?? $row['kode'] ?? '');
        $status = trim($row['status'] ?? '');
        
        // Skip rows with missing required fields
        if (empty($kodePetak) || empty($status)) {
            Log::warning('Skipping row due to missing required fields', [
                'row' => $row,
                'row_number' => $this->rowCount
            ]);
            return null;
        }
        
        // Increment processed count for valid rows
        $this->processedCount++;
        
        try {
            // Map the row data to model attributes
            $kodePetak = trim($row['kode_petak'] ?? $row['kode'] ?? '');
            $status = trim($row['status'] ?? '');
            
            // Skip rows with missing required fields
            if (empty($kodePetak) || empty($status)) {
                return null;
            }
            
            // Parse date or use current date if not provided
            $tanggalUpdate = now();
            if (!empty($row['tanggal_update'] ?? $row['tanggal'] ?? null)) {
                $dateValue = $row['tanggal_update'] ?? $row['tanggal'];
                
                try {
                    // Handle Excel date serial numbers
                    if (is_numeric($dateValue)) {
                        $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
                        $tanggalUpdate = Carbon::instance($dateObj);
                    } else {
                        // Try multiple date formats
                        $formats = [
                            'Y-m-d',     // 2025-06-12
                            'd/m/Y',     // 12/06/2025
                            'm/d/Y',     // 06/12/2025
                            'd-m-Y',     // 12-06-2025
                            'm-d-Y',     // 06-12-2025
                            'd M Y',     // 12 Jun 2025
                            'd F Y',     // 12 June 2025
                            'Y/m/d',     // 2025/06/12
                            'Ymd',       // 20250612
                            'dmy',       // 120625
                            'mdy'        // 061225
                        ];
                        
                        $parsed = false;
                        foreach ($formats as $format) {
                            try {
                                $tanggalUpdate = Carbon::createFromFormat($format, $dateValue);
                                $parsed = true;
                                break;
                            } catch (\Exception $e) {
                                continue;
                            }
                        }
                        
                        if (!$parsed) {
                            // If no format matched, try standard parse
                            $tanggalUpdate = Carbon::parse($dateValue);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Error parsing date: ' . $dateValue . ' - ' . $e->getMessage());
                    // Fall back to current date if parsing fails
                    $tanggalUpdate = now();
                }
            }
            
            // Handle the aktif field - convert to boolean
            $aktifValue = $row['aktif'] ?? $row['isactive'] ?? true;
            $aktif = filter_var($aktifValue, FILTER_VALIDATE_BOOLEAN);
            
            // Handle luas_status with different possible field names
            $luasStatus = (float)($row['luas_status'] ?? $row['luas'] ?? 0);
            
            return new StatusSubBlock([
                'kode_petak'     => $kodePetak,
                'tanggal_update' => $tanggalUpdate,
                'tahun'          => $tanggalUpdate->format('Y'),
                'status'         => $status,
                'luas_status'    => $luasStatus,
                'aktif'          => $aktif ? 1 : 0,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error processing row: ' . $e->getMessage(), [
                'row' => $row,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Validation rules
     *
     * @return array
     */
    public function rules(): array
    {
        // We'll handle date validation in the model method
        return [
            'kode_petak' => [
                'required',
                'string',
                'exists:sub_blocks,kode_petak',
                function ($attribute, $value, $fail) {
                    if (empty(trim($value))) {
                        $fail('Kolom kode_petak harus diisi');
                    }
                },
            ],
            'status' => [
                'required',
                'string',
                Rule::in(['Planned Cutting', 'Already Cut Down']),
                function ($attribute, $value, $fail) {
                    if (empty(trim($value))) {
                        $fail('Kolom status harus diisi');
                    }
                },
            ],
            'luas_status' => 'nullable|numeric|min:0',
            'tanggal_update' => 'nullable', // Remove date validation here
            'tanggal' => 'nullable', // Add alternative field name
            'aktif' => 'nullable|boolean'
        ];
    }

    /**
     * Custom validation messages
     *
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'kode_petak.required' => 'Kolom kode_petak harus diisi',
            'kode_petak.exists' => 'Kode petak :input tidak ditemukan di database',
            'status.required' => 'Kolom status harus diisi',
            'status.in' => 'Status harus berupa "Planned Cutting" atau "Already Cut Down" (nilai saat ini: :input)',
            'luas_status.numeric' => 'Luas status harus berupa angka (nilai saat ini: :input)',
            'luas_status.min' => 'Luas status minimal 0 (nilai saat ini: :input)',
            'aktif.boolean' => 'Nilai aktif harus berupa 1/0, true/false, yes/no, on/off',
        ];
    }

    /**
     * Custom validation attributes
     *
     * @return array
     */
    public function customValidationAttributes()
    {
        return [
            'kode_petak' => 'Kode Petak',
            'status' => 'Status',
            'luas_status' => 'Luas Status',
            'tanggal_update' => 'Tanggal Update',
        ];
    }
}
