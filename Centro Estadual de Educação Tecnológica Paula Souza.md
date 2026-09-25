# Centro Estadual de Educação Tecnológica Paula Souza
## ETEC Dr. Nelson Alves Vianna
### Ensino Técnico Integrado ao Médio – Desenvolvimento de Sistemas

# AgroStock: sistema web para gerenciamento de almoxarifado e controle de validade

**Ailton Moreira de Souza**¹  
**Luis Gabriel Matos de Oliveira**²  
**Kamilly Portella**³  
**Alerrandro Miguel de Almeida Lima**⁴  
**Cassiano Gonçalves Duarte**⁵

¹ ² ³ ⁴ ⁵ MTEC-PI em Desenvolvimento de Sistemas, na ETEC Dr. Nelson Alves Vianna.

## Resumo

Este trabalho apresenta o desenvolvimento do AgroStock, um sistema web para gerenciamento de almoxarifado e controle de estoque da empresa Ianni. A solução foi criada para substituir o acompanhamento manual realizado por planilhas eletrônicas, processo que dificulta a conferência de quantidades, o rastreamento de entradas e saídas e o monitoramento do vencimento de produtos. O sistema organiza categorias, produtos e lotes em um banco de dados relacional e disponibiliza operações de cadastro, consulta, edição e desativação. Também registra movimentações de estoque, impede saídas superiores ao saldo disponível, identifica produtos com estoque abaixo do mínimo e apresenta alertas de validade. Um painel consolida indicadores, movimentações recentes e gráficos de apoio à decisão. A implementação utiliza interface em HTML, CSS, JavaScript e Bootstrap, backend em PHP com PDO e banco de dados MySQL ou MariaDB. A conferência técnica realizada no repositório confirmou a criação do banco, a ausência de erros de sintaxe nos arquivos PHP e JavaScript e o funcionamento dos principais endpoints em testes HTTP locais. Como resultado, o AgroStock oferece uma base centralizada para melhorar a rastreabilidade, reduzir perdas por vencimento e apoiar a rotina operacional do almoxarifado.

**Palavras-chave:** Controle de estoque. Gestão de almoxarifado. Sistema web. Banco de dados. Controle de validade.

## Abstract

This paper presents the development of AgroStock, a web system for warehouse and inventory management at Ianni. The solution was created to replace manual tracking based on spreadsheets, which makes it difficult to check quantities, trace inbound and outbound movements, and monitor product expiration dates. The system organizes categories, products, and batches in a relational database and provides registration, consultation, editing, and deactivation operations. It also records inventory movements, prevents withdrawals above the available balance, identifies products below the minimum stock level, and displays expiration alerts. A dashboard consolidates indicators, recent movements, and decision-support charts. The implementation uses HTML, CSS, JavaScript, and Bootstrap for the interface, PHP with PDO for the backend, and MySQL or MariaDB as the database. The technical review of the repository confirmed database creation, the absence of syntax errors in PHP and JavaScript files, and the operation of the main endpoints in local HTTP tests. As a result, AgroStock provides a centralized foundation to improve traceability, reduce losses caused by expiration, and support warehouse operations.

**Keywords:** Inventory control. Warehouse management. Web system. Relational database. Expiration control.

# 1. Introdução

A administração de um almoxarifado exige o acompanhamento contínuo das quantidades armazenadas, da localização dos itens, das entradas e saídas e das datas de validade. Quando essas informações são mantidas manualmente ou distribuídas em planilhas, a operação fica mais sujeita a divergências, atrasos na atualização e perda de rastreabilidade. O problema torna-se mais relevante quando um mesmo produto possui diferentes lotes e cada lote apresenta quantidade e validade próprias.

O AgroStock foi desenvolvido para a empresa Ianni como uma aplicação web de controle de almoxarifado. A proposta delimita-se ao cadastro e ao acompanhamento de categorias, produtos, lotes, estoque, movimentações, usuários, alertas e relatórios. O sistema não contempla vendas, financeiro, logística de transporte, código de barras ou inteligência artificial. Essa delimitação mantém o projeto adequado à rotina de um almoxarifado e ao escopo do Trabalho de Conclusão de Curso.

