<?php
require __DIR__ . '/config.php';

exigir_login();

$usuario = usuario_logado();

// Carrega dados completos do banco
$stmtU = banco()->prepare("SELECT id, nome, cpf, email, tipo, criado_em FROM usuarios WHERE id = :id LIMIT 1");
$stmtU->execute(array('id' => $usuario['id']));
$dados = $stmtU->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = post_value('acao', '');

    // ── Atualizar dados pessoais ──
    if ($acao === 'dados') {
        $nome  = trim(post_value('nome', ''));
        $email = trim(post_value('email', ''));

        if ($nome === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            ir_para('perfil.php?tipo=error&msg=Nome+e+e-mail+válidos+são+obrigatórios');
        }

        $stmtCheck = banco()->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id LIMIT 1");
        $stmtCheck->execute(array('email' => $email, 'id' => $usuario['id']));
        if ($stmtCheck->fetch()) {
            ir_para('perfil.php?tipo=error&msg=E-mail+já+utilizado+por+outro+usuário');
        }

        banco()->prepare("UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id")
               ->execute(array('nome' => $nome, 'email' => $email, 'id' => $usuario['id']));

        $_SESSION['usuario']['nome']  = $nome;
        $_SESSION['usuario']['email'] = $email;

        ir_para('perfil.php?tipo=success&msg=Dados+atualizados+com+sucesso');
    }

    // ── Alterar senha ──
    if ($acao === 'senha') {
        $senhaAtual = post_value('senha_atual', '');
        $novaSenha  = post_value('nova_senha', '');
        $confirmar  = post_value('confirmar_nova', '');

        if ($senhaAtual === '' || $novaSenha === '' || $confirmar === '') {
            ir_para('perfil.php?tipo=error&msg=Preencha+todos+os+campos+de+senha');
        }

        $stmtS = banco()->prepare("SELECT senha_hash FROM usuarios WHERE id = :id LIMIT 1");
        $stmtS->execute(array('id' => $usuario['id']));
        $row = $stmtS->fetch();

        if (!senha_confere($senhaAtual, $row['senha_hash'])) {
            ir_para('perfil.php?tipo=error&msg=Senha+atual+incorreta');
        }
        if (strlen($novaSenha) < 6) {
            ir_para('perfil.php?tipo=error&msg=Nova+senha+deve+ter+ao+menos+6+caracteres');
        }
        if ($novaSenha !== $confirmar) {
            ir_para('perfil.php?tipo=error&msg=Confirmação+de+senha+não+confere');
        }

        banco()->prepare("UPDATE usuarios SET senha_hash = :hash WHERE id = :id")
               ->execute(array('hash' => gerar_senha($novaSenha), 'id' => $usuario['id']));

        ir_para('perfil.php?tipo=success&msg=Senha+alterada+com+sucesso');
    }
}

