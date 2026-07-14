# Contribuindo com o GameVault

Obrigado por contribuir! Este documento explica como propor mudanças de
forma consistente com a arquitetura do projeto.

## Fluxo de contribuição

1. Crie uma branch a partir de `main`: `feature/nome-da-feature` ou `fix/nome-do-bug`.
2. Faça commits pequenos e descritivos (em português ou inglês, seja consistente).
3. Garanta que o projeto continua rodando após clonar (`README.md` → Instalação).
4. Abra um Pull Request explicando o que foi alterado e por quê.

## Regras de código

Veja o documento completo em [`PROJECT_RULES.md`](PROJECT_RULES.md). Resumo:

- Nomes de arquivos, classes, métodos e variáveis **sempre em inglês**.
- Textos de interface (labels, mensagens ao usuário) **em português**.
- Nunca misture HTML com SQL.
- Nunca misture JavaScript com PHP.
- Toda query SQL deve viver em `src/GameRepository.php` — nenhuma outra
  classe deve executar SQL diretamente.
- Toda chamada à API externa deve viver em `src/FreeToGameClient.php`.
- Endpoints em `api/*.php` devem ser finos: validar input, chamar
  `GameService`, responder JSON. Nada de regra de negócio ali.

## Como rodar localmente

Siga a seção "Instalação" do `README.md`.

## Reportando bugs

Abra uma issue descrevendo:

- O que você esperava que acontecesse.
- O que de fato aconteceu.
- Passos para reproduzir.
- Versão do PHP/MySQL utilizada.
