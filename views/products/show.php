<?php
// Group columns by tab for section display
$sections = [];
foreach ($columns as $col) {
    $sections[$col['tab']][] = $col;
}

$imgRel = trim((string)($product['image_path'] ?? ''));
$base   = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$imgUrl = $imgRel !== '' ? ($base . '/' . ltrim($imgRel, '/')) : '';
?>

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div class="d-flex align-items-center">
    <a href="?c=product&a=index" class="btn btn-sm btn-outline-secondary me-2">
      <i class="bi bi-arrow-left"></i>
    </a>
    <div>
      <h5 class="mb-0 fw-semibold">
        <i class="bi bi-box-seam text-primary me-1"></i><?= e($product['name'] ?? '') ?>
      </h5>
      <small class="text-muted"><?= e($product['tqb_code'] ?? '') ?></small>
    </div>
  </div>
  <div class="d-flex gap-2">
    <a href="?c=product&a=edit&id=<?= (int)$product['id'] ?>" class="btn btn-primary btn-sm">
      <i class="bi bi-pencil me-1"></i>编辑
    </a>
    <form method="POST" action="?c=product&a=delete" class="d-inline">
      <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
      <button type="submit" class="btn btn-outline-danger btn-sm"
              data-confirm="确定删除「<?= e($product['tqb_code'] ?? $product['name'] ?? '') ?>」吗？">
        <i class="bi bi-trash me-1"></i>删除
      </button>
    </form>
  </div>
</div>

<?php if ($imgUrl !== ''): ?>
<div class="card border-0 shadow-sm mb-3">
  <div class="card-header bg-light py-2 fw-medium small">
    <i class="bi bi-image text-primary me-1"></i>产品图片
  </div>
  <div class="card-body py-3 text-center">
    <a href="<?= e($imgUrl) ?>" target="_blank" rel="noopener">
      <img src="<?= e($imgUrl) ?>" alt="产品图片"
           class="img-fluid rounded border"
           style="max-height:360px;object-fit:contain;">
    </a>
  </div>
</div>
<?php endif; ?>

<?php foreach ($sections as $sectionName => $cols): ?>
<div class="card border-0 shadow-sm mb-3">
  <div class="card-header bg-light py-2 fw-medium small">
    <?= e($sectionName) ?>
  </div>
  <div class="card-body py-2">
    <div class="row g-2">
      <?php foreach ($cols as $col): ?>
      <?php $val = $product[$col['field']] ?? ''; ?>
      <div class="col-md-3 col-sm-4 col-6">
        <div class="text-muted small"><?= e($col['label']) ?></div>
        <div class="fw-medium">
          <?php if ($val === ''): ?>
            <span class="text-muted">—</span>
          <?php elseif ($col['field'] === 'stock_status'): ?>
            <?php $cls = match($val) { '有货' => 'success', '缺货' => 'danger', '预订' => 'warning', default => 'secondary' }; ?>
            <span class="badge bg-<?= $cls ?>"><?= e($val) ?></span>
          <?php elseif ($col['field'] === 'warehouse_a'): ?>
            <span class="badge bg-<?= $val === '可出' ? 'success' : 'secondary' ?>"><?= e($val) ?></span>
          <?php else: ?>
            <?= e($val) ?>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endforeach; ?>

<div class="text-muted small mt-2">
  创建时间: <?= e($product['created_at'] ?? '') ?>
  &nbsp;|&nbsp;
  更新时间: <?= e($product['updated_at'] ?? '') ?>
</div>
