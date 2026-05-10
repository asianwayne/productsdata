<?php
require_once ROOT . '/core/Database.php';
require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Model.php';
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

        [$imported, $skipped, $errors, $successRows] = $this->importCsv($tmp);

        @unlink($tmp);

        $this->render('import/index', [
            'columns'     => $this->columns,
            'imported'    => $imported,
            'skipped'     => $skipped,
            'errors'      => $errors,
            'successRows' => $successRows,
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

    private function importCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) return [0, 0, ['无法读取文件'], []];

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return [0, 0, ['CSV 文件似乎是空的'], []];
        }
        $headers = array_map('trim', $headers);

        $imported = 0;
        $skipped  = 0;
        $errors   = [];
        $successRows = [];

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
                $newOem = $data['oem_number'] ?? '';

                if ($tqbCode !== '') {
                    $existing = Product::findByTqbCode($tqbCode);
                    if ($existing) {
                        $existingOem = $existing['oem_number'] ?? '';
                        
                        $oemParts = array_filter(array_map('trim', explode('/', $existingOem)), fn($v) => $v !== '');
                        $newOemParts = array_filter(array_map('trim', explode('/', $newOem)), fn($v) => $v !== '');
                        
                        // Check if all new OEMs are already in the existing OEMs
                        $isSubset = empty(array_diff($newOemParts, $oemParts));
                        
                        if ($isSubset) {
                            $skipped++;
                            continue;
                        } else {
                            $mergedOem = array_unique(array_merge($oemParts, $newOemParts));
                            $data['oem_number'] = implode('/', $mergedOem);

                            $updated = Product::update($existing['id'], $data);
                            if ($updated) {
                                $successRows[] = $data;
                                $imported++;
                            } else {
                                $skipped++;
                            }
                            continue;
                        }
                    }
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
        return [$imported, $skipped, $errors, array_slice($successRows, -20)];
    }

    private function renderWithError(string $message): void
    {
        $this->render('import/index', [
            'columns' => $this->columns,
            'error'   => $message,
        ]);
    }
}
