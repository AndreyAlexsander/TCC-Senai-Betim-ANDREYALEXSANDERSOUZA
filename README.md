# KAION

Sistema web para gerenciar produtos, acompanhar o funil de desenvolvimento e gerar relatorios com indicadores de status e potencial de receita.

## Funcionalidades

- Cadastro, login e logout de usuarios
- Recuperacao de acesso por orientacao ao administrador
- Cadastro, edicao e exclusao de produtos
- Funil visual com alteracao de status
- Campos de custo, potencial de receita, risco, previsao de lancamento e testes
- Registro simples de receita/testes do produto
- Dashboard com resumo geral
- Relatorios com filtros, grafico, exportacao PDF e exportacao Excel

## Tecnologias

- HTML5
- CSS3
- JavaScript
- PHP 8+
- MySQL

## Estrutura

```text
kaion-code/
|-- cadastro.html
|-- dashboard.html
|-- funil.html
|-- index.html
|-- novo_produto.html
|-- produtos.html
|-- recuperar_senha.html
|-- relatorios.html
|-- termos.html
|-- database.sql
|-- css/
|   |-- app.css
|   `-- termos.css
|-- js/
|   |-- app.js
|   |-- auth.js
|   |-- novo_produto.js
|   |-- script.js
|   |-- script_dashboard.js
|   |-- script_funil.js
|   |-- script_produtos.js
|   `-- script_relatorios.js
`-- php/
    |-- atualizar_produto.php
    |-- atualizar_status.php
    |-- cadastro.php
    |-- cadastro_produto.php
    |-- config.php
    |-- deletar_produto.php
    |-- exportar_excel.php
    |-- funil_produtos.php
    |-- instalar.php
    |-- login.php
    |-- logout.php
    |-- produtos.php
    |-- relatorios.php
    `-- verificar_login.php
```

## Banco de Dados

O sistema usa tres tabelas:

- `usuarios`: contas de acesso.
- `produtos`: dados principais, status, custos, potencial, risco e datas.
- `receitas`: versoes, ingredientes, preparo e observacoes de teste.


## Recuperacao de Senha

O projeto nao envia email de recuperacao. Se o usuario esquecer a senha, a tela `recuperar_senha.html` orienta procurar o administrador/professor responsavel para redefinir o acesso no banco de dados.

## Autores

Projeto academico do curso Tecnico em Informatica para Internet - SENAI Betim Maria Madalena Nogueira.

- Andrey Alexsander Souza Duarte
- Matheus Henrique Avelar Lacerda
- Nicolly Rafaela Teixeira de Souza
- Talyssa Miranda de Oliveira

## Licenca

Uso exclusivamente educacional.
