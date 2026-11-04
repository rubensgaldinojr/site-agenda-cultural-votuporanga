# Ambiente de teste do tema (fora do WordPress)

Simula o mínimo do WordPress (`wpstubs.php`) para servir as páginas usando os
**templates reais do tema** e o **plugin real `racr-player-radio`**, e roda um teste
automatizado de navegador (Playwright). Serve para pegar erros de marcação, de CSS e
de navegação persistente antes de subir o tema.

**Não substitui o teste no site real**: aqui não há Kadence, ACF, LiteSpeed nem
conteúdo de verdade. Nada desta pasta vai para o WordPress.

```bash
cd radio-agenda-cultural-site/tests/harness
php -S localhost:8099 router.php &        # sobe o servidor de teste
NODE_PATH=$(npm root -g) node teste.js          # Fase 1: header, footer, tema, navegação persistente
NODE_PATH=$(npm root -g) node teste-fase2.js    # Fase 2: player fixo, cards, volume, dropdown
NODE_PATH=$(npm root -g) node teste-fase3a.js   # Fase 3A: página de Estações e Estação individual
NODE_PATH=$(npm root -g) node teste-fase3b1.js  # Fase 3B-1: página inicial (precisa do Playwright)
php teste-home.php                              # lógica da Home em PHP puro: mais ouvidas, descoberta, cache
```

As capturas de tela ficam em `capturas/` (ignorada pelo git).
