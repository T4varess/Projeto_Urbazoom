<?php
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim(post_value('nome', ''));
    $cpf = formatar_cpf(post_value('cpf', ''));
    $email = trim(post_value('email', ''));
    $tipo = 'cidadao';
    $senha = post_value('senha', '');
    $confirmar = post_value('confirmar_senha', '');

    if ($nome === '' || $cpf === '' || $email === '' || $senha === '') {
        ir_para('cadastro.php?tipo=error&msg=Preencha+todos+os+campos');
    }

    if (strlen(preg_replace('/\D+/', '', $cpf)) !== 11) {
        ir_para('cadastro.php?tipo=error&msg=CPF+invalido');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        ir_para('cadastro.php?tipo=error&msg=Email+invalido');
    }

    if (strlen($senha) < 6) {
        ir_para('cadastro.php?tipo=error&msg=A+senha+precisa+ter+6+caracteres');
    }

    if ($senha !== $confirmar) {
        ir_para('cadastro.php?tipo=error&msg=As+senhas+estao+diferentes');
    }

    $stmt = banco()->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email OR cpf = :cpf");
    $stmt->execute(array('email' => $email, 'cpf' => $cpf));

    if ((int) $stmt->fetchColumn() > 0) {
        ir_para('cadastro.php?tipo=error&msg=Ja+existe+usuario+com+esse+email+ou+CPF');
    }

    $sql = "INSERT INTO usuarios (nome, cpf, email, senha_hash, tipo) VALUES (:nome, :cpf, :email, :senha_hash, :tipo)";
    $stmt = banco()->prepare($sql);
    $stmt->execute(array(
        'nome' => $nome,
        'cpf' => $cpf,
        'email' => $email,
        'senha_hash' => gerar_senha($senha),
        'tipo' => $tipo,
    ));

    $id = banco()->lastInsertId();
    $usuario = array(
        'id' => $id,
        'nome' => $nome,
        'email' => $email,
        'tipo' => $tipo,
    );

    fazer_login($usuario);
    ir_para(pagina_inicial_html($usuario) . '?tipo=success&msg=Conta+criada+com+sucesso');
}

render_header('Urbazoom | Cadastro', 'cadastro.php');
?>
<main class="page-shell auth-page">
  <section class="auth-card">
    <div class="auth-header">
      <img class="auth-logo" src="imagens/logo.png" alt="Logo do Urbazoom">
      <h1>Criar conta</h1>
      <p>Cadastre-se para enviar ocorrências e acompanhar o atendimento.</p>
    </div>

    <?php render_alerta(); ?>

    <form class="form-stack" action="cadastro.php" method="post">
      <div class="field">
        <label for="nome-completo">Nome completo</label>
        <input class="input" id="nome-completo" name="nome" type="text" placeholder="Digite seu nome">
      </div>
      <div class="field">
        <label for="cpf">CPF</label>
        <input class="input" id="cpf" name="cpf" type="text" placeholder="000.000.000-00">
      </div>
      <div class="field">
        <label for="cadastro-email">Email</label>
        <input class="input" id="cadastro-email" name="email" type="email" placeholder="voce@email.com">
      </div>
      <div class="field">
        <label for="cadastro-senha">Senha</label>
        <input class="input" id="cadastro-senha" name="senha" type="password" placeholder="Crie uma senha">
      </div>
      <div class="field">
        <label for="confirmar-senha">Confirmar senha</label>
        <input class="input" id="confirmar-senha" name="confirmar_senha" type="password" placeholder="Repita sua senha">
      </div>
      <button class="btn btn-primary" type="submit">Criar Conta</button>
    </form>
  </section>
</main>
<?php render_footer(); ?>
