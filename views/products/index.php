<?php
// Build export URL preserving current filters and search
$exportUrl = '?' . http_build_query(array_merge(
    ['c' => 'product', 'a' => 'export'],
    empty($filters) ? [] : ['f' => $filters],
    empty($q) ? [] : ['q' => $q]
));

// Columns shown in table
$listCols = array_values(array_filter($columns, fn($c) => $c['list']));
// Filterable columns
$filterCols = array_values(array_filter($columns, fn($c) => $c['filterable']));

// Current filter count
$activeFilters = count(array_filter($filters ?? [], fn($v) => trim((string)$v) !== ''));

// Base URL for product image thumbnails
$imgBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
?>

<!-- ── Page header ──────────────────────────────────────────────── -->
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <h5 class="mb-0 fw-semibold">
    <i class="bi bi-box-seam text-primary me-1"></i>产品列表
    <span class="badge bg-secondary ms-1"><?= number_format($total) ?></span>
  </h5>
  <div class="d-flex gap-2">
    <form method="POST" action="?c=product&a=deleteAll" class="m-0">
      <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('确定要清空所有产品数据吗？此操作不可恢复！');">
        <i class="bi bi-trash me-1"></i>清空数据
      </button>
    </form>
    <a href="<?= e($exportUrl) ?>" class="btn btn-outline-success btn-sm">
      <i class="bi bi-download me-1"></i>导出 CSV<?= $activeFilters ? '（筛选结果）' : '（全部）' ?>
    </a>
    <a href="?c=product&a=create" class="btn btn-primary btn-sm">
      <i class="bi bi-plus-lg me-1"></i>新增产品
    </a>
  </div>
</div>

