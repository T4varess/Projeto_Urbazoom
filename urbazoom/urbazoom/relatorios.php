<?php
require __DIR__ . '/config.php';
exigir_servidor();

$statuses   = banco()->query("SELECT id, nome FROM status_ocorrencia ORDER BY ordem_exibicao")->fetchAll();
$categorias = banco()->query("SELECT id, nome FROM categorias ORDER BY nome")->fetchAll();
$orgaos     = banco()->query("SELECT id, nome FROM orgaos WHERE ativo = 1 ORDER BY nome")->fetchAll();

// Defaults: últimos 30 dias
$dataInicio  = get_value('data_inicio', date('Y-m-d', strtotime('-30 days')));
$dataFim     = get_value('data_fim',    date('Y-m-d'));
$statusId    = get_value('status_id',   '');
$categoriaId = get_value('categoria_id','');
$prioridade  = get_value('prioridade',  '');
$orgaoId     = get_value('orgao_id',    '');

// Monta WHERE
$where  = array();
$params = array();

if ($dataInicio !== '') { $where[] = 'DATE(o.aberta_em) >= :di';   $params['di']  = $dataInicio; }
if ($dataFim    !== '') { $where[] = 'DATE(o.aberta_em) <= :df';   $params['df']  = $dataFim; }
if ($statusId   !== '') { $where[] = 'o.status_id = :sid';         $params['sid'] = $statusId; }
if ($categoriaId!== '') { $where[] = 'o.categoria_id = :cid';      $params['cid'] = $categoriaId; }
if ($prioridade !== '') { $where[] = 'o.prioridade = :pri';        $params['pri'] = $prioridade; }
if ($orgaoId    !== '') { $where[] = 'o.orgao_id = :oid';          $params['oid'] = $orgaoId; }

$clausula = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT o.id, o.protocolo, o.titulo, o.localizacao, o.prioridade, o.aberta_em, o.resolvida_em,
    s.nome AS status_nome, s.classe_badge,
    c.nome AS categoria_nome, org.nome AS orgao_nome, u.nome AS cidadao_nome
    FROM ocorrencias o
    INNER JOIN status_ocorrencia s  ON s.id   = o.status_id
    INNER JOIN categorias c         ON c.id   = o.categoria_id
    INNER JOIN orgaos org           ON org.id = o.orgao_id
    INNER JOIN usuarios u           ON u.id   = o.usuario_id
    $clausula
    ORDER BY o.aberta_em DESC";

$stmtR = banco()->prepare($sql);
$stmtR->execute($params);
$ocorrencias = $stmtR->fetchAll();

// Métricas do período
$total     = count($ocorrencias);
$abertas   = 0; $andamento = 0; $resolvidas = 0;
foreach ($ocorrencias as $oc) {
    if (strpos($oc['status_nome'], 'Aberta') !== false)       $abertas++;
    elseif ($oc['resolvida_em'])                               $resolvidas++;
    else                                                        $andamento++;
}
$taxa = $total > 0 ? round(($resolvidas / $total) * 100) : 0;

// Monta query string para exportar
$qs = http_build_query(array(
    'data_inicio'  => $dataInicio,
    'data_fim'     => $dataFim,
    'status_id'    => $statusId,
    'categoria_id' => $categoriaId,
    'prioridade'   => $prioridade,
    'orgao_id'     => $orgaoId,
));

