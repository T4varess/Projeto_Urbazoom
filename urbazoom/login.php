<?php
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(post_value('email', ''));
    $senha = post_value('senha', '');

    if ($email === '' || $senha === '') {
        ir_para('login.php?tipo=error&msg=Preencha+email+e+senha');
    }

    $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
    $stmt = banco()->prepare($sql);
    $stmt->execute(array('email' => $email));
    $usuario = $stmt->fetch();

    if (!$usuario) {
        ir_para('login.php?tipo=error&msg=Email+nao+cadastrado');
    }

    if ((int) $usuario['ativo'] !== 1) {
        ir_para('login.php?tipo=error&msg=Conta+desativada');
    }

    if (!senha_confere($senha, $usuario['senha_hash'])) {
        ir_para('login.php?tipo=error&msg=Senha+incorreta');
    }

    fazer_login($usuario);
    ir_para(pagina_inicial_html($usuario) . '?tipo=success&msg=Login+feito+com+sucesso');
}

render_header('Urbazoom | Login', 'login.php');
?>
<main class="page-shell auth-page">
  <section class="auth-card">
    <div class="auth-header">
      <img class="auth-logo" src="imagens/logo.png" alt="Logo do Urbazoom">
      <h1>Entrar no Urbazoom</h1>
      <p>Acesse sua conta para registrar e acompanhar ocorrências urbanas.</p>
    </div>

    <?php render_alerta(); ?>

    <?php if (esta_logado()) : ?>
      <div class="alert alert-info">Você já está logado. Se quiser, use o botão "Meu painel" no topo.</div>
    <?php endif; ?>

    <form class="form-stack" action="login.php" method="post">
      <div class="field">
        <label for="email">Email</label>
        <input class="input" id="email" name="email" type="email" placeholder="voce@email.com">
      </div>
      <div class="field">
        <label for="senha">Senha</label>
        <input class="input" id="senha" name="senha" type="password" placeholder="Digite sua senha">
      </div>
      <button class="btn btn-primary" type="submit">Entrar</button>
    </form>

    <p class="auth-link">Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
  </section>
</main>
<?php render_footer(); ?>
