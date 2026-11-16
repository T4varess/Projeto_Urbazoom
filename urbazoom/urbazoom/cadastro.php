<?php
require __DIR__ . '/config.php';

if (esta_logado()) {
    ir_para(pagina_inicial_html(usuario_logado()));
}

$erros  = array();
$campos = array('nome' => '', 'cpf' => '', 'email' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome      = trim(post_value('nome', ''));
    $cpfRaw    = preg_replace('/\D+/', '', post_value('cpf', ''));
    $email     = trim(post_value('email', ''));
    $senha     = post_value('senha', '');
    $confirmar = post_value('confirmar_senha', '');

    $campos = array('nome' => $nome, 'cpf' => post_value('cpf', ''), 'email' => $email);

    if ($nome === '')                                          $erros[] = 'Nome é obrigatório.';
    if (strlen($cpfRaw) !== 11)                               $erros[] = 'CPF inválido — informe os 11 dígitos.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
    if (strlen($senha) < 6)                                   $erros[] = 'Senha deve ter ao menos 6 caracteres.';
    if ($senha !== $confirmar)                                 $erros[] = 'As senhas não conferem.';

    if (!$erros) {
        $cpfFormatado = formatar_cpf($cpfRaw);

        $stmtE = banco()->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
        $stmtE->execute(array('email' => $email));
        if ($stmtE->fetch()) $erros[] = 'E-mail já cadastrado. Tente fazer login.';

        $stmtC = banco()->prepare("SELECT id FROM usuarios WHERE cpf = :cpf LIMIT 1");
        $stmtC->execute(array('cpf' => $cpfFormatado));
        if ($stmtC->fetch()) $erros[] = 'CPF já cadastrado.';
    }

    if (!$erros) {
        $cpfFormatado = formatar_cpf($cpfRaw);
        $hash = gerar_senha($senha);

        banco()->prepare(
            "INSERT INTO usuarios (nome, cpf, email, senha_hash, tipo) VALUES (:nome, :cpf, :email, :hash, 'cidadao')"
        )->execute(array('nome' => $nome, 'cpf' => $cpfFormatado, 'email' => $email, 'hash' => $hash));

        $stmtU = banco()->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmtU->execute(array('email' => $email));
        fazer_login($stmtU->fetch());

        ir_para('painel-cidadao.php?tipo=success&msg=Bem-vindo+ao+Urbazoom%21+Conta+criada+com+sucesso.');
    }
}

render_header('Urbazoom | Cadastro', 'cadastro.php');
?>
<main class="page-shell auth-page">
  <section class="auth-card auth-card-wide">
    <div class="auth-header">
      <img class="auth-logo" src="imagens/logo.png" alt="Logo do Urbazoom">
      <h1>Criar conta</h1>
      <p>Registre-se para abrir e acompanhar ocorrências urbanas.</p>
    </div>

    <?php render_alerta(); ?>

    <?php if ($erros): ?>
      <div class="alert alert-error">
        <?php foreach ($erros as $erro): ?><div><?php echo e($erro); ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form class="form-stack" action="cadastro.php" method="post">
      <div class="field">
        <label for="nome">Nome completo</label>
        <input class="input" id="nome" name="nome" type="text"
               placeholder="Seu nome completo" value="<?php echo e($campos['nome']); ?>" required>
      </div>

      <div class="cep-row-2">
        <div class="field" style="flex:1">
          <label for="cpf">CPF</label>
          <input class="input" id="cpf" name="cpf" type="text"
                 placeholder="000.000.000-00" maxlength="14"
                 value="<?php echo e($campos['cpf']); ?>" required>
        </div>
        <div class="field" style="flex:1">
          <label for="email">E-mail</label>
          <input class="input" id="email" name="email" type="email"
                 placeholder="voce@email.com" value="<?php echo e($campos['email']); ?>" required>
        </div>
      </div>

      <div class="cep-row-2">
        <div class="field" style="flex:1">
          <label for="senha">Senha <span class="field-hint" style="display:inline;margin:0">(mín. 6 caracteres)</span></label>
          <div class="input-pw-wrap">
            <input class="input" id="senha" name="senha" type="password" placeholder="Crie uma senha" required>
            <button type="button" class="pw-toggle" data-target="senha" aria-label="Mostrar senha">&#128065;</button>
          </div>
          <div class="pw-strength" id="pw-strength"></div>
        </div>
        <div class="field" style="flex:1">
          <label for="confirmar_senha">Confirmar senha</label>
          <div class="input-pw-wrap">
            <input class="input" id="confirmar_senha" name="confirmar_senha" type="password" placeholder="Repita a senha" required>
            <button type="button" class="pw-toggle" data-target="confirmar_senha" aria-label="Mostrar senha">&#128065;</button>
          </div>
          <div class="pw-match" id="pw-match"></div>
        </div>
      </div>

      <button class="btn btn-primary" type="submit" style="width:100%;margin-top:4px">Criar minha conta</button>
    </form>

    <p class="auth-link">Já tem uma conta? <a href="login.php">Entrar</a></p>
  </section>
</main>

<script>
(function () {
  // Máscara CPF
  document.getElementById('cpf').addEventListener('input', function () {
    var v = this.value.replace(/\D/g, '').substring(0, 11);
    if (v.length > 9)      v = v.substring(0,3)+'.'+v.substring(3,6)+'.'+v.substring(6,9)+'-'+v.substring(9);
    else if (v.length > 6) v = v.substring(0,3)+'.'+v.substring(3,6)+'.'+v.substring(6);
    else if (v.length > 3) v = v.substring(0,3)+'.'+v.substring(3);
    this.value = v;
  });

  // Força de senha
  var senhaInput  = document.getElementById('senha');
  var confirmInput = document.getElementById('confirmar_senha');
  var bar   = document.getElementById('pw-strength');
  var match = document.getElementById('pw-match');

  senhaInput.addEventListener('input', function () {
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

  confirmInput.addEventListener('input', checkMatch);

  function checkMatch() {
    if (!confirmInput.value) { match.textContent = ''; return; }
    if (senhaInput.value === confirmInput.value) {
      match.textContent = '✓ Senhas conferem';
      match.style.color = '#22c55e';
    } else {
      match.textContent = '✗ Senhas não conferem';
      match.style.color = '#ef4444';
    }
  }

  // Mostrar/ocultar senha
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