$paginaAtual = 'relatorios.php';
render_header('Urbazoom | Relatórios', $paginaAtual);
?>
<main class="page-shell dashboard-page">

  <section class="page-banner">
    <div class="container">
      <?php render_alerta(); ?>
      <div class="panel-header">
        <div>
          <h1>Relatórios</h1>
          <p>Filtre e exporte dados de ocorrências em CSV ou PDF.</p>
        </div>
        <a class="btn btn-outline" href="painel-admin.php">← Painel admin</a>
      </div>
    </div>
  </section>

  <div class="container" style="padding-top:28px;padding-bottom:48px">

    <!-- Filtros -->
    <section class="panel-box" style="margin-bottom:24px">
      <div class="panel-box-header" style="margin-bottom:16px">
        <div><h3>Filtros</h3></div>
      </div>
      <form class="report-filters" method="get" action="relatorios.php">
        <div class="field">
          <label>Data início</label>
          <input class="input" type="date" name="data_inicio" value="<?php echo e($dataInicio); ?>">
        </div>
        <div class="field">
          <label>Data fim</label>
          <input class="input" type="date" name="data_fim" value="<?php echo e($dataFim); ?>">
        </div>
        <div class="field">
          <label>Status</label>
          <select class="input" name="status_id">
            <option value="">Todos</option>
            <?php foreach ($statuses as $s): ?>
              <option value="<?php echo e($s['id']); ?>" <?php echo $statusId == $s['id'] ? 'selected' : ''; ?>><?php echo e($s['nome']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Categoria</label>
          <select class="input" name="categoria_id">
            <option value="">Todas</option>
            <?php foreach ($categorias as $cat): ?>
              <option value="<?php echo e($cat['id']); ?>" <?php echo $categoriaId == $cat['id'] ? 'selected' : ''; ?>><?php echo e($cat['nome']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Prioridade</label>
          <select class="input" name="prioridade">
            <option value="">Todas</option>
            <option value="alta"  <?php echo $prioridade === 'alta'  ? 'selected' : ''; ?>>Alta</option>
            <option value="media" <?php echo $prioridade === 'media' ? 'selected' : ''; ?>>Média</option>
            <option value="baixa" <?php echo $prioridade === 'baixa' ? 'selected' : ''; ?>>Baixa</option>
          </select>
        </div>
        <div class="field">
          <label>Órgão</label>
          <select class="input" name="orgao_id">
            <option value="">Todos</option>
            <?php foreach ($orgaos as $org): ?>
              <option value="<?php echo e($org['id']); ?>" <?php echo $orgaoId == $org['id'] ? 'selected' : ''; ?>><?php echo e($org['nome']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field report-filter-actions">
          <label>&nbsp;</label>
          <button class="btn btn-primary" type="submit">Aplicar</button>
        </div>
      </form>
    </section>

    <!-- Métricas do período -->
    <section class="grid-4 section-sm" style="margin-bottom:24px">
      <div class="metric-card">
        <div class="metric-top"><h3>Total</h3><span class="icon-circle icon-blue">&#128196;</span></div>
        <div class="metric-number"><?php echo $total; ?></div>
        <p class="metric-subtitle">No período selecionado</p>
      </div>
      <div class="metric-card">
        <div class="metric-top"><h3>Abertas</h3><span class="icon-circle icon-orange">&#9888;</span></div>
        <div class="metric-number"><?php echo $abertas; ?></div>
        <p class="metric-subtitle">Aguardando triagem</p>
      </div>
      <div class="metric-card">
        <div class="metric-top"><h3>Em andamento</h3><span class="icon-circle icon-blue">&#128339;</span></div>
        <div class="metric-number"><?php echo $andamento; ?></div>
        <p class="metric-subtitle">Em análise / atendimento</p>
      </div>
      <div class="metric-card">
        <div class="metric-top"><h3>Resolvidas</h3><span class="icon-circle icon-green">&#10003;</span></div>
        <div class="metric-number"><?php echo $resolvidas; ?></div>
        <p class="metric-subtitle">
          <?php if ($taxa > 0): ?>
            <span style="color:var(--success);font-weight:700"><?php echo $taxa; ?>% do total</span>
          <?php else: ?>
            Taxa de resolução
          <?php endif; ?>
        </p>
      </div>
    </section>

    <!-- Tabela + exportar -->
    <section class="panel-box">
      <div class="panel-box-header" style="margin-bottom:16px">
        <div>
          <h3>Resultados <span style="color:var(--text-secondary);font-size:0.9rem;font-weight:400">(<?php echo $total; ?> ocorrência<?php echo $total != 1 ? 's' : ''; ?>)</span></h3>
        </div>
        <div class="report-export-btns">
          <a class="btn btn-outline" href="exportar.php?formato=csv&<?php echo e($qs); ?>">⬇ Exportar CSV</a>
          <a class="btn btn-primary" href="exportar.php?formato=print&<?php echo e($qs); ?>" target="_blank">🖨 Imprimir / PDF</a>
        </div>
      </div>

      <?php if (!$ocorrencias): ?>
        <div class="empty-state">
          <h3>Nenhuma ocorrência no período</h3>
          <p>Ajuste os filtros para ver resultados.</p>
        </div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Protocolo</th>
                <th>Título</th>
                <th>Categoria</th>
                <th>Órgão</th>
                <th>Prioridade</th>
                <th>Status</th>
                <th>Cidadão</th>
                <th>Aberta em</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($ocorrencias as $oc): ?>
                <tr data-href="detalhe-ocorrencia.php?id=<?php echo e($oc['id']); ?>">
                  <td><strong style="color:var(--primary)"><?php echo e($oc['protocolo']); ?></strong></td>
                  <td><?php echo e($oc['titulo']); ?></td>
                  <td><?php echo e($oc['categoria_nome']); ?></td>
                  <td><?php echo e($oc['orgao_nome']); ?></td>
                  <td><span class="priority-<?php echo e($oc['prioridade']); ?>"><?php echo ucfirst(e($oc['prioridade'])); ?></span></td>
                  <td><span class="badge <?php echo e($oc['classe_badge']); ?>"><?php echo e($oc['status_nome']); ?></span></td>
                  <td><?php echo e($oc['cidadao_nome']); ?></td>
                  <td style="white-space:nowrap"><?php echo e(date('d/m/Y', strtotime($oc['aberta_em']))); ?></td>
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
