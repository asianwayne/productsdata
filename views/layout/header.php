<?php
// Helpers available in every view
function e(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function asset(string $path): string {
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    return $base . '/public/' . ltrim($path, '/');
}

$_c = $_GET['c'] ?? 'product';
$_a = $_GET['a'] ?? 'index';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>产品数据库</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
  <div class="container-fluid px-3">
    <a class="navbar-brand fw-bold" href="?c=product&a=index">
      <i class="bi bi-database-fill me-1"></i>产品数据库
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto gap-1">
        <li class="nav-item">
          <a class="nav-link <?= ($_c==='product'&&$_a==='index') ? 'active' : '' ?>" href="?c=product&a=index">
            <i class="bi bi-list-ul me-1"></i>产品列表
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($_c==='product'&&$_a==='create') ? 'active' : '' ?>" href="?c=product&a=create">
            <i class="bi bi-plus-circle me-1"></i>新增产品
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($_c==='import') ? 'active' : '' ?>" href="?c=import&a=index">
            <i class="bi bi-upload me-1"></i>导入 CSV
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($_c==='match') ? 'active' : '' ?>" href="?c=match&a=index">
            <i class="bi bi-search me-1"></i>OEM 匹配
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($_c==='category') ? 'active' : '' ?>" href="?c=category&a=index">
            <i class="bi bi-tags me-1"></i>分类管理
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container-fluid px-3 py-3">

<?php
// Flash messages
$msgs = [
    'created'     => ['success', '<i class="bi bi-check-circle me-1"></i>创建成功'],
    'updated'     => ['success', '<i class="bi bi-check-circle me-1"></i>更新成功'],
    'deleted'     => ['warning', '<i class="bi bi-trash me-1"></i>已删除'],
    'deleted_all' => ['warning', '<i class="bi bi-trash me-1"></i>所有产品数据已清空'],
];
if (isset($_GET['msg'], $msgs[$_GET['msg']])):
    [$type, $text] = $msgs[$_GET['msg']];
?>
<div class="alert alert-<?= $type ?> alert-dismissible fade show py-2" role="alert">
  <?= $text ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