O objetivo geral é desenvolver um sistema centralizado que permita registrar e consultar o estoque com maior consistência. Para alcançar esse objetivo, o projeto organiza os dados em um banco relacional, separa a interface do backend, aplica autenticação por sessão e concentra no PHP as regras que protegem o saldo de estoque. O usuário acessa o sistema pelo navegador, enquanto a interface se comunica com endpoints PHP por meio de requisições HTTP e respostas JSON.

A documentação está organizada conforme o modelo fornecido pela ETEC. O desenvolvimento apresenta a justificativa, os objetivos, os problemas encontrados, os pontos fortes, o cronograma, os custos, a metodologia, as tecnologias, a segurança, o treinamento e o protótipo. Ao final, são apresentadas as considerações finais, as referências e os apêndices de verificação técnica.

# 2. Desenvolvimento

## 2.1 Justificativa

O controle de almoxarifado precisa responder a duas perguntas operacionais: quanto existe de cada item e qual lote deve ser observado com prioridade. Planilhas podem registrar essas informações, mas não garantem, por si só, que todas as pessoas trabalhem com a mesma versão dos dados ou que cada alteração seja vinculada ao usuário responsável. Também é difícil produzir alertas automáticos quando a quantidade fica igual ou inferior ao estoque mínimo ou quando um lote se aproxima da validade.

O AgroStock é justificável porque centraliza os registros em uma aplicação acessível pelo navegador e relaciona produtos, lotes e movimentações. A existência de uma tabela específica para movimentações permite consultar o histórico de entradas e saídas. A associação entre lote e produto permite controlar quantidades por lote, em vez de manter somente um saldo geral. Dessa forma, a solução atende ao problema central identificado no projeto: melhorar a organização e a confiabilidade das informações do almoxarifado.

A adoção de tecnologias web também reduz a dependência de instalação de um aplicativo em cada computador. Em uma implantação local, o sistema pode ser executado em um ambiente com Apache, PHP e MySQL ou MariaDB. O repositório recomenda ambientes como Laragon, XAMPP ou WAMP, que reúnem os componentes necessários para esse tipo de aplicação [1].

## 2.2 Objetivo geral

Desenvolver e documentar um sistema web de gerenciamento de almoxarifado capaz de controlar produtos e lotes, registrar movimentações de entrada e saída, gerar alertas de estoque e validade e apresentar informações consolidadas para apoiar a tomada de decisão.

## 2.3 Objetivos específicos

Os objetivos específicos do projeto são:

1. Organizar o cadastro de categorias, produtos, lotes e usuários.
2. Associar cada lote ao produto correspondente e armazenar a quantidade disponível.
3. Registrar entradas e saídas com data, usuário responsável, produto, lote, quantidade e observação.
4. Impedir que uma saída resulte em estoque negativo.
5. Identificar produtos com saldo igual ou inferior ao estoque mínimo.
6. Identificar lotes vencidos e lotes que vencerão dentro do período configurado.
7. Disponibilizar dashboard, histórico e relatórios para consulta operacional.
8. Aplicar autenticação, controle de sessão, hash de senha e consultas parametrizadas.
9. Validar o funcionamento dos principais fluxos por análise de código, inicialização do banco e testes HTTP.

## 2.4 Problemas encontrados

A análise do repositório e do contexto descrito no próprio projeto evidenciou problemas operacionais que justificam a aplicação. O primeiro é a dependência de planilhas para o controle do estoque. Esse processo pode gerar versões divergentes, digitação duplicada e dificuldade para identificar a origem de uma alteração.

O segundo problema é a ausência de uma visão integrada de validade. Um produto pode possuir vários lotes, cada um com uma data diferente. Sem uma consulta específica, o responsável pelo almoxarifado precisa conferir as datas manualmente. O AgroStock trata essa necessidade com alertas e relatório de validade.

