<?php
require __DIR__ . '/config.php';

$usuario = usuario_logado();

// ── Ação do servidor: atualizar status + adicionar histórico ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && eh_servidor()) {
    $id_post  = (int) post_value('ocorrencia_id', 0);
    $st_id    = (int) post_value('status_id', 0);
    $org_id   = (int) post_value('orgao_id', 0);
    $mensagem = trim(post_value('mensagem', ''));

    if (!$id_post || !$st_id || !$org_id || $mensagem === '') {
        ir_para('detalhe-ocorrencia.php?id=' . $id_post . '&tipo=error&msg=Preencha+todos+os+campos+obrigat%C3%B3rios');
    }

    if ($st_id === 4) {
        $sqlUp = "UPDATE ocorrencias SET status_id = :st, orgao_id = :org, resolvida_em = NOW(), atualizada_em = NOW() WHERE id = :id";
    } else {
        $sqlUp = "UPDATE ocorrencias SET status_id = :st, orgao_id = :org, resolvida_em = NULL, atualizada_em = NOW() WHERE id = :id";
    }

    $stmt = banco()->prepare($sqlUp);
    $stmt->execute(array('st' => $st_id, 'org' => $org_id, 'id' => $id_post));

    $hist = banco()->prepare("INSERT INTO historico_ocorrencias (ocorrencia_id, usuario_id, status_id, mensagem) VALUES (:oc, :u, :st, :msg)");
    $hist->execute(array('oc' => $id_post, 'u' => $usuario['id'], 'st' => $st_id, 'msg' => $mensagem));

    ir_para('detalhe-ocorrencia.php?id=' . $id_post . '&tipo=success&msg=Ocorr%C3%AAncia+atualizada+com+sucesso');
}

// ── Carregar dados ──
$id = (int) get_value('id', 0);

$sqlBase = "SELECT
    o.*,
    s.nome AS status_nome,
    s.classe_badge,
    c.nome AS categoria_nome,
    org.nome AS orgao_nome,
    u.nome AS cidadao_nome
    FROM ocorrencias o
    INNER JOIN status_ocorrencia s ON s.id = o.status_id
    INNER JOIN categorias c ON c.id = o.categoria_id
    INNER JOIN orgaos org ON org.id = o.orgao_id
    INNER JOIN usuarios u ON u.id = o.usuario_id";

if ($id > 0) {
    if (!$usuario || eh_servidor()) {
        $stmt = banco()->prepare($sqlBase . " WHERE o.id = :id LIMIT 1");
        $stmt->execute(array('id' => $id));
    } else {
        $stmt = banco()->prepare($sqlBase . " WHERE o.id = :id AND o.usuario_id = :uid LIMIT 1");
        $stmt->execute(array('id' => $id, 'uid' => $usuario['id']));
    }
    $ocorrencia = $stmt->fetch();
} else {
    if (!$usuario || eh_servidor()) {
        $ocorrencia = banco()->query($sqlBase . " ORDER BY o.aberta_em DESC LIMIT 1")->fetch();
    } else {
        $stmt = banco()->prepare($sqlBase . " WHERE o.usuario_id = :uid ORDER BY o.aberta_em DESC LIMIT 1");
        $stmt->execute(array('uid' => $usuario['id']));
        $ocorrencia = $stmt->fetch();
    }
}

