<section class="page-hero">
  <div class="hero-bg" aria-hidden="true"><div class="hero-gridlines"></div><div class="orb o1"></div><div class="orb o2"></div></div>
  <div class="wrap">
    <span class="eyebrow">Contacto</span>
    <h1>Conversemos tu <em>proyecto.</em></h1>
    <p class="lead">Contanos qué querés lograr y te devolvemos un primer diagnóstico antes de la reunión. Si preferís, reservá directamente un espacio en la agenda.</p>
    <div class="hero-ctas"><a class="btn btn-ghost" href="<?= e(setting('agenda_url')) ?>" target="_blank" rel="noopener"><?= icon('calendar', 18) ?> Agendar reunión</a></div>
  </div>
</section>
<?php partial('partials/contact_form', ['from' => 'contacto']); ?>
