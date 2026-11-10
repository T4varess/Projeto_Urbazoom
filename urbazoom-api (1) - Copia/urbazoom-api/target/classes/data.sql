-- Dados iniciais do UrbaZoom (carregados automaticamente no H2 ao iniciar a API)
-- Senha de todos os usuarios de teste: 123456 (guardada como SHA-256)

INSERT INTO usuarios (id, nome, cpf, email, senha_hash, tipo, ativo) VALUES
  (1, 'Cidadão Demo',             '123.456.789-00', 'cidadao@urbazoom.com',   '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'CIDADAO', TRUE),
  (2, 'Servidor Demo',            '987.654.321-00', 'servidor@urbazoom.com',  '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'SERVIDOR', TRUE),
  (4, 'Ana Eloisa',               '111.222.333-44', 'ana@urbazoom.local',     '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'CIDADAO', TRUE),
  (9, 'Ryan de Lima Silva',       '222.333.444-55', 'ryan@urbazoom.local',    '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'CIDADAO', TRUE),
  (10,'Maria Ivone',              '333.444.555-66', 'maria@urbazoom.local',   '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'CIDADAO', TRUE),
  (11,'Paulo Cesar Rodrigues',    '444.555.666-77', 'paulo@urbazoom.local',   '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'CIDADAO', TRUE);

INSERT INTO status_ocorrencia (id, nome, slug, ordem_exibicao, classe_badge) VALUES
  (1, 'Aberta', 'aberta', 1, 'badge-open'),
  (2, 'Em análise', 'em-analise', 2, 'badge-analysis'),
  (3, 'Em atendimento', 'em-atendimento', 3, 'badge-service'),
  (4, 'Resolvida', 'resolvida', 4, 'badge-resolved');

INSERT INTO orgaos (id, nome, descricao, email_contato, ativo) VALUES
  (1, 'Secretaria de Obras', 'Demandas de pavimentação e sinalização urbana', 'obras@urbazoom.local', TRUE),
  (2, 'Secretaria de Iluminação Pública', 'Demandas de postes, lâmpadas e rede urbana', 'iluminacao@urbazoom.local', TRUE),
  (3, 'Secretaria de Limpeza Urbana', 'Demandas de coleta e descarte irregular', 'limpeza@urbazoom.local', TRUE),
  (4, 'Companhia de Água e Saneamento', 'Demandas de vazamento e manutenção hídrica', 'agua@urbazoom.local', TRUE);

INSERT INTO categorias (id, nome, descricao, orgao_id, ativa) VALUES
  (1, 'Buraco na rua', 'Problemas de asfalto, pavimento e crateras', 1, TRUE),
  (2, 'Iluminação pública', 'Postes apagados e falhas de iluminação', 2, TRUE),
  (3, 'Lixo acumulado', 'Acúmulo de resíduos e descarte irregular', 3, TRUE),
  (4, 'Vazamento de água', 'Vazamentos em ruas, calçadas ou vias', 4, TRUE),
  (5, 'Sinalização danificada', 'Placas, faixas e sinalização urbana comprometidas', 1, TRUE);

INSERT INTO ocorrencias (
  id, protocolo, usuario_id, categoria_id, orgao_id,
  titulo, descricao, localizacao, lat, lng,
  prioridade, status_id, aberta_em, atualizada_em, resolvida_em
) VALUES
  (1, 'URB-2038', 9, 2, 2,
   'Poste apagado próximo ao ponto de ônibus',
   'Moradores relatam falta de iluminação em trecho com grande circulação noturna, próximo ao ponto de ônibus e faixa de pedestres.',
   'Avenida Brasil, Jardim Novo', -23.6674, -46.4655,
   'ALTA', 2, '2026-04-04 10:30:00', '2026-04-04 14:00:00', NULL),
  (2, 'URB-2037', 10, 1, 1,
   'Afundamento no asfalto em via principal',
   'Buraco na rua prejudicando carros e motocicletas em corredor de transporte local.',
   'Rua das Flores, Centro', -23.6690, -46.4614,
   'ALTA', 3, '2026-04-03 09:10:00', '2026-04-03 16:30:00', NULL),
  (3, 'URB-2036', 9, 3, 3,
   'Acúmulo de resíduos em área pública',
   'Acúmulo de lixo em frente a área pública com risco sanitário para moradores.',
   'Praça Central, Primavera', -23.6620, -46.4580,
   'MEDIA', 1, '2026-04-01 08:00:00', '2026-04-01 08:00:00', NULL),
  (4, 'URB-2035', 11, 4, 4,
   'Vazamento constante escorrendo pela calçada',
   'Vazamento de água causando desperdício e erosão leve na lateral da calçada.',
   'Rua Aurora, União', -23.6710, -46.4590,
   'MEDIA', 4, '2026-03-29 07:40:00', '2026-03-30 15:20:00', '2026-03-30 15:20:00'),
  -- Janeiro 2026
  (5, 'URB-2010', 10, 1, 1,
   'Buraco profundo na saída da escola',
   'Cratera no asfalto próxima à entrada da escola municipal, representando risco para crianças e ciclistas que passam diariamente pelo local.',
   'Rua das Acácias, Jardim Esperança', -23.6580, -46.4720,
   'ALTA', 4, '2026-01-08 07:50:00', '2026-01-20 16:00:00', '2026-01-20 16:00:00'),
  (6, 'URB-2011', 9, 3, 3,
   'Descarte irregular de entulho em terreno vazio',
   'Acúmulo de entulho e resíduos de construção em terreno baldio, causando mau cheiro e proliferação de insetos no bairro.',
   'Rua Ipê Amarelo, Parque Novo Oratório', -23.6540, -46.4660,
   'MEDIA', 4, '2026-01-15 09:20:00', '2026-01-28 14:00:00', '2026-01-28 14:00:00'),
  -- Fevereiro 2026
  (7, 'URB-2018', 10, 2, 2,
   'Poste com fiação exposta na calçada',
   'Fiação elétrica do poste pendendo sobre a calçada após ventos fortes, oferecendo risco de choque aos pedestres.',
   'Avenida Capitão João, Vila Novaes', -23.6610, -46.4530,
   'ALTA', 4, '2026-02-03 11:10:00', '2026-02-14 10:30:00', '2026-02-14 10:30:00'),
  (8, 'URB-2022', 11, 4, 4,
   'Vazamento no hidrômetro causando alagamento',
   'Vazamento constante no hidrômetro externo provocando alagamento da calçada e parte da rua, com risco de deslizamento na encosta próxima.',
   'Rua Bela Vista, Jardim Zaíra', -23.6650, -46.4600,
   'ALTA', 4, '2026-02-10 08:30:00', '2026-02-18 17:00:00', '2026-02-18 17:00:00'),
  (9, 'URB-2026', 11, 5, 1,
   'Placa de sinalização tombada após acidente',
   'Placa de "Pare" derrubada após colisão, deixando cruzamento de alto movimento sem sinalização adequada.',
   'Cruzamento Rua Conceição com Rua XV de Novembro', -23.6695, -46.4480,
   'ALTA', 3, '2026-02-20 15:00:00', '2026-03-05 09:00:00', NULL),
  -- Março 2026
  (10, 'URB-2029', 9, 1, 1,
   'Afundamento de pista na esquina movimentada',
   'Afundamento progressivo do pavimento em esquina com grande fluxo de ônibus, provocando trepidações e danos a veículos.',
   'Esquina Avenida Nações Unidas com Rua Jequitibá', -23.6720, -46.4550,
   'ALTA', 3, '2026-03-05 10:00:00', '2026-03-15 11:00:00', NULL),
  (11, 'URB-2031', 10, 3, 3,
   'Lixo acumulado em frente à praça',
   'Contêiner transbordando há mais de cinco dias sem coleta, com resíduos espalhados pela calçada em frente à praça do bairro.',
   'Praça Dom Pedro II, Centro', -23.6630, -46.4640,
   'MEDIA', 2, '2026-03-12 08:45:00', '2026-03-13 10:30:00', NULL),
  (19, 'URB-2032', 11, 2, 2,
   'Iluminação pública apagada em toda a quadra',
   'Três postes consecutivos sem luz na quadra residencial, deixando a rua completamente escura à noite e aumentando sensação de insegurança.',
   'Rua Professor Mário, Vila São João', -23.6560, -46.4570,
   'MEDIA', 2, '2026-03-20 19:30:00', '2026-03-21 08:00:00', NULL);

INSERT INTO historico_ocorrencias (ocorrencia_id, usuario_id, status_id, mensagem, criado_em) VALUES
  -- ID 1 — Poste apagado (Ryan → Em análise)
  (1, 9, 1, 'Ocorrência registrada pelo cidadão na plataforma.', '2026-04-04 10:30:00'),
  (1, 2, 2, 'Chamado encaminhado para a equipe técnica de iluminação.', '2026-04-04 11:15:00'),
  (1, 2, 2, 'Status atualizado para em análise, aguardando vistoria no local.', '2026-04-04 14:00:00'),
  -- ID 2 — Afundamento asfalto (Maria Ivone → Em atendimento)
  (2, 10, 1, 'Ocorrência registrada com fotos do local.', '2026-04-03 09:10:00'),
  (2, 2, 2, 'Equipe de obras avaliou a extensão do dano no pavimento.', '2026-04-03 11:45:00'),
  (2, 2, 3, 'Reparo incluído na rota da equipe de manutenção viária.', '2026-04-03 16:30:00'),
  -- ID 3 — Acúmulo resíduos (Ryan → Aberta)
  (3, 9, 1, 'Chamado aberto aguardando triagem da limpeza urbana.', '2026-04-01 08:00:00'),
  -- ID 4 — Vazamento calçada (Paulo Cesar → Resolvida)
  (4, 11, 1, 'Vazamento informado com ponto de referência e fotos.', '2026-03-29 07:40:00'),
  (4, 2, 3, 'Equipe de saneamento deslocada para o local.', '2026-03-29 10:20:00'),
  (4, 2, 4, 'Reparo concluído e chamado encerrado.', '2026-03-30 15:20:00'),
  -- ID 5 — Buraco profundo (Maria Ivone → Resolvida)
  (5, 10, 1, 'Ocorrência registrada pelo cidadão com foto do local.', '2026-01-08 07:50:00'),
  (5, 2, 2, 'Chamado recebido e encaminhado para a equipe de obras.', '2026-01-08 13:00:00'),
  (5, 2, 3, 'Equipe de manutenção designada; tampagem provisória realizada.', '2026-01-12 09:30:00'),
  (5, 2, 4, 'Reparo definitivo concluído com aplicação de asfalto frio.', '2026-01-20 16:00:00'),
  -- ID 6 — Entulho irregular (Ryan → Resolvida)
  (6, 9, 1, 'Ocorrência registrada pelo cidadão após denúncia de vizinhos.', '2026-01-15 09:20:00'),
  (6, 2, 2, 'Situação verificada; terreno baldio identificado sem proprietário ativo.', '2026-01-16 14:00:00'),
  (6, 2, 3, 'Caminhão de coleta especial agendado para remoção do entulho.', '2026-01-22 08:00:00'),
  (6, 2, 4, 'Terreno limpo e notificação enviada ao proprietário cadastrado.', '2026-01-28 14:00:00'),
  -- ID 7 — Fiação exposta (Maria Ivone → Resolvida)
  (7, 10, 1, 'Ocorrência registrada com urgência pelo cidadão.', '2026-02-03 11:10:00'),
  (7, 2, 2, 'Situação classificada como risco imediato; equipe elétrica acionada.', '2026-02-03 12:00:00'),
  (7, 2, 3, 'Técnico no local realizando contenção provisória da fiação.', '2026-02-05 09:00:00'),
  (7, 2, 4, 'Reparo elétrico finalizado e poste restabelecido com segurança.', '2026-02-14 10:30:00'),
  -- ID 8 — Vazamento hidrômetro (Paulo Cesar → Resolvida)
  (8, 11, 1, 'Vazamento informado pelo cidadão com registro fotográfico.', '2026-02-10 08:30:00'),
  (8, 2, 2, 'Ordem de serviço aberta na companhia de saneamento.', '2026-02-11 10:00:00'),
  (8, 2, 3, 'Equipe técnica identificou rompimento no ramal externo do hidrômetro.', '2026-02-14 14:00:00'),
  (8, 2, 4, 'Substituição do hidrômetro concluída; calçada em processo de reparo.', '2026-02-18 17:00:00'),
  -- ID 9 — Placa tombada (Paulo Cesar → Em atendimento)
  (9, 11, 1, 'Ocorrência registrada imediatamente após acidente pelo cidadão.', '2026-02-20 15:00:00'),
  (9, 2, 2, 'Equipe de sinalização notificada sobre ausência de placa de "Pare".', '2026-02-21 08:00:00'),
  (9, 2, 3, 'Nova placa instalada provisoriamente; soldagem definitiva agendada.', '2026-03-05 09:00:00'),
  -- ID 10 — Afundamento de pista (Ryan → Em atendimento)
  (10, 9, 1, 'Ocorrência registrada com vídeo mostrando o afundamento progressivo.', '2026-03-05 10:00:00'),
  (10, 2, 2, 'Equipe de engenharia da prefeitura agendou vistoria técnica.', '2026-03-06 09:00:00'),
  (10, 2, 3, 'Vistoria realizada; obra de maior porte incluída no cronograma do mês.', '2026-03-15 11:00:00'),
  -- ID 11 — Lixo na praça (Maria Ivone → Em análise)
  (11, 10, 1, 'Reclamação registrada com foto do contêiner transbordando.', '2026-03-12 08:45:00'),
  (11, 2, 2, 'Situação confirmada por fiscal; coleta especial solicitada à contratada.', '2026-03-13 10:30:00'),
  -- ID 19 — Iluminação apagada (Paulo Cesar → Em análise)
  (19, 11, 1, 'Ocorrência registrada pelo cidadão após tentativa de assalto na rua escura.', '2026-03-20 19:30:00'),
  (19, 2, 2, 'Equipe de iluminação pública notificada para verificação dos três postes.', '2026-03-21 08:00:00');

ALTER TABLE usuarios ALTER COLUMN id RESTART WITH 100;
ALTER TABLE status_ocorrencia ALTER COLUMN id RESTART WITH 100;
ALTER TABLE orgaos ALTER COLUMN id RESTART WITH 100;
ALTER TABLE categorias ALTER COLUMN id RESTART WITH 100;
ALTER TABLE ocorrencias ALTER COLUMN id RESTART WITH 100;
ALTER TABLE historico_ocorrencias ALTER COLUMN id RESTART WITH 100;
ALTER TABLE evidencias ALTER COLUMN id RESTART WITH 100;
