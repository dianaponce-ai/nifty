<?php
$isNew = !$c;
$c = $c ?: ['id' => 0, 'slug' => '', 'client' => '', 'industry' => '', 'location' => '', 'scope' => '', 'title' => '', 'subtitle' => '', 'card_summary' => '', 'facts' => '',
    'challenge_title' => '', 'challenge_intro' => '', 'challenges' => '[]', 'work_title' => '', 'work_intro' => '', 'services_ops' => '[]', 'services_mk' => '[]', 'stats' => '[]',
    'highlight' => '{}', 'results_title' => '', 'results' => '[]', 'hero_image' => '', 'highlight_image' => '', 'gallery' => '[]', 'site_url' => '', 'published' => 0, 'sort' => 100];
$hl = jdec($c['highlight']);
$title = $isNew ? 'Nuevo caso de éxito' : 'Editar: ' . $c['client'];
$actions = '<a class="abtn ghost" href="/admin/casos">← Casos</a>' . (!$isNew ? '<a class="abtn" href="/casos-de-exito/' . e($c['slug']) . '" target="_blank">Ver página ↗</a>' : '');
$linked = array_map('intval', array_column(array_filter($reviews, fn($r) => (int) $r['case_id'] === (int) $c['id'] && $c['id']), 'id'));
?>
<form method="post" enctype="multipart/form-data" class="aform case-form" id="caseForm" action="<?= $isNew ? '/admin/casos/nuevo' : '/admin/casos/' . (int) $c['id'] ?>">
  <?= csrf_field() ?>
  <div class="grid-lead">
    <div>
      <section class="card">
        <h2>1 · Los hechos</h2>
        <p class="muted">Contá el caso con tus palabras: qué hacía el cliente, qué problema tenía, qué hicimos y qué logró (con números si los hay). A partir de esto se generan los títulos, las tarjetas de datos y todos los bloques.</p>
        <div class="field-row">
          <label>Cliente *<input name="client" value="<?= e($c['client']) ?>" required></label>
          <label>Rubro<input name="industry" value="<?= e($c['industry']) ?>" placeholder="Ej: Educación, Industria · Distribución B2B"></label>
        </div>
        <div class="field-row">
          <label>Ubicación<input name="location" value="<?= e($c['location']) ?>"></label>
          <label>Sitio del cliente<input name="site_url" value="<?= e($c['site_url']) ?>" placeholder="https://"></label>
        </div>
        <label>Hechos del caso<textarea name="facts" id="facts" rows="7" placeholder="Ej: Mayorista de rodamientos. Desarrollamos un sistema de ventas integrado con Netegia para más de 20.000 productos…"><?= e($c['facts']) ?></textarea></label>
        <button type="button" class="abtn magic" id="genBtn" <?= $ai ? '' : 'data-noai="1"' ?>><?= icon('sparkle', 16) ?> Generar títulos, tarjetas y bloques con IA</button>
        <span class="gen-status muted small" id="genStatus"><?= $ai ? 'Completa los campos de abajo; después podés editar todo.' : 'Para usarlo, cargá la API key de Claude en config.php. Mientras tanto podés completar los campos a mano.' ?></span>
      </section>

      <section class="card">
        <h2>2 · Encabezado y tarjeta</h2>
        <label>Titular (después de “<?= e($c['client'] ?: 'Cliente') ?>:”)<input name="title" value="<?= e($c['title']) ?>" data-k="title"></label>
        <label>Bajada<textarea name="subtitle" rows="3" data-k="subtitle"><?= e($c['subtitle']) ?></textarea></label>
        <div class="field-row">
          <label>Alcance (2-4 palabras)<input name="scope" value="<?= e($c['scope']) ?>" data-k="scope"></label>
          <label>Resumen para la tarjeta del listado<input name="card_summary" value="<?= e($c['card_summary']) ?>" data-k="card_summary"></label>
        </div>
        <label>Tarjetas de datos <span class="hint">una por línea: <code>valor | qué significa</code></span>
          <textarea name="stats" rows="4" data-k="stats" data-pairs="value,label" placeholder="+20.000 | productos gestionados"><?= e(pairs_to_lines($c['stats'], 'value', 'label')) ?></textarea></label>
      </section>

      <section class="card">
        <h2>3 · El desafío</h2>
        <label>Título<input name="challenge_title" value="<?= e($c['challenge_title']) ?>" data-k="challenge_title"></label>
        <label>Introducción<textarea name="challenge_intro" rows="3" data-k="challenge_intro"><?= e($c['challenge_intro']) ?></textarea></label>
        <label>Desafíos <span class="hint">uno por línea: <code>título | descripción</code></span><textarea name="challenges" rows="4" data-k="challenges" data-pairs="t,d"><?= e(pairs_to_lines($c['challenges'])) ?></textarea></label>
      </section>

      <section class="card">
        <h2>4 · Lo que hicimos</h2>
        <label>Título<input name="work_title" value="<?= e($c['work_title']) ?>" data-k="work_title"></label>
        <label>Introducción<textarea name="work_intro" rows="2" data-k="work_intro"><?= e($c['work_intro']) ?></textarea></label>
        <div class="field-row">
          <label>Operación y tecnología <span class="hint"><code>servicio | descripción</code></span><textarea name="services_ops" rows="5" data-k="services_ops" data-pairs="t,d"><?= e(pairs_to_lines($c['services_ops'])) ?></textarea></label>
          <label>Marca y demanda <span class="hint"><code>servicio | descripción</code></span><textarea name="services_mk" rows="5" data-k="services_mk" data-pairs="t,d"><?= e(pairs_to_lines($c['services_mk'])) ?></textarea></label>
        </div>
      </section>

      <section class="card">
        <h2>5 · Bloque destacado</h2>
        <div class="field-row">
          <label>Etiqueta<input name="hl_kicker" value="<?= e($hl['kicker'] ?? '') ?>" data-k="highlight.kicker" placeholder="Ej: Contenido UGC"></label>
          <label>Título<input name="hl_title" value="<?= e($hl['title'] ?? '') ?>" data-k="highlight.title"></label>
        </div>
        <label>Texto<textarea name="hl_text" rows="3" data-k="highlight.text"><?= e($hl['text'] ?? '') ?></textarea></label>
        <label>Pasos <span class="hint"><code>paso | descripción</code></span><textarea name="hl_steps" rows="3" data-k="highlight.steps" data-pairs="t,d"><?= e(implode("\n", array_map(fn($s) => $s['t'] . ' | ' . $s['d'], $hl['steps'] ?? []))) ?></textarea></label>
      </section>

      <section class="card">
        <h2>6 · Resultado</h2>
        <label>Título del resultado<input name="results_title" value="<?= e($c['results_title']) ?>" data-k="results_title"></label>
        <label>Resultados <span class="hint"><code>título | descripción</code></span><textarea name="results" rows="4" data-k="results" data-pairs="t,d"><?= e(pairs_to_lines($c['results'])) ?></textarea></label>
      </section>
    </div>

    <aside>
      <section class="card sticky">
        <h2>Publicación</h2>
        <label class="chk"><input type="checkbox" name="published" value="1"<?= $c['published'] ? ' checked' : '' ?>> Publicado en el sitio</label>
        <div class="field-row">
          <label>Orden<input type="number" name="sort" value="<?= (int) $c['sort'] ?>"></label>
          <label>URL<input name="slug" value="<?= e($c['slug']) ?>" placeholder="auto"></label>
        </div>
        <button class="abtn primary block">Guardar caso</button>
      </section>

      <section class="card">
        <h2>Fotos</h2>
        <label>Imagen principal (captura del sitio, foto del proyecto)</label>
        <?php if ($c['hero_image']): ?><img class="thumb" src="<?= e(asset_url($c['hero_image'])) ?>" alt=""><?php endif; ?>
        <input type="file" name="hero_image" accept="image/*">
        <label class="small">o URL<input name="hero_image_url" value="<?= e($c['hero_image']) ?>"></label>
        <hr>
        <label>Imagen del bloque destacado (vertical, tipo celular)</label>
        <?php if ($c['highlight_image']): ?><img class="thumb" src="<?= e(asset_url($c['highlight_image'])) ?>" alt=""><?php endif; ?>
        <input type="file" name="highlight_image" accept="image/*">
        <label class="small">o URL<input name="highlight_image_url" value="<?= e($c['highlight_image']) ?>"></label>
        <hr>
        <label>Galería</label>
        <div class="gal-admin">
          <?php foreach (jdec($c['gallery']) as $g): ?>
            <div class="gal-item"><img src="<?= e(asset_url($g['src'])) ?>" alt="">
              <input name="gallery_caption[<?= e($g['src']) ?>]" value="<?= e($g['caption'] ?? '') ?>" placeholder="Epígrafe">
              <label class="chk small"><input type="checkbox" name="remove_gallery[]" value="<?= e($g['src']) ?>"> Quitar</label></div>
          <?php endforeach; ?>
        </div>
        <input type="file" name="gallery_new[]" accept="image/*" multiple>
        <p class="muted small">JPG, PNG o WebP de hasta 8 MB.</p>
      </section>

      <section class="card">
        <h2>Reseñas de este cliente</h2>
        <?php if (!$reviews): ?><p class="muted small">No hay reseñas cargadas. <a href="/admin/resenas">Agregar</a></p><?php endif; ?>
        <?php foreach ($reviews as $r): ?>
          <label class="chk small"><input type="checkbox" name="review_ids[]" value="<?= (int) $r['id'] ?>"<?= in_array((int) $r['id'], $linked, true) ? ' checked' : '' ?>> <span><b><?= e($r['author']) ?></b> — <?= e(mb_strimwidth($r['text'], 0, 60, '…')) ?></span></label>
        <?php endforeach; ?>
        <p class="muted small">Si no vinculás ninguna, la página muestra la valoración general de Nifty en Google.</p>
      </section>
    </aside>
  </div>
</form>

<?php if (!$isNew && ($me['role'] ?? '') === 'admin'): ?>
<form method="post" action="/admin/casos/<?= (int) $c['id'] ?>/eliminar" onsubmit="return confirm('¿Eliminar este caso? No se puede deshacer.')" class="danger-zone">
  <?= csrf_field() ?><button class="abtn danger">Eliminar caso</button>
</form>
<?php endif; ?>
