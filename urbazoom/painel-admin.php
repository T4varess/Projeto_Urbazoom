<?php
require __DIR__ . '/config.php';

exigir_servidor();

$sqlMetricas = "SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN s.slug = 'aberta' THEN 1 ELSE 0 END) AS abertas,
    SUM(CASE WHEN s.slug IN ('em-analise', 'em-atendimento') THEN 1 ELSE 0 END) AS andamento,
    SUM(CASE WHEN s.slug = 'resolvida' THEN 1 ELSE 0 END) AS resolvidas
    FROM ocorrencias o
    INNER JOIN status_ocorrencia s ON s.id = o.status_id";
$metricas = banco()->query($sqlMetricas)->fetch();

$sqlOcorrencias = "SELECT
    o.id,
    o.titulo,
    o.localizacao,
    o.aberta_em,
    s.nome AS status_nome,
    s.classe_badge
    FROM ocorrencias o
    INNER JOIN status_ocorrencia s ON s.id = o.status_id
    ORDER BY o.aberta_em DESC
    LIMIT 6";
$ocorrencias = banco()->query($sqlOcorrencias)->fetchAll();

render_header('Urbazoom | Painel Administrativo', 'painel-admin.php');
?>
<main class="page-shell dashboard-page">
  <div class="container">
    <?php render_alerta(); ?>
    <div class="panel-header">
      <div>
        <h1>Painel Administrativo</h1>
        <p>Visualize indicadores e acompanhe a operação urbana.</p>
      </div>
    </div>

    <section class="grid-4 section-sm">
      <article class="metric-card"><div class="metric-top"><h3>Total</h3><span class="icon-circle icon-blue">&#128196;</span></div><div class="metric-number"><?php echo e((int) $metricas['total']); ?></div><p class="metric-subtitle">Ocorrências cadastradas</p></article>
      <article class="metric-card"><div class="metric-top"><h3>Abertas</h3><span class="icon-circle icon-orange">&#9888;</span></div><div class="metric-number"><?php echo e((int) $metricas['abertas']); ?></div><p class="metric-subtitle">Demandas aguardando triagem</p></article>
      <article class="metric-card"><div class="metric-top"><h3>Em Andamento</h3><span class="icon-circle icon-blue">&#128339;</span></div><div class="metric-number"><?php echo e((int) $metricas['andamento']); ?></div><p class="metric-subtitle">Ocorrências com equipes acionadas</p></article>
      <article class="metric-card"><div class="metric-top"><h3>Resolvidas</h3><span class="icon-circle icon-green">&#10003;</span></div><div class="metric-number"><?php echo e((int) $metricas['resolvidas']); ?></div><p class="metric-subtitle">Chamados finalizados</p></article>
    </section>

    <section class="panel-box">
      <div class="panel-box-header">
        <div>
          <h3>Ocorrências recentes</h3>
          <p>Lista simples das últimas demandas registradas no sistema.</p>
        </div>
      </div>

      <div class="occurrences-grid cols-3">
        <?php foreach ($ocorrencias as $ocorrencia) : ?>
          <article class="occurrence-card">
            <h3><?php echo e($ocorrencia['titulo']); ?></h3>
            <p>Chamado urbano registrado na plataforma.</p>
            <div class="occurrence-meta">
              <span>Localização: <?php echo e($ocorrencia['localizacao']); ?></span>
              <span>Data: <?php echo e(date('d/m/Y', strtotime($ocorrencia['aberta_em']))); ?></span>
              <span class="badge <?php echo e($ocorrencia['classe_badge']); ?>"><?php echo e($ocorrencia['status_nome']); ?></span>
            </div>
            <a class="card-link" href="detalhe-ocorrencia.php?id=<?php echo e($ocorrencia['id']); ?>">Ver detalhes</a>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</main>
<?php render_footer(); ?>
