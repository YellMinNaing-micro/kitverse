<?php
function filter_dropdown(string $name, string $allLabel, array $rows): void {
    $selected = (string)($_GET[$name] ?? '');
    $selectedLabel = $allLabel;
    $valid = false;
    foreach ($rows as $row) {
        if ((string)$row['id'] === $selected) { $selectedLabel = $row['name']; $valid = true; break; }
    }
    if (!$valid) $selected = '';
    ?>
    <details class="filter-dropdown" data-filter-dropdown>
      <summary><span><?=e($selectedLabel)?></span><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg></summary>
      <div class="filter-options" role="group" aria-label="<?=e($allLabel)?>">
        <label class="filter-option"><input type="radio" name="<?=e($name)?>" value="" aria-label="<?=e($allLabel)?>" <?=$selected===''?'checked':''?>><span><?=e($allLabel)?></span></label>
        <?php foreach($rows as $row): ?><label class="filter-option"><input type="radio" name="<?=e($name)?>" value="<?=$row['id']?>" aria-label="<?=e($row['name'])?>" <?=$selected===(string)$row['id']?'checked':''?>><span><?=e($row['name'])?></span></label><?php endforeach; ?>
      </div>
    </details>
    <?php
}
?>
<form class="filters shop-filters" method="get" id="filters" role="search" aria-label="Filter jerseys">
  <input type="hidden" name="page" value="shop">
  <?php if(($shopView??'all')!=='all'): ?><input type="hidden" name="view" value="<?=e($shopView)?>"><?php endif; ?>
  <label class="filter-search"><span class="sr-only">Search jerseys</span><svg aria-hidden="true"><use href="#i-search"/></svg><input type="search" name="q" placeholder="Search jerseys..." value="<?=e($_GET['q']??'')?>"></label>
  <?php filter_dropdown('league','All leagues',$leagues); ?>
  <?php filter_dropdown('club','All clubs',$clubs); ?>
  <?php filter_dropdown('category','All types',$categories); ?>
  <button class="btn dark filter-submit" type="submit"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 6h18M6 12h12M9 18h6"/></svg><span>Filter</span><span aria-hidden="true">↗</span></button>
</form>
