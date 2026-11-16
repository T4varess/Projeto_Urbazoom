<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

date_default_timezone_set('America/Sao_Paulo');

$CONFIG_BANCO = require __DIR__ . '/config/database.php';

function banco()
{
    global $CONFIG_BANCO;
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . $CONFIG_BANCO['host'] .
        ';port=' . $CONFIG_BANCO['port'] .
        ';dbname=' . $CONFIG_BANCO['dbname'] .
        ';charset=' . $CONFIG_BANCO['charset'];

    $pdo = new PDO(
        $dsn,
        $CONFIG_BANCO['username'],
        $CONFIG_BANCO['password'],
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        )
    );

    return $pdo;
}

function ir_para($pagina)
{
    header('Location: ' . $pagina);
    exit;
}

function usuario_logado()
{
    if (!isset($_SESSION['usuario'])) {
        return null;
    }

    return $_SESSION['usuario'];
}

function esta_logado()
{
    return usuario_logado() !== null;
}

function eh_servidor()
{
    $usuario = usuario_logado();
    return $usuario && $usuario['tipo'] === 'servidor';
}

function fazer_login($usuario)
{
    $_SESSION['usuario'] = array(
        'id' => (int) $usuario['id'],
        'nome' => $usuario['nome'],
        'email' => $usuario['email'],
        'tipo' => $usuario['tipo'],
    );
}

function fazer_logout()
{
    unset($_SESSION['usuario']);
}

function pagina_inicial_html($usuario)
{
    if ($usuario && $usuario['tipo'] === 'servidor') {
        return 'painel-admin.php';
    }

    return 'painel-cidadao.php';
}

function senha_confere($senhaDigitada, $senhaBanco)
{
    if (function_exists('password_get_info')) {
        $info = password_get_info($senhaBanco);

        if (!empty($info['algo'])) {
            return password_verify($senhaDigitada, $senhaBanco);
        }
    }

    return hash('sha256', $senhaDigitada) === $senhaBanco;
}

function gerar_senha($senha)
{
    if (function_exists('password_hash')) {
        return password_hash($senha, PASSWORD_DEFAULT);
    }

    return hash('sha256', $senha);
}

function formatar_cpf($cpf)
{
    $cpf = preg_replace('/\D+/', '', $cpf);

    if (strlen($cpf) !== 11) {
        return $cpf;
    }

    return substr($cpf, 0, 3) . '.' .
        substr($cpf, 3, 3) . '.' .
        substr($cpf, 6, 3) . '-' .
        substr($cpf, 9, 2);
}

function gerar_protocolo()
{
    return 'URB-' . date('ymd') . mt_rand(100, 999);
}

function post_value($chave, $padrao)
{
    return isset($_POST[$chave]) ? $_POST[$chave] : $padrao;
}

function get_value($chave, $padrao)
{
    return isset($_GET[$chave]) ? $_GET[$chave] : $padrao;
}