<!-- ── Global Search ──────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body py-2">
    <form method="GET" action="" class="m-0">
      <input type="hidden" name="c" value="product">
      <input type="hidden" name="a" value="index">
      <?php foreach ($filters as $k => $v): if (trim((string)$v) !== ''): ?>
        <input type="hidden" name="f[<?= e($k) ?>]" value="<?= e($v) ?>">
      <?php endif; endforeach; ?>
      <div class="input-group">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="全局搜索 (可搜索任何字段)..." value="<?= e($q ?? '') ?>">
        <button class="btn btn-primary px-4" type="submit">搜索</button>
        <?php if (!empty($q)): ?>
          <a href="?<?= http_build_query(array_merge(['c'=>'product','a'=>'index'], !empty($filters) ? ['f'=>$filters] : [])) ?>" class="btn btn-outline-secondary">清除</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- ── Filter panel ─────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-3">
  <div class="card-header bg-white d-flex align-items-center justify-content-between py-2"
       role="button" data-bs-toggle="collapse" data-bs-target="#filterPanel">
    <span class="fw-medium text-primary">
      <i class="bi bi-funnel me-1"></i>筛选条件
      <?php if ($activeFilters): ?>
        <span class="badge bg-primary ms-1"><?= $activeFilters ?></span>
      <?php endif; ?>
    </span>
    <i class="bi bi-chevron-down text-muted small"></i>
  </div>
  <div id="filterPanel" class="collapse <?= $activeFilters ? 'show' : '' ?>">
    <div class="card-body py-3">
      <form method="GET" action="">
        <input type="hidden" name="c" value="product">
        <input type="hidden" name="a" value="index">
        <?php if (!empty($q)): ?>
        <input type="hidden" name="q" value="<?= e($q) ?>">
        <?php endif; ?>
        <div class="row g-2">
          <!-- Category Filter -->
          <div class="col-md-3 col-sm-4 col-6">
            <label class="form-label form-label-sm mb-1">所属分类</label>
            <select name="f[category_id]" class="form-select form-select-sm">
              <option value="">-- 全部 --</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                  <?= e($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <?php foreach ($filterCols as $col): ?>
          <div class="col-md-3 col-sm-4 col-6">
            <label class="form-label form-label-sm mb-1"><?= e($col['label']) ?></label>
            <input type="text" class="form-control form-control-sm"
                   name="f[<?= e($col['field']) ?>]"
                   value="<?= e($filters[$col['field']] ?? '') ?>"
                   placeholder="搜索…">
          </div>
          <?php endforeach; ?>
        </div>
        <div class="d-flex gap-2 mt-3">
          <button type="submit" class="btn btn-primary btn-sm px-3">
            <i class="bi bi-search me-1"></i>搜索
          </button>
          <a href="?c=product&a=index" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-x-circle me-1"></i>清除
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ── Data table ───────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover table-sm align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-3">#</th>
          <th>图片</th>
          <th>分类</th>
          <?php foreach ($listCols as $col): ?>
          <th><?= e($col['label']) ?></th>
          <?php endforeach; ?>
          <th class="text-end pe-3">操作</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
        <tr>
          <td colspan="<?= count($listCols) + 4 ?>" class="text-center text-muted py-5">
            <i class="bi bi-inbox fs-3 d-block mb-2 opacity-50"></i>暂无数据
          </td>
        </tr>
        <?php else: ?>
        <?php foreach ($rows as $row): ?>
        <tr>
          <td class="ps-3 text-muted small"><?= e($row['id']) ?></td>
          <td>
            <?php $thumb = trim((string)($row['image_path'] ?? '')); ?>
            <?php if ($thumb !== ''): ?>
              <a href="<?= e($imgBase . '/' . ltrim($thumb, '/')) ?>" target="_blank" rel="noopener" title="查看大图">
                <img src="<?= e($imgBase . '/' . ltrim($thumb, '/')) ?>"
                     alt="" class="product-thumb rounded border"
                     style="width:48px;height:48px;object-fit:cover;">
              </a>
            <?php else: ?>
              <span class="text-muted small d-inline-flex align-items-center justify-content-center rounded border bg-light"
                    style="width:48px;height:48px;">
                <i class="bi bi-image opacity-50"></i>
              </span>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!empty($row['category_name'])): ?>
              <span class="badge bg-info text-dark"><?= e($row['category_name']) ?></span>
            <?php else: ?>
              <span class="text-muted small">无</span>
            <?php endif; ?>
          </td>
          <?php foreach ($listCols as $col): ?>
          <td><?php
            $val = $row[$col['field']] ?? '';
            if ($col['field'] === 'stock_status' && $val !== '') {
                $cls = match($val) {
                    '有货'  => 'success',
                    '缺货'  => 'danger',
                    '预订'  => 'warning',
                    default => 'secondary',
                };
                echo '<span class="badge bg-' . $cls . '">' . e($val) . '</span>';
            } elseif ($col['field'] === 'warehouse_a' && $val !== '') {
                $cls = $val === '可出' ? 'success' : 'secondary';
                echo '<span class="badge bg-' . $cls . '">' . e($val) . '</span>';
            } else {
                echo e($val);
            }
          ?></td>
          <?php endforeach; ?>
          <td class="text-end pe-3 text-nowrap">
            <a href="?c=product&a=show&id=<?= $row['id'] ?>" class="btn btn-xs btn-outline-secondary" title="查看">
              <i class="bi bi-eye"></i>
            </a>
            <a href="?c=product&a=edit&id=<?= $row['id'] ?>" class="btn btn-xs btn-outline-primary ms-1" title="编辑">
              <i class="bi bi-pencil"></i>
            </a>
            <form method="POST" action="?c=product&a=delete" class="d-inline ms-1">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="submit" class="btn btn-xs btn-outline-danger"
                      data-confirm="确定要删除「<?= e($row['tqb_code'] ?: $row['name']) ?>」吗？" title="删除">
                <i class="bi bi-trash"></i>
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- ── Pagination ─────────────────────────────────────────────── -->
  <?php if ($totalPages > 1): ?>
  <div class="card-footer bg-white border-top-0 d-flex align-items-center justify-content-between py-2 flex-wrap gap-2">
    <small class="text-muted">
      共 <?= number_format($total) ?> 条，第 <?= $page ?>/<?= $totalPages ?> 页
    </small>
    <nav>
      <ul class="pagination pagination-sm mb-0">
        <?php
        $baseParams = ['c' => 'product', 'a' => 'index'];
        if (!empty($filters)) $baseParams['f'] = $filters;
        if (!empty($q)) $baseParams['q'] = $q;
        $prevDisabled = $page <= 1;
        $nextDisabled = $page >= $totalPages;
        ?>
        <li class="page-item <?= $prevDisabled ? 'disabled' : '' ?>">
          <a class="page-link" href="?<?= http_build_query(array_merge($baseParams, ['page' => $page - 1])) ?>">‹</a>
        </li>
        <?php
        $start = max(1, $page - 2);
        $end   = min($totalPages, $page + 2);
        if ($start > 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif;
        for ($p = $start; $p <= $end; $p++):
        ?>
        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
          <a class="page-link" href="?<?= http_build_query(array_merge($baseParams, ['page' => $p])) ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
        <?php if ($end < $totalPages): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
        <li class="page-item <?= $nextDisabled ? 'disabled' : '' ?>">
          <a class="page-link" href="?<?= http_build_query(array_merge($baseParams, ['page' => $page + 1])) ?>">›</a>
        </li>
      </ul>
    </nav>
  </div>
  <?php endif; ?>
</div>
