<?php
require __DIR__ . '/config.php';
exigir_servidor();

// ── Filtros ──
$filtroStatus    = (int) get_value('status_id', 0);
$filtroCategoria = (int) get_value('categoria_id', 0);
$filtroOrgao     = (int) get_value('orgao_id', 0);
$filtroPrioridade = get_value('prioridade', '');
$filtroPrioridade = in_array($filtroPrioridade, array('baixa', 'media', 'alta')) ? $filtroPrioridade : '';

$where  = array();
$params = array();

if ($filtroStatus > 0) {
    $where[] = 'o.status_id = :status_id';
    $params['status_id'] = $filtroStatus;
}
if ($filtroCategoria > 0) {
    $where[] = 'o.categoria_id = :categoria_id';
    $params['categoria_id'] = $filtroCategoria;
}
if ($filtroOrgao > 0) {
    $where[] = 'o.orgao_id = :orgao_id';
    $params['orgao_id'] = $filtroOrgao;
}
if ($filtroPrioridade !== '') {
    $where[] = 'o.prioridade = :prioridade';
    $params['prioridade'] = $filtroPrioridade;
}

$whereClause = $where ? ' WHERE ' . implode(' AND ', $where) : '';

// ── Métricas globais (sem filtro de paginação) ──
$sqlMetricas = "SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN s.slug = 'aberta' THEN 1 ELSE 0 END) AS abertas,
    SUM(CASE WHEN s.slug IN ('em-analise','em-atendimento') THEN 1 ELSE 0 END) AS andamento,
    SUM(CASE WHEN s.slug = 'resolvida' THEN 1 ELSE 0 END) AS resolvidas
    FROM ocorrencias o
    INNER JOIN status_ocorrencia s ON s.id = o.status_id" . $whereClause;
$stmtM = banco()->prepare($sqlMetricas);
$stmtM->execute($params);
$metricas = $stmtM->fetch();

// ── Paginação ──
$porPagina   = 15;
$pagina      = max(1, (int) get_value('pagina', 1));
$totalRows   = (int) $metricas['total'];
$totalPaginas = max(1, (int) ceil($totalRows / $porPagina));
$pagina      = min($pagina, $totalPaginas);
$offset      = ($pagina - 1) * $porPagina;

// ── Lista de ocorrências ──
$sqlLista = "SELECT
    o.id, o.protocolo, o.titulo, o.localizacao, o.prioridade, o.aberta_em,
    s.nome AS status_nome, s.classe_badge,
    c.nome AS categoria_nome,
    org.nome AS orgao_nome,
    u.nome AS cidadao_nome
    FROM ocorrencias o
    INNER JOIN status_ocorrencia s ON s.id = o.status_id
    INNER JOIN categorias c ON c.id = o.categoria_id
    INNER JOIN orgaos org ON org.id = o.orgao_id
    INNER JOIN usuarios u ON u.id = o.usuario_id
    " . $whereClause . "
    ORDER BY o.aberta_em DESC";

$stmtL = banco()->prepare($sqlLista);
$stmtL->execute($params);
$todas = $stmtL->fetchAll();
$ocorrencias = array_slice($todas, $offset, $porPagina);

// ── Opções de filtro ──
$statuses   = banco()->query("SELECT id, nome, slug FROM status_ocorrencia ORDER BY ordem_exibicao")->fetchAll();
$categorias = banco()->query("SELECT id, nome FROM categorias WHERE ativa = 1 ORDER BY nome")->fetchAll();
$orgaos     = banco()->query("SELECT id, nome FROM orgaos WHERE ativo = 1 ORDER BY nome")->fetchAll();

// ── Monta query string de filtros para links de paginação ──
function qs($extra = array())
{
    $base = array();
    foreach (array('status_id','categoria_id','orgao_id','prioridade') as $k) {
        $v = get_value($k, '');
        if ($v !== '' && $v !== '0') {
            $base[$k] = $v;
        }
    }
    foreach ($extra as $k => $v) {
        $base[$k] = $v;
    }
    return $base ? '?' . http_build_query($base) : '';
}