O terceiro problema é a baixa rastreabilidade das movimentações. O sistema registra o usuário, o tipo de operação, a quantidade, a data e a observação. Esses campos permitem reconstruir a rotina de entradas e saídas com mais precisão do que uma atualização direta em uma planilha.

Durante a conferência técnica também foram encontrados pontos de manutenção no repositório. O arquivo `README.md` contém marcadores de conflito de merge, o que indica que duas versões de documentação foram mantidas no mesmo arquivo. O arquivo `tcc.md` descreve uma pasta `docs/` e arquivos de diagramas que não estão presentes na estrutura atual. Além disso, existem pontos que merecem revisão antes de uma implantação real, conforme detalhado na subseção seguinte.

## 2.4.1 Pontos críticos

O primeiro ponto crítico é a documentação de instalação. Os marcadores `<<<<<<< HEAD`, `=======` e `>>>>>>>` permanecem no `README.md`. Isso não impede diretamente a execução, mas prejudica a leitura e pode levar a instruções contraditórias. O arquivo deve ser consolidado em uma única versão antes da entrega final do TCC.

O segundo ponto crítico é a configuração de conexão. O arquivo `backend/config/conexao.php` utiliza usuário `root` sem senha por padrão. Essa configuração pode ser aceitável em um ambiente local de desenvolvimento, mas não deve ser usada em produção. A recomendação é retirar credenciais do código versionado e utilizar variáveis de ambiente ou um arquivo de configuração fora do repositório.

O terceiro ponto é o `init_db.php`. O script pode ser acessado pelo navegador e executa a criação do banco e das tabelas. Depois da instalação, esse arquivo deve ser removido, protegido ou desabilitado, pois uma rota pública de inicialização amplia a superfície de ataque e pode provocar reexecuções indesejadas.

O quarto ponto está no cadastro de lotes. O endpoint `backend/lotes/cadastrar.php` altera a quantidade em `produto_lote` quando o mesmo lote é cadastrado novamente, mas essa alteração não cria uma linha correspondente em `movimentacoes`. Se a operação representar uma entrada de estoque, o histórico pode ficar diferente do saldo atual. A recomendação é separar o cadastro do lote da entrada de estoque ou registrar a movimentação sempre que a quantidade for alterada.

O quinto ponto é de manutenção no relatório de estoque. O arquivo `backend/relatorios/estoque.php` declara uma consulta de resumo que usa agregações aninhadas em uma expressão `CASE`, reconhece no comentário que a consulta não funciona bem e calcula o resumo em PHP. Como a consulta problemática não é executada, o endpoint funciona nos testes, mas o trecho deve ser removido ou corrigido para evitar ambiguidade.

O sexto ponto é a ausência de uma suíte automatizada no repositório. A conferência realizada neste trabalho validou sintaxe, banco e endpoints por comandos locais, mas esses testes ainda não estão incorporados ao projeto como testes de regressão. A criação de testes para autenticação, CRUD, estoque negativo, validade e permissões aumentaria a segurança de futuras alterações.

## 2.5 Pontos fortes

A arquitetura do sistema é simples e adequada ao escopo. O frontend contém páginas HTML, folhas de estilo e scripts JavaScript. O backend está dividido por módulo e concentra as operações em endpoints PHP. O banco de dados possui scripts próprios de criação e carga de dados de teste. Essa separação facilita a localização das responsabilidades e evita que SQL seja colocado diretamente na interface.

A autenticação usa `password_hash()` para armazenar senhas e `password_verify()` para conferi-las. O login regenera o identificador da sessão e guarda o identificador, o nome e o nível de acesso do usuário. Os endpoints protegidos incluem `verificar_sessao.php`, que rejeita solicitações sem sessão válida. Os endpoints de usuários ainda exigem nível `admin`.

As consultas de alteração usam PDO e parâmetros nomeados. Essa prática reduz o risco de injeção de SQL e segue a orientação de utilizar consultas preparadas para dados recebidos do usuário [2]. As operações de entrada e saída utilizam transações. A saída também usa bloqueio de registro e verifica o saldo antes de diminuir a quantidade, preservando a regra de que o estoque não pode ficar negativo.

