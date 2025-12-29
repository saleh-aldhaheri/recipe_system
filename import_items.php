<?php

/**
 * Import Items from XLSX file
 * This script extracts items from the XLSX file in data folder and imports them to the database
 */
define('BASE', __DIR__.DIRECTORY_SEPARATOR);
define('Core', BASE.'core'.DIRECTORY_SEPARATOR);
define('APP', BASE.'app'.DIRECTORY_SEPARATOR);
define('DATA', BASE.'data'.DIRECTORY_SEPARATOR);

require_once BASE.'vendor/autoload.php';
require_once BASE.'bootstrap.php';

use App\Models\Item;
use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = DATA.'PROD-PR-01-CURRY LAKSA PASTE CLS1K-OS (251009) - Rev06.xlsx';

if (! file_exists($filePath)) {
    exit("Error: File not found: {$filePath}\n");
}

try {
    echo "Loading Excel file...\n";
    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    $dataArray = $worksheet->toArray(null, true, true, false);

    $allItems = [];
    $tables = [];

    $totalRows = count($dataArray);
    for ($i = 0; $i < $totalRows; $i++) {
        if (empty(array_filter($dataArray[$i]))) {
            continue;
        }

        $rowString = implode('', $dataArray[$i]);

        if (str_contains($rowString, 'Qty') && str_contains($rowString, 'ITEM') && str_contains($rowString, 'BATCH NUMBER')) {
            $tableGroups = [];
            $itemPositions = [];
            $batchPositions = [];
            $qtyPositions = [];

            foreach ($dataArray[$i] as $key => $value) {
                $value = trim($value);
                if ($value == 'ITEM') {
                    $itemPositions[] = $key;
                } elseif ($value == 'BATCH NUMBER') {
                    $batchPositions[] = $key;
                } elseif ($value == 'Qty') {
                    $qtyPositions[] = $key;
                }
            }

            foreach ($itemPositions as $itemPos) {
                $batchPos = null;
                foreach ($batchPositions as $bp) {
                    if ($bp > $itemPos && ($batchPos === null || $bp < $batchPos)) {
                        $batchPos = $bp;
                    }
                }

                if ($batchPos !== null) {
                    $qtyPos = null;
                    foreach ($qtyPositions as $qp) {
                        if ($qp > $batchPos && ($qtyPos === null || $qp < $qtyPos)) {
                            $qtyPos = $qp;
                        }
                    }

                    if ($qtyPos !== null) {
                        $tableGroups[] = [
                            'ITEM' => $itemPos,
                            'BATCH NUMBER' => $batchPos,
                            'Qty' => $qtyPos,
                        ];
                    }
                }
            }

            foreach ($tableGroups as $tableIndexes) {
                $tableData = [
                    'headerRow' => $i,
                    'columns' => $tableIndexes,
                    'items' => [],
                ];

                for ($j = $i + 1; $j < $totalRows; $j++) {
                    $nextRowString = implode('', $dataArray[$j]);

                    if (str_contains($nextRowString, 'Qty') && str_contains($nextRowString, 'ITEM') && str_contains($nextRowString, 'BATCH NUMBER')) {
                        break;
                    }

                    if (empty(array_filter($dataArray[$j]))) {
                        if (! empty($tableData['items'])) {
                            break;
                        }

                        continue;
                    }

                    $item = trim($dataArray[$j][$tableIndexes['ITEM']] ?? '');
                    $batchNumber = trim($dataArray[$j][$tableIndexes['BATCH NUMBER']] ?? '');
                    $qty = trim($dataArray[$j][$tableIndexes['Qty']] ?? '');

                    if (! empty($item) || ! empty($batchNumber) || ! empty($qty)) {
                        $tableData['items'][] = [
                            'item' => $item,
                            'batch_number' => $batchNumber,
                            'qty' => $qty,
                        ];
                    } elseif (! empty($tableData['items'])) {
                        break;
                    }
                }
                $tables[] = $tableData;
            }
        }
    }

    foreach ($tables as $table) {
        foreach ($table['items'] as $item) {
            $itemName = trim($item['item'] ?? '');
            if (! empty($itemName)) {
                $allItems[] = $itemName;
            }
        }
    }

    $uniqueItems = array_unique($allItems);
    $uniqueItems = array_filter($uniqueItems, function ($item) {
        return ! empty(trim($item));
    });

    echo "\n=== Found ".count($uniqueItems)." unique items ===\n\n";

    $imported = 0;
    $skipped = 0;
    $errors = 0;

    foreach ($uniqueItems as $itemName) {
        $itemName = trim($itemName);
        if (empty($itemName)) {
            continue;
        }

        try {
            $existingItem = Item::where('short_name', $itemName)->first();

            if ($existingItem) {
                echo "Skipped (already exists): {$itemName}\n";
                $skipped++;
            } else {
                Item::create([
                    'short_name' => $itemName,
                    'name' => $itemName,
                    'balance' => 0.0,
                    'unit' => 'pcs',
                ]);
                echo "Imported: {$itemName}\n";
                $imported++;
            }
        } catch (\Exception $e) {
            echo "Error importing {$itemName}: ".$e->getMessage()."\n";
            $errors++;
        }
    }

    echo "\n=== Import Summary ===\n";
    echo "Imported: {$imported}\n";
    echo "Skipped (already exists): {$skipped}\n";
    echo "Errors: {$errors}\n";
    echo 'Total processed: '.($imported + $skipped + $errors)."\n";

} catch (\Throwable $e) {
    echo 'Error: '.$e->getMessage()."\n";
    echo "Stack trace:\n".$e->getTraceAsString()."\n";
    exit(1);
}
