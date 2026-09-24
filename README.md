<<<<<<< HEAD
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
=======
Centro Estadual de Educação Tecnológica Paula Souza
ETEC Dr. Nelson Alves Vianna
Ensino Técnico Integrado ao Médio – Desenvolvimento de Sistemas

TÍTULO DO SEU ARTIGO: insira o subtítulo, se houver

Ailton Moreira de Souza
Luis gabriel matos de oliveira
Kamilly portella 
Alerrandro miguel de almeida lima
Cassiano Gonçalves Duarte

Resumo
Este trabalho apresenta o desenvolvimento de um sistema web para o gerenciamento de estoque e almoxarifado da empresa Ianni. O projeto surge com o objetivo de solucionar as fragilidades do processo operacional anterior, o qual dependia do controle manual por meio de planilhas eletrônicas, gerando inconsistências no acompanhamento de quantidades e na gestão do vencimento dos produtos. Como solução, foi desenvolvida uma aplicação centralizada em um banco de dados relacional que permite o cadastro parametrizado de categorias, lotes e produtos. Além disso, o sistema disponibiliza um painel (dashboard) com gráficos de análise que possibilitam a consulta em tempo real da quantidade em estoque, o monitoramento das datas de validade e o rastreamento das movimentações de entrada e saída. Com essa implementação, busca-se otimizar a tomada de decisões, reduzir perdas por vencimento e garantir a integridade dos dados operacionais da empresa.

Palavras-chave: Controle de estoque. Gestão de almoxarifado. Sistema web. Banco de dados. Monitoramento de validade.

Abstract
This study presents the development of a web-based system for inventory and warehouse management for the company Ianni. The project aims to solve the operational vulnerabilities of the previous control process, which relied on manual tracking using electronic spreadsheets, leading to inconsistencies in quantity control and product expiration date management. As a solution, a web system integrated with a relational database was developed, allowing the registered control of categories, batches, and products. Furthermore, the system provides a dashboard featuring analytical charts that enable real-time tracking of stock levels, monitoring of expiration dates, and tracking of inbound and outbound item movements. This implementation aims to optimize decision-making, minimize product losses due to expiration, and ensure the integrity of the company's operational data.

Keywords: Inventory control. Warehouse management. Web system. Database. Expiration monitoring.



1.	Introdução
Parte inicial do artigo, onde devem constar a delimitação do assunto tratado, os objetivos da pesquisa e outros elementos necessários para situar o tema do artigo.

2.	Desenvolvimento
2.1	Linguagens
2.1.1 PHP
O PHP (um acrônimo recursivo para PHP: Hypertext Preprocessor) é uma linguagem de script open source de uso geral, muito utilizada, e especialmente adequada para o desenvolvimento web e que pode ser embutida dentro do HTML.
O PHP é focado principalmente nos scripts do lado do servidor, portanto ele pode fazer qualquer coisa que qualquer outro programa CGI pode fazer, como coletar dados de formulários, gerar páginas com conteúdo dinâmico ou enviar e receber cookies. Mas o PHP pode fazer muito mais.
2.1.2 JavaScript
As funções de JavaScript podem melhorar a experiência do usuário durante a navegação em um site, como, por exemplo, desde a atualização do feed na página da mídia social até a exibição de animações e mapas interativos. 
Como uma linguagem de script do lado do cliente, ele é uma das tecnologias principais da World Wide Web. Por exemplo, ao navegar na Internet, é possível visualizar a qualquer momento um carrossel de imagens, um menu suspenso “clicar para visualizar” ou mesmo mudar dinamicamente as cores dos elementos de uma página da Web.
2.1.3 Chart.js
Chart.js é uma biblioteca JavaScript de código aberto que permite aos desenvolvedores criar gráficos e tabelas interativos e responsivos para aplicações web. Desenvolvido por Nick Downie, o Chart.js ganhou grande popularidade devido à sua facilidade de uso, flexibilidade e amplas opções de personalização para cada tipo de gráfico.
O Chart.js foi projetado para ser fácil de usar, mesmo para iniciantes, com uma API simples e intuitiva que facilita o início da criação de gráficos em JavaScript.
Os gráficos criados com Chart.js se adaptam automaticamente a diferentes tamanhos de tela e dispositivos, garantindo uma experiência de visualização consistente em todas as plataformas.

Desenvolvimento (Elemento obrigatório)
Divide-se em seções e subseções, conforme a NBR 6024, que variam em função da abordagem do tema e do método.
É a parte principal do artigo, e inclui a metodologia, fundamentação teórica, dados obtidos por meio de pesquisas, resultados alcançados e discussão. Divide-se em seções e subseções, conforme a NBR 6024, que variam em função da abordagem do tema e do método.

Considerações finais ou conclusão (Elemento obrigatório)
Parte final do artigo, na qual se apresentam as conclusões correspondentes aos objetivos e hipóteses, apresentados na introdução.
Aqui serão apresentadas as respostas às hipóteses e objetivos do TCC. As opiniões dos autores, devidamente embasadas pelos dados, conceitos e informações apresentados no desenvolvimento, devem ser inseridas aqui. Podem ser incluídas breves recomendações e sugestões para trabalhos futuros.

REFERÊNCIA
SOBRENOME, Prenome. Título: subtítulo (se houver). Edição. Local: Editora, data.
SOBRENOME, Prenome. Título: subtítulo (se houver). Edição. Local: Editora, data.

https://more.ufsc.br/homepage/inserir_homepage

 
Imagem 01 Referenciando artigos

Opcional – Exemplo

APÊNDICE A – Avaliação numérica de células inflamatórias totais aos quatro dias de evolução

Texto ou documento elaborado pelo autor, a fim de complementar sua argumentação, sem prejuízo da unidade nuclear do trabalho. O(s) apêndice(s) são identificados por letras maiúsculas consecutivas, travessão e pelos respectivos títulos.

Elementos pré-textuais	Obrigatório	Opcional
Título e subtítulo (se houver) 	X	
Nome(s) do(s) autor(es)	X	
Resumo na língua do texto	X	
Palavras-chave na língua do texto	X	
Elementos textuais
Introdução	X	
Desenvolvimento	X	
Conclusão	X	
Elementos pós-textuais
Título e subtítulo (se houver) em língua estrangeira	X	
Resumo em língua estrangeira	X	
Palavras-chave em língua estrangeira	X	
Nota(s) explicativa(s)		X
Referência	X	
Glossário		X
Apêndice(s)		X
Anexo(s)		X
Pag 43 manual cps

Resumo (último elemento pré-textual)
Abstract (último elemento pré-textual)
1. Introdução
2. Desenvolvimento
2.1 Justificativa
2.2 Objetivos Gerais
2.3 Objetivos Específicos
2.4 Problemas encontrados
2.4.1 Pontos Críticos (opcional)
2.5 Pontos Fortes (opcional)
2.6 Cronograma
2.7 Custos
2.7.1 Hardware
2.7.2 Software
2.7.3 Outros
2.8 Metodologia
2.8.1 Linguagens
2.8.2 Ferramentas
2.8.3 Banco de Dados
2.9 Segurança de Dados
2.10 Treinamento
2.11 Protótipo
3. Considerações Finais ou conclusão
4. Referências Bibliográficas
APÊNDICE (SE NECESSÁRIO)
ANEXOS (SE NECESSÁRIO)
>>>>>>> 43eab2bac9758f1d0d1fec1d2cec02d98898dc67
