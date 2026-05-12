<?php
require_once ROOT . '/core/Database.php';
require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Model.php';
require_once ROOT . '/core/ImageHelper.php';
require_once ROOT . '/models/Product.php';

class ImportController extends Controller
{
    private array $columns;

    public function __construct()
    {
        $this->columns = require ROOT . '/config/columns.php';
    }

    // ?? Upload form ???????????????????????????????????????????????????????????

    public function index(): void
    {
        $this->render('import/index', ['columns' => $this->columns]);
    }

    // ?? Process upload ????????????????????????????????????????????????????????

    public function upload(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($this->url(['c' => 'import', 'a' => 'index']));
        }

        $file = $_FILES['csv_file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->renderWithError('??????????????????? PHP ????: ' . ini_get('upload_max_filesize') . '?');
            return;
        }

        if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'csv') {
            $this->renderWithError('??? .csv ?????');
            return;
        }

        $encoding = $_POST['encoding'] ?? 'auto';
        $content  = file_get_contents($file['tmp_name']);

        // Convert to UTF-8
        $content = $this->toUtf8($content, $encoding);
        // Strip UTF-8 BOM if present
        $content = ltrim($content, "\xEF\xBB\xBF");

        // Write decoded content to a temp file for fgetcsv
        $tmp = tempnam(sys_get_temp_dir(), 'pdb_');
        file_put_contents($tmp, $content);

        // Pre-process the optional bundled product images.
        // The map is keyed by TQB code (case-insensitive) -> saved relative path.
        [$imageMap, $imageErrors, $imageSavedCount] = $this->processImages($_FILES['images'] ?? null);

        [$imported, $skipped, $errors, $successRows, $imageMatched, $imageMissing]
            = $this->importCsv($tmp, $imageMap);

        @unlink($tmp);

        // Clean up any images that did NOT match a TQB code in the CSV
        // (they were saved to disk during validation; remove the orphans).
        foreach ($imageMap as $key => $rel) {
            if (!isset($imageMatched[$key])) {
                ImageHelper::delete($rel);
            }
        }

        $this->render('import/index', [
            'columns'         => $this->columns,
            'imported'        => $imported,
            'skipped'         => $skipped,
            'errors'          => array_merge($errors, $imageErrors),
            'successRows'     => $successRows,
            'imageSavedCount' => $imageSavedCount,
            'imageMatchCount' => count($imageMatched),
            'imageMissing'    => $imageMissing,
        ]);
    }

    // ?? Private helpers ???????????????????????????????????????????????????????

    private function toUtf8(string $content, string $encoding): string
    {
        if ($encoding === 'auto') {
            $detected = mb_detect_encoding($content, ['UTF-8', 'GBK', 'GB2312', 'Big5'], true);
            $from = ($detected && $detected !== 'UTF-8') ? $detected : null;
        } elseif (in_array(strtoupper($encoding), ['GBK', 'GB2312'], true)) {
            $from = 'GBK';
        } else {
            $from = null; // already UTF-8
        }
        return $from ? mb_convert_encoding($content, 'UTF-8', $from) : $content;
    }

    /**
     * Save uploaded images (multi-file input "images[]") to the products
     * upload directory. Returns:
     *   [map, errors, savedCount]
     * where `map` is keyed by the lower-cased TQB code derived from the
     * filename basename (everything before the final ".").
     */
    private function processImages(?array $filesField): array
    {
        if (!$filesField) return [[], [], 0];

        $files = ImageHelper::normalizeMulti($filesField);
        if (empty($files)) return [[], [], 0];

        $map     = [];
        $errors  = [];
        $saved   = 0;

        foreach ($files as $f) {
            $name = (string)($f['name'] ?? '');
            // Browsers using <input webkitdirectory> send "subdir/file.jpg"
            // — keep only the file's base name.
            $base = basename(str_replace('\\', '/', $name));
            $tqb  = pathinfo($base, PATHINFO_FILENAME); // strip extension
            $tqb  = trim($tqb);
            if ($tqb === '') {
                $errors[] = '跳过未命名图片: ' . $name;
                continue;
            }

            $err = ImageHelper::validate($f);
            if ($err !== null) {
                $errors[] = '图片 ' . $base . ' 跳过: ' . $err;
                continue;
            }

            try {
                $rel = ImageHelper::save($f, $tqb, true);
                $key = mb_strtolower($tqb);
                // If the same TQB appears twice, the later upload wins;
                // delete the previous saved file to avoid orphans.
                if (isset($map[$key])) {
                    ImageHelper::delete($map[$key]);
                }
                $map[$key] = $rel;
                $saved++;
            } catch (Throwable $e) {
                $errors[] = '图片 ' . $base . ' 保存失败: ' . $e->getMessage();
            }
        }

        return [$map, $errors, $saved];
    }

    /**
     * Import CSV rows. If $imageMap is non-empty, each row whose TQB code
     * matches a key in the map gets its `image_path` set to the matched
     * relative path. The set of matched keys is returned to the caller
     * so unmatched images can be cleaned up.
     */
    private function importCsv(string $filePath, array $imageMap = []): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) return [0, 0, ['无法读取文件'], [], [], []];

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return [0, 0, ['CSV 文件似乎是空的'], [], [], []];
        }
        $headers = array_map('trim', $headers);

        $imported = 0;
        $skipped  = 0;
        $errors   = [];
        $successRows  = [];
        $imageMatched = []; // map-key => true (TQB codes that consumed an image)
        $imageMissing = []; // TQB codes in CSV that had no matching image

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            set_time_limit(300); // allow up to 5 minutes for large files

            while (($row = fgetcsv($handle)) !== false) {
                // Skip rows that don't match header count
                if (count($row) < count($headers)) {
                    $row = array_pad($row, count($headers), '');
                }
                $csvRow = array_combine($headers, array_slice($row, 0, count($headers)));
                $data   = Product::fromCsvRow($csvRow, $this->columns);

                // Skip completely empty rows
                if (empty(array_filter($data, fn($v) => $v !== ''))) {
                    $skipped++;
                    continue;
                }

                $tqbCode = $data['tqb_code'] ?? '';
                $newOem  = $data['oem_number'] ?? '';

                // Resolve a possible image for this TQB code.
                $imgKey   = $tqbCode !== '' ? mb_strtolower($tqbCode) : '';
                $matchRel = ($imgKey !== '' && isset($imageMap[$imgKey])) ? $imageMap[$imgKey] : null;

                if ($tqbCode !== '') {
                    $existing = Product::findByTqbCode($tqbCode);
                    if ($existing) {
                        $existingOem = $existing['oem_number'] ?? '';

                        $oemParts    = array_filter(array_map('trim', explode('/', $existingOem)), fn($v) => $v !== '');
                        $newOemParts = array_filter(array_map('trim', explode('/', $newOem)),     fn($v) => $v !== '');

                        // OEM is a subset of what's already stored AND there's no
                        // new image to attach -> safe to skip this row entirely.
                        $isSubset = empty(array_diff($newOemParts, $oemParts));

                        if ($isSubset && $matchRel === null) {
                            $skipped++;
                            if ($imgKey !== '' && !isset($imageMap[$imgKey])) {
                                $imageMissing[$tqbCode] = true;
                            }
                            continue;
                        }

                        if (!$isSubset) {
                            $mergedOem = array_unique(array_merge($oemParts, $newOemParts));
                            $data['oem_number'] = implode('/', $mergedOem);
                        } else {
                            // Avoid wiping the merged-OEM with a strict-subset value.
                            $data['oem_number'] = $existingOem;
                        }

                        if ($matchRel !== null) {
                            // Replace any pre-existing image with the new one.
                            ImageHelper::delete($existing['image_path'] ?? null);
                            $data['image_path'] = $matchRel;
                            $imageMatched[$imgKey] = true;
                        }

                        $updated = Product::update($existing['id'], $data);
                        if ($updated) {
                            $successRows[] = $data + ['id' => $existing['id']];
                            $imported++;
                        } else {
                            $skipped++;
                        }

                        if ($imgKey !== '' && $matchRel === null) {
                            $imageMissing[$tqbCode] = true;
                        }
                        continue;
                    }
                }

                // New product
                if ($matchRel !== null) {
                    $data['image_path'] = $matchRel;
                    $imageMatched[$imgKey] = true;
                } elseif ($tqbCode !== '') {
                    $imageMissing[$tqbCode] = true;
                }

                $newId = Product::create($data);
                if ($newId) {
                    $data['id'] = $newId;
                    $successRows[] = $data;
                    $imported++;
                }
            }
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            $errors[] = '导入时出错: ' . $e->getMessage();
        }

        fclose($handle);
        return [
            $imported,
            $skipped,
            $errors,
            array_slice($successRows, -20),
            $imageMatched,
            array_keys($imageMissing),
        ];
    }

    private function renderWithError(string $message): void
    {
        $this->render('import/index', [
            'columns' => $this->columns,
            'error'   => $message,
        ]);
    }
}