Outro ponto forte é a existência de regras de negócio explícitas. Produtos precisam de categoria, nome e unidade de medida. Lotes precisam de validade e quantidade positiva. As movimentações registram tipo, quantidade, usuário e observação. Os alertas consideram o estoque mínimo e um limite de trinta dias para validade, valor centralizado em `DIAS_ALERTA_VALIDADE`.

A interface utiliza Bootstrap 5, Bootstrap Icons e Chart.js. O Bootstrap contribui para formulários, tabelas, cards e responsividade. O Chart.js é usado no dashboard para gráficos de estoque por categoria e comparação de entradas e saídas [3] [4].

## 2.6 Cronograma

O repositório registra as fases de implementação, mas não apresenta datas acadêmicas fechadas. Por isso, o quadro abaixo funciona como cronograma proposto para organização do desenvolvimento e deve ser ajustado ao calendário da turma.

| Período sugerido | Atividade | Entrega esperada |
|---|---|---|
| Semana 1 | Diagnóstico do problema e levantamento de requisitos | Escopo delimitado e lista de funcionalidades |
| Semana 2 | Modelagem do banco de dados | Tabelas, chaves e relacionamentos definidos |
| Semana 3 | Configuração do ambiente e conexão PDO | Banco inicializado e conexão validada |
| Semana 4 | Autenticação e sessões | Login, logout e proteção de rotas |
| Semana 5 | Cadastros básicos | CRUD de categorias e produtos |
| Semana 6 | Controle de lotes | Cadastro, edição, associação e consulta de lotes |
| Semana 7 | Movimentações | Entrada, saída e bloqueio de saldo negativo |
| Semana 8 | Alertas e dashboard | Indicadores, gráficos e alertas de validade e estoque |
| Semana 9 | Relatórios e interface | Relatórios de estoque, movimentações e validade |
| Semana 10 | Testes, revisão e documentação | Correções, evidências e versão final do TCC |

## 2.7 Custos

### 2.7.1 Hardware

O sistema não exige hardware dedicado para desenvolvimento. Pode ser executado em um computador compatível com PHP 8 ou superior, um servidor web local e MySQL 8 ou MariaDB 10.5 ou superior, conforme os requisitos registrados no repositório [1]. Para o protótipo acadêmico, não foi identificado custo adicional obrigatório de aquisição de equipamento.

Em uma implantação maior, o custo de hardware dependerá da quantidade de usuários, do volume de movimentações, da necessidade de backup e da disponibilidade desejada. Esses valores não podem ser definidos a partir do repositório e devem ser levantados junto à empresa.

### 2.7.2 Software

O projeto utiliza PHP, MySQL ou MariaDB, Apache, HTML, CSS, JavaScript, Bootstrap, Bootstrap Icons, Chart.js e Git. O repositório indica que o sistema pode ser executado em ambientes locais como Laragon, XAMPP ou WAMP. Não há indicação de cobrança de licença para os componentes utilizados. Ainda assim, custos de hospedagem, domínio, suporte, backup ou administração do servidor podem existir em uma implantação fora do ambiente local.

### 2.7.3 Outros

Podem existir custos indiretos de treinamento, manutenção, atualização do servidor, armazenamento de cópias de segurança e conexão de rede. Como o protótipo foi planejado para execução local, esses custos não são necessários para a demonstração acadêmica. Uma estimativa financeira definitiva deve ser feita somente depois de definir a infraestrutura de produção.

## 2.8 Metodologia

O trabalho caracteriza-se como uma pesquisa aplicada de desenvolvimento tecnológico. A equipe delimitou um problema operacional, definiu um escopo de controle de almoxarifado e implementou um protótipo web com persistência em banco relacional. O desenvolvimento seguiu uma abordagem incremental: primeiro foram organizados o banco e a conexão; depois foram implementados autenticação, cadastros, lotes, movimentações, alertas, dashboard e relatórios.

