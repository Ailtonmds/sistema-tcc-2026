# Arquitetura e Plano de Implementação - Sistema TCC 2026

## 1. Objetivo

Este documento define a arquitetura de pastas, arquivos, responsabilidades e regras de implementação para o **Sistema de Gerenciamento de Almoxarifado** do TCC.

O objetivo é orientar o OpenCode na organização e conclusão do sistema sem alterar a proposta funcional do projeto.

### Tecnologias

- Frontend: HTML5, CSS3, JavaScript
- Framework visual: Bootstrap
- Backend: PHP
- Banco de dados: MySQL/MariaDB
- Servidor local: Apache/Laragon
- Gráficos: Chart.js, quando necessário
- Comunicação frontend/backend: `fetch()` utilizando endpoints PHP

> Importante: este TCC não utiliza React e não possui IA.

---

# 2. Arquitetura geral

```text
sistema-tcc-2026/
│
├── frontend/
│   ├── index.html
│   ├── login/
│   ├── dashboard/
│   ├── produtos/
│   ├── categorias/
│   ├── lotes/
│   ├── estoque/
│   ├── alertas/
│   ├── relatorios/
│   ├── usuarios/
│   └── assets/
│
├── backend/
│   ├── config/
│   ├── auth/
│   ├── produtos/
│   ├── categorias/
│   ├── lotes/
│   ├── estoque/
│   ├── alertas/
│   ├── relatorios/
│   └── usuarios/
│
├── database/
│   ├── database.sql
│   └── dados_teste.sql
│
├── docs/
│   ├── diagramas/
│   └── documentacao.md
│
├── README.md
└── .gitignore
```

---

# 3. Estrutura completa

```text
sistema-tcc-2026/
│
├── frontend/
│   │
│   ├── index.html
│   │
│   ├── login/
│   │   └── index.html
│   │
│   ├── dashboard/
│   │   └── index.html
│   │
│   ├── produtos/
│   │   └── index.html
│   │
│   ├── categorias/
│   │   └── index.html
│   │
│   ├── lotes/
│   │   └── index.html
│   │
│   ├── estoque/
│   │   ├── entrada.html
│   │   ├── saida.html
│   │   └── movimentacoes.html
│   │
│   ├── alertas/
│   │   └── index.html
│   │
│   ├── relatorios/
│   │   └── index.html
│   │
│   ├── usuarios/
│   │   └── index.html
│   │
│   └── assets/
│       ├── css/
│       │   ├── style.css
│       │   ├── login.css
│       │   ├── dashboard.css
│       │   └── responsivo.css
│       │
│       ├── js/
│       │   ├── main.js
│       │   ├── login.js
│       │   ├── dashboard.js
│       │   ├── produtos.js
│       │   ├── categorias.js
│       │   ├── lotes.js
│       │   ├── estoque.js
│       │   ├── alertas.js
│       │   └── relatorios.js
│       │
│       └── img/
│           ├── logo.png
│           └── favicon.png
│
├── backend/
│   │
│   ├── config/
│   │   ├── conexao.php
│   │   └── config.php
│   │
│   ├── auth/
│   │   ├── login.php
│   │   ├── logout.php
│   │   └── verificar_sessao.php
│   │
│   ├── produtos/
│   │   ├── listar.php
│   │   ├── cadastrar.php
│   │   ├── editar.php
│   │   └── excluir.php
│   │
│   ├── categorias/
│   │   ├── listar.php
│   │   ├── cadastrar.php
│   │   ├── editar.php
│   │   └── excluir.php
│   │
│   ├── lotes/
│   │   ├── listar.php
│   │   ├── cadastrar.php
│   │   ├── editar.php
│   │   └── excluir.php
│   │
│   ├── estoque/
│   │   ├── entrada.php
│   │   ├── saida.php
│   │   └── movimentacoes.php
│   │
│   ├── alertas/
│   │   ├── estoque_baixo.php
│   │   └── validade.php
│   │
│   ├── relatorios/
│   │   ├── estoque.php
│   │   ├── movimentacoes.php
│   │   └── validade.php
│   │
│   └── usuarios/
│       ├── listar.php
│       ├── cadastrar.php
│       ├── editar.php
│       └── excluir.php
│
├── database/
│   ├── database.sql
│   └── dados_teste.sql
│
├── docs/
│   ├── diagramas/
│   │   ├── modelo-er.png
│   │   ├── caso-de-uso.png
│   │   ├── fluxo-entrada.png
│   │   └── fluxo-saida.png
│   │
│   └── documentacao.md
│
├── README.md
└── .gitignore
```

