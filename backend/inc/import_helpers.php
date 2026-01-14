<?php

/**
 * Shared helper functions for CSV/Excel import flows.
 */
if (!function_exists('import_read_csv')) {
    function import_read_csv(string $filePath): array
    {
        $data = [];
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            return [];
        }

        // Handle UTF-8 BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return [];
        }

        $header = array_map(function ($h) {
            return trim(strtolower($h));
        }, $header);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($header)) {
                continue;
            }

            $dataRow = [];
            foreach ($header as $index => $key) {
                $dataRow[$key] = isset($row[$index]) ? trim($row[$index]) : '';
            }

            if (!empty(array_filter($dataRow, fn($v) => $v !== ''))) {
                $data[] = $dataRow;
            }
        }

        fclose($handle);
        return $data;
    }
}

if (!function_exists('import_read_excel')) {
    function import_read_excel(string $filePath): array
    {
        if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            throw new Exception('Để import file Excel, vui lòng cài PhpSpreadsheet (composer require phpoffice/phpspreadsheet) hoặc chuyển file sang CSV.');
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = [];

            $header = [];
            $highestColumn = $worksheet->getHighestColumn();
            $highestRow = $worksheet->getHighestRow();

            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $cellValue = $worksheet->getCell($col . '1')->getValue();
                $header[] = trim(strtolower($cellValue ?? ''));
            }

            for ($row = 2; $row <= $highestRow; $row++) {
                $dataRow = [];
                $colIndex = 0;
                $isEmpty = true;

                for ($col = 'A'; $col <= $highestColumn; $col++) {
                    $cellValue = $worksheet->getCell($col . $row)->getValue();
                    $value = trim($cellValue ?? '');
                    if ($value !== '') {
                        $isEmpty = false;
                    }

                    $key = $header[$colIndex] ?? '';
                    if ($key) {
                        $dataRow[$key] = $value;
                    }
                    $colIndex++;
                }

                if (!$isEmpty) {
                    $data[] = $dataRow;
                }
            }

            return $data;
        } catch (Exception $e) {
            throw new Exception('Lỗi đọc file Excel: ' . $e->getMessage());
        }
    }
}

