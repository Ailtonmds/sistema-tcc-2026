# Sistema de Gerenciamento de Almoxarifado - TCC 2026

Sistema web para controle de estoque de almoxarifado desenvolvido como Trabalho de Conclusão de Curso (TCC).

## 📋 Funcionalidades

- **Autenticação**: Login/logout com sessões PHP seguras, senhas com hash (password_hash)
- **Dashboard**: Visão geral com estatísticas, gráficos (Chart.js) e movimentações recentes
- **Produtos**: CRUD completo (nome, categoria, unidade, estoque mínimo, localização)
- **Categorias**: CRUD de categorias de produtos
- **Lotes**: Controle de lotes com fabricação, validade e quantidade por produto
- **Estoque**: 
  - Entrada de produtos
  - Saída de produtos (bloqueio de estoque negativo)
  - Histórico de movimentações com filtros
- **Alertas**: 
  - Estoque abaixo do mínimo
  - Próximos do vencimento (configurável)
  - Produtos vencidos
- **Relatórios**:
  - Estoque atual por categoria/status
  - Movimentações por período
  - Validade dos lotes
- **Usuários**: Gerenciamento de usuários (admin/operador)

## 🛠 Tecnologias

- **Frontend**: HTML5, CSS3, JavaScript (ES6+), Bootstrap 5, Bootstrap Icons
- **Backend**: PHP 8+, PDO (MySQL/MariaDB)
- **Banco de Dados**: MySQL 8+ / MariaDB 10+
- **Gráficos**: Chart.js
- **Servidor**: Apache (Laragon/XAMPP/WAMP)

## 📁 Estrutura do Projeto

```
sistema-tcc-2026/
│
├── frontend/
│   ├── index.html                 # Redireciona para login
│   ├── login/                     # Tela de login
│   ├── dashboard/                 # Painel principal
│   ├── produtos/                  # CRUD de produtos
│   ├── categorias/                # CRUD de categorias
│   ├── lotes/                     # Controle de lotes
│   ├── estoque/                   # Entrada, saída, movimentações
│   ├── alertas/                   # Alertas de estoque/validade
│   ├── relatorios/                # Relatórios diversos
│   ├── usuarios/                  # Gerenciamento de usuários
│   ├── criar-login/               # Criação de usuário inicial
│   └── assets/
│       ├── css/style.css          # Estilos customizados
│       └── js/                    # Scripts por módulo
│
├── backend/
│   ├── config/
│   │   ├── conexao.php            # Conexão PDO
│   │   └── config.php             # Configurações gerais
│   ├── auth/
│   │   ├── login.php              # Autenticação
│   │   ├── logout.php             # Encerrar sessão
│   │   └── verificar_sessao.php   # Middleware de proteção
│   ├── dashboard.php              # API do dashboard
│   ├── categorias/                # CRUD categorias
│   ├── produtos/                  # CRUD produtos
│   ├── lotes/                     # CRUD lotes
│   ├── estoque/                   # Entrada, saída, movimentações
│   ├── alertas/                   # Alertas de estoque/validade
│   ├── relatorios/                # Relatórios
│   └── usuarios/                  # CRUD usuários
│
├── database/
│   ├── database.sql               # Schema completo
│   └── dados_teste.sql            # Dados de exemplo
│
├── init_db.php                    # Script de inicialização do banco
└── tcc.md                         # Documentação da arquitetura
```

## 🚀 Instalação

### Pré-requisitos
- PHP 8.0+
- MySQL 8.0+ ou MariaDB 10.5+
- Apache (mod_rewrite habilitado)
- Extensões PHP: pdo_mysql, mbstring, json

### Passos

1. **Clone/baixe o projeto** para a pasta do servidor web (ex: `C:\laragon\www\sistema-tcc-2026`)

2. **Configure o banco de dados**:
   - Abra o MySQL/MariaDB
   - O script `init_db.php` criará o banco `almoxarifado` automaticamente
   - Ou execute manualmente: `database/database.sql`

3. **Configure a conexão** (se necessário):
   - Edite `backend/config/conexao.php`
   - Ajuste host, porta, usuário e senha do MySQL

4. **Inicie o servidor** (Apache via Laragon/XAMPP)

5. **Acesse o inicializador**:
   ```
   http://localhost/sistema-tcc-2026/init_db.php
   ```
   Clique em "Inicializar" para criar as tabelas e dados de teste.

6. **Acesse o sistema**:
   ```
   http://localhost/sistema-tcc-2026/frontend/login/
   ```

### Usuário Padrão
Após executar o `database.sql`:
- **Usuário**: `admin`
- **Senha**: `admin123`

Para criar o primeiro usuário (se banco estiver vazio):
```
http://localhost/sistema-tcc-2026/frontend/criar-login/
```

## 🔐 Segurança

- Senhas armazenadas com `password_hash()` (bcrypt)
- Verificação com `password_verify()`
- Prepared statements (PDO) contra SQL Injection
- Sessões PHP seguras (httponly, samesite)
- Validação no backend (não confia apenas no frontend)
- Proteção de rotas via `verificar_sessao.php`

## 🎨 Interface

- **Tema**: Verde agronômico (`#246b3a`, `#123d24`, `#82d39c`)
- **Framework**: Bootstrap 5.3 (responsivo, mobile-first)
- **Ícones**: Bootstrap Icons 1.13
- **Gráficos**: Chart.js (doughnut, line, bar)

## 📊 Regras de Negócio

1. **Produtos**: Devem ter categoria, nome, unidade; estoque mínimo ≥ 0
2. **Lotes**: Vinculados a produtos via `produto_lote`; validade obrigatória
3. **Entradas**: Qtd > 0; atualiza lote + registra movimentação
4. **Saídas**: Qtd > 0; verifica estoque; bloqueia negativo; registra movimentação
5. **Alertas**: 
   - Estoque baixo: qtd total ≤ estoque mínimo
   - Vencimento: ≤ 30 dias (configurável em `config.php`)
6. **Exclusão**: Não remove se houver vínculos (apenas desativa)

## 📝 Licença

Projeto acadêmico - TCC 2026. Uso educacional.

## 👨‍💻 Autor

Desenvolvido para Trabalho de Conclusão de Curso - Sistema de Gerenciamento de Almoxarifado.