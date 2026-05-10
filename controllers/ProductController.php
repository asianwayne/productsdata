<?php
require_once ROOT . '/core/Database.php';
require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Model.php';
require_once ROOT . '/models/Product.php';
require_once ROOT . '/models/Category.php';

class ProductController extends Controller
{
    private array $columns;

    public function __construct()
    {
        $this->columns = require ROOT . '/config/columns.php';
    }

    // ?? List ??????????????????????????????????????????????????????????????????

    public function index(): void
    {
        $filters = $_GET['f'] ?? [];
        $q       = trim($_GET['q'] ?? '');
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $result  = Product::search($filters, $page, 50, $q);

        $this->render('products/index', [
            'columns'    => $this->columns,
            'categories' => Category::all([], ['name' => 'ASC']),
            'filters'    => $filters,
            'q'          => $q,
            'rows'       => $result['rows'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'perPage'    => $result['perPage'],
            'totalPages' => $result['totalPages'],
        ]);
    }

    // ?? Detail ????????????????????????????????????????????????????????????????

    public function show(): void
    {
        $product = $this->findOrRedirect();
        $this->render('products/show', [
            'product' => $product,
            'columns' => $this->columns,
        ]);
    }

    // ?? Create ????????????????????????????????????????????????????????????????

    public function create(): void
    {
        $this->render('products/form', [
            'product'    => [],
            'columns'    => $this->columns,
            'categories' => Category::all([], ['name' => 'ASC']),
            'isEdit'     => false,
            'title'      => '新增',
        ]);
    }

    public function store(): void
    {
        $this->requirePost();
        $data = $_POST['product'] ?? [];
        $data['category_id'] = $this->resolveCategory($data, $_POST['new_category_name'] ?? '');

        Product::create($data);
        $this->redirect($this->url(['c' => 'product', 'a' => 'index', 'msg' => 'created']));
    }

    // ?? Edit ??????????????????????????????????????????????????????????????????

    public function edit(): void
    {
        $product = $this->findOrRedirect();
        $this->render('products/form', [
            'product'    => $product,
            'columns'    => $this->columns,
            'categories' => Category::all([], ['name' => 'ASC']),
            'isEdit'     => true,
            'title'      => '编辑',
        ]);
    }

    public function update(): void
    {
        $this->requirePost();
        $id = (int) ($_POST['id'] ?? 0);
        $data = $_POST['product'] ?? [];
        $data['category_id'] = $this->resolveCategory($data, $_POST['new_category_name'] ?? '');

        Product::update($id, $data);
        $this->redirect($this->url(['c' => 'product', 'a' => 'show', 'id' => $id, 'msg' => 'updated']));
    }

    // ?? Delete ????????????????????????????????????????????????????????????????

    public function delete(): void
    {
        $this->requirePost();
        $id = (int) ($_POST['id'] ?? 0);
        Product::delete($id);
        $this->redirect($this->url(['c' => 'product', 'a' => 'index', 'msg' => 'deleted']));
    }

    public function deleteAll(): void
    {
        $this->requirePost();
        Product::truncate();
        $this->redirect($this->url(['c' => 'product', 'a' => 'index', 'msg' => 'deleted_all']));
    }

    // ?? Export CSV ????????????????????????????????????????????????????????????

    public function export(): void
    {
        $filters = $_GET['f'] ?? [];
        $q       = trim($_GET['q'] ?? '');
        $clean   = array_filter($filters, fn($v) => trim((string) $v) !== '');
        $rows    = Product::all($clean, ['id' => 'ASC'], 0, 0, $q);

        $filename = 'products_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM so Excel opens without garbled characters
        fwrite($out, "\xEF\xBB\xBF");

        // Header row (Chinese labels)
        fputcsv($out, array_column($this->columns, 'label'));

        // Data rows
        foreach ($rows as $row) {
            $line = [];
            foreach ($this->columns as $col) {
                $line[] = $row[$col['field']] ?? '';
            }
            fputcsv($out, $line);
        }
        fclose($out);
        exit;
    }

    // ?? Private helpers ???????????????????????????????????????????????????????

    private function findOrRedirect(): array
    {
        $id      = (int) ($_GET['id'] ?? 0);
        $product = Product::find($id);
        if (!$product) {
            $this->redirect($this->url(['c' => 'product', 'a' => 'index']));
        }
        return $product;
    }

    private function resolveCategory(array $data, string $newName): ?int
    {
        $catId = $data['category_id'] ?? '';
        if ($catId === 'NEW' && trim($newName) !== '') {
            return Category::create(['name' => trim($newName)]);
        }
        return (is_numeric($catId) && $catId > 0) ? (int)$catId : null;
    }

    private function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($this->url(['c' => 'product', 'a' => 'index']));
        }
    }
}
