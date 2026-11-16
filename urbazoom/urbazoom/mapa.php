<?php
require __DIR__ . '/config.php';

$usuario = usuario_logado();

// Ocorrências com coordenadas
$stmt = banco()->query("
    SELECT o.id, o.protocolo, o.titulo, o.localizacao, o.prioridade,
           CAST(o.lat AS CHAR) AS lat, CAST(o.lng AS CHAR) AS lng,
           s.nome AS status_nome, s.classe_badge, s.slug AS status_slug,
           c.nome AS categoria_nome
    FROM ocorrencias o
    INNER JOIN status_ocorrencia s ON s.id = o.status_id
    INNER JOIN categorias c ON c.id = o.categoria_id
    WHERE o.lat IS NOT NULL AND o.lng IS NOT NULL
    ORDER BY o.aberta_em DESC
");
$ocorrencias = $stmt->fetchAll();

// Total sem coordenadas (para info)
$semCoord = (int) banco()->query("SELECT COUNT(*) FROM ocorrencias WHERE lat IS NULL")->fetchColumn();

$paginaAtual = 'mapa.php';
render_header('Urbazoom | Mapa de Ocorrências', $paginaAtual);
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<main class="page-shell">
  <section class="page-banner">
    <div class="container">
      <?php render_alerta(); ?>
      <div class="panel-header">
        <div>
          <h1>Mapa de Ocorrências</h1>
          <p>
            <?php echo count($ocorrencias); ?> ocorrência<?php echo count($ocorrencias) != 1 ? 's' : ''; ?> no mapa
            <?php if ($semCoord > 0): ?>
              <span style="color:var(--text-secondary);font-size:0.85rem">(<?php echo $semCoord; ?> sem localização cadastrada)</span>
            <?php endif; ?>
          </p>
        </div>
        <?php if ($usuario): ?>
          <a class="btn btn-outline" href="<?php echo $usuario['tipo'] === 'servidor' ? 'painel-admin.php' : 'painel-cidadao.php'; ?>">← Voltar ao painel</a>
        <?php else: ?>
          <a class="btn btn-primary" href="login.php">Entrar</a>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <div class="container map-page-wrap">

    <!-- Filtros rápidos -->
    <div class="map-filters">
      <span class="map-filter-label">Filtrar por status:</span>
      <button class="map-filter-btn active" data-status="">Todos</button>
      <button class="map-filter-btn" data-status="aberta">Abertas</button>
      <button class="map-filter-btn" data-status="em-analise">Em análise</button>
      <button class="map-filter-btn" data-status="em-atendimento">Em atendimento</button>
      <button class="map-filter-btn" data-status="resolvida">Resolvidas</button>
      <span class="map-count" id="map-count"></span>
    </div>

    <!-- Legenda -->
    <div class="map-legend">
      <span class="map-legend-item"><span class="map-dot" style="background:#ef4444"></span>Alta prioridade</span>
      <span class="map-legend-item"><span class="map-dot" style="background:#f97316"></span>Média</span>
      <span class="map-legend-item"><span class="map-dot" style="background:#22c55e"></span>Baixa</span>
    </div>

    <!-- Mapa -->
    <div id="map" class="map-container"></div>

  </div>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
  var dados = <?php echo json_encode($ocorrencias, JSON_UNESCAPED_UNICODE); ?>;

  var map = L.map('map').setView([-23.668, -46.461], 13);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);

  var cores = { alta: '#ef4444', media: '#f97316', baixa: '#22c55e' };

  var markers = [];

  function criarMarcadores(filtroStatus) {
    markers.forEach(function (m) { map.removeLayer(m); });
    markers = [];

    var visiveis = dados.filter(function (oc) {
      return !filtroStatus || oc.status_slug === filtroStatus;
    });

    document.getElementById('map-count').textContent =
      visiveis.length + ' ocorrência' + (visiveis.length !== 1 ? 's' : '') + ' exibida' + (visiveis.length !== 1 ? 's' : '');

    visiveis.forEach(function (oc) {
      var cor = cores[oc.prioridade] || '#3b5998';
      var m = L.circleMarker([parseFloat(oc.lat), parseFloat(oc.lng)], {
        radius: 10,
        color: '#fff',
        weight: 2,
        fillColor: cor,
        fillOpacity: 0.85
      });

      var detailLink = <?php echo $usuario ? 'true' : 'false'; ?>;
      var popup = '<div class="map-popup">' +
        '<strong class="map-popup-title">' + oc.titulo + '</strong>' +
        '<div class="map-popup-proto">Protocolo: ' + oc.protocolo + '</div>' +
        '<div style="margin:4px 0"><span class="badge ' + oc.classe_badge + '">' + oc.status_nome + '</span></div>' +
        '<div class="map-popup-loc">📍 ' + oc.localizacao + '</div>' +
        '<div class="map-popup-cat">' + oc.categoria_nome + '</div>';

      if (detailLink) {
        popup += '<a class="map-popup-link" href="detalhe-ocorrencia.php?id=' + oc.id + '">Ver detalhes →</a>';
      }
      popup += '</div>';

      m.bindPopup(popup);
      m.addTo(map);
      markers.push(m);
    });

    if (visiveis.length > 0) {
      var group = L.featureGroup(markers);
      map.fitBounds(group.getBounds().pad(0.15));
    }
  }

  criarMarcadores('');

  document.querySelectorAll('.map-filter-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.map-filter-btn').forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      criarMarcadores(btn.dataset.status);
    });
  });
}());
</script>
<?php render_footer(); ?>
