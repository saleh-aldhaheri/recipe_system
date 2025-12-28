<?php

define('BASE', __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
define('DATA', BASE.'data');

require_once BASE.'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = DATA.DIRECTORY_SEPARATOR.'PROD-PR-01-CURRY LAKSA PASTE CLS1K-OS (251009) - Rev06.xlsx';

try {
    $sheet = IOFactory::load($filePath);

    $worksheet = $sheet->getActiveSheet();

    $dataArray = $worksheet->toArray(null, true, true, false);

    $getDateRow = [];
    $getProduceRow = [];
    $tables = [];

    $totalRows = count($dataArray);
    for ($i = 0; $i < $totalRows; $i++) {

        if (empty(array_filter($dataArray[$i]))) {
            continue;
        }

        $rowString = implode('', $dataArray[$i]);

        if (str_contains($rowString, 'DATE:')) {
            if (empty($getDateRow)) {
                $getDateRow = $dataArray[$i];

                continue;
            }
        }

        if (str_contains($rowString, 'PRODUCT:')) {
            if (empty($getProduceRow)) {
                $getProduceRow = $dataArray[$i];

                continue;
            }
        }

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

    $date = '';
    foreach ($getDateRow as $item) {
        $item = trim($item);
        if (! empty($item)) {
            $date = preg_replace('/^DATE:\s*/i', '', $item);
            if (! empty($date)) {
                break;
            }
        }
    }

    $product = '';
    foreach ($getProduceRow as $item) {
        $item = trim($item);
        if (! empty($item)) {
            $product = preg_replace('/^PRODUCT:\s*/i', '', $item);
            if (! empty($product)) {
                break;
            }
        }
    }

    $allItems = [];
    foreach ($tables as $table) {
        foreach ($table['items'] as $item) {
            $allItems[] = $item;
        }
    }

    $result = [
        'date' => $date,
        'product' => $product,
        'items' => $allItems,
    ];

    echo 'Date: '.$date."\n";
    echo 'Product: '.$product."\n";
    echo "\n=== Collected Data (All Items Merged) ===\n\n";
    print_r($result);

} catch (Throwable $e) {
    echo 'Error: '.$e->getMessage()."\n";
    echo "Stack trace:\n".$e->getTraceAsString()."\n";
}
