# Arquitetura — GameVault

## Camadas

```
Navegador (public/)
      │  fetch()
      ▼
api/*.php            ← Controllers finos (HTTP In/Out, JSON)
      │
      ▼
src/GameService.php  ← Regras de negócio (orquestração)
      │            │
      ▼            ▼
src/FreeToGame     src/GameRepository
Client.php         .php
(fala com API      (fala com MySQL
 externa)           via PDO)
```

## Fluxo de sincronização

```
JS (app.js)
   │ POST
   ▼
api/sync.php
   │
   ▼
GameService::syncCatalog()
   │
   ▼
FreeToGameClient::fetchAllGames()  →  GET https://www.freetogame.com/api/games
   │
   ▼
GameRepository::upsert() (para cada jogo)  →  INSERT ... ON DUPLICATE KEY UPDATE
   │
   ▼
Resposta JSON  →  JS atualiza a interface
```

## Fluxo de listagem

```
JS (app.js)
   │ GET
   ▼
api/games.php
   │
   ▼
GameService::listGames()
   │
   ▼
GameRepository::findAll()  →  SELECT ... com filtros dinâmicos
   │
   ▼
JSON  →  HTML (cards)
```

## Por que essa separação?

- **api/** nunca contém SQL nem lógica de negócio — só recebe input HTTP, valida
  o essencial e delega para `src/`.
- **src/GameRepository** é a única classe autorizada a escrever SQL. Isso evita
  consultas espalhadas pelo projeto e facilita trocar de banco no futuro.
- **src/FreeToGameClient** isola tudo que é específico da API externa (cURL,
  parsing de JSON, tratamento de erros de rede). Se a API mudar de contrato,
  só esse arquivo muda.
- **src/GameService** é o único ponto que conhece tanto o client quanto o
  repositório — os controllers em `api/` nunca falam diretamente com eles.
- O **JavaScript nunca acessa a FreeToGame diretamente**: ele só conhece os
  endpoints internos (`api/games.php`, `api/game.php`, `api/sync.php`),
  conforme exigido no fluxo do projeto.
