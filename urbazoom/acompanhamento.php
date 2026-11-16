<?php
require __DIR__ . '/config.php';

$usuario = usuario_logado();
$filtroUsuario = '';
$params = array();
$tituloLista = 'Ocorrências da cidade';
$textoLista = 'Visualize localização, data e status atual dos chamados.';
$textoTopo = 'Consulte o andamento das ocorrências registradas na plataforma.';

if ($usuario && !eh_servidor()) {
    $filtroUsuario = ' WHERE o.usuario_id = :usuario_id';
    $params['usuario_id'] = $usuario['id'];
    $tituloLista = 'Minhas ocorrências';
    $textoLista = 'Visualize localização, data e status atual de cada chamado.';
    $textoTopo = 'Consulte o andamento das suas ocorrências e acompanhe as atualizações mais recentes.';
}

$sqlMetricas = "SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN s.slug = 'aberta' THEN 1 ELSE 0 END) AS abertas,
    SUM(CASE WHEN s.slug IN ('em-analise', 'em-atendimento') THEN 1 ELSE 0 END) AS andamento,
    SUM(CASE WHEN s.slug = 'resolvida' THEN 1 ELSE 0 END) AS resolvidas
    FROM ocorrencias o
    INNER JOIN status_ocorrencia s ON s.id = o.status_id" . $filtroUsuario;
$stmt = banco()->prepare($sqlMetricas);
$stmt->execute($params);
$metricas = $stmt->fetch();

$sqlOcorrencias = "SELECT
    o.id,
    o.protocolo,
    o.titulo,
    o.localizacao,
    o.aberta_em,
    s.nome AS status_nome,
    s.classe_badge
    FROM ocorrencias o
    INNER JOIN status_ocorrencia s ON s.id = o.status_id
    " . $filtroUsuario . "
    ORDER BY o.aberta_em DESC";
$stmt = banco()->prepare($sqlOcorrencias);
$stmt->execute($params);
$ocorrencias = $stmt->fetchAll();

render_header('Urbazoom | Acompanhamento', 'acompanhamento.php');
?>
<main class="page-shell dashboard-page">
  <section class="page-banner">
    <div class="container">
      <?php render_alerta(); ?>
      <div class="panel-header">
        <div>
          <h1>Acompanhamento</h1>
          <p><?php echo e($textoTopo); ?></p>
        </div>
        <?php if (esta_logado() && !eh_servidor()) : ?>
          <a class="btn btn-primary" href="registro-ocorrencia.php">+ Nova ocorrência</a>
        <?php else : ?>
          <a class="btn btn-primary" href="login.php">Entrar para registrar</a>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <div class="container">
    <section class="grid-4 section-sm">
      <article class="metric-card"><div class="metric-top"><h3>Total</h3><span class="icon-circle icon-blue">&#128196;</span></div><div class="metric-number"><?php echo e((int) $metricas['total']); ?></div><p class="metric-subtitle">Ocorrências registradas</p></article>
      <article class="metric-card"><div class="metric-top"><h3>Abertas</h3><span class="icon-circle icon-orange">&#9888;</span></div><div class="metric-number"><?php echo e((int) $metricas['abertas']); ?></div><p class="metric-subtitle">Aguardando análise inicial</p></article>
      <article class="metric-card"><div class="metric-top"><h3>Em Andamento</h3><span class="icon-circle icon-blue">&#128339;</span></div><div class="metric-number"><?php echo e((int) $metricas['andamento']); ?></div><p class="metric-subtitle">Com atendimento em curso</p></article>
      <article class="metric-card"><div class="metric-top"><h3>Resolvidas</h3><span class="icon-circle icon-green">&#10003;</span></div><div class="metric-number"><?php echo e((int) $metricas['resolvidas']); ?></div><p class="metric-subtitle">Finalizadas pelo órgão</p></article>
    </section>

    <section class="panel-box">
      <div class="panel-box-header">
        <div>
          <h3><?php echo e($tituloLista); ?></h3>
          <p><?php echo e($textoLista); ?></p>
        </div>
      </div>

      <?php if (!$ocorrencias) : ?>
        <div class="empty-state">
          <h3>Nenhuma ocorrência cadastrada</h3>
          <p>Quando houver ocorrências registradas, elas vão aparecer aqui.</p>
          <?php if (esta_logado() && !eh_servidor()) : ?>
            <a class="btn btn-primary" href="registro-ocorrencia.php">Registrar agora</a>
          <?php else : ?>
            <a class="btn btn-primary" href="login.php">Entrar para registrar</a>
          <?php endif; ?>
        </div>
      <?php else : ?>
        <div class="occurrences-grid cols-2">
          <?php foreach ($ocorrencias as $ocorrencia) : ?>
            <article class="occurrence-card">
              <h3><?php echo e($ocorrencia['titulo']); ?></h3>
              <p>Protocolo <?php echo e($ocorrencia['protocolo']); ?></p>
              <div class="occurrence-meta">
                <span>Localização: <?php echo e($ocorrencia['localizacao']); ?></span>
                <span>Data: <?php echo e(date('d/m/Y', strtotime($ocorrencia['aberta_em']))); ?></span>
                <span class="badge <?php echo e($ocorrencia['classe_badge']); ?>"><?php echo e($ocorrencia['status_nome']); ?></span>
              </div>
              <a class="card-link" href="detalhe-ocorrencia.php?id=<?php echo e($ocorrencia['id']); ?>">Ver detalhes</a>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </div>
</main>
<?php render_footer(); ?>
