# 🎮 GameVault

Mini catálogo web de jogos gratuitos, consumindo a API pública [FreeToGame](https://www.freetogame.com/), com sincronização para banco de dados próprio, busca, filtros e interface no tema **Dark Gamer**.

Projeto desenvolvido em **PHP puro (orientado a objetos)**, sem frameworks, seguindo princípios de Clean Code e arquitetura em camadas.

---

## 📷 Visão geral

- Sincroniza o catálogo completo da FreeToGame para um banco MySQL local.
- Impede duplicação de registros via `INSERT ... ON DUPLICATE KEY UPDATE`.
- Permite buscar por nome e filtrar por gênero/plataforma.
- Exibe detalhes completos de cada jogo em um modal, com dados enriquecidos
  diretamente da API quando disponíveis.
- Interface responsiva (desktop, tablet e celular), com estados visuais para
  loading, erro, catálogo vazio e busca sem resultados.

---

## 🛠️ Tecnologias

| Camada     | Tecnologia                          |
|------------|--------------------------------------|
| Backend    | PHP 8+ (POO, PDO, Prepared Statements) |
| Frontend   | HTML5, CSS3 puro, JavaScript ES6 (Fetch API, async/await) |
| Banco      | MySQL / MariaDB                      |
| Ambiente   | Laragon (ou qualquer stack Apache/Nginx + PHP + MySQL) |

Nenhum framework (Laravel, Bootstrap, jQuery etc.) foi utilizado — apenas PHP, CSS e JS puros.

---

## 🏗️ Arquitetura

```
gamevault/
├── api/          → Endpoints HTTP (controllers finos, retornam JSON)
├── config/       → Configuração (.env loader, conexão PDO)
├── src/          → Regras de negócio (Client, Repository, Service)
├── database/     → Dump SQL (banco + tabela)
├── docs/         → Documentação técnica adicional
├── public/       → Document root: HTML, CSS, JS
├── README.md
├── .env.example
├── .gitignore
├── CONTRIBUTING.md
└── PROJECT_RULES.md
```

Veja a explicação detalhada de cada camada em [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).

---

## 📦 Instalação

### 1. Pré-requisitos

- PHP 8.0 ou superior, com extensões `pdo_mysql` e `curl` habilitadas.
- MySQL ou MariaDB.
- Laragon (recomendado no Windows) ou qualquer servidor local equivalente.

### 2. Clonar o repositório

```bash
git clone https://github.com/seu-usuario/gamevault.git
cd gamevault
```

Se estiver usando **Laragon**, coloque a pasta `gamevault` dentro de `laragon/www/`.

### 3. Configurar variáveis de ambiente

```bash
cp .env.example .env
```

Ajuste `.env` se seus dados de conexão forem diferentes do padrão do Laragon
(`root` sem senha em `127.0.0.1:3306`).

### 4. Criar o banco de dados

Importe o dump disponível em `database/gamevault.sql`:

```bash
mysql -u root -p < database/gamevault.sql
```

Ou, via **phpMyAdmin/HeidiSQL** (interface do Laragon): abra a aba *Import* e
selecione o arquivo `database/gamevault.sql`.

### 5. Apontar o document root para `public/`

No Laragon, a raiz do domínio virtual deve apontar para a pasta `public/`
(padrão em projetos com `Auto Virtual Hosts` ativado, criando algo como
`http://gamevault.test/`).

Se preferir rodar com o servidor embutido do PHP:

```bash
cd public
php -S localhost:8000
```

E acesse `http://localhost:8000`.

---

## ▶️ Como executar

1. Suba o Apache/MySQL pelo Laragon (ou o servidor embutido do PHP, passo acima).
2. Acesse a URL do projeto no navegador.
3. Na primeira execução, o catálogo estará vazio — isso é esperado.

## 🔄 Como sincronizar

Clique no botão **"Sincronizar Catálogo"** no topo da página. Isso dispara
uma requisição `POST` para `api/sync.php`, que:

1. Busca a lista completa de jogos em `GET https://www.freetogame.com/api/games`.
2. Faz `INSERT ... ON DUPLICATE KEY UPDATE` para cada jogo (nunca duplica).
3. Retorna o total sincronizado, e a interface é atualizada automaticamente.

Você pode repetir a sincronização quantas vezes quiser: registros existentes
são apenas atualizados.

---

## 🔌 Endpoints internos

| Método | Endpoint             | Descrição                                    |
|--------|-----------------------|-----------------------------------------------|
| GET    | `api/games.php`       | Lista jogos do banco (aceita `search`, `genre`, `platform`) |
| GET    | `api/game.php?id=`    | Detalhes completos de um jogo (id interno)    |
| POST   | `api/sync.php`        | Dispara a sincronização com a FreeToGame      |

Todos os endpoints retornam JSON e nunca expõem stack traces ou mensagens
brutas de erro do PHP/PDO em produção (`APP_ENV=production` no `.env`).

---

## 🧪 Como testar

Não há suíte automatizada neste MVP. Roteiro de teste manual sugerido:

1. Importar o banco vazio e abrir a aplicação → deve exibir o estado
   "catálogo vazio".
2. Clicar em "Sincronizar Catálogo" → deve popular o grid de jogos.
3. Buscar por um nome parcial (ex: "war") → deve filtrar em tempo real.
4. Aplicar filtro de gênero/plataforma → lista deve respeitar a combinação.
5. Buscar por algo inexistente (ex: "zzzzz") → deve exibir "nenhum jogo encontrado".
6. Clicar em "Detalhes" em um card → modal deve abrir com dados completos.
7. Desligar a internet e clicar em "Sincronizar" → deve exibir mensagem de
   API indisponível, sem quebrar a página.

---

## 🚀 Como publicar

1. Suba os arquivos para um servidor com PHP 8+, MySQL e `mod_rewrite`
   (opcional, não é exigido neste projeto).
2. Aponte o document root do domínio para a pasta `public/`.
3. Importe `database/gamevault.sql` no banco de produção.
4. Configure `.env` em produção com `APP_ENV=production` (esconde detalhes
   de erro) e as credenciais reais do banco.
5. Garanta que a extensão `curl` do PHP esteja habilitada para a
   sincronização funcionar.

---

## 🩹 Solução de problemas

### Erro 502 / "A API do FreeToGame está indisponível" ao sincronizar

No Windows (comum em instalações do Laragon/XAMPP), o PHP às vezes não vem
com um bundle de certificados CA configurado, e o cURL falha com
`SSL certificate problem: unable to get local issuer certificate`.

Para evitar depender de configuração manual do `php.ini`, o projeto já
inclui um bundle de certificados em `config/cacert.pem`, usado
automaticamente pelo `FreeToGameClient` via `CURLOPT_CAINFO`. Isso resolve
o problema na grande maioria dos ambientes sem nenhuma ação extra.

Se mesmo assim o erro persistir, verifique com `APP_ENV=local` no `.env`
(assim a resposta JSON de `api/sync.php` inclui um campo `"debug"` com a
mensagem real do cURL) e confirme que:

- a extensão `curl` está habilitada no `php.ini` (`extension=curl` sem `;`);
- seu firewall/antivírus não está bloqueando conexões HTTPS de saída do PHP;
- sua internet consegue alcançar `https://www.freetogame.com` normalmente.

## 🧠 Decisões técnicas

- **PDO com Prepared Statements** em 100% das queries, evitando SQL Injection.
- **`ON DUPLICATE KEY UPDATE`** sobre `external_id UNIQUE`, garantindo
  sincronizações idempotentes (rodar 10x não duplica nada).
- **Autoloader manual leve** (`spl_autoload_register`) em vez de Composer,
  para manter o projeto "PHP puro" conforme solicitado, sem abrir mão de
  namespaces e organização orientada a objetos.
- **Enriquecimento best-effort**: ao abrir o modal de detalhes, o backend
  tenta buscar `GET /game?id=` na FreeToGame para trazer descrição completa;
  se a API estiver fora do ar, os dados já salvos no banco continuam sendo
  exibidos normalmente (degradação suave, não falha total).
- **JS dividido em 3 módulos** (`api.js`, `ui.js`, `app.js`) para separar
  comunicação de rede, renderização de DOM e orquestração de eventos.

## 🔮 Possíveis melhorias

- Paginação server-side para catálogos muito grandes.
- Cache de resposta da FreeToGame (ex: Redis) para reduzir latência de sync.
- Testes automatizados (PHPUnit) para `GameService` e `GameRepository`.
- Autenticação de admin para restringir quem pode disparar sincronizações.
- Favoritar jogos (exigiria tabela de usuários).

---

## 📄 Licença

Projeto desenvolvido para fins de avaliação técnica. Uso livre para estudo.

## 🙏 Créditos da API

Dados fornecidos por [FreeToGame](https://www.freetogame.com/) — API pública
e gratuita de jogos free-to-play.
