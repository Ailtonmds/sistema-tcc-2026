-- Schema mínimo para login e cadastro em uma instalação nova.
CREATE DATABASE IF NOT EXISTS almoxarifado
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE almoxarifado;

CREATE TABLE IF NOT EXISTS user_login (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(100) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS produto (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  categoria VARCHAR(50) NOT NULL,
  unidade VARCHAR(50) NOT NULL,
  lote VARCHAR(100) NOT NULL,
  validade DATE NOT NULL,
  localizacao VARCHAR(150) NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Usuário inicial de desenvolvimento: admin / admin123.
INSERT IGNORE INTO user_login (usuario, senha_hash)
VALUES ('admin', '$2y$12$YsJGoNiDCQxf2o78gr1x.ebwhnGN14y8YCbv9C2SUi3AWqnwGtm36');
