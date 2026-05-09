<?php
/**
 * Front Controller – single entry point for the entire application.
 *
 * Routing:  ?c=<controller>&a=<action>
 * Examples: ?c=product&a=index   → ProductController::index()
 *           ?c=import&a=upload   → ImportController::upload()
 */

declare(strict_types=1);

// Ensure UTF-8 output for all responses
header('Content-Type: text/html; charset=utf-8');

define('ROOT', __DIR__);

// Global exception handler – shows a friendly error instead of a blank page
set_exception_handler(function (Throwable $e): void {
    http_response_code(500);
    $msg   = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    $isDb  = ($e instanceof PDOException)
          || str_contains($e->getMessage(), 'SQLSTATE')
          || str_contains($e->getMessage(), 'refused')
          || str_contains($e->getMessage(), "Can't connect");
    $title = $isDb ? '数据库连接失败' : '系统错误';
    $bs    = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css';

    echo "<!DOCTYPE html><html lang='zh-CN'><head><meta charset='UTF-8'><title>错误</title>";
    echo "<link rel='stylesheet' href='{$bs}'></head><body class='bg-light'>";
    echo "<div class='container py-5' style='max-width:640px'>";
    echo "<div class='card border-danger shadow-sm'>";
    echo "<div class='card-header bg-danger text-white fw-bold'>⚠ {$title}</div>";
    echo "<div class='card-body'><p class='mb-2'>{$msg}</p>";
    if ($isDb) {
        echo "<hr><p class='fw-semibold mb-1'>请按以下步骤排查：</p><ol class='small'>";
        echo "<li>确认 <strong>MySQL 已启动</strong>（XAMPP / PHPStudy → 点击 Start MySQL）</li>";
        echo "<li>打开 <code>config/database.php</code>，确认用户名和密码正确</li>";
        echo "<li>在 MySQL 客户端中执行 <code>setup.sql</code> 建库建表</li>";
        echo "<li>刷新此页面</li></ol>";
    }
    echo "</div></div></div></body></html>";
    exit;
});

// Simple PSR-0-style autoloader for core/, models/, controllers/
spl_autoload_register(function (string $class): void {
    foreach ([ROOT . '/core/', ROOT . '/models/', ROOT . '/controllers/'] as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Sanitize routing inputs – only letters allowed
$c = preg_replace('/[^a-zA-Z]/', '', $_GET['c'] ?? 'product');
$a = preg_replace('/[^a-zA-Z]/', '', $_GET['a'] ?? 'index');

$controllerClass = ucfirst(strtolower($c)) . 'Controller';
$controllerFile  = ROOT . '/controllers/' . $controllerClass . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(404);
    exit('404 – Controller not found');
}

require_once $controllerFile;

if (!class_exists($controllerClass) || !method_exists($controllerClass, $a)) {
    http_response_code(404);
    exit('404 – Action not found');
}

(new $controllerClass())->$a();
