CREATE DATABASE IF NOT EXISTS urbazoom
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE urbazoom;

SET NAMES utf8mb4;

DROP TABLE IF EXISTS historico_ocorrencias;
DROP TABLE IF EXISTS ocorrencias;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS orgaos;
DROP TABLE IF EXISTS status_ocorrencia;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  cpf CHAR(14) NOT NULL,
  email VARCHAR(150) NOT NULL,
  senha_hash VARCHAR(255) NOT NULL,
  tipo ENUM('cidadao', 'servidor') NOT NULL DEFAULT 'cidadao',
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_usuarios_cpf (cpf),
  UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB;

CREATE TABLE status_ocorrencia (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(50) NOT NULL,
  slug VARCHAR(50) NOT NULL,
  ordem_exibicao TINYINT UNSIGNED NOT NULL,
  classe_badge VARCHAR(30) NOT NULL,
  UNIQUE KEY uq_status_slug (slug)
) ENGINE=InnoDB;

CREATE TABLE orgaos (
  id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  descricao VARCHAR(255) DEFAULT NULL,
  email_contato VARCHAR(150) DEFAULT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE categorias (
  id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  descricao VARCHAR(255) DEFAULT NULL,
  orgao_id SMALLINT UNSIGNED NOT NULL,
  ativa TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_categorias_nome (nome),
  CONSTRAINT fk_categorias_orgao
    FOREIGN KEY (orgao_id) REFERENCES orgaos (id)
) ENGINE=InnoDB;

CREATE TABLE ocorrencias (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  protocolo VARCHAR(20) NOT NULL,
  usuario_id BIGINT UNSIGNED NOT NULL,
  categoria_id SMALLINT UNSIGNED NOT NULL,
  orgao_id SMALLINT UNSIGNED NOT NULL,
  titulo VARCHAR(180) NOT NULL,
  descricao TEXT NOT NULL,
  localizacao VARCHAR(255) NOT NULL,
  prioridade ENUM('baixa', 'media', 'alta') NOT NULL DEFAULT 'media',
  status_id TINYINT UNSIGNED NOT NULL,
  anexo_path VARCHAR(255) DEFAULT NULL,
  aberta_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  resolvida_em DATETIME DEFAULT NULL,
  UNIQUE KEY uq_ocorrencias_protocolo (protocolo),
  KEY idx_ocorrencias_usuario (usuario_id),
  KEY idx_ocorrencias_categoria (categoria_id),
  KEY idx_ocorrencias_status (status_id),
  KEY idx_ocorrencias_orgao (orgao_id),
  CONSTRAINT fk_ocorrencias_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id),
  CONSTRAINT fk_ocorrencias_categoria
    FOREIGN KEY (categoria_id) REFERENCES categorias (id),
  CONSTRAINT fk_ocorrencias_orgao
    FOREIGN KEY (orgao_id) REFERENCES orgaos (id),
  CONSTRAINT fk_ocorrencias_status
    FOREIGN KEY (status_id) REFERENCES status_ocorrencia (id)
) ENGINE=InnoDB;

