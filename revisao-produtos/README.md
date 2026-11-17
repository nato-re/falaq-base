# Atividade da aula de revisão – TPA – 3ª etapa

**Versão:** REV-ATIV · treino no formato da prova final (vale como exercício, não como nota da prova)

## Contexto

A **Loja de Produtos** é um marketplace: **vendedores** cadastram Produtos e **clientes** compram.

O site já está pronto, com layout, telas e rotas. Sua tarefa é implementar as features que faltam, descritas nos requisitos abaixo.

Crie o que faltar com o Artisan (`make:model`, `make:migration`, `make:middleware`, `make:request`). O seeder de usuários e o de Produtos já existem. Comece pelo R1.

## Como rodar

```bash
git clone https://github.com/nato-re/tpa-revisao-produtos.git
cd tpa-revisao-produtos
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan serve
```

Até você concluir o R1, o seeder avisa que os Produtos não foram semeados. Isso é esperado.

Usuários de teste (senha `senha123`):

| E-mail | Tipo |
|---|---|
| ana@loja.test | vendedor |
| bruno@loja.test | vendedor |
| carla@loja.test | cliente |
| diego@loja.test | cliente |

## Contratos (não altere)

As telas já usam estes nomes. Seu código precisa respeitá-los.

**Entidade:** model `Produto`, tabela `produtos`.

| Coluna | Tipo | Validação |
|---|---|---|
| nome | texto | obrigatório, texto até 255 caracteres |
| categoria | texto | obrigatório, um destes: `Eletrônicos`, `Vestuário`, `Casa`, `Alimentos` |
| garantia_meses | inteiro | **obrigatório, inteiro entre 0 e 36** |
| preco | decimal (8,2) | obrigatório, número maior que zero |
| estoque | inteiro | obrigatório, inteiro ≥ 0 |
| vendedor_id | chave estrangeira → `users` | definido pelo sistema (R6) |

**Rotas** (já existem em `routes/web.php`):

| Método e URL | Nome | Quem acessa |
|---|---|---|
| `GET /produtos` | `produtos.index` | qualquer pessoa |
| `GET /login` · `POST /login` · `POST /logout` | `login` · `login.attempt` · `logout` | qualquer pessoa |
| `GET /produtos/create` · `POST /produtos` | `produtos.create` · `produtos.store` | só vendedor logado |
| `GET /compras/create` · `POST /compras` · `GET /compras` | `compras.create` · `compras.store` · `compras.index` | só usuário logado |

**Venda:** model `Venda`, tabela `vendas` (`id`, `cliente_id` → `users`, timestamps). A relação N:M com Produtos se chama `produtos()` e usa a tabela pivô `produto_venda` (`venda_id`, `produto_id`, `quantidade`, `preco_unitario`, timestamps).

**Formulário de compra:** envia `itens[id_do_produto] = quantidade`. Itens com quantidade 0 ficam de fora.

**Middleware de perfil:** apelido `vendedor`.

## Requisitos

Cada requisito traz o que implementar e um roteiro para conferir no navegador, com o servidor rodando em http://localhost:8000. Os requisitos são independentes na correção, mas a ordem abaixo é a mais natural para implementar.

| # | Requisito | Pontos |
|---|---|---|
| R1 | Listagem pública de Produtos | 2,0 |
| R2 | Login e logout | 1,5 |
| R3 | Middleware `auth` | 1,0 |
| R4 | Middleware `vendedor` | 2,0 |
| R5 | Cadastro validado | 2,0 |
| R6 | Dono do produto | 1,0 |
| R7 | Compra | 2,5 |
| R8 | Minhas compras | 2,0 |
| Bônus | Estoque | 2,0 |

O bônus pode compensar requisitos não feitos, mas a nota máxima continua sendo 14,0.

### R1 – Listagem pública de Produtos (2,0)

**O que fazer:** crie a migration e a model `Produto` compatíveis com o `ProdutoSeeder` (tabela de contratos acima), com o relacionamento `vendedor()` (belongsTo `User`, chave `vendedor_id`). Implemente `ProdutoController@index`: busque os Produtos carregando o vendedor com `with()` e devolva a view `produtos.index` com a variável `$produtos`.

**Como testar no navegador:**

1. Rode `php artisan migrate:fresh --seed`. O aviso "não semeados" não pode mais aparecer.
2. **Sem fazer login**, abra http://localhost:8000/produtos.
3. A tabela deve mostrar os Produtos do seeder, e a coluna **Vendedor** deve trazer "Ana Vendedora" ou "Bruno Vendedor" em cada linha.

### R2 – Login e logout (1,5)

**O que fazer:** implemente `LoginController@attempt` com `Auth::attempt` (campos `email` e `password`). Se as credenciais forem inválidas, volte ao login com uma mensagem de erro. Implemente `LoginController@logout` para encerrar a sessão.

**Como testar no navegador:**

1. Abra http://localhost:8000/login e entre com `carla@loja.test` e uma senha errada. Você deve voltar ao login com uma mensagem de erro.
2. Entre com `carla@loja.test` e `senha123`. Você deve ser redirecionado, e o topo da página deve mostrar "Carla Cliente" com o tipo `cliente`.
3. Clique em **Sair**. O topo deve voltar a mostrar o botão **Entrar**.