A conferência técnica desta documentação utilizou quatro formas de verificação. A primeira foi a inspeção da estrutura de diretórios e dos arquivos fonte. A segunda foi a leitura do esquema SQL e a inicialização do banco em um ambiente MariaDB local. A terceira foi a validação de sintaxe, realizada com `php -l` para os arquivos PHP e `node --check` para os scripts JavaScript. A quarta foi um teste de fumaça HTTP, com login, acesso à sessão, consulta dos módulos, tentativa de saída acima do saldo e execução de entrada e saída compensatórias.

Essa metodologia permite afirmar que os fluxos principais foram observados em ambiente local, mas não substitui testes de aceitação com usuários da empresa, avaliação visual em diferentes dispositivos, teste de carga ou auditoria de segurança independente.

## 2.8.1 Linguagens

O frontend utiliza HTML5 para estruturar as páginas, CSS3 para a identidade visual e JavaScript para validação de formulários, requisições assíncronas e atualização dos componentes da interface. Os scripts usam `fetch()` para enviar dados aos endpoints e processar respostas JSON.

O backend utiliza PHP 8 ou superior. O PHP recebe requisições, valida os dados, controla a sessão, executa consultas por PDO e retorna objetos JSON. A documentação oficial descreve o PHP como uma linguagem voltada ao desenvolvimento web no lado do servidor [2].

O banco utiliza SQL compatível com MySQL e MariaDB. O schema emprega tabelas InnoDB, chaves primárias, chaves estrangeiras, índices, `ENUM`, `TIMESTAMP`, `DATE` e agregações para produzir os indicadores do dashboard e dos relatórios.

## 2.8.2 Ferramentas

As principais ferramentas e bibliotecas identificadas são:

| Ferramenta ou biblioteca | Uso no sistema |
|---|---|
| Apache | Servir as páginas e os endpoints PHP no ambiente local |
| PHP 8+ | Implementar o backend e as regras de negócio |
| PDO | Conectar ao banco e executar consultas parametrizadas |
| MySQL/MariaDB | Persistir usuários, categorias, produtos, lotes e movimentações |
| Bootstrap 5 | Construir componentes responsivos da interface |
| Bootstrap Icons | Exibir ícones nos menus e botões |
| Chart.js | Gerar gráficos do dashboard |
| Git e GitHub | Versionar e armazenar o código do projeto |
| `fetch()` | Comunicar frontend e backend por HTTP |

O sistema também possui scripts de inicialização, como `init_db.php`, e arquivos SQL separados para schema e dados de teste. Para uma versão de produção, recomenda-se substituir a inicialização aberta pelo uso de migrações ou por um procedimento administrativo protegido.

## 2.8.3 Banco de dados

O banco é denominado `almoxarifado` e possui seis tabelas principais. A tabela `usuarios` armazena nome, usuário, hash da senha, nível de acesso, situação e data de criação. A tabela `categorias` organiza os produtos. A tabela `produtos` armazena nome, descrição, unidade de medida, estoque mínimo, localização e situação.

A tabela `lotes` registra número, data de fabricação e data de validade. A tabela `produto_lote` relaciona produtos e lotes e guarda a quantidade disponível. Esse relacionamento evita que a validade seja perdida quando um produto possui mais de um lote. A tabela `movimentacoes` registra produto, lote, tipo, quantidade, data, usuário e observação.

Os relacionamentos principais são os seguintes:

```text
categorias 1 ─── N produtos
produtos   N ─── N lotes, por meio de produto_lote
usuarios   1 ─── N movimentacoes
produtos   1 ─── N movimentacoes
lotes      1 ─── N movimentacoes
```

As chaves estrangeiras utilizam `ON DELETE RESTRICT` nas tabelas de produtos e movimentações para preservar referências históricas. O relacionamento `produto_lote` utiliza `ON DELETE CASCADE`, permitindo remover o vínculo quando um produto ou lote ainda não possui histórico de movimentações. O schema também possui índices para categoria, situação, validade, produto, lote, data e tipo de movimentação.

