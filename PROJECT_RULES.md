# Regras Internas do Projeto — GameVault

Documento de referência para qualquer desenvolvedor que for dar manutenção
ou evoluir este projeto.

## 1. Padrão de código

- PHP 8+, `declare(strict_types=1)` no topo de todo arquivo `.php`.
- Nomenclatura sempre em **inglês**: classes em `PascalCase`, métodos e
  variáveis em `camelCase`, tabelas e colunas de banco em `snake_case`.
- Textos exibidos ao usuário final (labels, botões, mensagens de erro
  amigáveis) ficam em **português**.
- Comentários apenas quando agregam contexto que o código por si não explica
  (por que, não o quê).

## 2. Organização de camadas

| Pasta      | Pode conter                                   | Não pode conter                     |
|------------|------------------------------------------------|--------------------------------------|
| `api/`     | Leitura de `$_GET`/`$_POST`, chamadas a `src/`, `echo json_encode(...)` | SQL, lógica de negócio, HTML |
| `src/`     | Classes de domínio (Client, Repository, Service) | `echo`, `$_GET`, HTML |
| `config/`  | Conexão PDO, leitura de `.env`, constantes     | Regras de negócio |
| `public/`  | HTML, CSS, JS                                  | PHP com lógica de negócio, credenciais |

## 3. Como criar um novo endpoint

1. Crie o arquivo em `api/nome-do-endpoint.php`.
2. Comece sempre com `require_once __DIR__ . '/bootstrap.php';`.
3. Valide o método HTTP (`$_SERVER['REQUEST_METHOD']`).
4. Valide e sanitize os parâmetros de entrada.
5. Chame um método de `GameService` (crie um novo método lá se necessário —
   nunca acesse `GameRepository` ou `FreeToGameClient` diretamente do endpoint).
6. Responda com `jsonResponse(...)` em caso de sucesso ou `jsonError(...)`
   em caso de falha.

## 4. Como criar uma nova funcionalidade de negócio

1. Se envolve **acesso a dados**, adicione um método em
   `src/GameRepository.php`, usando sempre *prepared statements*.
2. Se envolve **acesso à API externa**, adicione um método em
   `src/FreeToGameClient.php`.
3. Orquestre a nova regra em `src/GameService.php`, combinando os métodos
   acima. É este método que o `api/*.php` correspondente deve chamar.

## 5. Como criar uma nova tela/elemento de interface

1. Adicione o HTML estático necessário em `public/index.html`.
2. Adicione os estilos em `public/assets/css/style.css`, respeitando as
   seções já existentes e reutilizando as variáveis CSS (`:root`).
3. Se precisar buscar dados novos do backend, adicione um método em
   `public/assets/js/api.js`.
4. Adicione a renderização correspondente em `public/assets/js/ui.js`.
5. Conecte tudo (eventos, chamadas) em `public/assets/js/app.js`.

## 6. Como sincronizar dados / atualizar banco

- A sincronização sempre passa por `GameService::syncCatalog()`, que usa
  `GameRepository::upsert()` com `ON DUPLICATE KEY UPDATE` sobre
  `external_id UNIQUE` — isso é o que impede duplicação. **Nunca** crie um
  método de sincronização que use `INSERT` puro sem essa cláusula.
- Alterações estruturais na tabela `games` devem ser refletidas em
  `database/gamevault.sql` (mantenha o dump como fonte da verdade).

## 7. Boas práticas obrigatórias

- Toda query usa **prepared statements** (`PDO::prepare` + `execute`).
- Todo `try/catch` em `api/*.php` deve responder com `jsonError()` — nunca
  deixar um erro do PHP vazar cru para o navegador.
- Toda chamada de rede (cURL, fetch) tem tratamento de timeout e de falha.
- Evite duplicar lógica: se dois endpoints precisam da mesma regra, extraia
  para `GameService`.
