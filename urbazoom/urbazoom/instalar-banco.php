<?php
header('Content-Type: text/plain; charset=utf-8');

$config = require __DIR__ . '/config/database.php';
$arquivoSql = __DIR__ . '/database/urbazoom.sql';

if (!file_exists($arquivoSql)) {
    exit("Arquivo SQL nao encontrado.\n");
}

try {
    $dsnServidor = 'mysql:host=' . $config['host'] .
        ';port=' . $config['port'] .
        ';charset=' . $config['charset'];

    $pdoServidor = new PDO(
        $dsnServidor,
        $config['username'],
        $config['password'],
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        )
    );

    $pdoServidor->exec('CREATE DATABASE IF NOT EXISTS ' . $config['dbname']);

    $dsnBanco = 'mysql:host=' . $config['host'] .
        ';port=' . $config['port'] .
        ';dbname=' . $config['dbname'] .
        ';charset=' . $config['charset'];

    $pdo = new PDO(
        $dsnBanco,
        $config['username'],
        $config['password'],
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        )
    );

    $linhas = file($arquivoSql);
    $sqlAtual = '';
    $executadas = 0;
    $pulandoComando = false;

    foreach ($linhas as $linha) {
        $linhaLimpa = trim($linha);

        if ($linhaLimpa === '' || strpos($linhaLimpa, '--') === 0 || strpos($linhaLimpa, '#') === 0) {
            continue;
        }

        if ($pulandoComando) {
            if (substr(rtrim($linha), -1) === ';') {
                $pulandoComando = false;
            }
            continue;
        }

        if (
            stripos($linhaLimpa, 'DROP DATABASE') === 0 ||
            stripos($linhaLimpa, 'CREATE DATABASE') === 0 ||
            stripos($linhaLimpa, 'USE ') === 0
        ) {
            if (substr(rtrim($linha), -1) !== ';') {
                $pulandoComando = true;
            }
            continue;
        }

        $sqlAtual .= $linha;

        if (substr(rtrim($linha), -1) === ';') {
            $pdo->exec($sqlAtual);
            $sqlAtual = '';
            $executadas++;
        }
    }

    echo "Banco instalado com sucesso.\n";
    echo "Comandos executados: " . $executadas . "\n";
} catch (Exception $e) {
    echo "Erro ao instalar banco: " . $e->getMessage() . "\n";
}