render_header('Urbazoom | Gerenciar Ocorrências', 'gerenciar-ocorrencias.php');
?>
<main class="page-shell dashboard-page">
  <section class="page-banner">
    <div class="container">
      <?php render_alerta(); ?>
      <div class="panel-header">
        <div>
          <h1>Gerenciar ocorrências</h1>
          <p>Filtre, visualize e atualize as demandas registradas pelos cidadãos.</p>
        </div>
      </div>
    </div>
  </section>

  <div class="container">

    <!-- Métricas -->
    <section class="grid-4 section-sm">
      <article class="metric-card">
        <div class="metric-top"><h3>Total</h3><span class="icon-circle icon-blue">&#128196;</span></div>
        <div class="metric-number"><?php echo e((int) $metricas['total']); ?></div>
        <p class="metric-subtitle">No filtro atual</p>
      </article>
      <article class="metric-card">
        <div class="metric-top"><h3>Abertas</h3><span class="icon-circle icon-orange">&#9888;</span></div>
        <div class="metric-number"><?php echo e((int) $metricas['abertas']); ?></div>
        <p class="metric-subtitle">Aguardando triagem</p>
      </article>
      <article class="metric-card">
        <div class="metric-top"><h3>Em Andamento</h3><span class="icon-circle icon-blue">&#128339;</span></div>
        <div class="metric-number"><?php echo e((int) $metricas['andamento']); ?></div>
        <p class="metric-subtitle">Com equipe acionada</p>
      </article>
      <article class="metric-card">
        <div class="metric-top"><h3>Resolvidas</h3><span class="icon-circle icon-green">&#10003;</span></div>
        <div class="metric-number"><?php echo e((int) $metricas['resolvidas']); ?></div>
        <p class="metric-subtitle">Chamados finalizados</p>
      </article>
    </section>

    <!-- Filtros -->
    <section class="panel-box" style="margin-bottom:24px">
      <form method="GET" action="gerenciar-ocorrencias.php">
        <div class="filters">
          <select class="select" name="status_id" style="min-width:160px">
            <option value="0">Todos os status</option>
            <?php foreach ($statuses as $st) : ?>
              <option value="<?php echo e($st['id']); ?>" <?php echo $filtroStatus == $st['id'] ? 'selected' : ''; ?>>
                <?php echo e($st['nome']); ?>
              </option>
            <?php endforeach; ?>
          </select>

          <select class="select" name="categoria_id" style="min-width:180px">
            <option value="0">Todas as categorias</option>
            <?php foreach ($categorias as $cat) : ?>
              <option value="<?php echo e($cat['id']); ?>" <?php echo $filtroCategoria == $cat['id'] ? 'selected' : ''; ?>>
                <?php echo e($cat['nome']); ?>
              </option>
            <?php endforeach; ?>
          </select>

          <select class="select" name="orgao_id" style="min-width:200px">
            <option value="0">Todos os órgãos</option>
            <?php foreach ($orgaos as $org) : ?>
              <option value="<?php echo e($org['id']); ?>" <?php echo $filtroOrgao == $org['id'] ? 'selected' : ''; ?>>
                <?php echo e($org['nome']); ?>
              </option>
            <?php endforeach; ?>
          </select>

          <select class="select" name="prioridade" style="min-width:140px">
            <option value="">Todas as prioridades</option>
            <option value="alta"  <?php echo $filtroPrioridade === 'alta'  ? 'selected' : ''; ?>>Alta</option>
            <option value="media" <?php echo $filtroPrioridade === 'media' ? 'selected' : ''; ?>>Média</option>
            <option value="baixa" <?php echo $filtroPrioridade === 'baixa' ? 'selected' : ''; ?>>Baixa</option>
          </select>

          <button class="btn btn-primary" type="submit">Filtrar</button>
          <a class="btn btn-outline" href="gerenciar-ocorrencias.php">Limpar</a>
        </div>
      </form>
    </section>

    <!-- Lista -->
    <section class="panel-box">
      <div class="panel-box-header">
        <div>
          <h3>Ocorrências</h3>
          <p><?php echo e($totalRows); ?> resultado<?php echo $totalRows !== 1 ? 's' : ''; ?> encontrado<?php echo $totalRows !== 1 ? 's' : ''; ?>.</p>
        </div>
      </div>

      <?php if (!$ocorrencias) : ?>
        <div class="empty-state">
          <h3>Nenhuma ocorrência encontrada</h3>
          <p>Tente ajustar os filtros para encontrar resultados.</p>
          <a class="btn btn-outline" href="gerenciar-ocorrencias.php">Limpar filtros</a>
        </div>
      <?php else : ?>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Protocolo</th>
                <th>Título</th>
                <th>Cidadão</th>
                <th>Categoria</th>
                <th>Órgão</th>
                <th>Prioridade</th>
                <th>Status</th>
                <th>Data</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($ocorrencias as $oc) : ?>
                <tr data-href="detalhe-ocorrencia.php?id=<?php echo e($oc['id']); ?>">
                  <td><strong style="color:var(--primary)"><?php echo e($oc['protocolo']); ?></strong></td>
                  <td><?php echo e($oc['titulo']); ?></td>
                  <td><?php echo e($oc['cidadao_nome']); ?></td>
                  <td><?php echo e($oc['categoria_nome']); ?></td>
                  <td><?php echo e($oc['orgao_nome']); ?></td>
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

        <?php if ($totalPaginas > 1) : ?>
          <div class="pagination">
            <?php if ($pagina > 1) : ?>
              <a href="gerenciar-ocorrencias.php<?php echo qs(array('pagina' => $pagina - 1)); ?>">‹</a>
            <?php endif; ?>

            <?php for ($p = 1; $p <= $totalPaginas; $p++) : ?>
              <?php if ($p == $pagina) : ?>
                <span class="pag-ativa"><?php echo $p; ?></span>
              <?php elseif ($p == 1 || $p == $totalPaginas || abs($p - $pagina) <= 2) : ?>
                <a href="gerenciar-ocorrencias.php<?php echo qs(array('pagina' => $p)); ?>"><?php echo $p; ?></a>
              <?php elseif (abs($p - $pagina) == 3) : ?>
                <span>…</span>
              <?php endif; ?>
            <?php endfor; ?>

            <?php if ($pagina < $totalPaginas) : ?>
              <a href="gerenciar-ocorrencias.php<?php echo qs(array('pagina' => $pagina + 1)); ?>">›</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>

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
