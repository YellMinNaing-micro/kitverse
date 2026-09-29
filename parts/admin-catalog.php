<?php
$kind = (string)($_GET['kind'] ?? 'clubs');
if (!in_array($kind, ['clubs', 'categories', 'leagues'], true)) $kind = 'clubs';
$labels = ['clubs'=>'Clubs','categories'=>'Categories','leagues'=>'Leagues'];
$singular = ['clubs'=>'Club','categories'=>'Category','leagues'=>'League'];
$search = trim((string)($_GET['q'] ?? ''));
$pageNumber = max(1, (int)($_GET['p'] ?? 1));
$pageSize = 20;
$where = $search === '' ? '' : ' WHERE x.name LIKE ?';
$params = $search === '' ? [] : ['%'.$search.'%'];
$count = db()->prepare('SELECT COUNT(*) FROM '.$kind.' x'.$where);
$count->execute($params);
$total = (int)$count->fetchColumn();
$pageCount = max(1, (int)ceil($total/$pageSize));
$pageNumber = min($pageNumber,$pageCount);
$offset = ($pageNumber-1)*$pageSize;
$query = $kind === 'clubs' ? 'SELECT x.*,l.name league_name FROM clubs x LEFT JOIN leagues l ON l.id=x.league_id' : 'SELECT x.* FROM '.$kind.' x';
$statement = db()->prepare($query.$where.' ORDER BY x.id DESC LIMIT '.$pageSize.' OFFSET '.$offset);
$statement->execute($params);
$rows = $statement->fetchAll();
?>
<div class="admin-catalog-tabs" aria-label="Catalog sections"><?php foreach($labels as $key=>$label): ?><a class="<?=$kind===$key?'active':''?>" href="<?=path('admin',['tab'=>'catalog','kind'=>$key])?>"><?=e($label)?></a><?php endforeach; ?></div>
<div class="admin-list-panel">
  <div class="admin-list-toolbar"><a class="admin-create" href="<?=path('admin',['tab'=>'catalog','kind'=>$kind,'mode'=>'create'])?>">＋ Create <?=e($singular[$kind])?></a><form class="admin-list-search" method="get"><input type="hidden" name="page" value="admin"><input type="hidden" name="tab" value="catalog"><input type="hidden" name="kind" value="<?=e($kind)?>"><svg><use href="#a-search"/></svg><input type="search" name="q" value="<?=e($search)?>" placeholder="Search <?=strtolower($labels[$kind])?>" aria-label="Search <?=strtolower($labels[$kind])?>"></form></div>
  <?php if(($_GET['mode']??'')==='create'): ?><div class="admin-editor"><div class="admin-editor-heading"><h2>Create <?=e($singular[$kind])?></h2><a href="<?=path('admin',['tab'=>'catalog','kind'=>$kind])?>" aria-label="Close form">✕</a></div><form method="post" class="form"><input type="hidden" name="csrf" value="<?=token()?>"><input type="hidden" name="action" value="admin_catalog"><input type="hidden" name="type" value="<?=e($kind)?>"><label>NAME<input name="name" required></label><?php if($kind==='clubs'): ?><label>LEAGUE<select name="league_id" required><?php foreach($leagues as $row): ?><option value="<?=$row['id']?>"><?=e($row['name'])?></option><?php endforeach; ?></select></label><?php elseif($kind==='leagues'): ?><label>COUNTRY<input name="country"></label><?php else: ?><label>DESCRIPTION<input name="description"></label><?php endif; ?><button class="btn lime">SAVE <?=e(strtoupper($singular[$kind]))?></button></form></div><?php endif; ?>
  <div class="admin-table-scroll"><table class="admin-data-table"><thead><tr><th>#</th><th>Name</th><?php if($kind==='clubs'): ?><th>League</th><?php elseif($kind==='leagues'): ?><th>Country</th><?php else: ?><th>Description</th><?php endif; ?><th>Status</th><th>Created on</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><?=e($row['id'])?></td><td><strong><?=e($row['name'])?></strong></td><td><?=e($kind==='clubs'?($row['league_name']??'—'):($kind==='leagues'?($row['country']?:'—'):($row['description']?:'—')))?></td><td><span class="admin-status <?=$row['status']?'is-active':'is-hidden'?>"><?=$row['status']?'Active':'Hidden'?></span></td><td><?=e($row['created_at']??'—')?></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="5" class="admin-table-empty">No <?=strtolower($labels[$kind])?> found.</td></tr><?php endif; ?></tbody></table></div>
  <div class="admin-list-footer"><span>Showing <?=$total?$offset+1:0?> to <?=min($offset+$pageSize,$total)?> of <?=$total?> entries</span><div class="admin-page-links"><?php if($pageNumber>1): ?><a href="<?=path('admin',['tab'=>'catalog','kind'=>$kind,'q'=>$search,'p'=>$pageNumber-1])?>">‹ Previous</a><?php endif; ?><span><?=$pageNumber?> / <?=$pageCount?></span><?php if($pageNumber<$pageCount): ?><a href="<?=path('admin',['tab'=>'catalog','kind'=>$kind,'q'=>$search,'p'=>$pageNumber+1])?>">Next ›</a><?php endif; ?></div></div>
</div>
