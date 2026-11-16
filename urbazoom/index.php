<?php
require __DIR__ . '/config.php';

render_header('Urbazoom | Início', 'index.php');
?>
<main class="page-shell">
  <section class="section hero">
    <div class="container">
      <div class="chip chip-primary">
        <span>Conectando cidadãos e órgãos públicos</span>
      </div>
      <h1>Registre ocorrências urbanas e acompanhe a resolução</h1>
      <p>
        O Urbazoom aproxima a população dos órgãos públicos com uma plataforma simples para abrir
        solicitações, acompanhar o andamento e dar mais visibilidade aos problemas da cidade.
      </p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="cadastro.php">Criar Conta Gratuita</a>
        <a class="btn btn-outline" href="login.php">Já tenho conta</a>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="section-title">
        <h2>Como Funciona</h2>
        <p>Um fluxo simples para transformar reclamações em atendimentos acompanhados.</p>
      </div>
      <div class="grid-3">
        <article class="card feature-card">
          <div class="icon-circle icon-blue">&#128196;</div>
          <h3>Registre a ocorrência</h3>
          <p>Descreva o problema, informe o local e selecione a categoria para encaminhar ao setor correto.</p>
        </article>
        <article class="card feature-card">
          <div class="icon-circle icon-blue">&#128339;</div>
          <h3>Acompanhe o andamento</h3>
          <p>Consulte o status da solicitação, veja atualizações do atendimento e acompanhe os prazos.</p>
        </article>
        <article class="card feature-card">
          <div class="icon-circle icon-blue">&#10003;</div>
          <h3>Problema resolvido</h3>
          <p>Receba a confirmação quando a demanda for tratada e acompanhe o histórico das ocorrências.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="section section-muted">
    <div class="container">
      <div class="section-title">
        <h2>Categorias de Ocorrências</h2>
        <p>Tipos de problemas urbanos mais comuns para facilitar o registro e a triagem.</p>
      </div>
      <div class="category-chips">
        <div class="category-pill"><span class="icon-circle icon-orange">&#128679;</span><span>Buraco na rua</span></div>
        <div class="category-pill"><span class="icon-circle icon-blue">&#128161;</span><span>Iluminação pública</span></div>
        <div class="category-pill"><span class="icon-circle icon-green">&#128465;</span><span>Lixo acumulado</span></div>
        <div class="category-pill"><span class="icon-circle icon-yellow">&#128167;</span><span>Vazamento de água</span></div>
        <div class="category-pill"><span class="icon-circle icon-purple">&#9888;</span><span>Sinalização danificada</span></div>
      </div>
    </div>
  </section>

  <section class="section-sm">
    <div class="container">
      <div class="cta-box">
        <h2>Faça parte da mudança</h2>
        <p>Participe da melhoria urbana com registros organizados, acompanhamento transparente e resposta mais rápida.</p>
        <a class="btn btn-light" href="cadastro.php">Começar Agora</a>
      </div>
    </div>
  </section>
</main>
<?php render_footer(); ?>
