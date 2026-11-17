# Resolução da revisão — Loja de Produtos (passo a passo para reproduzir na prova)

> Siga na ordem. Cada passo diz **o comando do Artisan**, **o arquivo**, **o código** e **por que** foi feito assim.
> Na prova os nomes (model, colunas, categorias, limites) mudam — troque só os nomes, a estrutura é a mesma.

---

## 0. Preparar o projeto

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan serve
```

**Antes de começar, leia no projeto da prova:**
- `README.md` → tabela de contratos (nome da model, tabela, colunas, regras de validação, rotas).
- `database/seeders/*Seeder.php` → mostra **exatamente** quais colunas a migration precisa ter.
- `resources/views/...` → mostra o **nome das variáveis** que a view espera (`$produtos`, `$vendas`) e o nome dos campos do form.

---

## Mapa geral (o que criar)

| Requisito | Comando | Arquivo |
|---|---|---|
| R1 | `php artisan make:model Produto` | `app/Models/Produto.php` |
| R1 | `php artisan make:migration create_produtos_table` | `database/migrations/..._create_produtos_table.php` |
| R1 | — | `ProdutoController@index` |
| R2 | — | `LoginController@attempt` e `@logout` |
| R3 | — | `routes/web.php` (middleware `auth`) |
| R4 | `php artisan make:middleware VendedorMiddleware` | `app/Http/Middleware/VendedorMiddleware.php` + `bootstrap/app.php` |
| R5 | `php artisan make:request StoreProdutoRequest` | `app/Http/Requests/StoreProdutoRequest.php` |
| R6 | — | `ProdutoController@store` (`Auth::id()`) |
| R7 | `php artisan make:model Venda` | `app/Models/Venda.php` |
| R7 | `php artisan make:migration create_vendas_table` | migration |
| R7 | `php artisan make:migration create_produto_venda_table` | migration (pivô) |
| R7/R8/Bônus | — | `CompraController@store` e `@index` |

> ⚠️ **Ordem das migrations importa**: `produtos` → `vendas` → `produto_venda`. A pivô tem FK para as duas, então tem que ser criada por último. O Artisan nomeia pela data/hora, então **crie na ordem certa** (ou renomeie o prefixo do arquivo).

---

## R1 — Listagem pública (2,0)

### 1.1 Migration
```bash
php artisan make:migration create_produtos_table
```
No `up()`:
```php
Schema::create('produtos', function (Blueprint $table) {
    $table->id();
    $table->string('nome');
    $table->string('categoria');
    $table->integer('garantia_meses');
    $table->decimal('preco', 8, 2);
    $table->integer('estoque');
    $table->foreignId('vendedor_id')->constrained('users');
    $table->timestamps();
});
```
**Por quê:**
- As colunas são **exatamente** as do seeder (`ProdutoSeeder` usa `nome, categoria, garantia_meses, preco, estoque, vendedor_id`). Se faltar uma, o seeder mostra o aviso "não semeados".
- `foreignId('vendedor_id')->constrained('users')`: cria a coluna e a chave estrangeira. Precisa passar `'users'` porque o Laravel adivinha a tabela pelo nome da coluna (`vendedor_id` → tabela `vendedors`, que não existe).
- Tabela de tipos: texto → `string`, inteiro → `integer`, decimal(8,2) → `decimal('x', 8, 2)`, data → `date`, booleano → `boolean`.

### 1.2 Model
```bash
php artisan make:model Produto
```
```php
class Produto extends Model
{
    protected $fillable = ['nome', 'categoria', 'garantia_meses', 'preco', 'estoque', 'vendedor_id'];

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }
}
```
**Por quê:**
- `$fillable` libera o `Produto::create($dados)` (mass assignment). Sem isso dá `MassAssignmentException`.
- `belongsTo(User::class, 'vendedor_id')`: "o produto **pertence a** um usuário". O 2º parâmetro é a FK — obrigatório porque não é `user_id`. **O nome do método (`vendedor`) é o que a view usa**: `$produto->vendedor->name`.

### 1.3 Controller
```php
use App\Models\Produto;

public function index()
{
    $produtos = Produto::with('vendedor')->get();
    return view('produtos.index', compact('produtos'));
}
```
**Por quê:** `with('vendedor')` = *eager loading*: busca todos os vendedores de uma vez (evita N+1 consultas). `compact('produtos')` = `['produtos' => $produtos]` — o nome **tem** que bater com a variável da view.

**Teste:** `php artisan migrate:fresh --seed` (sem aviso) → `/produtos` sem login mostra a tabela com vendedor.

---

## R2 — Login e logout (1,5)

`app/Http/Controllers/LoginController.php`:
```php
use Illuminate\Support\Facades\Auth;

public function attempt(Request $request)
{
    $credenciais = $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credenciais)) {
        $request->session()->regenerate();
        return redirect()->intended(route('produtos.index'));
    }

    return back()
        ->withErrors(['email' => 'E-mail ou senha inválidos.'])
        ->onlyInput('email');
}

public function logout(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
}
```
**Por quê:**
- `Auth::attempt(['email'=>..., 'password'=>...])` procura o usuário pelo email e compara a senha com o hash. Retorna `true/false`.
- `session()->regenerate()` troca o id da sessão depois do login (segurança).
- `redirect()->intended(...)` volta para a página que o usuário tentou abrir antes de ser mandado ao login (ex.: clicou em "Comprar").
- `back()->withErrors([...])` volta ao form e a view mostra `@error('email')`. `onlyInput('email')` mantém o e-mail preenchido (não a senha).
- No logout: desloga, invalida a sessão e gera novo token CSRF.
- **Não esqueça o `use Illuminate\Support\Facades\Auth;`** no topo.

---

## R3 — Middleware `auth` (1,0)

`routes/web.php`:
```php
// pública — fica FORA dos grupos
Route::get('/produtos', [ProdutoController::class, 'index'])->name('produtos.index');

Route::middleware(['auth', 'vendedor'])->group(function () {
    Route::get('/produtos/create', [ProdutoController::class, 'create'])->name('produtos.create');
    Route::post('/produtos', [ProdutoController::class, 'store'])->name('produtos.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/compras', [CompraController::class, 'index'])->name('compras.index');
    Route::get('/compras/create', [CompraController::class, 'create'])->name('compras.create');
    Route::post('/compras', [CompraController::class, 'store'])->name('compras.store');
});
```
**Por quê:**
- `auth` já vem pronto no Laravel. Se não estiver logado, redireciona para a rota chamada **`login`** (por isso a rota de login tem que ter `->name('login')`).
- Agrupar com `Route::middleware(...)->group(...)` aplica a todas as rotas de dentro. Alternativa equivalente: `->middleware('auth')` no final de cada rota.
- **Não mude os nomes das rotas** (`->name(...)`), as views dependem deles.

---

## R4 — Middleware `vendedor` (2,0)

### 4.1 Criar
```bash
php artisan make:middleware VendedorMiddleware
```
`app/Http/Middleware/VendedorMiddleware.php`:
```php
use Illuminate\Support\Facades\Auth;

public function handle(Request $request, Closure $next): Response
{
    if (Auth::user()->tipo !== 'vendedor') {
        abort(403);
    }
    return $next($request);
}
```

### 4.2 Registrar o apelido — `bootstrap/app.php`
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'vendedor' => \App\Http\Middleware\VendedorMiddleware::class,
    ]);
})
```

### 4.3 Aplicar nas rotas
Já feito no R3: `Route::middleware(['auth', 'vendedor'])`.

**Por quê:**
- Middleware é um "filtro" que roda **antes** do controller. `$next($request)` = deixa passar; `abort(403)` = barra (mostra `errors/403.blade.php`).
- **Ordem `['auth', 'vendedor']`**: o `auth` roda primeiro e garante que existe usuário. Se o `vendedor` rodasse antes com alguém deslogado, `Auth::user()` seria `null` → erro.
- O apelido (`alias`) é o que permite escrever `'vendedor'` nas rotas em vez do nome da classe inteira.
- `tipo` é a coluna da tabela `users` (`'vendedor'` ou `'cliente'`). Na prova pode ser outro nome/valor — veja a migration de users.

---

## R5 — Cadastro validado (2,0)

```bash
php artisan make:request StoreProdutoRequest
```
`app/Http/Requests/StoreProdutoRequest.php`:
```php
public function authorize(): bool
{
    return true;   // ⚠️ vem "false" por padrão → daria 403 sempre
}

public function rules(): array
{
    return [
        'nome'           => 'required|string|max:255',
        'categoria'      => 'required|in:Eletrônicos,Vestuário,Casa,Alimentos',
        'garantia_meses' => 'required|integer|between:0,36',
        'preco'          => 'required|numeric|gt:0',
        'estoque'        => 'required|integer|min:0',
    ];
}
```
No controller:
```php
use App\Http\Requests\StoreProdutoRequest;
use Illuminate\Support\Facades\Auth;

public function store(StoreProdutoRequest $request)
{
    $dados = $request->validated();
    $dados['vendedor_id'] = Auth::id();   // R6
    Produto::create($dados);
    return redirect()->route('produtos.index')->with('sucesso', 'Produto cadastrado!');
}
```
**Por quê:**
- Trocar `Request` por `StoreProdutoRequest` no parâmetro faz o Laravel validar **automaticamente** antes de executar o método. Se falhar, volta para o formulário com `$errors` e `old()`.
- `$request->validated()` devolve **só** os campos validados (seguro contra campos extras).

**Cola de regras de validação (traduza a tabela do README):**

| No README | Regra |
|---|---|
| obrigatório | `required` |
| texto até N caracteres | `string|max:N` |
| um destes: A, B, C | `in:A,B,C` |
| inteiro entre X e Y | `integer|between:X,Y` (ou `min:X|max:Y`) |
| inteiro ≥ 0 | `integer|min:0` |
| número maior que zero | `numeric|gt:0` |
| número ≥ X | `numeric|min:X` |
| data | `date` · data futura: `date|after:today` |
| e-mail | `email` |
| opcional | `nullable` |

---

## R6 — Dono do produto (1,0)

Já está no `store` acima: `$dados['vendedor_id'] = Auth::id();`

**Por quê:** o dono é **sempre** o usuário logado. Nunca coloque `vendedor_id` no form nem nas regras — o usuário poderia forjar o valor. `Auth::id()` é o id de quem está logado.

---

## R7 — Compra N:M (2,5)

### 7.1 Migrations (nesta ordem!)
```bash
php artisan make:migration create_vendas_table
php artisan make:migration create_produto_venda_table
```
```php
// vendas
Schema::create('vendas', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cliente_id')->constrained('users');
    $table->timestamps();
});

// produto_venda (pivô)
Schema::create('produto_venda', function (Blueprint $table) {
    $table->id();
    $table->foreignId('venda_id')->constrained('vendas');
    $table->foreignId('produto_id')->constrained('produtos');
    $table->integer('quantidade');
    $table->decimal('preco_unitario', 8, 2);
    $table->timestamps();
});
```
**Por quê:** relação **N:M** (uma venda tem vários produtos; um produto está em várias vendas) precisa de uma **tabela pivô**. Convenção do nome: as duas models no singular, **ordem alfabética**, com `_` → `produto_venda`. Colunas extras (`quantidade`, `preco_unitario`) ficam na pivô porque são dados *daquele produto naquela venda*.

### 7.2 Model Venda
```bash
php artisan make:model Venda
```
```php
class Venda extends Model
{
    protected $fillable = ['cliente_id'];

    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'produto_venda')
            ->withPivot('quantidade', 'preco_unitario')
            ->withTimestamps();
    }
}
```
**Por quê:**
- `belongsToMany` = relação N:M.
- `withPivot(...)` é obrigatório para conseguir ler `$produto->pivot->quantidade` na view (sem isso o `pivot` só traz os ids).
- `withTimestamps()` preenche `created_at/updated_at` da pivô no `attach` (se a pivô tem `timestamps()`).

### 7.3 `CompraController@store`
```php
use App\Models\Venda;
use Illuminate\Support\Facades\Auth;

public function store(Request $request)
{
    // 1. tira os itens com quantidade 0
    $itens = array_filter($request->input('itens', []), fn ($qtd) => $qtd > 0);

    if (empty($itens)) {
        return back()->withErrors(['itens' => 'Escolha ao menos um produto.']);
    }

    // BÔNUS: valida estoque ANTES de criar a venda
    foreach ($itens as $produtoId => $quantidade) {
        $produto = Produto::findOrFail($produtoId);
        if ($quantidade > $produto->estoque) {
            return back()
                ->withErrors(['itens' => "Estoque insuficiente para {$produto->nome}."])
                ->withInput();
        }
    }

    // 2. cria a venda do cliente logado
    $venda = Venda::create(['cliente_id' => Auth::id()]);

    // 3. attach de cada item na pivô
    foreach ($itens as $produtoId => $quantidade) {
        $produto = Produto::findOrFail($produtoId);

        $venda->produtos()->attach($produto->id, [
            'quantidade'     => $quantidade,
            'preco_unitario' => $produto->preco,
        ]);

        $produto->decrement('estoque', $quantidade);   // BÔNUS
    }

    // 4.
    return redirect()->route('compras.index')->with('sucesso', 'Compra realizada!');
}
```
**Por quê:**
- O form envia `itens[ID] = quantidade` → no PHP chega como array `[1 => 2, 2 => 0, 5 => 1]`. A **chave** é o id do produto e o **valor** é a quantidade; por isso o `foreach ($itens as $produtoId => $quantidade)`.
- `array_filter(..., fn ($qtd) => $qtd > 0)` mantém só os itens com quantidade > 0 (preserva as chaves).
- `attach($id, [colunas extras])` insere uma linha na pivô.
- `preco_unitario` = preço **atual** do produto, guardado na venda (se o preço mudar depois, a compra antiga continua certa).
- `with('sucesso', ...)` → o layout mostra `session('sucesso')`.

---

## R8 — Minhas compras (2,0)

```php
public function index()
{
    $vendas = Venda::with('produtos')
        ->where('cliente_id', Auth::id())
        ->get();

    return view('compras.index', compact('vendas'));
}
```
**Por quê:** o `where('cliente_id', Auth::id())` é o que impede o Diego de ver as compras da Carla. `with('produtos')` carrega os itens (e o `pivot`) de uma vez.

---

## Bônus — Estoque (2,0)

Já está no `store` do R7:
1. **Antes** de criar a venda, percorre os itens e, se `quantidade > estoque`, `return back()->withErrors(...)` → nenhuma venda é criada.
2. Depois do `attach`, `$produto->decrement('estoque', $quantidade)` faz `UPDATE ... SET estoque = estoque - N`.

**Por que validar antes:** se validasse dentro do mesmo loop do `attach`, metade da compra poderia ser gravada antes de achar o item sem estoque.

---

## Erros comuns (checklist rápido)

- [ ] Esqueceu `use App\Models\X;` / `use Illuminate\Support\Facades\Auth;` no topo → `Class not found`.
- [ ] `authorize()` do FormRequest ainda `return false` → 403 ao cadastrar.
- [ ] Faltou `$fillable` → `MassAssignmentException` (o seeder usa `forceCreate`, então só quebra no seu `create`).
- [ ] Faltou o 2º parâmetro em `belongsTo(User::class, 'vendedor_id')` → `$produto->vendedor` vem `null`.
- [ ] Pivô criada antes das tabelas que ela referencia → erro na migration. Renomeie a data do arquivo.
- [ ] Faltou `withPivot(...)` → quantidade/preço aparecem vazios em Minhas compras.
- [ ] Nome da variável no `compact()` diferente do usado na view.
- [ ] Middleware não registrado no `bootstrap/app.php` → `Target class [vendedor] does not exist`.
- [ ] Sempre rodar `php artisan migrate:fresh --seed` depois de mexer em migration.

## Comandos úteis
```bash
php artisan route:list           # confere rotas, nomes e middlewares
php artisan migrate:fresh --seed # recria o banco do zero
php artisan tinker               # testar consultas: App\Models\Produto::with('vendedor')->first()
```