---

# 4. Responsabilidade de cada pasta

## 4.1 `frontend/`

Contém somente a interface que o usuário acessa.

Não colocar SQL ou regras de banco diretamente nos arquivos HTML/JS.

### `frontend/index.html`

Pode funcionar como página inicial ou redirecionamento para o login.

### `frontend/login/`

Tela de autenticação.

Responsabilidades:

- formulário de usuário e senha;
- validação básica dos campos;
- chamada ao backend;
- tratamento das respostas;
- redirecionamento para o dashboard após login.

### `frontend/dashboard/`

Painel principal.

Deve apresentar, quando possível:

- quantidade total de produtos;
- quantidade em estoque;
- produtos com estoque baixo;
- produtos próximos do vencimento;
- produtos vencidos;
- movimentações recentes;
- gráficos úteis.

### `frontend/produtos/`

CRUD visual de produtos.

Deve permitir:

- listar;
- cadastrar;
- editar;
- excluir/desativar;
- pesquisar.

Campos esperados:

- nome;
- descrição;
- categoria;
- unidade de medida;
- estoque mínimo;
- localização;
- status.

**Não criar campo de código de barras.**

### `frontend/categorias/`

CRUD de categorias.

### `frontend/lotes/`

Controle de lotes.

Deve trabalhar com:

- número do lote;
- data de fabricação;
- data de validade;
- produto relacionado;
- quantidade disponível.

### `frontend/estoque/`

Módulo central de movimentações.

Arquivos:

- `entrada.html`
- `saida.html`
- `movimentacoes.html`

A entrada e a saída devem ser telas separadas.

**Não utilizar código de barras.**

### `frontend/alertas/`

Exibir:

- estoque abaixo do mínimo;
- produtos próximos da validade;
- produtos vencidos.

### `frontend/relatorios/`

Interface para consultas e relatórios.

### `frontend/usuarios/`

Gerenciamento do usuário do sistema.

O sistema foi pensado inicialmente para uma empresa e acesso controlado, portanto não criar um sistema complexo de permissões sem necessidade.

---

# 5. Backend

## 5.1 `backend/config/`

### `conexao.php`

Responsável exclusivamente pela conexão com MySQL/MariaDB.

Usar PDO ou mysqli de forma consistente em todo o projeto.

Requisitos:

- tratamento de erro;
- charset UTF-8;
- credenciais centralizadas;
- não repetir código de conexão nos endpoints.

### `config.php`

Configurações gerais do backend, como:

- URL base da API;
- configurações de sessão;
- constantes necessárias.

Não armazenar senhas reais do banco no GitHub.

---

# 6. Autenticação

## `backend/auth/`

### `login.php`

Receber:

```text
POST
usuario
senha
```

Validar usuário e senha.

Senhas devem ser armazenadas usando:

```php
password_hash()
```

e verificadas usando:

```php
password_verify()
```

Nunca armazenar senha em texto puro.

### `logout.php`

Encerrar sessão.

### `verificar_sessao.php`

Verificar se existe uma sessão válida antes de permitir acesso às operações protegidas.

---

# 7. CRUD de produtos

## `backend/produtos/`

### `listar.php`

Retornar produtos.

### `cadastrar.php`

Cadastrar produto.

### `editar.php`

Atualizar produto.

### `excluir.php`

Excluir ou desativar produto conforme a estrutura existente do banco.

Antes de excluir fisicamente, verificar se o produto possui:

- lotes;
- movimentações.

Não quebrar integridade referencial.

---

# 8. CRUD de categorias

## `backend/categorias/`

Implementar:

```text
listar
cadastrar
editar
excluir
```

Não permitir exclusão de categoria que esteja sendo utilizada por produtos sem tratar previamente a referência.

