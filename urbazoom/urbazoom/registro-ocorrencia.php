<?php
require __DIR__ . '/config.php';

exigir_cidadao();

function salvar_evidencias($ocorrenciaId)
{
    if (empty($_FILES['evidencias']['name'][0])) return;

    $maxFiles    = 5;
    $maxBytes    = 10 * 1024 * 1024;
    $allowedExt  = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf');
    $allowedMime = array('image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf');

    $uploadDir = __DIR__ . '/uploads/evidencias/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $nomes = $_FILES['evidencias']['name'];
    $tmps  = $_FILES['evidencias']['tmp_name'];
    $tipos = $_FILES['evidencias']['type'];
    $erros = $_FILES['evidencias']['error'];
    $sizes = $_FILES['evidencias']['size'];

    $salvos = 0;
    foreach ($nomes as $i => $nome) {
        if ($erros[$i] !== UPLOAD_ERR_OK || $salvos >= $maxFiles) continue;
        if ($sizes[$i] > $maxBytes) continue;

        $ext = strtolower(pathinfo($nome, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt)) continue;

        $mime = $tipos[$i];
        if (!in_array($mime, $allowedMime)) continue;

        $filename = 'ev_' . uniqid('', true) . '.' . $ext;
        if (!move_uploaded_file($tmps[$i], $uploadDir . $filename)) continue;

        $stmt = banco()->prepare(
            "INSERT INTO evidencias (ocorrencia_id, nome_original, nome_arquivo, tipo_mime, tamanho)
             VALUES (:oid, :nome, :arquivo, :mime, :size)"
        );
        $stmt->execute(array(
            'oid'     => $ocorrenciaId,
            'nome'    => $nome,
            'arquivo' => $filename,
            'mime'    => $mime,
            'size'    => $sizes[$i],
        ));
        $salvos++;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo      = trim(post_value('titulo', ''));
    $categoriaId = post_value('categoria_id', '');
    $localizacao = trim(post_value('localizacao', ''));
    $prioridade  = post_value('prioridade', 'media');
    $descricao   = trim(post_value('descricao', ''));
    $latRaw      = post_value('lat', '');
    $lngRaw      = post_value('lng', '');
    $lat         = is_numeric($latRaw) ? (float) $latRaw : null;
    $lng         = is_numeric($lngRaw) ? (float) $lngRaw : null;

    if ($titulo === '' || $categoriaId === '' || $localizacao === '' || $descricao === '') {
        ir_para('registro-ocorrencia.php?tipo=error&msg=Preencha+todos+os+campos');
    }

    $stmt = banco()->prepare("SELECT * FROM categorias WHERE id = :id LIMIT 1");
    $stmt->execute(array('id' => $categoriaId));
    $categoria = $stmt->fetch();

    if (!$categoria) {
        ir_para('registro-ocorrencia.php?tipo=error&msg=Categoria+inválida');
    }

    $protocolo = gerar_protocolo();

    $sql = "INSERT INTO ocorrencias (protocolo, usuario_id, categoria_id, orgao_id, titulo, descricao, localizacao, lat, lng, prioridade, status_id)
            VALUES (:protocolo, :usuario_id, :categoria_id, :orgao_id, :titulo, :descricao, :localizacao, :lat, :lng, :prioridade, 1)";
    $stmt = banco()->prepare($sql);
    $stmt->execute(array(
        'protocolo'    => $protocolo,
        'usuario_id'   => usuario_logado()['id'],
        'categoria_id' => $categoria['id'],
        'orgao_id'     => $categoria['orgao_id'],
        'titulo'       => $titulo,
        'descricao'    => $descricao,
        'localizacao'  => $localizacao,
        'lat'          => $lat,
        'lng'          => $lng,
        'prioridade'   => $prioridade,
    ));

    $idOcorrencia = banco()->lastInsertId();

    $sql = "INSERT INTO historico_ocorrencias (ocorrencia_id, usuario_id, status_id, mensagem)
            VALUES (:ocorrencia_id, :usuario_id, 1, :mensagem)";
    $stmt = banco()->prepare($sql);
    $stmt->execute(array(
        'ocorrencia_id' => $idOcorrencia,
        'usuario_id'    => usuario_logado()['id'],
        'mensagem'      => 'Ocorrência criada pelo usuário.',
    ));

    salvar_evidencias($idOcorrencia);

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

        <form class="form-stack" action="registro-ocorrencia.php" method="post" enctype="multipart/form-data">
          <input type="hidden" name="lat" id="lat-input">
          <input type="hidden" name="lng" id="lng-input">

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

          <!-- CEP lookup -->
          <div class="field">
            <label for="cep-input">CEP</label>
            <div class="cep-row">
              <input class="input" id="cep-input" type="text" placeholder="00000-000" maxlength="9" autocomplete="postal-code">
              <button class="btn btn-outline" type="button" id="btn-buscar-cep">Buscar</button>
            </div>
            <span id="cep-status" class="cep-status"></span>
          </div>

          <div id="cep-address-fields" style="display:none">
            <div class="field">
              <label for="cep-logradouro">Logradouro</label>
              <input class="input" id="cep-logradouro" type="text" placeholder="Rua / Avenida">
            </div>
            <div class="cep-row-2">
              <div class="field">
                <label for="cep-numero">Número</label>
                <input class="input" id="cep-numero" type="text" placeholder="Ex.: 123">
              </div>
              <div class="field" style="flex:1">
                <label for="cep-complemento">Complemento</label>
                <input class="input" id="cep-complemento" type="text" placeholder="Apto, sala... (opcional)">
              </div>
            </div>
            <div class="cep-row-2">
              <div class="field" style="flex:1">
                <label for="cep-bairro">Bairro</label>
                <input class="input" id="cep-bairro" type="text" placeholder="Bairro">
              </div>
              <div class="field" style="flex:1">
                <label for="cep-cidade">Cidade / UF</label>
                <input class="input" id="cep-cidade" type="text" readonly>
              </div>
            </div>
          </div>

          <div class="field">
            <label for="localizacao-ocorrencia">Localização completa</label>
            <input class="input" id="localizacao-ocorrencia" name="localizacao" type="text" placeholder="Rua, número, bairro e ponto de referência">
            <span class="field-hint">Preenchida automaticamente pelo CEP ou edite manualmente.</span>
          </div>

          <div class="field">
            <label for="prioridade-ocorrencia">Prioridade</label>
            <select class="select" id="prioridade-ocorrencia" name="prioridade">
              <option value="alta">Alta</option>
              <option value="media" selected>Média</option>
              <option value="baixa">Baixa</option>
            </select>
          </div>

          <div class="field">
            <label for="descricao-ocorrencia">Descrição detalhada</label>
            <textarea class="textarea" id="descricao-ocorrencia" name="descricao" placeholder="Descreva como o problema afeta o local e a população"></textarea>
          </div>

          <!-- Evidências -->
          <div class="field">
            <label>Evidências <span class="field-hint" style="display:inline;margin-top:0">(fotos, prints ou documentos — até 5 arquivos, 10 MB cada)</span></label>
            <div class="upload-zone" id="upload-zone">
              <input type="file" name="evidencias[]" id="evidencias-input" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf" style="display:none">
              <label for="evidencias-input" class="upload-zone-inner" id="upload-trigger">
                <span class="upload-icon">&#128194;</span>
                <p>Arraste os arquivos aqui ou <strong>clique para selecionar</strong></p>
                <p class="field-hint" style="margin-top:4px">JPG, PNG, GIF, WEBP, PDF</p>
              </label>
            </div>
            <div class="upload-preview" id="upload-preview"></div>
          </div>

          <button class="btn btn-primary" type="submit">Registrar ocorrência</button>
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
            <strong>Anexe evidências</strong>
            <p>Fotos do problema aceleram a triagem e o encaminhamento ao órgão correto.</p>
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

<script>
(function () {
  /* ── CEP ── */
  var cepInput      = document.getElementById('cep-input');
  var btnBuscar     = document.getElementById('btn-buscar-cep');
  var cepStatus     = document.getElementById('cep-status');
  var addressFields = document.getElementById('cep-address-fields');
  var fLogradouro   = document.getElementById('cep-logradouro');
  var fNumero       = document.getElementById('cep-numero');
  var fComplemento  = document.getElementById('cep-complemento');
  var fBairro       = document.getElementById('cep-bairro');
  var fCidade       = document.getElementById('cep-cidade');
  var fLocalizacao  = document.getElementById('localizacao-ocorrencia');

  var latInput  = document.getElementById('lat-input');
  var lngInput  = document.getElementById('lng-input');
  var geoTimer  = null;

  function geocodificar(address) {
    clearTimeout(geoTimer);
    if (!address || address.length < 8) return;
    geoTimer = setTimeout(function () {
      fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=br&q=' + encodeURIComponent(address))
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (d && d.length > 0) {
            latInput.value = d[0].lat;
            lngInput.value = d[0].lon;
          }
        })
        .catch(function () {});
    }, 900);
  }

  function buildLocalizacao() {
    var parts = [];
    var log  = fLogradouro.value.trim();
    var num  = fNumero.value.trim();
    var comp = fComplemento.value.trim();
    var bairro = fBairro.value.trim();
    var cidade = fCidade.value.trim();
    if (log) parts.push(log + (num ? ', ' + num : '') + (comp ? ' - ' + comp : ''));
    if (bairro) parts.push(bairro);
    if (cidade) parts.push(cidade);
    fLocalizacao.value = parts.join(' - ');
    geocodificar(fLocalizacao.value);
  }

  function setCepStatus(msg, ok) {
    cepStatus.textContent = msg;
    cepStatus.className = 'cep-status' + (ok === true ? ' cep-ok' : ok === false ? ' cep-err' : '');
  }

  function buscarCep() {
    var cep = cepInput.value.replace(/\D/g, '');
    if (cep.length !== 8) { setCepStatus('CEP deve ter 8 dígitos.', false); return; }
    setCepStatus('Buscando...', null);
    btnBuscar.disabled = true;

    fetch('https://viacep.com.br/ws/' + cep + '/json/')
      .then(function (r) { return r.json(); })
      .then(function (d) {
        btnBuscar.disabled = false;
        if (d.erro) { setCepStatus('CEP não encontrado.', false); addressFields.style.display = 'none'; return; }
        fLogradouro.value  = d.logradouro || '';
        fBairro.value      = d.bairro     || '';
        fCidade.value      = (d.localidade || '') + (d.uf ? ' / ' + d.uf : '');
        fNumero.value      = '';
        fComplemento.value = '';
        addressFields.style.display = 'block';
        buildLocalizacao();
        setCepStatus('Endereço encontrado! 📍 Coordenadas sendo obtidas…', true);
        fNumero.focus();
      })
      .catch(function () {
        btnBuscar.disabled = false;
        setCepStatus('Erro ao buscar CEP. Verifique a conexão.', false);
      });
  }

  cepInput.addEventListener('input', function () {
    var v = this.value.replace(/\D/g, '').substring(0, 8);
    this.value = v.length > 5 ? v.substring(0, 5) + '-' + v.substring(5) : v;
    if (v.length === 8) buscarCep();
  });
  btnBuscar.addEventListener('click', buscarCep);
  cepInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); buscarCep(); } });
  fLogradouro.addEventListener('input', buildLocalizacao);
  fNumero.addEventListener('input', buildLocalizacao);
  fComplemento.addEventListener('input', buildLocalizacao);
  fBairro.addEventListener('input', buildLocalizacao);
  fLocalizacao.addEventListener('change', function () { geocodificar(this.value); });

  /* ── Upload / drag-and-drop ── */
  var zone     = document.getElementById('upload-zone');
  var trigger  = document.getElementById('upload-trigger');
  var fileInput = document.getElementById('evidencias-input');
  var preview  = document.getElementById('upload-preview');
  var MAX_FILES = 5;
  var MAX_BYTES = 10 * 1024 * 1024;
  var ALLOWED   = ['image/jpeg','image/png','image/gif','image/webp','application/pdf'];
  var files     = []; // DataTransfer polyfill list

  function refreshInput() {
    var dt = new DataTransfer();
    files.forEach(function (f) { dt.items.add(f); });
    fileInput.files = dt.files;
  }

  function renderPreview() {
    preview.innerHTML = '';
    files.forEach(function (f, idx) {
      var card = document.createElement('div');
      card.className = 'upload-thumb';

      if (f.type.startsWith('image/')) {
        var img = document.createElement('img');
        img.src = URL.createObjectURL(f);
        img.onload = function () { URL.revokeObjectURL(img.src); };
        card.appendChild(img);
      } else {
        var icon = document.createElement('div');
        icon.className = 'upload-thumb-icon';
        icon.textContent = 'PDF';
        card.appendChild(icon);
      }

      var name = document.createElement('span');
      name.className = 'upload-thumb-name';
      name.textContent = f.name.length > 20 ? f.name.substring(0, 18) + '…' : f.name;
      card.appendChild(name);

      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'upload-thumb-remove';
      btn.innerHTML = '&times;';
      btn.title = 'Remover';
      btn.addEventListener('click', function () { files.splice(idx, 1); renderPreview(); refreshInput(); });
      card.appendChild(btn);

      preview.appendChild(card);
    });
  }

  function addFiles(newFiles) {
    var msgs = [];
    Array.from(newFiles).forEach(function (f) {
      if (files.length >= MAX_FILES) { msgs.push('Máximo de ' + MAX_FILES + ' arquivos.'); return; }
      if (!ALLOWED.includes(f.type)) { msgs.push(f.name + ': tipo não permitido.'); return; }
      if (f.size > MAX_BYTES) { msgs.push(f.name + ': maior que 10 MB.'); return; }
      files.push(f);
    });
    renderPreview();
    refreshInput();
    if (msgs.length) alert(msgs.join('\n'));
  }

  fileInput.addEventListener('change', function () { addFiles(this.files); this.value = ''; });

  zone.addEventListener('dragover', function (e) { e.preventDefault(); zone.classList.add('drag-over'); });
  zone.addEventListener('dragleave', function () { zone.classList.remove('drag-over'); });
  zone.addEventListener('drop', function (e) {
    e.preventDefault();
    zone.classList.remove('drag-over');
    addFiles(e.dataTransfer.files);
  });
}());
</script>
<?php render_footer(); ?>
