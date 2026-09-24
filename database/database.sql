-- Sistema TCC 2026 - Banco de Dados Almoxarifado
-- Schema completo conforme arquitetura definida em tcc.md

CREATE DATABASE IF NOT EXISTS almoxarifado
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE almoxarifado;

-- ========================================
-- Tabela: usuarios
-- ========================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    nivel_acesso ENUM('admin', 'operador') NOT NULL DEFAULT 'operador',
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ========================================
-- Tabela: categorias
-- ========================================
CREATE TABLE IF NOT EXISTS categorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ========================================
-- Tabela: produtos
-- ========================================
CREATE TABLE IF NOT EXISTS produtos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT UNSIGNED NOT NULL,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    unidade_medida VARCHAR(50) NOT NULL,
    estoque_minimo INT UNSIGNED NOT NULL DEFAULT 0,
    localizacao VARCHAR(150) NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_produtos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ========================================
-- Tabela: lotes
-- ========================================
CREATE TABLE IF NOT EXISTS lotes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero_lote VARCHAR(100) NOT NULL,
    data_fabricacao DATE NULL,
    data_validade DATE NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_numero_lote (numero_lote)
) ENGINE=InnoDB;

-- ========================================
-- Tabela: produto_lote (relacionamento N:M com quantidade)
-- ========================================
CREATE TABLE IF NOT EXISTS produto_lote (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produto_id INT UNSIGNED NOT NULL,
    lote_id INT UNSIGNED NOT NULL,
    quantidade INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_produto_lote_produto FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    CONSTRAINT fk_produto_lote_lote FOREIGN KEY (lote_id) REFERENCES lotes(id) ON DELETE CASCADE,
    UNIQUE KEY uk_produto_lote (produto_id, lote_id)
) ENGINE=InnoDB;

-- ========================================
-- Tabela: movimentacoes
-- ========================================
CREATE TABLE IF NOT EXISTS movimentacoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produto_id INT UNSIGNED NOT NULL,
    lote_id INT UNSIGNED NOT NULL,
    tipo ENUM('entrada', 'saida') NOT NULL,
    quantidade INT NOT NULL,
    data_movimentacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT UNSIGNED NOT NULL,
    observacao TEXT NULL,
    CONSTRAINT fk_movimentacoes_produto FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE RESTRICT,
    CONSTRAINT fk_movimentacoes_lote FOREIGN KEY (lote_id) REFERENCES lotes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_movimentacoes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ========================================
-- Índices para performance
-- ========================================
CREATE INDEX idx_produtos_categoria ON produtos(categoria_id);
CREATE INDEX idx_produtos_ativo ON produtos(ativo);
CREATE INDEX idx_lotes_validade ON lotes(data_validade);
CREATE INDEX idx_produto_lote_produto ON produto_lote(produto_id);
CREATE INDEX idx_produto_lote_lote ON produto_lote(lote_id);
CREATE INDEX idx_movimentacoes_produto ON movimentacoes(produto_id);
CREATE INDEX idx_movimentacoes_lote ON movimentacoes(lote_id);
CREATE INDEX idx_movimentacoes_data ON movimentacoes(data_movimentacao);
CREATE INDEX idx_movimentacoes_tipo ON movimentacoes(tipo);

-- ========================================
-- Dados de teste
-- ========================================

-- Usuário admin padrão (senha: admin123)
INSERT IGNORE INTO usuarios (nome, usuario, senha_hash, nivel_acesso) VALUES
('Administrador', 'admin', '$2y$10$uy5qA89j.U9xlITZ.1kUsull95YEV6ewap.onR0sx0C.ZLKTaMRLm', 'admin');

UPDATE usuarios
SET senha_hash = '$2y$10$uy5qA89j.U9xlITZ.1kUsull95YEV6ewap.onR0sx0C.ZLKTaMRLm'
WHERE usuario = 'admin'
  AND senha_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- Categorias
INSERT IGNORE INTO categorias (nome, descricao) VALUES
('Medicamentos', 'Medicamentos veterinários e de uso geral'),
('Vacinas', 'Vacinas para animais'),
('EPIs', 'Equipamentos de Proteção Individual'),
('Insumos', 'Insumos gerais para almoxarifado');

-- Produtos de exemplo
INSERT IGNORE INTO produtos (categoria_id, nome, descricao, unidade_medida, estoque_minimo, localizacao, ativo) VALUES
(1, 'Anti-inflamatório Veterinário', 'Para tratamento de inflamações em bovinos', 'Frasco', 10, 'Prateleira A1', TRUE),
(1, 'Antibiótico de Longa Ação', 'Tratamento de infecções bacterianas', 'Frasco', 5, 'Prateleira A2', TRUE),
(2, 'Vacina Febre Aftosa', 'Vacina obrigatória para bovinos', 'Frasco', 20, 'Refrigerador 1', TRUE),
(2, 'Vacina Brucelose', 'Vacina para prevenção de brucelose', 'Frasco', 15, 'Refrigerador 1', TRUE),
(3, 'Luva de Nitrilo', 'Luvas descartáveis para manuseio', 'Caixa', 30, 'Prateleira B1', TRUE),
(3, 'Bota de Borracha', 'Botas de proteção para área de contenção', 'Par', 10, 'Prateleira B2', TRUE);

-- Lotes de exemplo
INSERT IGNORE INTO lotes (numero_lote, data_fabricacao, data_validade) VALUES
('LOTE-2024-001', '2024-01-15', '2026-01-15'),
('LOTE-2024-002', '2024-02-20', '2026-02-20'),
('LOTE-2024-003', '2024-03-10', '2025-12-10'),
('LOTE-2024-004', '2024-04-05', '2026-04-05'),
('LOTE-2024-005', '2024-05-12', '2025-11-12');

-- Relacionamento produto_lote com quantidades iniciais
INSERT IGNORE INTO produto_lote (produto_id, lote_id, quantidade) VALUES
(1, 1, 50),
(2, 2, 30),
(3, 3, 100),
(4, 4, 80),
(5, 5, 200),
(6, 5, 50);

-- Movimentações de exemplo
INSERT IGNORE INTO movimentacoes (produto_id, lote_id, tipo, quantidade, usuario_id, observacao) VALUES
(1, 1, 'entrada', 50, 1, 'Estoque inicial'),
(2, 2, 'entrada', 30, 1, 'Estoque inicial'),
(3, 3, 'entrada', 100, 1, 'Estoque inicial'),
(4, 4, 'entrada', 80, 1, 'Estoque inicial'),
(5, 5, 'entrada', 200, 1, 'Estoque inicial'),
(6, 5, 'entrada', 50, 1, 'Estoque inicial');