---

# 9. CRUD de lotes

## `backend/lotes/`

Implementar:

```text
listar
cadastrar
editar
excluir
```

Validar:

- número do lote;
- produto;
- validade;
- fabricação;
- quantidade.

A validade deve ser uma informação importante para os alertas.

---

# 10. Estoque

## `backend/estoque/`

Este módulo é crítico.

### `entrada.php`

Responsável por registrar entrada de produtos.

Fluxo:

```text
Receber dados
     ↓
Validar produto
     ↓
Validar lote
     ↓
Validar quantidade
     ↓
Atualizar quantidade do lote
     ↓
Registrar movimentação
     ↓
Retornar sucesso
```

### `saida.php`

Responsável por registrar saída.

Fluxo:

```text
Receber dados
     ↓
Validar produto
     ↓
Validar lote
     ↓
Verificar quantidade disponível
     ↓
Impedir estoque negativo
     ↓
Diminuir quantidade
     ↓
Registrar movimentação
     ↓
Retornar sucesso
```

### Regra fundamental

Entrada e saída possuem telas diferentes, mas ambas devem registrar o histórico na tabela:

```text
movimentacoes
```

O campo `tipo` deve identificar:

```text
entrada
saida
```

---

# 11. Histórico de movimentações

`backend/estoque/movimentacoes.php`

Deve permitir consultar:

- produto;
- lote;
- tipo;
- quantidade;
- data;
- usuário;
- observação.

Filtros podem incluir:

- período;
- produto;
- lote;
- tipo de movimentação.

---

# 12. Banco de dados

A estrutura deve respeitar o relacionamento:

```text
usuarios
    │
    └───────────────┐
                    │
categorias          │
    │               │
    ▼               │
produtos            │
    │               │
    ▼               │
produto_lote        │
    │               │
    ▼               │
lotes               │
    │               │
    └───────┐       │
            ▼       ▼
        movimentacoes
```

## Tabelas principais

### `usuarios`

```text
id
nome
usuario
senha
nivel_acesso
criado_em
```

### `categorias`

```text
id
nome
descricao
```

### `produtos`

```text
id
categoria_id
nome
descricao
unidade_medida
estoque_minimo
localizacao
ativo
criado_em
```

### `lotes`

```text
id
numero_lote
data_fabricacao
data_validade
```

### `produto_lote`

```text
id
produto_id
lote_id
quantidade
```

### `movimentacoes`

```text
id
produto_id
lote_id
tipo
quantidade
data_movimentacao
usuario_id
observacao
```

---

# 13. Regras de negócio

O OpenCode deve preservar estas regras.

## Produtos

1. Produto deve possuir categoria.
2. Produto deve possuir nome.
3. Estoque mínimo não pode ser negativo.
4. Localização deve poder ser informada.
5. Não utilizar código de barras.

## Lotes

1. Lote deve estar associado a um produto através de `produto_lote`.
2. Lote deve possuir validade.
3. Quantidade não pode ser negativa.
4. Produtos diferentes podem possuir lotes diferentes.
5. Um produto pode possuir vários lotes.

## Entradas

1. Quantidade deve ser maior que zero.
2. Registrar movimentação.
3. Atualizar quantidade disponível.
4. Registrar usuário responsável.
5. Registrar data.

## Saídas

1. Quantidade deve ser maior que zero.
2. Verificar estoque disponível.
3. Não permitir estoque negativo.
4. Atualizar quantidade disponível.
5. Registrar movimentação.
6. Registrar usuário responsável.
7. Registrar data.

## Alertas

### Estoque baixo

Gerar alerta quando:

```text
quantidade disponível <= estoque mínimo
```

### Validade

Identificar:

- produtos vencidos;
- produtos próximos do vencimento.

O número de dias para considerar "próximo do vencimento" deve ser configurável, preferencialmente em uma constante/configuração, e não espalhado pelo código.

---

# 14. Comunicação Frontend → Backend

Usar `fetch()`.

Exemplo conceitual:

```javascript
fetch('../../backend/produtos/listar.php')
    .then(response => response.json())
    .then(data => {
        // atualizar interface
    });
```

Para POST:

