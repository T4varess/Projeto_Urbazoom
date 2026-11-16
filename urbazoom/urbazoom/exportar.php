<?php
require __DIR__ . '/config.php';
exigir_servidor();

$formato     = get_value('formato', 'csv');
$dataInicio  = get_value('data_inicio', '');
$dataFim     = get_value('data_fim',    '');
$statusId    = get_value('status_id',   '');
$categoriaId = get_value('categoria_id','');
$prioridade  = get_value('prioridade',  '');
$orgaoId     = get_value('orgao_id',    '');

$where  = array();
$params = array();

if ($dataInicio !== '') { $where[] = 'DATE(o.aberta_em) >= :di';  $params['di']  = $dataInicio; }
if ($dataFim    !== '') { $where[] = 'DATE(o.aberta_em) <= :df';  $params['df']  = $dataFim; }
if ($statusId   !== '') { $where[] = 'o.status_id = :sid';        $params['sid'] = $statusId; }
if ($categoriaId!== '') { $where[] = 'o.categoria_id = :cid';     $params['cid'] = $categoriaId; }
if ($prioridade !== '') { $where[] = 'o.prioridade = :pri';       $params['pri'] = $prioridade; }
if ($orgaoId    !== '') { $where[] = 'o.orgao_id = :oid';         $params['oid'] = $orgaoId; }

$clausula = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT o.protocolo, o.titulo, o.localizacao, o.prioridade, o.aberta_em, o.resolvida_em,
    s.nome AS status_nome, c.nome AS categoria_nome, org.nome AS orgao_nome, u.nome AS cidadao_nome
    FROM ocorrencias o
    INNER JOIN status_ocorrencia s  ON s.id   = o.status_id
    INNER JOIN categorias c         ON c.id   = o.categoria_id
    INNER JOIN orgaos org           ON org.id = o.orgao_id
    INNER JOIN usuarios u           ON u.id   = o.usuario_id
    $clausula
    ORDER BY o.aberta_em DESC";

$stmt = banco()->prepare($sql);
$stmt->execute($params);
$ocorrencias = $stmt->fetchAll();

// ── CSV ──────────────────────────────────────────────────────────────────────
if ($formato === 'csv') {
    $filename = 'urbazoom_relatorio_' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 para Excel

    fputcsv($out, array(
        'Protocolo', 'Título', 'Localização', 'Categoria',
        'Prioridade', 'Status', 'Órgão', 'Cidadão',
        'Aberta em', 'Resolvida em'
    ), ';');

    foreach ($ocorrencias as $oc) {
        fputcsv($out, array(
            $oc['protocolo'],
            $oc['titulo'],
            $oc['localizacao'],
            $oc['categoria_nome'],
            ucfirst($oc['prioridade']),
            $oc['status_nome'],
            $oc['orgao_nome'],
            $oc['cidadao_nome'],
            date('d/m/Y H:i', strtotime($oc['aberta_em'])),
            $oc['resolvida_em'] ? date('d/m/Y H:i', strtotime($oc['resolvida_em'])) : '',
        ), ';');
    }

    fclose($out);
    exit;
}

// ── PRINT / PDF ──────────────────────────────────────────────────────────────
$total      = count($ocorrencias);
$resolvidas = 0;
foreach ($ocorrencias as $oc) { if ($oc['resolvida_em']) $resolvidas++; }
$taxa = $total > 0 ? round(($resolvidas / $total) * 100) : 0;

