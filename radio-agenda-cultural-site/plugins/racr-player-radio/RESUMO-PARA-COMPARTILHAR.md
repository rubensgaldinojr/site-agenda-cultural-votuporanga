# Resumo — Plugin do Player de Rádio Persistente

## O que é
Plugin próprio do WordPress (`racr-player-radio`, v0.4) que adiciona um player de rádio
que **não para de tocar enquanto o visitante navega pelo site**. Toca as estações que já
existem cadastradas no site (CPT "Estação", com campos ACF), sem duplicar nenhum cadastro.

## Status: funcionalmente completo e validado
Testado em desktop (Chrome) e Android — sem bugs encontrados. Cobre os 6 requisitos
originais do projeto:

1. ✅ Lê as estações cadastradas (título, cor de destaque, URL do stream) — hoje são 14
   estações publicadas com stream configurado.
2. ✅ Seletor de estação — troca sem sair da página, sem dar reload.
3. ✅ Player persistente entre páginas — navegação interna do site passou a funcionar via
   AJAX (troca só o conteúdo da página, sem recarregar), então o áudio não é interrompido
   ao clicar em links do menu, nem ao usar voltar/avançar do navegador.
4. ✅ Metadado de "tocando agora" (música + artista) — todas as 14 estações fornecem esse
   dado em tempo real (via protocolo ICY dos streams do Zeno.fm), atualizado a cada ~20s.
5. ✅ Fácil de inserir — na real, nem precisa inserir nada: o player aparece sozinho em
   todo canto do site (canto inferior direito, recolhido, expande ao clicar), sem precisar
   de shortcode nem configuração no editor de páginas.
6. ⏳ Pageview automático no Google Analytics/GTM a cada navegação — **não implementado
   ainda porque o site (domínio de desenvolvimento atual) não tem Analytics/Tag Manager
   instalado**. É uma mudança pequena e isolada de implementar assim que houver uma conta
   de GA4/GTM configurada — só avisar quando estiver pronta.

## Decisões técnicas relevantes pra quem for planejar o site
- **Controles de mídia no celular funcionam** (nome da estação aparece na tela de bloqueio
  do Android, com play/pause/lock screen) — ainda não testado num iPhone real (só ~3% da
  audiência da rádio é iPhone, segundo as próprias estatísticas do Zeno.fm; ficou como
  pendência de baixa prioridade).
- **O visual do player é um placeholder funcional**, não o design final — está numa
  caixinha simples no canto da tela. O layout definitivo (ideia atual: barra fixa no
  rodapé, estilo Spotify Web) está **esperando um wireframe/mockup** que o Rubens vai
  desenhar com referências próprias.
- **O player depende da estrutura do tema Kadence** (especificamente do elemento
  `#primary` como "área de conteúdo que muda a cada página"). Se o tema ou o layout do
  site mudar significativamente mais pra frente, vale reconferir se esse ponto de
  integração continua válido.
- Código versionado com git (`racr-player-radio/` na pasta do projeto), com histórico de
  cada fase — não deve ter risco de perda de trabalho.

## O que ainda falta / depende de decisão de vocês
| Item | Depende de |
|---|---|
| Layout visual definitivo | Rubens trazer o mockup/wireframe |
| Pageview automático no GA/GTM | Instalar Google Analytics/Tag Manager no site (ainda não existe) |
| Teste em iPhone real | Baixa prioridade — só se quiserem fechar esse ponto |
| Revalidar tudo no domínio final | Quando o site sair do domínio de desenvolvimento atual pro domínio definitivo |

## Se for preciso mexer no código
O plugin está pronto pra manutenção incremental — CSS já separado num arquivo próprio
(documentado, dá pra sobrescrever visual direto em Aparência → Personalizar → CSS
Adicional do WordPress, sem editar o plugin). Qualquer ajuste futuro (visual, GA/GTM, etc.)
é uma mudança pequena e isolada, não uma reescrita.