```javascript
fetch('../../backend/produtos/cadastrar.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(dados)
});
```

O backend deve retornar respostas JSON padronizadas.

Exemplo:

```json
{
    "sucesso": true,
    "mensagem": "Produto cadastrado com sucesso.",
    "dados": {}
}
```

Em erro:

```json
{
    "sucesso": false,
    "mensagem": "Não foi possível cadastrar o produto."
}
```

---

# 15. Segurança

Implementar pelo menos:

- `password_hash()`;
- `password_verify()`;
- sessões PHP;
- validação de dados no backend;
- prepared statements;
- proteção contra SQL Injection;
- validação de quantidade;
- proteção contra estoque negativo;
- verificação de sessão nas operações protegidas;
- não expor credenciais do banco;
- tratamento adequado de erros.

Nunca confiar somente na validação JavaScript.

Toda validação importante deve existir também no PHP.

---

# 16. Interface

Manter o padrão visual existente do TCC.

Paleta definida:

```css
--verde: #246b3a;
--verde-escuro: #123d24;
--verde-hover: #1b542d;
--verde-claro: #82d39c;
--background: #f5f7f5;
```

Utilizar Bootstrap para:

- tabelas;
- formulários;
- cards;
- botões;
- modais;
- alertas;
- navbar/sidebar;
- responsividade.

Evitar criar vários arquivos CSS desnecessários.

---

# 17. Dashboard

O dashboard deve priorizar informações úteis do almoxarifado.

Sugestão:

```text
┌────────────────┬────────────────┬────────────────┐
│   Produtos     │ Estoque baixo  │ Próx. validade │
│      120       │       8        │       5        │
└────────────────┴────────────────┴────────────────┘

┌───────────────────────────────────────────────────┐
│          Movimentações recentes                   │
└───────────────────────────────────────────────────┘

┌──────────────────────┬────────────────────────────┐
│ Estoque por categoria│ Entradas x Saídas          │
│       gráfico        │       gráfico              │
└──────────────────────┴────────────────────────────┘
```

Usar Chart.js somente onde realmente ajudar.

---

# 18. API / endpoints

Os endpoints devem ser simples e consistentes.

Exemplo:

```text
GET  backend/produtos/listar.php
POST backend/produtos/cadastrar.php
POST backend/produtos/editar.php
POST backend/produtos/excluir.php

GET  backend/categorias/listar.php
POST backend/categorias/cadastrar.php
POST backend/categorias/editar.php
POST backend/categorias/excluir.php

GET  backend/lotes/listar.php
POST backend/lotes/cadastrar.php
POST backend/lotes/editar.php
POST backend/lotes/excluir.php

POST backend/estoque/entrada.php
POST backend/estoque/saida.php
GET  backend/estoque/movimentacoes.php

GET backend/alertas/estoque_baixo.php
GET backend/alertas/validade.php

GET backend/relatorios/estoque.php
GET backend/relatorios/movimentacoes.php
GET backend/relatorios/validade.php
```

Antes de criar novos endpoints, verificar se já existe funcionalidade equivalente no projeto.

---

# 19. Regras para o OpenCode

## Regra 1 - Analisar antes de alterar

Antes de criar ou excluir arquivos:

1. analisar a estrutura atual;
2. analisar o banco atual;
3. analisar os arquivos existentes;
4. verificar funcionalidades já implementadas;
5. identificar código duplicado;
6. preservar funcionalidades que já funcionam.

Não sobrescrever o projeto inteiro sem necessidade.

## Regra 2 - Não remover funcionalidades

Uma funcionalidade existente somente deve ser removida se:

- estiver quebrada e sua substituição for necessária;
- houver duplicidade;
- ou esta documentação determinar explicitamente a mudança.

## Regra 3 - Não inventar requisitos

O sistema deve permanecer focado em:

> Gerenciamento de almoxarifado para controle de produtos, lotes, estoque, movimentações, validade e alertas.

Não adicionar:

- IA;
- código de barras;
- sistema financeiro;
- rotas;
- logística de transporte;
- chat;
- funcionalidades sem relação com o almoxarifado.

## Regra 4 - Banco é prioridade

