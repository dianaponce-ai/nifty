<?php $title = 'Reseñas'; $r = $edit ?: ['id' => '', 'author' => '', 'role' => '', 'text' => '', 'rating' => 5, 'source' => 'Google', 'case_id' => null, 'featured' => 1, 'sort' => 0]; ?>
<div class="grid-lead">
  <section class="card">
    <h2>Reseñas cargadas</h2>
    <div class="table-wrap"><table class="atable">
      <thead><tr><th>Autor</th><th>Reseña</th><th>Caso</th><th>Home</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($reviews as $rv): ?>
        <tr><td><b><?= e($rv['author']) ?></b><br><span class="muted small"><?= e($rv['role'] ?: $rv['source']) ?> · <?= str_repeat('★', (int) $rv['rating']) ?></span></td>
          <td class="small"><?= e(mb_strimwidth($rv['text'], 0, 120, '…')) ?></td>
          <td><?= e($rv['client'] ?? '—') ?></td><td><?= $rv['featured'] ? '✓' : '' ?></td>
          <td class="nowrap"><a class="abtn ghost sm" href="/admin/resenas?editar=<?= (int) $rv['id'] ?>">Editar</a>
            <form method="post" style="display:inline" onsubmit="return confirm('¿Eliminar la reseña?')"><?= csrf_field() ?><input type="hidden" name="act" value="delete"><input type="hidden" name="id" value="<?= (int) $rv['id'] ?>"><button class="abtn danger sm">×</button></form></td></tr>
      <?php endforeach; ?>
      </tbody></table></div>
  </section>
  <aside><section class="card">
    <h2><?= $edit ? 'Editar reseña' : 'Nueva reseña' ?></h2>
    <form method="post" class="aform">
      <?= csrf_field() ?><input type="hidden" name="act" value="save"><input type="hidden" name="id" value="<?= e($r['id']) ?>">
      <label>Autor *<input name="author" value="<?= e($r['author']) ?>" required></label>
      <label>Cargo / empresa<input name="role" value="<?= e($r['role']) ?>" placeholder="Ej: Directora, Eunoia Campus"></label>
      <label>Reseña *<textarea name="text" rows="5" required><?= e($r['text']) ?></textarea></label>
      <div class="field-row">
        <label>Estrellas<select name="rating"><?php for ($i = 5; $i >= 1; $i--): ?><option<?= (int) $r['rating'] === $i ? ' selected' : '' ?>><?= $i ?></option><?php endfor; ?></select></label>
        <label>Fuente<input name="source" value="<?= e($r['source']) ?>"></label>
      </div>
      <label>Vincular a un caso<select name="case_id"><option value="">— Ninguno —</option><?php foreach ($cases as $c): ?><option value="<?= (int) $c['id'] ?>"<?= (int) $r['case_id'] === (int) $c['id'] ? ' selected' : '' ?>><?= e($c['client']) ?></option><?php endforeach; ?></select></label>
      <div class="field-row"><label class="chk"><input type="checkbox" name="featured" value="1"<?= $r['featured'] ? ' checked' : '' ?>> Mostrar en la home</label><label>Orden<input type="number" name="sort" value="<?= (int) $r['sort'] ?>"></label></div>
      <button class="abtn primary block">Guardar</button>
      <?php if ($edit): ?><a class="abtn ghost block" href="/admin/resenas">Cancelar</a><?php endif; ?>
    </form>
  </section></aside>
</div>
