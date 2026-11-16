<?php
require __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=utf-8');
try {
    $pdo = banco();
    echo "Conexao com banco: OK\n";

    $tabelas = array('usuarios', 'status_ocorrencia', 'orgaos', 'categorias', 'ocorrencias', 'historico_ocorrencias');

    foreach ($tabelas as $tabela) {
        $stmt = $pdo->query("SELECT COUNT(*) AS total FROM " . $tabela);
        $linha = $stmt->fetch();
        echo $tabela . ': ' . $linha['total'] . "\n";
    }
} catch (Exception $e) {
    echo "Erro na conexao: " . $e->getMessage() . "\n";
}