CREATE TABLE historico_ocorrencias (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ocorrencia_id BIGINT UNSIGNED NOT NULL,
  usuario_id BIGINT UNSIGNED DEFAULT NULL,
  status_id TINYINT UNSIGNED DEFAULT NULL,
  mensagem TEXT NOT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_historico_ocorrencia (ocorrencia_id),
  KEY idx_historico_usuario (usuario_id),
  KEY idx_historico_status (status_id),
  CONSTRAINT fk_historico_ocorrencia
    FOREIGN KEY (ocorrencia_id) REFERENCES ocorrencias (id) ON DELETE CASCADE,
  CONSTRAINT fk_historico_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL,
  CONSTRAINT fk_historico_status
    FOREIGN KEY (status_id) REFERENCES status_ocorrencia (id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO usuarios (id, nome, cpf, email, senha_hash, tipo) VALUES
  (1, 'Cidadao Demo', '123.456.789-00', 'cidadao@urbazoom.com', SHA2('123456', 256), 'cidadao'),
  (2, 'Servidor Demo', '987.654.321-00', 'servidor@urbazoom.com', SHA2('123456', 256), 'servidor');

INSERT INTO status_ocorrencia (id, nome, slug, ordem_exibicao, classe_badge) VALUES
  (1, 'Aberta', 'aberta', 1, 'badge-open'),
  (2, 'Em analise', 'em-analise', 2, 'badge-analysis'),
  (3, 'Em atendimento', 'em-atendimento', 3, 'badge-service'),
  (4, 'Resolvida', 'resolvida', 4, 'badge-resolved');

INSERT INTO orgaos (id, nome, descricao, email_contato) VALUES
  (1, 'Secretaria de Obras', 'Demandas de pavimentacao e sinalizacao urbana', 'obras@urbazoom.local'),
  (2, 'Secretaria de Iluminacao Publica', 'Demandas de postes, lampadas e rede urbana', 'iluminacao@urbazoom.local'),
  (3, 'Secretaria de Limpeza Urbana', 'Demandas de coleta e descarte irregular', 'limpeza@urbazoom.local'),
  (4, 'Companhia de Agua e Saneamento', 'Demandas de vazamento e manutencao hidrica', 'agua@urbazoom.local');

INSERT INTO categorias (id, nome, descricao, orgao_id) VALUES
  (1, 'Buraco na rua', 'Problemas de asfalto, pavimento e crateras', 1),
  (2, 'Iluminacao publica', 'Postes apagados e falhas de iluminacao', 2),
  (3, 'Lixo acumulado', 'Acumulo de residuos e descarte irregular', 3),
  (4, 'Vazamento de agua', 'Vazamentos em ruas, calcadas ou vias', 4),
  (5, 'Sinalizacao danificada', 'Placas, faixas e sinalizacao urbana comprometidas', 1);

INSERT INTO ocorrencias (
  id,
  protocolo,
  usuario_id,
  categoria_id,
  orgao_id,
  titulo,
  descricao,
  localizacao,
  prioridade,
  status_id,
  aberta_em,
  atualizada_em,
  resolvida_em
) VALUES
  (
    1,
    'URB-2038',
    1,
    2,
    2,
    'Poste apagado proximo ao ponto de onibus',
    'Moradores relatam falta de iluminacao em trecho com grande circulacao noturna, proximo ao ponto de onibus e faixa de pedestres.',
    'Avenida Brasil, Jardim Novo',
    'alta',
    2,
    '2026-04-04 10:30:00',
    '2026-04-04 14:00:00',
    NULL
  ),
  (
    2,
    'URB-2037',
    1,
    1,
    1,
    'Afundamento no asfalto em via principal',
    'Buraco na rua prejudicando carros e motocicletas em corredor de transporte local.',
    'Rua das Flores, Centro',
    'alta',
    3,
    '2026-04-03 09:10:00',
    '2026-04-03 16:30:00',
    NULL
  ),
  (
    3,
    'URB-2036',
    1,
    3,
    3,
    'Acumulo de residuos em area publica',
    'Acumulo de lixo em frente a area publica com risco sanitario para moradores.',
    'Praca Central, Primavera',
    'media',
    1,
    '2026-04-01 08:00:00',
    '2026-04-01 08:00:00',
    NULL
  ),
  (
    4,
    'URB-2035',
    1,
    4,
    4,
    'Vazamento constante escorrendo pela calcada',
    'Vazamento de agua causando desperdicio e erosao leve na lateral da calcada.',
    'Rua Aurora, Uniao',
    'media',
    4,
    '2026-03-29 07:40:00',
    '2026-03-30 15:20:00',
    '2026-03-30 15:20:00'
  );

INSERT INTO historico_ocorrencias (ocorrencia_id, usuario_id, status_id, mensagem, criado_em) VALUES
  (1, 1, 1, 'Ocorrencia registrada pelo cidadao na plataforma.', '2026-04-04 10:30:00'),
  (1, 2, 2, 'Chamado encaminhado para a equipe tecnica de iluminacao.', '2026-04-04 11:15:00'),
  (1, 2, 2, 'Status atualizado para em analise, aguardando vistoria no local.', '2026-04-04 14:00:00'),
  (2, 1, 1, 'Ocorrencia registrada com fotos do local.', '2026-04-03 09:10:00'),
  (2, 2, 2, 'Equipe de obras avaliou a extensao do dano no pavimento.', '2026-04-03 11:45:00'),
  (2, 2, 3, 'Reparo incluido na rota da equipe de manutencao viaria.', '2026-04-03 16:30:00'),
  (3, 1, 1, 'Chamado aberto aguardando triagem da limpeza urbana.', '2026-04-01 08:00:00'),
  (4, 1, 1, 'Vazamento informado com ponto de referencia e fotos.', '2026-03-29 07:40:00'),
  (4, 2, 3, 'Equipe de saneamento deslocada para o local.', '2026-03-29 10:20:00'),
  (4, 2, 4, 'Reparo concluido e chamado encerrado.', '2026-03-30 15:20:00');