## 2.9 Segurança de dados

A segurança é aplicada em diferentes camadas. No armazenamento de credenciais, as senhas são transformadas em hash com `password_hash()` e verificadas com `password_verify()`. O login rejeita credenciais inválidas, impede o acesso de usuários inativos e regenera o identificador da sessão após a autenticação.

Nas sessões, o sistema configura `httponly`, modo estrito e `SameSite=Lax`. Os endpoints protegidos incluem o arquivo de verificação de sessão. O sistema ainda atualiza a situação do usuário a partir do banco e encerra a sessão quando o usuário não existe ou está inativo.

Na persistência, os endpoints utilizam prepared statements do PDO. A validação ocorre no backend mesmo quando o frontend também valida os formulários. As entradas e saídas são protegidas por transações e a saída utiliza `FOR UPDATE` para conferir o saldo antes da alteração. O sistema bloqueia quantidades menores ou iguais a zero e rejeita uma retirada superior à quantidade disponível.

Há, contudo, medidas necessárias para uma implantação segura. As credenciais do banco devem sair do código, o acesso deve usar HTTPS, o servidor deve limitar o acesso ao `init_db.php`, e a aplicação deve adotar proteção contra falsificação de requisições entre sites (CSRF) em operações de alteração. Também é necessário definir uma política de backup e avaliar a necessidade de logs de auditoria e de controle de tentativas de login.

## 2.10 Treinamento

O treinamento pode ser dividido em dois perfis. O administrador cadastra e atualiza usuários, categorias, produtos e lotes. O operador consulta os itens e registra as operações de entrada e saída. Ambos os perfis consultam o dashboard, os alertas e os relatórios conforme as permissões disponíveis.

O procedimento básico de uso é:

1. Acessar a tela de login e informar usuário e senha.
2. Conferir os indicadores do dashboard.
3. Cadastrar ou revisar categorias e produtos.
4. Cadastrar o lote, informando número, produto, quantidade e validade.
5. Registrar entradas e saídas nas telas próprias de estoque.
6. Consultar alertas de estoque baixo e de validade.
7. Usar o histórico e os relatórios para conferência.
8. Encerrar a sessão ao terminar o uso.

O treinamento deve reforçar que o cadastro do lote e a entrada de estoque são conceitos diferentes. O lote identifica a origem e a validade do item. A movimentação documenta a entrada ou a saída. Essa distinção é importante para preservar o histórico operacional.

## 2.11 Protótipo

O protótipo implementado segue o fluxo abaixo:

```text
Navegador
   │
   │ HTML, CSS, JavaScript, Bootstrap
   ▼
Frontend
   │
   │ fetch() / HTTP / JSON
   ▼
Endpoints PHP
   │
   │ PDO / prepared statements
   ▼
Banco almoxarifado
   ├── usuarios
   ├── categorias
   ├── produtos
   ├── lotes
   ├── produto_lote
   └── movimentacoes
```

A tela de login envia usuário e senha para `backend/auth/login.php`. Depois do sucesso, o frontend armazena informações auxiliares em `sessionStorage` e direciona o usuário ao dashboard. A autenticação efetiva permanece na sessão PHP do servidor; o `sessionStorage` não substitui a verificação do backend.

O dashboard consulta `backend/dashboard.php` e apresenta o total de produtos ativos, a quantidade total em estoque, os produtos em estoque baixo, os lotes próximos da validade, os lotes vencidos, as movimentações recentes e os dados dos gráficos. O módulo de produtos permite listar, filtrar, cadastrar, editar e desativar. Categorias e lotes possuem operações equivalentes, com regras adicionais para integridade referencial.

O módulo de estoque separa entrada, saída e histórico. A entrada aumenta a quantidade do vínculo `produto_lote` e cria uma movimentação. A saída verifica o saldo, diminui a quantidade e cria uma movimentação. O histórico possui filtros de período, produto, lote e tipo e oferece paginação.

