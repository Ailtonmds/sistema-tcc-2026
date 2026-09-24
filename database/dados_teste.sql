-- Dados de teste adicionais para o Sistema TCC 2026
-- Execute após o database.sql principal

USE almoxarifado;

-- Mais categorias
INSERT IGNORE INTO categorias (nome, descricao) VALUES
('Desinfetantes', 'Produtos para desinfecção de ambientes e equipamentos'),
('Rações', 'Rações para diferentes tipos de animais'),
('Ferramentas', 'Ferramentas e equipamentos de manejo'),
('Vestuário', 'Roupas e acessórios para trabalho no campo');

-- Mais produtos
INSERT IGNORE INTO produtos (categoria_id, nome, descricao, unidade_medida, estoque_minimo, localizacao, ativo) VALUES
(5, 'Desinfetante Concentrado', 'Para desinfecção de curris e baias', 'Litro', 20, 'Prateleira C1', TRUE),
(5, 'Álcool 70%', 'Antisséptico para uso geral', 'Frasco', 30, 'Prateleira C2', TRUE),
(6, 'Ração Bovinos Corte', 'Ração para bovinos de corte', 'Saco', 50, 'Galpão A', TRUE),
(6, 'Ração Leiteiras', 'Ração para vacas leiteiras', 'Saco', 40, 'Galpão A', TRUE),
(7, 'Mangueira 1/2"', 'Mangueira para irrigação', 'Metro', 100, 'Prateleira D1', TRUE),
(7, 'Balde Plástico 20L', 'Balde para uso geral', 'Unidade', 15, 'Prateleira D2', TRUE),
(8, 'Camisa Manga Longa UV', 'Proteção solar para trabalho externo', 'Unidade', 20, 'Prateleira E1', TRUE),
(8, 'Chapéu de Palha', 'Proteção solar', 'Unidade', 25, 'Prateleira E2', TRUE);

-- Mais lotes
INSERT IGNORE INTO lotes (numero_lote, data_fabricacao, data_validade) VALUES
('LOTE-2024-006', '2024-06-01', '2026-06-01'),
('LOTE-2024-007', '2024-06-15', '2025-10-15'),
('LOTE-2024-008', '2024-07-01', '2026-07-01'),
('LOTE-2024-009', '2024-07-20', '2025-09-20'),
('LOTE-2024-010', '2024-08-01', '2026-08-01');

-- Mais produto_lote
INSERT IGNORE INTO produto_lote (produto_id, lote_id, quantidade) VALUES
(7, 6, 100),
(8, 7, 80),
(9, 8, 200),
(10, 8, 150),
(11, 9, 500),
(12, 9, 100),
(13, 10, 60),
(14, 10, 80);

-- Mais movimentações
INSERT IGNORE INTO movimentacoes (produto_id, lote_id, tipo, quantidade, usuario_id, observacao) VALUES
(7, 6, 'entrada', 100, 1, 'Compra inicial'),
(8, 7, 'entrada', 80, 1, 'Compra inicial'),
(9, 8, 'entrada', 200, 1, 'Compra inicial'),
(10, 8, 'entrada', 150, 1, 'Compra inicial'),
(11, 9, 'entrada', 500, 1, 'Compra inicial'),
(12, 9, 'entrada', 100, 1, 'Compra inicial'),
(13, 10, 'entrada', 60, 1, 'Compra inicial'),
(14, 10, 'entrada', 80, 1, 'Compra inicial'),
(1, 1, 'saida', 5, 1, 'Uso em propriedade'),
(3, 3, 'saida', 10, 1, 'Vacinação de rebanho');