$paginaAtual = 'perfil.php';
render_header('Urbazoom | Meu Perfil', $paginaAtual);
?>
<main class="page-shell dashboard-page">

  <section class="page-banner">
    <div class="container">
      <?php render_alerta(); ?>
      <div class="panel-header">
        <div>
          <h1>Meu perfil</h1>
          <p>Gerencie seus dados pessoais e configurações de acesso.</p>
        </div>
        <a class="btn btn-outline" href="<?php echo $usuario['tipo'] === 'servidor' ? 'painel-admin.php' : 'painel-cidadao.php'; ?>">← Voltar ao painel</a>
      </div>
    </div>
  </section>

  <div class="container" style="padding-top:32px;padding-bottom:48px">
    <div class="perfil-grid">

      <!-- Dados pessoais -->
      <section class="panel-box">
        <div class="panel-box-header" style="margin-bottom:20px">
          <div>
            <h3>Dados pessoais</h3>
            <p>Atualize seu nome e e-mail de acesso.</p>
          </div>
        </div>

        <!-- Info somente leitura -->
        <div class="detail-grid" style="margin-bottom:24px">
          <div class="detail-item">
            <strong>CPF</strong>
            <span><?php echo e($dados['cpf']); ?></span>
          </div>
          <div class="detail-item">
            <strong>Tipo de conta</strong>
            <span><?php echo $dados['tipo'] === 'servidor' ? 'Servidor' : 'Cidadão'; ?></span>
          </div>
          <div class="detail-item">
            <strong>Membro desde</strong>
            <span><?php echo e(date('d/m/Y', strtotime($dados['criado_em']))); ?></span>
          </div>
        </div>

        <form class="form-stack" action="perfil.php" method="post">
          <input type="hidden" name="acao" value="dados">
          <div class="field">
            <label for="nome">Nome completo</label>
            <input class="input" id="nome" name="nome" type="text"
                   value="<?php echo e($dados['nome']); ?>" required>
          </div>
          <div class="field">
            <label for="email">E-mail</label>
            <input class="input" id="email" name="email" type="email"
                   value="<?php echo e($dados['email']); ?>" required>
          </div>
          <button class="btn btn-primary" type="submit">Salvar dados</button>
        </form>
      </section>

      <!-- Alterar senha -->
      <section class="panel-box">
        <div class="panel-box-header" style="margin-bottom:20px">
          <div>
            <h3>Alterar senha</h3>
            <p>Escolha uma senha segura e diferente das anteriores.</p>
          </div>
        </div>

        <form class="form-stack" action="perfil.php" method="post">
          <input type="hidden" name="acao" value="senha">
          <div class="field">
            <label for="senha_atual">Senha atual</label>
            <div class="input-pw-wrap">
              <input class="input" id="senha_atual" name="senha_atual" type="password" placeholder="Digite sua senha atual" required>
              <button type="button" class="pw-toggle" data-target="senha_atual" aria-label="Mostrar">&#128065;</button>
            </div>
          </div>
          <div class="field">
            <label for="nova_senha">Nova senha <span class="field-hint" style="display:inline;margin:0">(mín. 6 caracteres)</span></label>
            <div class="input-pw-wrap">
              <input class="input" id="nova_senha" name="nova_senha" type="password" placeholder="Nova senha" required>
              <button type="button" class="pw-toggle" data-target="nova_senha" aria-label="Mostrar">&#128065;</button>
            </div>
            <div class="pw-strength" id="pw-strength-perfil"></div>
          </div>
          <div class="field">
            <label for="confirmar_nova">Confirmar nova senha</label>
            <div class="input-pw-wrap">
              <input class="input" id="confirmar_nova" name="confirmar_nova" type="password" placeholder="Repita a nova senha" required>
              <button type="button" class="pw-toggle" data-target="confirmar_nova" aria-label="Mostrar">&#128065;</button>
            </div>
            <div class="pw-match" id="pw-match-perfil"></div>
          </div>
          <button class="btn btn-primary" type="submit">Alterar senha</button>
        </form>
      </section>

    </div>
  </div>
</main>

<script>
(function () {
  var nova    = document.getElementById('nova_senha');
  var confirma = document.getElementById('confirmar_nova');
  var bar     = document.getElementById('pw-strength-perfil');
  var match   = document.getElementById('pw-match-perfil');

  nova.addEventListener('input', function () {
    var v = this.value;
    var score = 0;
    if (v.length >= 6) score++;
    if (v.length >= 10) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/\d/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    var labels = ['', 'Fraca', 'Razoável', 'Boa', 'Forte', 'Muito forte'];
    var colors = ['', '#ef4444', '#f97316', '#eab308', '#22c55e', '#15803d'];
    bar.textContent = v.length ? (labels[score] || labels[4]) : '';
    bar.style.color = v.length ? (colors[score] || colors[4]) : '';
    checkMatch();
  });

  confirma.addEventListener('input', checkMatch);

  function checkMatch() {
    if (!confirma.value) { match.textContent = ''; return; }
    if (nova.value === confirma.value) {
      match.textContent = '✓ Senhas conferem';
      match.style.color = '#22c55e';
    } else {
      match.textContent = '✗ Senhas não conferem';
      match.style.color = '#ef4444';
    }
  }

  document.querySelectorAll('.pw-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var inp = document.getElementById(btn.dataset.target);
      inp.type = inp.type === 'password' ? 'text' : 'password';
      btn.innerHTML = inp.type === 'password' ? '&#128065;' : '&#128584;';
    });
  });
}());
</script>
<?php render_footer(); ?>