O catálogo de endpoints identificado é:

| Módulo | Endpoint | Método principal | Função |
|---|---|---:|---|
| Autenticação | `backend/auth/login.php` | POST | Autenticar usuário |
| Autenticação | `backend/auth/logout.php` | GET/POST | Encerrar sessão |
| Dashboard | `backend/dashboard.php` | GET | Consolidar indicadores e gráficos |
| Categorias | `backend/categorias/*.php` | GET/POST | CRUD de categorias |
| Produtos | `backend/produtos/*.php` | GET/POST | CRUD de produtos |
| Lotes | `backend/lotes/*.php` | GET/POST | CRUD e vínculo de lotes |
| Estoque | `backend/estoque/entrada.php` | POST | Registrar entrada |
| Estoque | `backend/estoque/saida.php` | POST | Registrar saída |
| Estoque | `backend/estoque/movimentacoes.php` | GET | Consultar histórico |
| Alertas | `backend/alertas/*.php` | GET | Consultar estoque baixo e validade |
| Relatórios | `backend/relatorios/*.php` | GET | Gerar estoque, movimentações e validade |
| Usuários | `backend/usuarios/*.php` | GET/POST | Administração de usuários |

# 3. Considerações finais ou conclusão

O AgroStock atende ao objetivo de criar uma base web centralizada para o gerenciamento de almoxarifado. A solução reúne produtos, categorias, lotes e movimentações em um banco relacional e transforma esses registros em consultas, alertas, indicadores e relatórios. A separação entre frontend, backend e banco facilita a manutenção e mantém as regras de estoque no servidor.

A análise e a validação local confirmaram os elementos principais do protótipo. O banco foi inicializado sem erros e permaneceu consistente na segunda execução. Os arquivos PHP passaram pela verificação de sintaxe. Os scripts JavaScript também foram verificados. Nos testes HTTP, o login inválido foi rejeitado, o login válido abriu a sessão, as consultas dos módulos retornaram sucesso, a saída acima do saldo retornou conflito e as operações compensatórias de entrada e saída foram concluídas.

O sistema ainda possui limitações que devem ser tratadas antes de uma implantação definitiva. A documentação do repositório precisa ser consolidada, o uso de credenciais padrão deve ser eliminado, o inicializador do banco deve ser protegido, o cadastro de lotes deve ser alinhado ao histórico de movimentações e o projeto deve receber uma suíte de testes automatizados. Também é recomendável realizar testes com usuários reais da empresa, avaliar a interface em dispositivos móveis, configurar backups e revisar a segurança para produção.

Como trabalho futuro, podem ser incorporados filtros mais avançados, exportação formal dos relatórios, histórico de alterações cadastrais, confirmação de operações críticas, notificações internas e integração com um mecanismo de backup. Essas melhorias devem preservar o escopo do almoxarifado e ser priorizadas conforme as necessidades da empresa.

# 4. Referências bibliográficas

A documentação técnica e a análise do sistema foram baseadas no repositório do projeto, no documento de arquitetura mantido pelos autores e na documentação oficial das tecnologias utilizadas.

[1] AILTONMDS. sistema-tcc-2026: Sistema de Gerenciamento de Almoxarifado – TCC 2026. GitHub. Disponível em: https://github.com/Ailtonmds/sistema-tcc-2026. Acesso em: 24 set. 2026.

[2] PHP GROUP. PHP Manual: Password Hashing, Sessions, PDO and Prepared Statements. PHP Documentation. Disponível em: https://www.php.net/manual/en/. Acesso em: 24 set. 2026.

[3] THE BOOTSTRAP AUTHORS. Bootstrap documentation. Disponível em: https://getbootstrap.com/docs/5.3/. Acesso em: 24 set. 2026.

[4] CHART.JS CONTRIBUTORS. Chart.js documentation. Disponível em: https://www.chartjs.org/docs/latest/. Acesso em: 24 set. 2026.

