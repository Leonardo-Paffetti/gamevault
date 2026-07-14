
VIDEO TUTORIAL: https://youtu.be/vB5YN39vRlo


# 🎮 GameVault

GameVault é uma aplicação desenvolvida em **PHP**, **MySQL** e **JavaScript**, que consome a API pública **FreeToGame** para sincronizar e exibir um catálogo de jogos gratuitos.

O projeto foi desenvolvido como teste técnico, seguindo boas práticas de organização, separação de responsabilidades e integração com APIs externas.

---

## 🚀 Tecnologias

- PHP 8+
- MySQL / MariaDB
- HTML5
- CSS3
- JavaScript (Vanilla JS)
- FreeToGame API
- Laragon (ambiente recomendado)

---

# 📋 Requisitos

Antes de executar o projeto, certifique-se de possuir:

- PHP 8 ou superior
- MySQL ou MariaDB
- Apache
- Laragon (recomendado)
- phpMyAdmin

---

# 📥 Clonando o projeto

```bash
git clone https://github.com/Leonardo-Paffetti/gamevault.git
```

Entre na pasta do projeto:

```bash
cd gamevault
```

---

# ⚙️ Configuração

## 1. Copie o arquivo de ambiente

Renomeie o arquivo:

```
.env.example
```

para

```
.env
```

ou execute:

### Windows

```bash
copy .env.example .env
```

### Linux / macOS

```bash
cp .env.example .env
```

---

## 2. Configure o banco

Edite o arquivo `.env` com as configurações do seu ambiente.

Exemplo utilizado no Laragon:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=gamevault
DB_USER=root
DB_PASS=

API_BASE_URL=https://www.freetogame.com/api

APP_ENV=local
APP_TIMEZONE=America/Sao_Paulo
```

---

# 🗄️ Banco de Dados

Abra o **phpMyAdmin**.

Crie um banco chamado:

```
gamevault
```

Depois selecione esse banco e importe o arquivo:

```
database/gamevault.sql
```

Esse arquivo criará automaticamente:

- Banco de dados
- Tabela `games`
- Índices necessários

Após a importação, a tabela estará vazia.

Ela será preenchida automaticamente pela sincronização da API.

---

# ▶️ Executando

Inicie:

- Apache
- MySQL

No Laragon.

Depois acesse:

```
http://localhost/gamevault/public/
```

---

# 🔄 Sincronizando os jogos

Ao abrir a aplicação, clique em:

```
Sincronizar Catálogo
```

A aplicação irá:

- Consumir a API FreeToGame
- Buscar todos os jogos disponíveis
- Inserir os jogos no banco de dados
- Atualizar registros existentes automaticamente
- Evitar duplicações utilizando `ON DUPLICATE KEY UPDATE`

Após a sincronização, todos os jogos serão exibidos na aplicação.

---

# 📁 Estrutura do Projeto

```
gamevault/
│
├── api/
│
├── config/
│
├── database/
│   └── gamevault.sql
│
├── docs/
│
├── public/
│   ├── assets/
│   └── index.html
│
├── src/
│
├── .env.example
├── .gitignore
├── README.md
└── PROJECT_RULES.md
```

---

# ✅ Funcionalidades

- Consumo da API FreeToGame
- Sincronização completa do catálogo
- Atualização automática de registros
- Prevenção de registros duplicados
- Pesquisa de jogos
- Filtro por plataforma
- Filtro por gênero
- Interface responsiva
- Organização em camadas (Repository / Service)

---

# 🌐 API utilizada

FreeToGame API

https://www.freetogame.com/api

---

# 📝 Observações

- O arquivo `.env` não faz parte do repositório por conter configurações locais.
- Utilize o arquivo `.env.example` como base para criar seu ambiente.
- O dump do banco está disponível em `database/gamevault.sql`, conforme solicitado no teste técnico.

---

# 👨‍💻 Autor

**Leonardo Paffetti**


