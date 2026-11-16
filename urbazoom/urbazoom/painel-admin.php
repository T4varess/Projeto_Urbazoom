<?php
require __DIR__ . '/config.php';
exigir_servidor();

$usuario = usuario_logado();
$primeiroNome = explode(' ', $usuario['nome'])[0];

// Métricas globais
$metricas = banco()->query("SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN s.slug = 'aberta' THEN 1 ELSE 0 END) AS abertas,
    SUM(CASE WHEN s.slug IN ('em-analise','em-atendimento') THEN 1 ELSE 0 END) AS andamento,
    SUM(CASE WHEN s.slug = 'resolvida' THEN 1 ELSE 0 END) AS resolvidas
    FROM ocorrencias o
    INNER JOIN status_ocorrencia s ON s.id = o.status_id")->fetch();

$taxaResolucao = $metricas['total'] > 0
    ? round(($metricas['resolvidas'] / $metricas['total']) * 100)
    : 0;

// Ocorrências urgentes: alta prioridade + abertas ou em análise
$urgentes = banco()->query("SELECT
    o.id, o.protocolo, o.titulo, o.prioridade, o.aberta_em,
    s.nome AS status_nome, s.classe_badge,
    u.nome AS cidadao_nome,
    DATEDIFF(NOW(), o.aberta_em) AS dias_aberta
    FROM ocorrencias o
    INNER JOIN status_ocorrencia s ON s.id = o.status_id
    INNER JOIN usuarios u ON u.id = o.usuario_id
    WHERE s.slug IN ('aberta','em-analise') AND o.prioridade = 'alta'
    ORDER BY o.aberta_em ASC
    LIMIT 5")->fetchAll();

// Últimas 10 ocorrências
$ocorrencias = banco()->query("SELECT
    o.id, o.protocolo, o.titulo, o.localizacao, o.prioridade, o.aberta_em,
    s.nome AS status_nome, s.classe_badge,
    u.nome AS cidadao_nome
    FROM ocorrencias o
    INNER JOIN status_ocorrencia s ON s.id = o.status_id
    INNER JOIN usuarios u ON u.id = o.usuario_id
    ORDER BY o.aberta_em DESC
    LIMIT 10")->fetchAll();

render_header('Urbazoom | Painel Administrativo', 'painel-admin.php');
?>
<main class="page-shell dashboard-page">

  <!-- Banner -->
  <section class="page-banner">
    <div class="container">
      <?php render_alerta(); ?>
      <div class="panel-header">
        <div>
          <p class="dash-greeting">Olá, <strong><?php echo e($primeiroNome); ?></strong> 👋</p>
          <h1>Painel Administrativo</h1>
          <p>
            <?php if ($metricas['abertas'] > 0): ?>
              <strong style="color:var(--warning)"><?php echo e((int)$metricas['abertas']); ?></strong> ocorrência<?php echo $metricas['abertas'] != 1 ? 's' : ''; ?> aguardando triagem.
            <?php else: ?>
              Nenhuma ocorrência aberta no momento.
            <?php endif; ?>
            Taxa de resolução atual: <strong style="color:var(--success)"><?php echo e($taxaResolucao); ?>%</strong>.
          </p>
        </div>
        <a class="btn btn-primary" href="gerenciar-ocorrencias.php">Gerenciar ocorrências</a>
      </div>
    </div>
  </section>

  <div class="container">

    <!-- Métricas clicáveis -->
    <section class="grid-4 section-sm">
      <a href="gerenciar-ocorrencias.php" class="metric-card metric-card-link">
        <div class="metric-top"><h3>Total</h3><span class="icon-circle icon-blue">&#128196;</span></div>
        <div class="metric-number"><?php echo e((int)$metricas['total']); ?></div>
        <p class="metric-subtitle">Ocorrências cadastradas</p>
      </a>
      <a href="gerenciar-ocorrencias.php?status_id=1" class="metric-card metric-card-link">
        <div class="metric-top"><h3>Abertas</h3><span class="icon-circle icon-orange">&#9888;</span></div>
        <div class="metric-number"><?php echo e((int)$metricas['abertas']); ?></div>
        <p class="metric-subtitle">Aguardando triagem</p>
      </a>
      <a href="gerenciar-ocorrencias.php?status_id=2" class="metric-card metric-card-link">
        <div class="metric-top"><h3>Em andamento</h3><span class="icon-circle icon-blue">&#128339;</span></div>
        <div class="metric-number"><?php echo e((int)$metricas['andamento']); ?></div>
        <p class="metric-subtitle">Com equipes acionadas</p>
      </a>
      <a href="gerenciar-ocorrencias.php?status_id=4" class="metric-card metric-card-link">
        <div class="metric-top"><h3>Resolvidas</h3><span class="icon-circle icon-green">&#10003;</span></div>
        <div class="metric-number"><?php echo e((int)$metricas['resolvidas']); ?></div>
        <p class="metric-subtitle">
          <?php if ($taxaResolucao > 0): ?>
            <span style="color:var(--success);font-weight:700"><?php echo e($taxaResolucao); ?>% do total</span>
          <?php else: ?>
            Chamados finalizados
          <?php endif; ?>
        </p>
      </a>
    </section>

    <!-- Atenção urgente -->
    <?php if ($urgentes): ?>
    <section class="panel-box urgency-box" style="margin-bottom:24px">
      <div class="panel-box-header">
        <div>
          <h3 style="color:var(--error)">&#9888; Requer atenção</h3>
          <p>Alta prioridade ainda em aberto ou análise.</p>
        </div>
        <a class="btn btn-outline" href="gerenciar-ocorrencias.php?prioridade=alta&status_id=1">Ver todas urgentes</a>
      </div>
      <div class="urgency-list">
        <?php foreach ($urgentes as $u): ?>
          <a class="urgency-item" href="detalhe-ocorrencia.php?id=<?php echo e($u['id']); ?>">
            <div class="urgency-item-info">
              <strong><?php echo e($u['titulo']); ?></strong>
              <span>Protocolo <?php echo e($u['protocolo']); ?> &bull; <?php echo e($u['cidadao_nome']); ?></span>
            </div>
            <div class="urgency-item-right">
              <span class="badge <?php echo e($u['classe_badge']); ?>"><?php echo e($u['status_nome']); ?></span>
              <span class="urgency-days"><?php echo e((int)$u['dias_aberta']); ?>d</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- Ocorrências recentes -->
    <section class="panel-box">
      <div class="panel-box-header">
        <div>
          <h3>Ocorrências recentes</h3>
          <p>Últimas 10 demandas registradas no sistema.</p>
        </div>
        <a class="btn btn-outline" href="gerenciar-ocorrencias.php">Ver todas →</a>
      </div>

      <?php if (!$ocorrencias): ?>
        <div class="empty-state">
          <h3>Nenhuma ocorrência cadastrada</h3>
          <p>Quando cidadãos registrarem chamados, eles aparecerão aqui.</p>
        </div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Protocolo</th>
                <th>Título</th>
                <th>Cidadão</th>
                <th>Prioridade</th>
                <th>Status</th>
                <th>Data</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($ocorrencias as $oc): ?>
                <tr data-href="detalhe-ocorrencia.php?id=<?php echo e($oc['id']); ?>"
                    class="<?php echo $oc['prioridade'] === 'alta' ? 'row-urgente' : ''; ?>">
                  <td><strong style="color:var(--primary)"><?php echo e($oc['protocolo']); ?></strong></td>
                  <td><?php echo e($oc['titulo']); ?></td>
                  <td><?php echo e($oc['cidadao_nome']); ?></td>
                  <td><span class="priority-<?php echo e($oc['prioridade']); ?>"><?php echo e(ucfirst($oc['prioridade'])); ?></span></td>
                  <td><span class="badge <?php echo e($oc['classe_badge']); ?>"><?php echo e($oc['status_nome']); ?></span></td>
                  <td style="white-space:nowrap"><?php echo e(date('d/m/Y', strtotime($oc['aberta_em']))); ?></td>
                  <td>
                    <a class="btn-table-action" href="detalhe-ocorrencia.php?id=<?php echo e($oc['id']); ?>">
                      <span class="bta-icon">&#9998;</span> Atualizar
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>

  </div>
</main>
<script>
document.querySelectorAll('.data-table tbody tr[data-href]').forEach(function(tr) {
  tr.addEventListener('click', function(e) {
    if (e.target.closest('a,button')) return;
    location.href = tr.dataset.href;
  });
});
</script>
<?php render_footer(); ?>
