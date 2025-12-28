<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Recipe;
use Illuminate\Database\Capsule\Manager as DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Psr\Http\Message\UploadedFileInterface;

class ImportRecipeService
{
    /**
     * @param  array  $files  Normalized uploaded files
     * @return array ['results' => array, 'failed_ingredients' => array]
     */
    public function processFiles(array $files): array
    {
        $results = [];
        $allFailedIngredients = [];

        $this->processFilesRecursive($files, $results, $allFailedIngredients);

        return [
            'results' => $results,
            'failed_ingredients' => $allFailedIngredients,
        ];
    }


    private function processFilesRecursive($files, array &$results, array &$allFailedIngredients): void
    {
        foreach ($files as $fileKey => $file) {
            if (is_array($file)) {
                $this->processFilesRecursive($file, $results, $allFailedIngredients);
            } elseif ($file instanceof UploadedFileInterface) {
                $result = $this->processFile($file);
                $results[] = $result;
                if (! empty($result['failed_ingredients'])) {
                    $allFailedIngredients = array_merge($allFailedIngredients, $result['failed_ingredients']);
                }
            }
        }
    }


    public function processFile(UploadedFileInterface $file): array
    {
        try {
            $extractedData = $this->extractData($file);

            $date = $extractedData['date'] ?? '';
            $product = $extractedData['product'] ?? '';
            $items = $extractedData['items'] ?? [];

            if (empty($product)) {
                return [
                    'file' => $file->getClientFilename(),
                    'status' => 'error',
                    'message' => 'Product name not found in file',
                    'failed_ingredients' => [],
                ];
            }

            if (empty($date)) {
                return [
                    'file' => $file->getClientFilename(),
                    'status' => 'error',
                    'message' => 'Date not found in file',
                    'failed_ingredients' => [],
                ];
            }

            return DB::connection()->transaction(function () use ($product, $date, $items, $file) {
                $recipe = Recipe::firstOrCreate(
                    [
                        'name' => $product,
                        'date' => $date,
                    ],
                    [
                        'name' => $product,
                        'date' => $date,
                    ]
                );

                $ingredientsData = [];
                $failedIngredients = [];

                foreach ($items as $itemData) {
                    $itemName = trim($itemData['item'] ?? '');
                    $quantity = trim($itemData['qty'] ?? '0');
                    $batchNumber = trim($itemData['batch_number'] ?? '');

                    if (empty($itemName)) {
                        continue;
                    }

                    $quantity = is_numeric($quantity) ? (float) $quantity : 0.0;

                    $item = Item::where('short_name', $itemName)->first();

                    if (! $item) {
                        $item = Item::create([
                            'short_name' => $itemName,
                            'name' => $itemName,
                            'balance' => 0.0,
                            'unit' => 'pcs',
                        ]);

                        $failedIngredients[] = [
                            'short_name' => $itemName,
                            'name' => $itemName,
                            'quantity' => $quantity,
                            'batch_number' => $batchNumber,
                            'reason' => 'Item not found, created automatically',
                        ];
                    }

                    $ingredientsData[] = [
                        'item_id' => $item->id,
                        'quantity' => $quantity,
                    ];
                }

                if (! empty($ingredientsData)) {
                    $recipe->ingredients()->createMany($ingredientsData);
                }

                return [
                    'file' => $file->getClientFilename(),
                    'status' => 'success',
                    'recipe_id' => $recipe->id,
                    'recipe_name' => $recipe->name,
                    'date' => $recipe->date,
                    'ingredients_count' => count($ingredientsData),
                    'failed_ingredients' => $failedIngredients,
                ];
            });
        } catch (\Throwable $e) {
            return [
                'file' => $file->getClientFilename(),
                'status' => 'error',
                'message' => $e->getMessage(),
                'failed_ingredients' => [],
            ];
        }
    }

    /**
     * @return array ['date' => string, 'product' => string, 'items' => array]
     */
    public function extractData(UploadedFileInterface $file): array
    {
        $tempPath = $this->saveTemporaryFile($file);

        try {
            $sheet = IOFactory::load($tempPath);
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

            return [
                'date' => $date,
                'product' => $product,
                'items' => $allItems,
            ];
        } finally {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    private function saveTemporaryFile(UploadedFileInterface $file): string
    {
        $tempPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('excel_', true).'.xlsx';

        $stream = $file->getStream();
        $handle = fopen($tempPath, 'w');

        while (! $stream->eof()) {
            fwrite($handle, $stream->read(8192));
        }

        fclose($handle);

        return $tempPath;
    }
}