$periodoTexto = '';
if ($dataInicio || $dataFim) {
    $periodoTexto = 'Período: ';
    $periodoTexto .= $dataInicio ? date('d/m/Y', strtotime($dataInicio)) : '–';
    $periodoTexto .= ' até ';
    $periodoTexto .= $dataFim    ? date('d/m/Y', strtotime($dataFim))    : '–';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Relatório Urbazoom — <?php echo date('d/m/Y'); ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 12px; color: #111; padding: 24px; }
    .print-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 2px solid #3b5998; padding-bottom: 12px; }
    .print-header h1 { font-size: 20px; color: #3b5998; }
    .print-header p  { font-size: 11px; color: #555; margin-top: 2px; }
    .print-meta { text-align: right; font-size: 11px; color: #555; }
    .print-metrics { display: flex; gap: 16px; margin-bottom: 20px; }
    .print-metric { flex: 1; border: 1px solid #ddd; border-radius: 6px; padding: 10px; text-align: center; }
    .print-metric strong { display: block; font-size: 22px; color: #3b5998; }
    .print-metric span   { font-size: 10px; color: #666; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th { background: #3b5998; color: #fff; padding: 7px 8px; text-align: left; font-size: 11px; }
    td { padding: 6px 8px; border-bottom: 1px solid #eee; font-size: 11px; }
    tr:nth-child(even) td { background: #f8f9fc; }
    .badge-alta  { color: #dc2626; font-weight: 700; }
    .badge-media { color: #ea580c; }
    .badge-baixa { color: #16a34a; }
    .no-print { margin-bottom: 16px; }
    @media print {
      .no-print { display: none; }
      body { padding: 0; }
    }
  </style>
</head>
<body>

<div class="no-print">
  <button onclick="window.print()" style="padding:8px 18px;background:#3b5998;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:14px">🖨 Imprimir / Salvar como PDF</button>
  <a href="relatorios.php" style="margin-left:12px;font-size:13px;color:#3b5998">← Voltar</a>
</div>

<div class="print-header">
  <div>
    <h1>Urbazoom — Relatório de Ocorrências</h1>
    <p><?php echo e($periodoTexto ?: 'Todos os períodos'); ?></p>
    <?php if ($prioridade || $statusId || $categoriaId || $orgaoId): ?>
      <p style="margin-top:3px;color:#666">Filtros ativos:
        <?php if ($prioridade): ?> Prioridade: <b><?php echo ucfirst(e($prioridade)); ?></b><?php endif; ?>
      </p>
    <?php endif; ?>
  </div>
  <div class="print-meta">
    <p>Gerado em: <?php echo date('d/m/Y \à\s H:i'); ?></p>
    <p>Total de registros: <?php echo $total; ?></p>
  </div>
</div>

<div class="print-metrics">
  <div class="print-metric"><strong><?php echo $total; ?></strong><span>Total</span></div>
  <div class="print-metric"><strong><?php echo $resolvidas; ?></strong><span>Resolvidas</span></div>
  <div class="print-metric"><strong><?php echo $total - $resolvidas; ?></strong><span>Em aberto</span></div>
  <div class="print-metric"><strong><?php echo $taxa; ?>%</strong><span>Taxa resolução</span></div>
</div>

<?php if (!$ocorrencias): ?>
  <p style="color:#888;text-align:center;padding:32px">Nenhuma ocorrência no período selecionado.</p>
<?php else: ?>
<table>
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
      <th>Resolvida em</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($ocorrencias as $oc): ?>
      <tr>
        <td><b><?php echo e($oc['protocolo']); ?></b></td>
        <td><?php echo e($oc['titulo']); ?></td>
        <td><?php echo e($oc['categoria_nome']); ?></td>
        <td><?php echo e($oc['orgao_nome']); ?></td>
        <td class="badge-<?php echo e($oc['prioridade']); ?>"><?php echo ucfirst(e($oc['prioridade'])); ?></td>
        <td><?php echo e($oc['status_nome']); ?></td>
        <td><?php echo e($oc['cidadao_nome']); ?></td>
        <td><?php echo e(date('d/m/Y', strtotime($oc['aberta_em']))); ?></td>
        <td><?php echo $oc['resolvida_em'] ? e(date('d/m/Y', strtotime($oc['resolvida_em']))) : '—'; ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<script>
  // Auto-print quando aberto em aba separada
  if (window.opener || document.referrer) {
    window.addEventListener('load', function() { window.print(); });
  }
</script>
</body>
</html>
<?php exit; ?>