$historico  = array();
$evidencias = array();
if ($ocorrencia) {
    $stmtH = banco()->prepare("SELECT h.criado_em, h.mensagem, u.nome AS autor
        FROM historico_ocorrencias h
        LEFT JOIN usuarios u ON u.id = h.usuario_id
        WHERE h.ocorrencia_id = :id
        ORDER BY h.criado_em ASC");
    $stmtH->execute(array('id' => $ocorrencia['id']));
    $historico = $stmtH->fetchAll();

    $stmtE = banco()->prepare("SELECT id, nome_original, nome_arquivo, tipo_mime, tamanho, enviado_em
        FROM evidencias WHERE ocorrencia_id = :id ORDER BY enviado_em ASC");
    $stmtE->execute(array('id' => $ocorrencia['id']));
    $evidencias = $stmtE->fetchAll();
}

// Opções para o formulário admin
$statuses = array();
$orgaos   = array();
if (eh_servidor()) {
    $statuses = banco()->query("SELECT id, nome FROM status_ocorrencia ORDER BY ordem_exibicao")->fetchAll();
    $orgaos   = banco()->query("SELECT id, nome FROM orgaos WHERE ativo = 1 ORDER BY nome")->fetchAll();
}

render_header('Urbazoom | Detalhes da Ocorrência', 'detalhe-ocorrencia.php');
?>
<main class="page-shell dashboard-page">
  <div class="container">
    <?php render_alerta(); ?>
    <div class="panel-header">
      <div>
        <h1>Detalhes da ocorrência</h1>
        <p>Informações completas e histórico de atendimento.</p>
      </div>
      <a class="btn btn-outline" href="<?php echo eh_servidor() ? 'gerenciar-ocorrencias.php' : 'acompanhamento.php'; ?>">← Voltar</a>
    </div>

    <?php if (!$ocorrencia) : ?>
      <div class="empty-state">
        <h3>Ocorrência não encontrada</h3>
        <p>Não foi possível localizar a ocorrência solicitada.</p>
      </div>
    <?php else : ?>
      <div class="detail-layout">

        <section class="panel-box">
          <div class="detail-header">
            <span class="badge <?php echo e($ocorrencia['classe_badge']); ?>"><?php echo e($ocorrencia['status_nome']); ?></span>
            <h3><?php echo e($ocorrencia['titulo']); ?></h3>
            <p>Protocolo #<?php echo e($ocorrencia['protocolo']); ?></p>
          </div>

          <div class="detail-grid">
            <div class="detail-item"><strong>Categoria</strong><span><?php echo e($ocorrencia['categoria_nome']); ?></span></div>
            <div class="detail-item"><strong>Localização</strong><span><?php echo e($ocorrencia['localizacao']); ?></span></div>
            <div class="detail-item"><strong>Data de abertura</strong><span><?php echo e(date('d/m/Y H:i', strtotime($ocorrencia['aberta_em']))); ?></span></div>
            <div class="detail-item"><strong>Prioridade</strong>
              <span class="priority-<?php echo e($ocorrencia['prioridade']); ?>"><?php echo e(ucfirst($ocorrencia['prioridade'])); ?></span>
            </div>
            <?php if (eh_servidor()) : ?>
              <div class="detail-item"><strong>Cidadão</strong><span><?php echo e($ocorrencia['cidadao_nome']); ?></span></div>
            <?php endif; ?>
            <div class="detail-item"><strong>Órgão responsável</strong><span><?php echo e($ocorrencia['orgao_nome']); ?></span></div>
            <div class="detail-item detail-item-wide"><strong>Descrição</strong><span><?php echo e($ocorrencia['descricao']); ?></span></div>
            <?php if ($evidencias) : ?>
            <div class="detail-item detail-item-wide">
              <strong>Evidências</strong>
              <div class="evidence-gallery">
                <?php foreach ($evidencias as $ev) : ?>
                  <?php $isImg = strpos($ev['tipo_mime'], 'image/') === 0; ?>
                  <?php $url = 'uploads/evidencias/' . e($ev['nome_arquivo']); ?>
                  <a class="evidence-thumb" href="<?php echo $url; ?>" target="_blank" title="<?php echo e($ev['nome_original']); ?>">
                    <?php if ($isImg) : ?>
                      <img src="<?php echo $url; ?>" alt="<?php echo e($ev['nome_original']); ?>">
                    <?php else : ?>
                      <div class="evidence-thumb-icon">PDF</div>
                    <?php endif; ?>
                    <span><?php echo e(strlen($ev['nome_original']) > 18 ? substr($ev['nome_original'], 0, 16) . '…' : $ev['nome_original']); ?></span>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endif; ?>
            <?php if ($ocorrencia['resolvida_em']) : ?>
              <div class="detail-item"><strong>Resolvida em</strong><span><?php echo e(date('d/m/Y H:i', strtotime($ocorrencia['resolvida_em']))); ?></span></div>
            <?php endif; ?>
          </div>
        </section>

        <aside class="panel-box">
          <div class="panel-box-header">
            <div>
              <h3>Histórico</h3>
              <p>Atualizações registradas.</p>
            </div>
          </div>
          <div class="timeline-list">
            <?php if (!$historico) : ?>
              <p style="color:var(--text-secondary);font-size:0.9rem">Nenhuma atualização ainda.</p>
            <?php else : ?>
              <?php foreach ($historico as $item) : ?>
                <div class="timeline-item-box">
                  <strong><?php echo e(date('d/m/Y - H:i', strtotime($item['criado_em']))); ?></strong>
                  <p><?php echo e($item['mensagem']); ?></p>
                  <?php if ($item['autor']) : ?>
                    <span class="timeline-author">por <?php echo e($item['autor']); ?></span>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </aside>
      </div>

      <?php if (eh_servidor()) : ?>
        <div class="action-box">
          <h3>Atualizar ocorrência</h3>
          <form method="POST" action="detalhe-ocorrencia.php">
            <input type="hidden" name="ocorrencia_id" value="<?php echo e($ocorrencia['id']); ?>">
            <div class="grid-3" style="gap:16px;margin-bottom:16px">
              <div class="field">
                <label for="status_id">Novo status</label>
                <select class="select" id="status_id" name="status_id" required>
                  <?php foreach ($statuses as $st) : ?>
                    <option value="<?php echo e($st['id']); ?>" <?php echo $st['id'] == $ocorrencia['status_id'] ? 'selected' : ''; ?>>
                      <?php echo e($st['nome']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="field">
                <label for="orgao_id">Órgão responsável</label>
                <select class="select" id="orgao_id" name="orgao_id" required>
                  <?php foreach ($orgaos as $org) : ?>
                    <option value="<?php echo e($org['id']); ?>" <?php echo $org['id'] == $ocorrencia['orgao_id'] ? 'selected' : ''; ?>>
                      <?php echo e($org['nome']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="field" style="margin-bottom:16px">
              <label for="mensagem">Nota / atualização <span style="color:var(--error)">*</span></label>
              <textarea class="textarea" id="mensagem" name="mensagem" placeholder="Descreva a ação tomada ou o motivo da atualização..." required></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Salvar atualização</button>
          </form>
        </div>
      <?php endif; ?>

    <?php endif; ?>
  </div>
</main>
<?php render_footer(); ?>
