<!-- ── Page header ──────────────────────────────────────────────── -->
<div class="d-flex align-items-center mb-3">
  <h5 class="mb-0 fw-semibold">
    <i class="bi bi-upload text-primary me-1"></i>导入 CSV
  </h5>
</div>

<div class="row g-3">

  <!-- Upload form -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-medium py-2">上传文件</div>
      <div class="card-body">

        <?php if (isset($error)): ?>
        <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle me-1"></i><?= e($error) ?></div>
        <?php endif; ?>

        <?php if (isset($imported)): ?>
        <div class="alert alert-success py-2 small">
          <i class="bi bi-check-circle me-1"></i>
          导入完成：<strong><?= (int)$imported ?></strong> 条成功，<strong><?= (int)$skipped ?></strong> 条跳过
        </div>
        <?php if (!empty($errors)): ?>
        <div class="alert alert-warning py-2 small">
          <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <form method="POST" action="?c=import&a=upload" enctype="multipart/form-data">

          <div class="mb-3">
            <label class="form-label fw-medium">选择 CSV 文件 <span class="text-danger">*</span></label>
            <input type="file" class="form-control" name="csv_file" accept=".csv" required>
            <div class="form-text">支持最大 <?= ini_get('upload_max_filesize') ?> 的文件</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">文件编码</label>
            <select class="form-select form-select-sm" name="encoding">
              <option value="auto" selected>自动检测（推荐）</option>
              <option value="gbk">GBK / GB2312（Excel 默认保存格式）</option>
              <option value="utf8">UTF-8</option>
            </select>
          </div>

          <button type="submit" class="btn btn-primary">
            <i class="bi bi-upload me-1"></i>开始导入
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Column mapping reference -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-medium py-2">
        CSV 列映射参考
        <span class="badge bg-secondary ms-1"><?= count($columns) ?> 列</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive" style="max-height:420px;overflow-y:auto;">
          <table class="table table-sm table-striped mb-0 small">
            <thead class="table-light sticky-top">
              <tr><th>CSV 表头</th><th>数据库字段</th></tr>
            </thead>
            <tbody>
              <?php foreach ($columns as $col): ?>
              <tr>
                <td><?= e($col['label']) ?></td>
                <td class="text-muted font-monospace"><?= e($col['field']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Tips -->
<div class="card border-0 border-start border-4 border-info bg-info bg-opacity-10 shadow-sm mt-3">
  <div class="card-body py-2 small">
    <strong>注意事项：</strong>
    <ul class="mb-0 mt-1 ps-3">
      <li>CSV 第一行必须是表头（列名需与上方表格中的"CSV 表头"完全一致）</li>
      <li>导入为追加模式，不会覆盖已有数据，重复执行会重复插入</li>
      <li>Excel 默认保存的 .csv 通常为 GBK 编码，选"自动检测"或"GBK"即可</li>
      <li>若需重置数据库，请在 MySQL 中执行：<code>TRUNCATE TABLE products;</code></li>
    </ul>
  </div>
</div>