function e($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function mensagem_tipo()
{
    return get_value('tipo', '');
}

function mensagem_texto()
{
    return get_value('msg', '');
}

function render_alerta()
{
    $tipo = mensagem_tipo();
    $texto = mensagem_texto();

    if ($tipo === '' || $texto === '') {
        return;
    }

    echo '<div class="alert alert-' . e($tipo) . '">' . e($texto) . '</div>';
}

function nav_ativa($arquivo, $atual)
{
    if ($arquivo === $atual) {
        return 'active';
    }

    return '';
}

function render_header($tituloPagina, $paginaAtual)
{
    $usuario = usuario_logado();

    echo '<!DOCTYPE html>';
    echo '<html lang="pt-BR">';
    echo '<head>';
    echo '  <meta charset="UTF-8">';
    echo '  <meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '  <title>' . e($tituloPagina) . '</title>';
    echo '  <link rel="stylesheet" href="styles.css?v=3">';
    echo '  <script>(function(){var t=localStorage.getItem("urbazoom_theme");if(t==="dark")document.documentElement.setAttribute("data-theme","dark");})()</script>';
    echo '</head>';
    echo '<body>';
    echo '  <header class="site-header">';
    echo '    <div class="container">';
    echo '      <a class="brand" href="index.php">';
    echo '        <img class="brand-logo" src="imagens/logo.png" alt="Logo do Urbazoom">';
    echo '        <span class="brand-text">';
    echo '          <span class="brand-name">Urbazoom</span>';
    echo '          <span class="brand-slogan">SUA VOZ, NOSSA CIDADE</span>';
    echo '        </span>';
    echo '      </a>';
    echo '      <nav class="header-nav" aria-label="Submenu principal">';

    if (!$usuario) {
        echo '        <a class="' . nav_ativa('index.php', $paginaAtual) . '" href="index.php">Início</a>';
        echo '        <a class="' . nav_ativa('acompanhamento.php', $paginaAtual) . '" href="acompanhamento.php">Acompanhamento</a>';
        echo '        <a class="' . nav_ativa('mapa.php', $paginaAtual) . '" href="mapa.php">Mapa</a>';
    } elseif ($usuario['tipo'] === 'servidor') {
        echo '        <a class="' . nav_ativa('index.php', $paginaAtual) . '" href="index.php">Início</a>';
        echo '        <a class="' . nav_ativa('painel-admin.php', $paginaAtual) . '" href="painel-admin.php">Painel admin</a>';
        echo '        <a class="' . nav_ativa('mapa.php', $paginaAtual) . '" href="mapa.php">Mapa</a>';
        echo '        <a class="' . nav_ativa('relatorios.php', $paginaAtual) . '" href="relatorios.php">Relatórios</a>';
        echo '        <a class="' . nav_ativa('perfil.php', $paginaAtual) . '" href="perfil.php">Perfil</a>';
    } else {
        echo '        <a class="' . nav_ativa('index.php', $paginaAtual) . '" href="index.php">Início</a>';
        echo '        <a class="' . nav_ativa('painel-cidadao.php', $paginaAtual) . '" href="painel-cidadao.php">Meu painel</a>';
        echo '        <a class="' . nav_ativa('registro-ocorrencia.php', $paginaAtual) . '" href="registro-ocorrencia.php">Registrar</a>';
        echo '        <a class="' . nav_ativa('mapa.php', $paginaAtual) . '" href="mapa.php">Mapa</a>';
        echo '        <a class="' . nav_ativa('perfil.php', $paginaAtual) . '" href="perfil.php">Perfil</a>';
    }

    echo '      </nav>';
    echo '      <div class="header-actions">';

    if ($usuario) {
        echo '        <a class="user-chip" href="perfil.php">' . e($usuario['nome']) . '</a>';
        echo '        <a class="btn btn-primary" href="logout.php">Sair</a>';
    } else {
        echo '        <a class="btn btn-ghost" href="login.php">Entrar</a>';
        echo '        <a class="btn btn-primary" href="cadastro.php">Cadastrar</a>';
    }

    echo '        <button class="dark-toggle" id="darkToggle" type="button" aria-label="Alternar modo noturno"><span id="darkIcon">&#9790;</span><span id="darkLabel">Noturno</span></button>';
    echo '        <button class="menu-toggle" type="button" aria-label="Abrir menu">&#9776;</button>';
    echo '      </div>';
    echo '    </div>';
    echo '  </header>';
    echo '  <div class="session-status ' . ($usuario ? 'session-status-on' : 'session-status-off') . '">';

    if ($usuario) {
        echo 'Status: logado como ' . e($usuario['nome']) . ' (' . e($usuario['tipo']) . ').';
    } else {
        echo 'Status: você não está logado.';
    }

    echo '  </div>';
}

function render_footer()
{
    echo '  <footer class="footer">';
    echo '    <div class="container">';
    echo '      <p>&copy; 2026 Urbazoom. Projeto Fatec Mauá.</p>';
    echo '      <p>Agatha Vitória &middot; Ana Eloisa &middot; Rafaela Tavares</p>';
    echo '      <div class="footer-links"></div>';
    echo '    </div>';
    echo '  </footer>';
    echo '  <script>';
    echo '    (function(){';
    echo '      var btn   = document.getElementById("darkToggle");';
    echo '      var icon  = document.getElementById("darkIcon");';
    echo '      var label = document.getElementById("darkLabel");';
    echo '      var html  = document.documentElement;';
    echo '      function updateBtn(){';
    echo '        var dark = html.getAttribute("data-theme") === "dark";';
    echo '        icon.textContent  = dark ? "☀" : "☾";';
    echo '        label.textContent = dark ? "Claro"  : "Noturno";';
    echo '        btn.setAttribute("aria-pressed", dark ? "true" : "false");';
    echo '      }';
    echo '      updateBtn();';
    echo '      btn.addEventListener("click", function(){';
    echo '        var dark = html.getAttribute("data-theme") === "dark";';
    echo '        if(dark){';
    echo '          html.removeAttribute("data-theme");';
    echo '          localStorage.removeItem("urbazoom_theme");';
    echo '        } else {';
    echo '          html.setAttribute("data-theme","dark");';
    echo '          localStorage.setItem("urbazoom_theme","dark");';
    echo '        }';
    echo '        updateBtn();';
    echo '      });';
    echo '    })();';
    echo '  </script>';
    echo '</body>';
    echo '</html>';
}

function exigir_login()
{
    if (!esta_logado()) {
        ir_para('login.php?tipo=error&msg=Faça+login+primeiro');
    }
}

function exigir_cidadao()
{
    if (!esta_logado()) {
        ir_para('login.php?tipo=error&msg=Faça+login+para+registrar+uma+ocorrência');
    }

    if (eh_servidor()) {
        ir_para('painel-admin.php?tipo=info&msg=Você+está+logado+como+servidor');
    }
}

function exigir_servidor()
{
    exigir_login();

    if (!eh_servidor()) {
        ir_para('painel-cidadao.php?tipo=error&msg=Acesso+restrito+ao+servidor');
    }
}
