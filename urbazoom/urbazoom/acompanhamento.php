<?php
require __DIR__ . '/config.php';

$usuario = usuario_logado();

// ── Filtro por status ──
$filtroStatus = (int) get_value('status_id', 0);

$whereArr = array();
$params   = array();

if ($usuario && !eh_servidor()) {
    $whereArr[] = 'o.usuario_id = :usuario_id';
    $params['usuario_id'] = $usuario['id'];
    $tituloLista = 'Minhas ocorrências';
    $textoLista  = 'Visualize localização, data e status atual de cada chamado.';
    $textoTopo   = 'Consulte o andamento das suas ocorrências e acompanhe as atualizações.';
} else {
    $tituloLista = 'Ocorrências da cidade';
    $textoLista  = 'Visualize localização, data e status atual dos chamados.';
    $textoTopo   = 'Consulte o andamento das ocorrências registradas na plataforma.';
}

if ($filtroStatus > 0) {
    $whereArr[] = 'o.status_id = :status_id';
    $params['status_id'] = $filtroStatus;
}

$whereClause = $whereArr ? ' WHERE ' . implode(' AND ', $whereArr) : '';

// ── Métricas (baseadas no filtro de usuário, sem filtro de status) ──
$paramsMetricas = isset($params['usuario_id']) ? array('usuario_id' => $params['usuario_id']) : array();
$whereMetricas  = isset($params['usuario_id']) ? ' WHERE o.usuario_id = :usuario_id' : '';

$stmtM = banco()->prepare("SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN s.slug = 'aberta' THEN 1 ELSE 0 END) AS abertas,
    SUM(CASE WHEN s.slug IN ('em-analise','em-atendimento') THEN 1 ELSE 0 END) AS andamento,
    SUM(CASE WHEN s.slug = 'resolvida' THEN 1 ELSE 0 END) AS resolvidas
    FROM ocorrencias o
    INNER JOIN status_ocorrencia s ON s.id = o.status_id" . $whereMetricas);
$stmtM->execute($paramsMetricas);
$metricas = $stmtM->fetch();

// ── Lista filtrada ──
$stmtL = banco()->prepare("SELECT
    o.id, o.protocolo, o.titulo, o.localizacao, o.aberta_em,
    s.id AS status_id_val, s.nome AS status_nome, s.classe_badge
    FROM ocorrencias o
    INNER JOIN status_ocorrencia s ON s.id = o.status_id
    " . $whereClause . "
    ORDER BY o.aberta_em DESC");
$stmtL->execute($params);
$ocorrencias = $stmtL->fetchAll();

// ── Opções de status para o filtro ──
$statuses = banco()->query("SELECT id, nome FROM status_ocorrencia ORDER BY ordem_exibicao")->fetchAll();

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
        <?php elseif (!esta_logado()) : ?>
          <a class="btn btn-primary" href="login.php">Entrar para registrar</a>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <div class="container">
    <section class="grid-4 section-sm">
      <article class="metric-card">
        <div class="metric-top"><h3>Total</h3><span class="icon-circle icon-blue">&#128196;</span></div>
        <div class="metric-number"><?php echo e((int) $metricas['total']); ?></div>
        <p class="metric-subtitle">Ocorrências registradas</p>
      </article>
      <article class="metric-card">
        <div class="metric-top"><h3>Abertas</h3><span class="icon-circle icon-orange">&#9888;</span></div>
        <div class="metric-number"><?php echo e((int) $metricas['abertas']); ?></div>
        <p class="metric-subtitle">Aguardando análise inicial</p>
      </article>
      <article class="metric-card">
        <div class="metric-top"><h3>Em Andamento</h3><span class="icon-circle icon-blue">&#128339;</span></div>
        <div class="metric-number"><?php echo e((int) $metricas['andamento']); ?></div>
        <p class="metric-subtitle">Com atendimento em curso</p>
      </article>
      <article class="metric-card">
        <div class="metric-top"><h3>Resolvidas</h3><span class="icon-circle icon-green">&#10003;</span></div>
        <div class="metric-number"><?php echo e((int) $metricas['resolvidas']); ?></div>
        <p class="metric-subtitle">Finalizadas pelo órgão</p>
      </article>
    </section>

    <section class="panel-box">
      <div class="panel-box-header">
        <div>
          <h3><?php echo e($tituloLista); ?></h3>
          <p><?php echo e($textoLista); ?></p>
        </div>
        <!-- Filtro de status -->
        <form method="GET" action="acompanhamento.php" style="display:flex;gap:8px;align-items:center">
          <select class="select" name="status_id" style="min-width:160px">
            <option value="0">Todos os status</option>
            <?php foreach ($statuses as $st) : ?>
              <option value="<?php echo e($st['id']); ?>" <?php echo $filtroStatus == $st['id'] ? 'selected' : ''; ?>>
                <?php echo e($st['nome']); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-outline" type="submit">Filtrar</button>
          <?php if ($filtroStatus > 0) : ?>
            <a class="btn btn-ghost" href="acompanhamento.php">Limpar</a>
          <?php endif; ?>
        </form>
      </div>

      <?php if (!$ocorrencias) : ?>
        <div class="empty-state">
          <h3>Nenhuma ocorrência encontrada</h3>
          <p>
            <?php if ($filtroStatus > 0) : ?>
              Nenhuma ocorrência com esse status. <a href="acompanhamento.php">Ver todas</a>.
            <?php else : ?>
              Quando houver ocorrências registradas, elas vão aparecer aqui.
            <?php endif; ?>
          </p>
          <?php if (esta_logado() && !eh_servidor() && !$filtroStatus) : ?>
            <a class="btn btn-primary" href="registro-ocorrencia.php">Registrar agora</a>
          <?php endif; ?>
        </div>
      <?php else : ?>
        <div class="occurrences-grid cols-2">
          <?php foreach ($ocorrencias as $ocorrencia) : ?>
            <article class="occurrence-card">
              <div class="occurrence-card-body">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:8px">
                  <span class="badge <?php echo e($ocorrencia['classe_badge']); ?>"><?php echo e($ocorrencia['status_nome']); ?></span>
                  <span style="font-size:0.78rem;color:var(--text-secondary);white-space:nowrap"><?php echo e(date('d/m/Y', strtotime($ocorrencia['aberta_em']))); ?></span>
                </div>
                <h3 style="margin-bottom:4px"><?php echo e($ocorrencia['titulo']); ?></h3>
                <p style="font-size:0.82rem;margin-bottom:10px">Protocolo <strong><?php echo e($ocorrencia['protocolo']); ?></strong></p>
                <div class="occurrence-meta">
                  <span>&#128205; <?php echo e($ocorrencia['localizacao']); ?></span>
                </div>
              </div>
              <a class="card-link" href="detalhe-ocorrencia.php?id=<?php echo e($ocorrencia['id']); ?>">
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