### R3 – Middleware `auth` (1,0)

**O que fazer:** em `routes/web.php`, aplique o middleware `auth` às rotas de cadastro de Produtos e às três rotas de compras. A listagem continua pública.

**Como testar no navegador:**

1. Saia da conta (ou use uma janela anônima).
2. Clique em **Cadastrar Produto**, depois em **Comprar** e depois em **Minhas compras**. Nos três casos você deve cair na tela de login.
3. Clique em **Produtos**. A listagem deve abrir normalmente.

### R4 – Middleware `vendedor` (2,0)

**O que fazer:** crie um middleware que interrompa a requisição com `abort(403)` quando o usuário logado não for do tipo `vendedor`. Registre-o com o apelido `vendedor` em `bootstrap/app.php` e aplique-o às rotas de cadastro de Produtos.

**Como testar no navegador:**

1. Entre como `carla@loja.test` (cliente) e clique em **Cadastrar Produto**. Deve aparecer a página **403 – Acesso negado**.
2. Saia, entre como `ana@loja.test` (vendedor) e clique em **Cadastrar Produto**. O formulário deve aparecer.

### R5 – Cadastro validado (2,0)

**O que fazer:** crie o FormRequest `StoreProdutoRequest` com as regras da tabela de contratos e use-o em `ProdutoController@store`. Salve o registro com os dados validados e redirecione para a listagem.

**Como testar no navegador** (logado como `ana@loja.test`):

1. Abra **Cadastrar Produto** e envie o formulário vazio. Cada campo deve mostrar uma mensagem de erro.
2. Preencha o formulário com os dados válidos do passo 4, mas troque o valor abaixo. Ao enviar, o erro deve aparecer nesse campo:
   - **Garantia (meses)**: `48`
3. Preencha o campo **Preço (R$)** com `0`. Deve aparecer um erro nesse campo.
4. Envie os dados válidos abaixo. Você deve voltar à listagem, com o novo item na tabela:
   - **Nome**: `Liquidificador`
   - **Categoria**: `Casa`
   - **Garantia (meses)**: `12`
   - **Preço (R$)**: `149.90`
   - **Estoque**: `7`

### R6 – Dono do produto (1,0)

**O que fazer:** em `ProdutoController@store`, preencha `vendedor_id` com o id do usuário logado (`Auth::id()`). Esse valor nunca vem do formulário.

**Como testar no navegador:**

1. Logado como `ana@loja.test`, cadastre um item. Na listagem, a coluna **Vendedor** dele deve mostrar "Ana Vendedora".
2. Saia, entre como `bruno@loja.test` e cadastre outro item. A coluna **Vendedor** deve mostrar "Bruno Vendedor".

### R7 – Compra (2,5)

**O que fazer:** crie as migrations de `vendas` e da pivô `produto_venda` (colunas nos contratos) e a model `Venda`, com a relação `produtos()` (belongsToMany com `withPivot('quantidade', 'preco_unitario')`). Em `CompraController@store`:

1. ignore os itens com quantidade 0;
2. crie a venda com `cliente_id` igual ao id do usuário logado;
3. para cada item, chame `attach` com a `quantidade` e o `preco_unitario` (o preço atual do produto);
4. redirecione para `compras.index`.

**Como testar no navegador** (logado como `carla@loja.test`):

1. Abra **Comprar**, coloque quantidade **2** em um item e **1** em outro, e deixe os demais com 0.
2. Clique em **Finalizar compra**. Você deve ser redirecionado para **Minhas compras** com a mensagem "Compra realizada!".
3. Depois de fazer o R8, confira em **Minhas compras** que a compra tem exatamente os dois itens, com as quantidades 2 e 1.

### R8 – Minhas compras (2,0)

**O que fazer:** em `CompraController@index`, busque só as vendas do usuário logado, carregando os produtos com `with('produtos')`, e devolva a view `compras.index` com a variável `$vendas`. A view já calcula subtotais e total a partir da pivô.

**Como testar no navegador:**

1. Logado como `carla@loja.test`, faça uma compra (R7) e abra **Minhas compras**. Cada compra deve mostrar os itens, as quantidades, o preço unitário, os subtotais e o total.
2. Saia e entre como `diego@loja.test`. Em **Minhas compras**, as compras da Carla **não** podem aparecer.

### Bônus – Estoque (2,0)

**O que fazer:** em `CompraController@store`, recuse a compra (volte com uma mensagem de erro) se alguma quantidade passar do estoque. Ao concluir a compra, diminua o estoque de cada item com `decrement`.

**Como testar no navegador** (logado como `carla@loja.test`):

1. Em **Comprar**, coloque num item uma quantidade maior que a coluna **Em estoque** e finalize. Você deve voltar com uma mensagem de erro, e nenhuma compra pode ser criada.
2. Compre **1** unidade desse item e volte a **Comprar**. A coluna **Em estoque** dele deve ter diminuído em 1.

## Entrega

1. Confira se o projeto roda com `php artisan migrate:fresh --seed` e `php artisan serve`.
2. Compacte a pasta do projeto inteira em um `.zip`.
3. Envie o `.zip` na atividade do Google Classroom.
