<?php
require __DIR__ . '/config.php';

exigir_cidadao();

$usuario = usuario_logado();

$stmtM = banco()->prepare("SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN s.slug = 'aberta' THEN 1 ELSE 0 END) AS abertas,
    SUM(CASE WHEN s.slug IN ('em-analise','em-atendimento') THEN 1 ELSE 0 END) AS andamento,
    SUM(CASE WHEN s.slug = 'resolvida' THEN 1 ELSE 0 END) AS resolvidas
    FROM ocorrencias o
    INNER JOIN status_ocorrencia s ON s.id = o.status_id
    WHERE o.usuario_id = :uid");
$stmtM->execute(array('uid' => $usuario['id']));
$metricas = $stmtM->fetch();

$stmtR = banco()->prepare("SELECT
    o.id, o.protocolo, o.titulo, o.localizacao, o.aberta_em, o.prioridade,
    s.nome AS status_nome, s.classe_badge
    FROM ocorrencias o
    INNER JOIN status_ocorrencia s ON s.id = o.status_id
    WHERE o.usuario_id = :uid
    ORDER BY o.aberta_em DESC
    LIMIT 3");
$stmtR->execute(array('uid' => $usuario['id']));
$recentes = $stmtR->fetchAll();

$primeiroNome = explode(' ', $usuario['nome'])[0];
$taxaResolucao = $metricas['total'] > 0
    ? round(($metricas['resolvidas'] / $metricas['total']) * 100)
    : 0;

render_header('Urbazoom | Meu Painel', 'painel-cidadao.php');
?>
<main class="page-shell dashboard-page">

  <!-- Banner de boas-vindas -->
  <section class="page-banner">
    <div class="container">
      <?php render_alerta(); ?>
      <div class="panel-header">
        <div>
          <p class="dash-greeting">Olá, <strong><?php echo e($primeiroNome); ?></strong> 👋</p>
          <h1>Meu painel</h1>
          <p>
            <?php if ($metricas['total'] == 0): ?>
              Você ainda não abriu nenhum chamado. Que tal começar agora?
            <?php elseif ($metricas['andamento'] > 0): ?>
              Você tem <strong><?php echo e((int)$metricas['andamento']); ?></strong> chamado<?php echo $metricas['andamento'] != 1 ? 's' : ''; ?> em atendimento.
            <?php elseif ($metricas['abertas'] > 0): ?>
              Você tem <strong><?php echo e((int)$metricas['abertas']); ?></strong> chamado<?php echo $metricas['abertas'] != 1 ? 's' : ''; ?> aguardando triagem.
            <?php else: ?>
              Todos os seus chamados foram resolvidos. Parabéns!
            <?php endif; ?>
          </p>
        </div>
        <a class="btn btn-primary" href="registro-ocorrencia.php">+ Nova ocorrência</a>
      </div>
    </div>
  </section>

  <div class="container">

    <!-- Métricas clicáveis -->
    <section class="grid-4 section-sm">
      <a href="acompanhamento.php" class="metric-card metric-card-link">
        <div class="metric-top"><h3>Total</h3><span class="icon-circle icon-blue">&#128196;</span></div>
        <div class="metric-number"><?php echo e((int)$metricas['total']); ?></div>
        <p class="metric-subtitle">Chamados registrados</p>
      </a>
      <a href="acompanhamento.php?status_id=1" class="metric-card metric-card-link">
        <div class="metric-top"><h3>Abertas</h3><span class="icon-circle icon-orange">&#9888;</span></div>
        <div class="metric-number"><?php echo e((int)$metricas['abertas']); ?></div>
        <p class="metric-subtitle">Aguardando análise</p>
      </a>
      <a href="acompanhamento.php?status_id=2" class="metric-card metric-card-link">
        <div class="metric-top"><h3>Em andamento</h3><span class="icon-circle icon-blue">&#128339;</span></div>
        <div class="metric-number"><?php echo e((int)$metricas['andamento']); ?></div>
        <p class="metric-subtitle">Com atendimento em curso</p>
      </a>
      <a href="acompanhamento.php?status_id=4" class="metric-card metric-card-link">
        <div class="metric-top"><h3>Resolvidas</h3><span class="icon-circle icon-green">&#10003;</span></div>
        <div class="metric-number"><?php echo e((int)$metricas['resolvidas']); ?></div>
        <p class="metric-subtitle">
          <?php if ($taxaResolucao > 0): ?>
            <span style="color:var(--success);font-weight:700"><?php echo e($taxaResolucao); ?>% de resolução</span>
          <?php else: ?>
            Finalizadas pelo órgão
          <?php endif; ?>
        </p>
      </a>
    </section>

    <!-- Ações rápidas -->
    <section class="panel-box" style="margin-bottom:32px">
      <div class="panel-box-header" style="margin-bottom:16px">
        <div>
          <h3>Ações rápidas</h3>
          <p>O que você precisa fazer agora.</p>
        </div>
      </div>
      <div class="quick-actions-grid">
        <a href="registro-ocorrencia.php" class="action-card action-card-primary">
          <span class="action-card-icon">&#43;</span>
          <strong>Nova ocorrência</strong>
          <span>Registrar um novo chamado urbano</span>
        </a>
        <a href="acompanhamento.php" class="action-card">
          <span class="action-card-icon">&#128203;</span>
          <strong>Meus chamados</strong>
          <span>Ver e acompanhar todos os registros</span>
        </a>
        <?php if ($recentes): ?>
        <a href="detalhe-ocorrencia.php?id=<?php echo e($recentes[0]['id']); ?>" class="action-card">
          <span class="action-card-icon">&#128336;</span>
          <strong>Último chamado</strong>
          <span>Protocolo <?php echo e($recentes[0]['protocolo']); ?> &mdash; <span class="badge <?php echo e($recentes[0]['classe_badge']); ?>" style="font-size:0.72rem;padding:2px 8px"><?php echo e($recentes[0]['status_nome']); ?></span></span>
        </a>
        <?php else: ?>
        <a href="registro-ocorrencia.php" class="action-card">
          <span class="action-card-icon">&#128204;</span>
          <strong>Primeira ocorrência</strong>
          <span>Comece registrando um problema urbano</span>
        </a>
        <?php endif; ?>
      </div>
    </section>

    <!-- Ocorrências recentes -->
    <section class="panel-box">
      <div class="panel-box-header">
        <div>
          <h3>Chamados recentes</h3>
          <p>Suas últimas <?php echo count($recentes); ?> ocorrências registradas.</p>
        </div>
        <?php if ($metricas['total'] > 3): ?>
          <a class="btn btn-outline" href="acompanhamento.php">Ver todos (<?php echo e((int)$metricas['total']); ?>) →</a>
        <?php endif; ?>
      </div>

      <?php if (!$recentes): ?>
        <div class="empty-state">
          <h3>Você ainda não abriu chamados</h3>
          <p>Quando registrar a primeira ocorrência, ela vai aparecer aqui.</p>
          <a class="btn btn-primary" href="registro-ocorrencia.php">Registrar agora</a>
        </div>
      <?php else: ?>
        <div class="occurrences-grid cols-<?php echo min(count($recentes), 3); ?>">
          <?php foreach ($recentes as $oc): ?>
            <article class="occurrence-card">
              <div class="occurrence-card-body">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:8px">
                  <span class="badge <?php echo e($oc['classe_badge']); ?>"><?php echo e($oc['status_nome']); ?></span>
                  <span style="font-size:0.78rem;color:var(--text-secondary);white-space:nowrap"><?php echo e(date('d/m/Y', strtotime($oc['aberta_em']))); ?></span>
                </div>
                <h3 style="margin-bottom:4px"><?php echo e($oc['titulo']); ?></h3>
                <p style="font-size:0.82rem;margin-bottom:10px">Protocolo <strong><?php echo e($oc['protocolo']); ?></strong></p>
                <div class="occurrence-meta">
                  <span>&#128205; <?php echo e($oc['localizacao']); ?></span>
                </div>
              </div>
              <a class="card-link" href="detalhe-ocorrencia.php?id=<?php echo e($oc['id']); ?>">
                Ver andamento &#8594;
              </a>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

  </div>
</main>
<?php render_footer(); ?>