[5] MARIADB FOUNDATION. MariaDB Server documentation. Disponível em: https://mariadb.com/kb/en/documentation/. Acesso em: 24 set. 2026.

## Apêndice A – Verificação técnica do protótipo

A conferência foi realizada em ambiente local com PHP 8.3.6 e MariaDB 10.11.14. Os números abaixo registram a situação observada durante a análise do repositório; eles não representam um teste de produção.

| Verificação | Resultado observado |
|---|---|
| Arquivos PHP analisados | 31 arquivos em `backend/` e `frontend/`, além do `init_db.php` na raiz |
| Sintaxe PHP | Nenhum erro detectado pelo `php -l` |
| Sintaxe JavaScript | Nenhum erro detectado pelo `node --check` |
| Primeira inicialização do banco | 24 comandos executados e 0 erros |
| Segunda inicialização do banco | 15 comandos executados, 9 índices ignorados por já existirem e 0 erros |
| Contagem após a inicialização | 1 usuário, 4 categorias, 12 produtos, 5 lotes, 6 vínculos produto-lote e 12 movimentações |
| Integridade referencial | Nenhum registro órfão nas relações verificadas |
| Login inválido | HTTP 401 |
| Login válido | HTTP 200 e sessão criada |
| Dashboard, cadastros, alertas e relatórios | HTTP 200 nos endpoints testados |
| Saída superior ao saldo | HTTP 409 e resposta de falha, sem permitir saldo negativo |
| Entrada e saída compensatórias | HTTP 200 |
| Telas HTML previstas | HTTP 200 nas páginas testadas |

Os testes acima são testes de fumaça. Eles demonstram que os principais caminhos respondem no ambiente de validação, mas não substituem testes funcionais completos, testes de carga, testes de segurança e validação com os usuários finais.

## Apêndice B – Regras de negócio conferidas

| Regra | Implementação observada |
|---|---|
| Produto precisa de categoria, nome e unidade | Validação no endpoint de cadastro e edição |
| Estoque mínimo não pode ser negativo | Validação no backend e campo `UNSIGNED` no schema |
| Lote precisa de validade e quantidade positiva | Validação no cadastro e na edição |
| Entrada precisa ter quantidade maior que zero | Validação em `estoque/entrada.php` |
| Saída precisa ter saldo suficiente | Consulta com bloqueio e comparação antes do `UPDATE` |
| Estoque baixo | Soma dos vínculos menor ou igual ao estoque mínimo |
| Próximo do vencimento | Lotes com validade entre a data atual e trinta dias |
| Produto vencido | Lotes com validade anterior à data atual e quantidade positiva |
| Usuários administrativos | Endpoints de usuários exigem nível `admin` |
| Integridade histórica | Produtos e lotes com movimentações não são removidos de forma indiscriminada |

## Anexo – Estrutura resumida do repositório

```text
sistema-tcc-2026/
├── backend/
│   ├── alertas/
│   ├── auth/
│   ├── categorias/
│   ├── config/
│   ├── estoque/
│   ├── lotes/
│   ├── produtos/
│   ├── relatorios/
│   └── usuarios/
├── database/
│   ├── database.sql
│   └── dados_teste.sql
├── frontend/
│   ├── alertas/
│   ├── categorias/
│   ├── dashboard/
│   ├── estoque/
│   ├── lotes/
│   ├── login/
│   ├── produtos/
│   ├── relatorios/
│   ├── usuarios/
│   └── assets/
├── init_db.php
├── README.md
└── tcc.md
```

[1]: https://github.com/Ailtonmds/sistema-tcc-2026 "sistema-tcc-2026: Sistema de Gerenciamento de Almoxarifado – TCC 2026"
[2]: https://www.php.net/manual/en/ "PHP Manual: Password Hashing, Sessions, PDO and Prepared Statements"
[3]: https://getbootstrap.com/docs/5.3/ "Bootstrap documentation"
[4]: https://www.chartjs.org/docs/latest/ "Chart.js documentation"
[5]: https://mariadb.com/kb/en/documentation/ "MariaDB Server documentation"