Antes de implementar uma funcionalidade, verificar se o banco suporta corretamente a operação.

Se necessário, atualizar `database/database.sql`.

Não criar tabelas duplicadas para resolver problemas que já podem ser resolvidos com as tabelas existentes.

## Regra 5 - Backend valida tudo

O JavaScript pode validar para melhorar a experiência, mas o PHP deve validar novamente.

## Regra 6 - Não duplicar regras

Regras de estoque devem ficar no backend.

Não colocar uma regra diferente no frontend e outra no backend.

---

# 20. Ordem recomendada de implementação

O OpenCode deve trabalhar nesta ordem:

## Fase 1 - Diagnóstico

- analisar repositório;
- analisar banco atual;
- identificar arquivos existentes;
- identificar funcionalidades concluídas;
- identificar erros;
- comparar com esta arquitetura.

## Fase 2 - Banco

Garantir:

- tabelas;
- chaves primárias;
- chaves estrangeiras;
- índices;
- relacionamentos;
- dados de teste.

## Fase 3 - Conexão

Implementar/testar:

```text
backend/config/conexao.php
```

## Fase 4 - Autenticação

Implementar:

```text
login
sessão
logout
proteção
```

## Fase 5 - Cadastros

Implementar na ordem:

```text
categorias
↓
produtos
↓
lotes
↓
produto_lote
```

## Fase 6 - Estoque

Implementar:

```text
entrada
↓
saída
↓
movimentações
```

## Fase 7 - Alertas

Implementar:

```text
estoque baixo
validade
vencidos
```

## Fase 8 - Dashboard

Integrar os dados reais do banco.

## Fase 9 - Relatórios

Implementar:

```text
estoque
movimentações
validade
```

## Fase 10 - Testes

Testar:

- login;
- logout;
- cadastro;
- edição;
- exclusão;
- entrada;
- saída;
- estoque negativo;
- lote;
- validade;
- alerta;
- relatórios;
- dashboard;
- sessão;
- responsividade.

---

# 21. Critérios de conclusão

O sistema será considerado concluído quando:

- [ ] Banco de dados funcionando.
- [ ] Conexão PHP funcionando.
- [ ] Login funcionando.
- [ ] Sessão funcionando.
- [ ] Logout funcionando.
- [ ] CRUD de categorias funcionando.
- [ ] CRUD de produtos funcionando.
- [ ] CRUD de lotes funcionando.
- [ ] Relacionamento produto/lote funcionando.
- [ ] Entrada de estoque funcionando.
- [ ] Saída de estoque funcionando.
- [ ] Estoque negativo bloqueado.
- [ ] Histórico de movimentações funcionando.
- [ ] Alerta de estoque baixo funcionando.
- [ ] Alerta de validade funcionando.
- [ ] Produtos vencidos identificados.
- [ ] Dashboard funcionando com dados reais.
- [ ] Relatórios funcionando.
- [ ] Layout responsivo.
- [ ] Código sem erros PHP/JS.
- [ ] Queries protegidas contra SQL Injection.
- [ ] Senhas protegidas.
- [ ] README atualizado.
- [ ] Documentação atualizada.

---

# 22. Instrução final para o OpenCode

Você está trabalhando em um projeto de TCC existente.

**Não reescreva o sistema do zero.**

Primeiro analise o repositório e identifique o que já está implementado.

Depois:

1. compare a estrutura atual com esta documentação;
2. reorganize os arquivos quando necessário;
3. preserve o código funcional;
4. corrija os erros existentes;
5. implemente as funcionalidades faltantes;
6. mantenha frontend e backend separados;
7. mantenha o banco separado;
8. teste cada módulo antes de avançar;
9. atualize a documentação conforme as funcionalidades forem concluídas.

A arquitetura final desejada é:

```text
FRONTEND
HTML + CSS + JavaScript + Bootstrap
        │
        │ fetch / HTTP
        ▼
BACKEND
PHP
        │
        │ SQL
        ▼
DATABASE
MySQL / MariaDB
```

O sistema deve permanecer simples, funcional, seguro e adequado para apresentação de TCC.

**Prioridade:** funcionamento correto > complexidade arquitetural.
