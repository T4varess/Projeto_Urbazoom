<?php
require __DIR__ . '/config.php';

exigir_cidadao();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim(post_value('titulo', ''));
    $categoriaId = post_value('categoria_id', '');
    $localizacao = trim(post_value('localizacao', ''));
    $prioridade = post_value('prioridade', 'media');
    $descricao = trim(post_value('descricao', ''));

    if ($titulo === '' || $categoriaId === '' || $localizacao === '' || $descricao === '') {
        ir_para('registro-ocorrencia.php?tipo=error&msg=Preencha+todos+os+campos');
    }

    $stmt = banco()->prepare("SELECT * FROM categorias WHERE id = :id LIMIT 1");
    $stmt->execute(array('id' => $categoriaId));
    $categoria = $stmt->fetch();

    if (!$categoria) {
        ir_para('registro-ocorrencia.php?tipo=error&msg=Categoria+invalida');
    }

    $protocolo = gerar_protocolo();

    $sql = "INSERT INTO ocorrencias (protocolo, usuario_id, categoria_id, orgao_id, titulo, descricao, localizacao, prioridade, status_id)
            VALUES (:protocolo, :usuario_id, :categoria_id, :orgao_id, :titulo, :descricao, :localizacao, :prioridade, 1)";
    $stmt = banco()->prepare($sql);
    $stmt->execute(array(
        'protocolo' => $protocolo,
        'usuario_id' => usuario_logado()['id'],
        'categoria_id' => $categoria['id'],
        'orgao_id' => $categoria['orgao_id'],
        'titulo' => $titulo,
        'descricao' => $descricao,
        'localizacao' => $localizacao,
        'prioridade' => $prioridade,
    ));

    $idOcorrencia = banco()->lastInsertId();

    $sql = "INSERT INTO historico_ocorrencias (ocorrencia_id, usuario_id, status_id, mensagem) VALUES (:ocorrencia_id, :usuario_id, 1, :mensagem)";
    $stmt = banco()->prepare($sql);
    $stmt->execute(array(
        'ocorrencia_id' => $idOcorrencia,
        'usuario_id' => usuario_logado()['id'],
        'mensagem' => 'Ocorrência criada pelo usuário.',
    ));

    ir_para('acompanhamento.php?tipo=success&msg=Ocorrência+registrada+com+sucesso');
}

$categorias = banco()->query("SELECT id, nome FROM categorias WHERE ativa = 1 ORDER BY nome")->fetchAll();

render_header('Urbazoom | Registro de Ocorrência', 'registro-ocorrencia.php');
?>
<main class="page-shell dashboard-page">
  <div class="container">
    <?php render_alerta(); ?>
    <div class="panel-header">
      <div>
        <h1>Registro de Ocorrência</h1>
        <p>Informe o problema, localização e prioridade para encaminhamento ao órgão responsável.</p>
      </div>
      <a class="btn btn-outline" href="painel-cidadao.php">Voltar ao meu painel</a>
    </div>

    <div class="form-page-grid">
      <section class="panel-box">
        <div class="panel-box-header">
          <div>
            <h3>Formulário de registro</h3>
            <p>Preencha os dados para abrir sua solicitação urbana.</p>
          </div>
        </div>

        <form class="form-stack" action="registro-ocorrencia.php" method="post">
          <div class="field">
              <label for="titulo-ocorrencia">Título da ocorrência</label>
              <input class="input" id="titulo-ocorrencia" name="titulo" type="text" placeholder="Ex.: Poste sem luz na rua principal">
          </div>
          <div class="field">
            <label for="categoria-ocorrencia">Categoria</label>
            <select class="select" id="categoria-ocorrencia" name="categoria_id">
              <option value="">Selecione</option>
              <?php foreach ($categorias as $categoria) : ?>
                <option value="<?php echo e($categoria['id']); ?>"><?php echo e($categoria['nome']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
              <label for="localizacao-ocorrencia">Localização</label>
              <input class="input" id="localizacao-ocorrencia" name="localizacao" type="text" placeholder="Rua, número, bairro e ponto de referência">
          </div>
          <div class="field">
            <label for="prioridade-ocorrencia">Prioridade</label>
            <select class="select" id="prioridade-ocorrencia" name="prioridade">
              <option value="alta">Alta</option>
              <option value="media">Média</option>
              <option value="baixa">Baixa</option>
            </select>
          </div>
          <div class="field">
              <label for="descricao-ocorrencia">Descrição detalhada</label>
              <textarea class="textarea" id="descricao-ocorrencia" name="descricao" placeholder="Descreva como o problema afeta o local e a população"></textarea>
          </div>
          <button class="btn btn-primary" type="submit">Registrar ocorrencia</button>
        </form>
      </section>

      <aside class="panel-box">
        <div class="panel-box-header">
          <div>
            <h3>Orientações</h3>
            <p>Informações bem preenchidas ajudam no atendimento.</p>
          </div>
        </div>
        <div class="helper-list">
          <div class="helper-item">
            <strong>Descreva o local com precisão</strong>
            <p>Adicione rua, número, bairro e referências próximas para facilitar a localização.</p>
          </div>
          <div class="helper-item">
            <strong>Selecione a prioridade correta</strong>
            <p>Use alta prioridade quando houver risco direto para circulação ou segurança.</p>
          </div>
          <div class="helper-item">
            <strong>Inclua detalhes objetivos</strong>
            <p>Quanto mais claro for o relato, mais rápido o chamado chega ao setor adequado.</p>
          </div>
        </div>
      </aside>
    </div>
  </div>
</main>
<?php render_footer(); ?>
