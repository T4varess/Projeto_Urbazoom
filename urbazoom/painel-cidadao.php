<?php
require __DIR__ . '/config.php';

exigir_cidadao();

$usuario = usuario_logado();

$sqlMetricas = "SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN s.slug = 'aberta' THEN 1 ELSE 0 END) AS abertas,
    SUM(CASE WHEN s.slug IN ('em-analise', 'em-atendimento') THEN 1 ELSE 0 END) AS andamento,
    SUM(CASE WHEN s.slug = 'resolvida' THEN 1 ELSE 0 END) AS resolvidas
    FROM ocorrencias o
    INNER JOIN status_ocorrencia s ON s.id = o.status_id
    WHERE o.usuario_id = :usuario_id";
$stmt = banco()->prepare($sqlMetricas);
$stmt->execute(array('usuario_id' => $usuario['id']));
$metricas = $stmt->fetch();

$sqlUltima = "SELECT
    o.id,
    o.protocolo,
    o.titulo,
    o.localizacao,
    o.aberta_em,
    s.nome AS status_nome,
    s.classe_badge
    FROM ocorrencias o
    INNER JOIN status_ocorrencia s ON s.id = o.status_id
    WHERE o.usuario_id = :usuario_id
    ORDER BY o.aberta_em DESC
    LIMIT 1";
$stmt = banco()->prepare($sqlUltima);
$stmt->execute(array('usuario_id' => $usuario['id']));
$ultima = $stmt->fetch();

render_header('Urbazoom | Meu Painel', 'painel-cidadao.php');
?>
<main class="page-shell dashboard-page">
  <section class="page-banner">
    <div class="container">
      <?php render_alerta(); ?>
      <div class="panel-header">
        <div>
          <h1>Meu painel</h1>
          <p>Resumo rápido da sua conta, dos seus chamados e dos próximos passos.</p>
        </div>
        <a class="btn btn-primary" href="registro-ocorrencia.php">+ Nova ocorrência</a>
      </div>
    </div>
  </section>

  <div class="container">
    <section class="grid-4 section-sm">
      <article class="metric-card"><div class="metric-top"><h3>Total</h3><span class="icon-circle icon-blue">&#128196;</span></div><div class="metric-number"><?php echo e((int) $metricas['total']); ?></div><p class="metric-subtitle">Chamados cadastrados</p></article>
      <article class="metric-card"><div class="metric-top"><h3>Abertas</h3><span class="icon-circle icon-orange">&#9888;</span></div><div class="metric-number"><?php echo e((int) $metricas['abertas']); ?></div><p class="metric-subtitle">Aguardando primeira análise</p></article>
      <article class="metric-card"><div class="metric-top"><h3>Em andamento</h3><span class="icon-circle icon-blue">&#128339;</span></div><div class="metric-number"><?php echo e((int) $metricas['andamento']); ?></div><p class="metric-subtitle">Com atendimento em curso</p></article>
      <article class="metric-card"><div class="metric-top"><h3>Resolvidas</h3><span class="icon-circle icon-green">&#10003;</span></div><div class="metric-number"><?php echo e((int) $metricas['resolvidas']); ?></div><p class="metric-subtitle">Finalizadas pelo órgão</p></article>
    </section>

    <section class="grid-2 section-sm">
      <article class="panel-box">
        <div class="panel-box-header">
          <div>
            <h3>Bem-vindo</h3>
            <p>Informações básicas da sessão atual.</p>
          </div>
        </div>
        <div class="detail-grid">
          <div class="detail-item">
            <strong>Nome</strong>
            <span><?php echo e($usuario['nome']); ?></span>
          </div>
          <div class="detail-item">
            <strong>Email</strong>
            <span><?php echo e($usuario['email']); ?></span>
          </div>
          <div class="detail-item">
            <strong>Tipo</strong>
            <span>Cidadão</span>
          </div>
          <div class="detail-item">
            <strong>Status</strong>
            <span>Logado</span>
          </div>
        </div>
      </article>

      <article class="panel-box">
        <div class="panel-box-header">
          <div>
            <h3>Acoes rapidas</h3>
            <p>Atalhos do que voce mais usa.</p>
          </div>
        </div>
        <div class="helper-list">
          <div class="helper-item">
            <strong><a href="registro-ocorrencia.php">Registrar nova ocorrência</a></strong>
            <p>Abra um novo chamado urbano com local e descrição.</p>
          </div>
          <div class="helper-item">
            <strong><a href="<?php echo $ultima ? 'detalhe-ocorrencia.php?id=' . e($ultima['id']) : 'registro-ocorrencia.php'; ?>">Abrir último chamado</a></strong>
            <p>Veja rapidamente o detalhe da ocorrência mais recente.</p>
          </div>
          <div class="helper-item">
            <strong><a href="logout.php">Sair do sistema</a></strong>
            <p>Encerre a sessao atual quando terminar.</p>
          </div>
        </div>
      </article>
    </section>

    <section class="panel-box">
      <div class="panel-box-header">
        <div>
          <h3>Última ocorrência</h3>
          <p>Resumo rápido do chamado mais recente.</p>
        </div>
      </div>

      <?php if (!$ultima) : ?>
        <div class="empty-state">
          <h3>Você ainda não abriu chamados</h3>
          <p>Quando registrar a primeira ocorrência, ela vai aparecer aqui.</p>
          <a class="btn btn-primary" href="registro-ocorrencia.php">Registrar agora</a>
        </div>
      <?php else : ?>
        <div class="occurrences-grid cols-2">
          <article class="occurrence-card">
            <h3><?php echo e($ultima['titulo']); ?></h3>
            <p>Protocolo <?php echo e($ultima['protocolo']); ?></p>
            <div class="occurrence-meta">
              <span>Localização: <?php echo e($ultima['localizacao']); ?></span>
              <span>Data: <?php echo e(date('d/m/Y', strtotime($ultima['aberta_em']))); ?></span>
              <span class="badge <?php echo e($ultima['classe_badge']); ?>"><?php echo e($ultima['status_nome']); ?></span>
            </div>
            <a class="card-link" href="detalhe-ocorrencia.php?id=<?php echo e($ultima['id']); ?>">Ver detalhes</a>
          </article>
        </div>
      <?php endif; ?>
    </section>
  </div>
</main>
<?php render_footer(); ?>
