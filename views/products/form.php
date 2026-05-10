<?php
$action = $isEdit
    ? '?c=product&a=update'
    : '?c=product&a=store';

// Group columns by tab
$tabs = [];
foreach ($columns as $col) {
    $tabs[$col['tab']][] = $col;
}
$tabKeys   = array_keys($tabs);
$firstTab  = $tabKeys[0] ?? '';
?>

<div class="d-flex align-items-center mb-3">
  <a href="<?= $isEdit ? '?c=product&a=show&id=' . (int)$product['id'] : '?c=product&a=index' ?>"
     class="btn btn-sm btn-outline-secondary me-2">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h5 class="mb-0 fw-semibold">
    <i class="bi bi-<?= $isEdit ? 'pencil-square' : 'plus-circle' ?> text-primary me-1"></i>
    <?= e($title) ?>
    <?php if ($isEdit): ?>
      <small class="text-muted fw-normal ms-2"># <?= e($product['tqb_code'] ?? '') ?></small>
    <?php endif; ?>
  </h5>
</div>

<form method="POST" action="<?= e($action) ?>" autocomplete="off">
  <?php if ($isEdit): ?>
  <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
  <?php endif; ?>

  <!-- Category Selection -->
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label fw-medium small mb-1">所属分类</label>
          <select name="product[category_id]" class="form-select form-select-sm" id="categorySelect" onchange="toggleNewCategory(this)">
            <option value="">-- 无分类 --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                <?= e($cat['name']) ?>
              </option>
            <?php endforeach; ?>
            <option value="NEW" class="fw-bold text-primary">+ 添加新分类...</option>
          </select>
        </div>
        <div class="col-md-4" id="newCategoryWrapper" style="display:none;">
          <label class="form-label fw-medium small mb-1 text-primary">新分类名称 <span class="text-danger">*</span></label>
          <input type="text" name="new_category_name" class="form-control form-control-sm border-primary" placeholder="输入新分类名称...">
        </div>
      </div>
    </div>
  </div>

  <!-- Tab navigation -->
  <ul class="nav nav-tabs mb-0" id="formTabs" role="tablist">
    <?php foreach ($tabKeys as $i => $tab): ?>
    <li class="nav-item" role="presentation">
      <button class="nav-link <?= $i === 0 ? 'active' : '' ?>"
              id="tab-<?= $i ?>-btn"
              data-bs-toggle="tab" data-bs-target="#tab-<?= $i ?>"
              type="button" role="tab">
        <?= e($tab) ?>
      </button>
    </li>
    <?php endforeach; ?>
  </ul>

  <div class="tab-content border border-top-0 rounded-bottom bg-white p-3 mb-3 shadow-sm">
    <?php foreach ($tabKeys as $i => $tab): ?>
    <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>"
         id="tab-<?= $i ?>" role="tabpanel">
      <div class="row g-3 pt-1">
        <?php foreach ($tabs[$tab] as $col): ?>
        <div class="col-md-4 col-sm-6">
          <label class="form-label fw-medium small mb-1"><?= e($col['label']) ?></label>
          <input
            type="<?= $col['type'] === 'number' ? 'text' : 'text' ?>"
            class="form-control form-control-sm"
            name="product[<?= e($col['field']) ?>]"
            value="<?= e($product[$col['field']] ?? '') ?>"
            placeholder="<?= e($col['label']) ?>">
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary px-4">
      <i class="bi bi-check-lg me-1"></i><?= $isEdit ? '保存修改' : '创建产品' ?>
    </button>
    <a href="<?= $isEdit ? '?c=product&a=show&id=' . (int)$product['id'] : '?c=product&a=index' ?>"
       class="btn btn-outline-secondary">取消</a>
  </div>
</form>

<script>
function toggleNewCategory(select) {
  var wrapper = document.getElementById('newCategoryWrapper');
  var input = wrapper.querySelector('input');
  if (select.value === 'NEW') {
    wrapper.style.display = 'block';
    input.required = true;
    input.focus();
  } else {
    wrapper.style.display = 'none';
    input.required = false;
  }
}
// Init on load
document.addEventListener('DOMContentLoaded', function() {
  toggleNewCategory(document.getElementById('categorySelect'));
});
</script>
