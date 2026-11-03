# Rádio Agenda Cultural — Tema claro / escuro

## Como funciona
- **Fonte única de cores:** `tema.css`. Todas as páginas e componentes usam só `var(--token)`; nenhuma cor de tema está escrita direto nos arquivos.
- **Troca de tema:** atributo `data-theme="light" | "dark"` no `<html>`.
- **Chave seletora:** componente `ThemeToggle.dc.html` (sol ☀ / lua ☾, com os rótulos discretos "Claro" e "Escuro").
  - Salva a escolha em `localStorage` (`rac-tema`).
  - Na primeira visita, segue a preferência do sistema (`prefers-color-scheme`).
  - Avisa as outras chaves da página pelo evento `rac-tema`, para que todas mudem juntas.
  - Acessível: `role="switch"`, `aria-checked` e `aria-label="Alternar entre modo claro e modo escuro"`.
- **Onde a chave aparece:**
  - Desktop: no cabeçalho, à direita do menu, depois de um divisor.
  - Mobile: chave compacta (só ícones) ao lado do menu ☰, e uma linha "Aparência do site" com rótulos dentro do menu.

## Tokens
| Token | Claro | Escuro | Uso |
|---|---|---|---|
| --bg | #fbf8f3 | #171411 | fundo da página |
| --surface | #ffffff | #211d19 | cards, header, footer |
| --surface2 | #f5efe6 | #2a251f | seções alternadas, hover, chips |
| --bar | #ffffff | #1d1a16 | player fixo |
| --line | #ebe4d9 | #3a332c | bordas |
| --text | #1f1b17 | #f3ece3 | títulos |
| --text-body | #4d463e | #cfc6ba | texto corrido |
| --muted | #7f766b | #9b9187 | rótulos, secundário |
| --accent | #ee8133 | #ee8133 | botões, play (identidade) |
| --accent-dark | #d9722a | #f29551 | hover do laranja |
| --accent-text | #b2561a | #f4a066 | links e texto laranja |
| --accent-soft | #fdeee2 | #3a2618 | fundo de item ativo |
| --live / --live-text | #e2483d / #c23c32 | #ff5a4e / #ff8a7f | selo "Ao vivo" |
| --ph1 / --ph2 | #efe8dd / #e8e0d3 | #2a251f / #241f1a | placeholder listrado |

## Boas práticas aplicadas no escuro
1. Nada de preto puro: a base é grafite quente, alinhada ao creme e ao laranja da marca.
2. Elevação por luminosidade: bg < bar < surface < surface2. Sombras quase não aparecem no escuro, então quem cria a separação é o tom do fundo.
3. Texto off-white, nunca #fff puro, para reduzir o ofuscamento.
4. Laranja de texto mais claro (#f4a066) para manter contraste de pelo menos 4,5:1. O laranja dos botões é o mesmo nos dois temas.
5. Hover clareia no escuro, em vez de escurecer.
6. Botões invertidos (fundo `--text`) usam texto `--bg`, então invertem sozinhos nos dois temas.
7. Gradientes que mesclam a foto com o fundo usam `color-mix(var(--bg) …)` e se adaptam ao tema.
8. Fotos reais ficam com brilho de 92% no escuro.
9. Os scrims escuros sobre fotos dos cards de estação são iguais nos dois temas, com texto branco.

## Pendências
- **Logo:** no escuro, um filtro gera uma versão monocromática clara. O ideal é ter um arquivo de logo próprio para fundo escuro e trocar pela regra `img[src*="cropped-Horizontal"]` do `tema.css`.
- **No WordPress:** carregar o `tema.css` antes de tudo e aplicar `data-theme` no `<html>` com um script inline no `<head>`, para evitar o "flash" de tema claro.
