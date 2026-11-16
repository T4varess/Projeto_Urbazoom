<?php
require __DIR__ . '/config.php';

$usuario = usuario_logado();
$id = (int) get_value('id', 0);

if ($id > 0) {
    if (!$usuario || eh_servidor()) {
        $sql = "SELECT
            o.*,
            s.nome AS status_nome,
            s.classe_badge,
            c.nome AS categoria_nome,
            org.nome AS orgao_nome
            FROM ocorrencias o
            INNER JOIN status_ocorrencia s ON s.id = o.status_id
            INNER JOIN categorias c ON c.id = o.categoria_id
            INNER JOIN orgaos org ON org.id = o.orgao_id
            WHERE o.id = :id
            LIMIT 1";
        $stmt = banco()->prepare($sql);
        $stmt->execute(array('id' => $id));
    } else {
        $sql = "SELECT
            o.*,
            s.nome AS status_nome,
            s.classe_badge,
            c.nome AS categoria_nome,
            org.nome AS orgao_nome
            FROM ocorrencias o
            INNER JOIN status_ocorrencia s ON s.id = o.status_id
            INNER JOIN categorias c ON c.id = o.categoria_id
            INNER JOIN orgaos org ON org.id = o.orgao_id
            WHERE o.id = :id AND o.usuario_id = :usuario_id
            LIMIT 1";
        $stmt = banco()->prepare($sql);
        $stmt->execute(array('id' => $id, 'usuario_id' => $usuario['id']));
    }

    $ocorrencia = $stmt->fetch();
} else {
    if (!$usuario || eh_servidor()) {
        $sql = "SELECT
            o.*,
            s.nome AS status_nome,
            s.classe_badge,
            c.nome AS categoria_nome,
            org.nome AS orgao_nome
            FROM ocorrencias o
            INNER JOIN status_ocorrencia s ON s.id = o.status_id
            INNER JOIN categorias c ON c.id = o.categoria_id
            INNER JOIN orgaos org ON org.id = o.orgao_id
            ORDER BY o.aberta_em DESC
            LIMIT 1";
        $ocorrencia = banco()->query($sql)->fetch();
    } else {
        $sql = "SELECT
            o.*,
            s.nome AS status_nome,
            s.classe_badge,
            c.nome AS categoria_nome,
            org.nome AS orgao_nome
            FROM ocorrencias o
            INNER JOIN status_ocorrencia s ON s.id = o.status_id
            INNER JOIN categorias c ON c.id = o.categoria_id
            INNER JOIN orgaos org ON org.id = o.orgao_id
            WHERE o.usuario_id = :usuario_id
            ORDER BY o.aberta_em DESC
            LIMIT 1";
        $stmt = banco()->prepare($sql);
        $stmt->execute(array('usuario_id' => $usuario['id']));
        $ocorrencia = $stmt->fetch();
    }
}

$historico = array();

if ($ocorrencia) {
    $stmt = banco()->prepare("SELECT criado_em, mensagem FROM historico_ocorrencias WHERE ocorrencia_id = :id ORDER BY criado_em ASC");
    $stmt->execute(array('id' => $ocorrencia['id']));
    $historico = $stmt->fetchAll();
}

render_header('Urbazoom | Detalhes da Ocorrência', 'detalhe-ocorrencia.php');
?>
<main class="page-shell dashboard-page">
  <div class="container">
    <?php render_alerta(); ?>
    <div class="panel-header">
      <div>
        <h1>Detalhes da ocorrência</h1>
        <p>Visualize informações completas da solicitação e o histórico de atendimento.</p>
      </div>
      <a class="btn btn-outline" href="<?php echo eh_servidor() ? 'painel-admin.php' : 'acompanhamento.php'; ?>">Voltar</a>
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
            <div>
              <span class="badge <?php echo e($ocorrencia['classe_badge']); ?>"><?php echo e($ocorrencia['status_nome']); ?></span>
              <h3><?php echo e($ocorrencia['categoria_nome']); ?></h3>
              <p>Protocolo #<?php echo e($ocorrencia['protocolo']); ?></p>
            </div>
          </div>

          <div class="detail-grid">
            <div class="detail-item"><strong>Título</strong><span><?php echo e($ocorrencia['titulo']); ?></span></div>
            <div class="detail-item"><strong>Localização</strong><span><?php echo e($ocorrencia['localizacao']); ?></span></div>
            <div class="detail-item"><strong>Data de abertura</strong><span><?php echo e(date('d/m/Y H:i', strtotime($ocorrencia['aberta_em']))); ?></span></div>
            <div class="detail-item"><strong>Prioridade</strong><span><?php echo e(ucfirst($ocorrencia['prioridade'])); ?></span></div>
            <div class="detail-item detail-item-wide"><strong>Descrição</strong><span><?php echo e($ocorrencia['descricao']); ?></span></div>
            <div class="detail-item detail-item-wide"><strong>Órgão responsável</strong><span><?php echo e($ocorrencia['orgao_nome']); ?></span></div>
          </div>
        </section>

        <aside class="panel-box">
          <div class="panel-box-header">
            <div>
              <h3>Histórico</h3>
              <p>Atualizações registradas até o momento.</p>
            </div>
          </div>

          <div class="timeline-list">
            <?php foreach ($historico as $item) : ?>
              <div class="timeline-item-box">
                <strong><?php echo e(date('d/m/Y - H:i', strtotime($item['criado_em']))); ?></strong>
                <p><?php echo e($item['mensagem']); ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        </aside>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php render_footer(); ?>
