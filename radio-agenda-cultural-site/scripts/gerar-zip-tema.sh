#!/usr/bin/env bash
# Gera o .zip do tema filho, pronto para subir no WordPress.
#
# O que faz, nesta ordem:
#   1. mostra a branch, o último commit e a versão do tema;
#   2. avisa se você parece estar na branch errada ou desatualizado;
#   3. apaga na pasta "themes" os zips antigos do tema (rac-kadence-child*.zip);
#   4. cria o zip novo, com a VERSÃO NO NOME: rac-kadence-child-<versão>.zip;
#   5. mostra a versão em destaque (e, no Mac, abre o Finder já no arquivo).
#
# Só apaga arquivos chamados rac-kadence-child*.zip; qualquer outro zip fica intacto.
#
# Uso (de qualquer pasta dentro do projeto):
#   ./radio-agenda-cultural-site/scripts/gerar-zip-tema.sh
set -euo pipefail

raiz="$(git rev-parse --show-toplevel)"
temas="$raiz/radio-agenda-cultural-site/themes"
tema="$temas/rac-kadence-child"

branch="$(git -C "$raiz" branch --show-current)"
commit="$(git -C "$raiz" log --oneline -1)"
versao="$(grep -i '^Version:' "$tema/style.css" | awk '{print $2}')"

echo "Pasta .........: $temas"
echo "Branch ........: $branch"
echo "Último commit .: $commit"
echo "Versão do tema : $versao"
echo

avisos=0

# alterações no tema que ainda não foram commitadas (entram no zip)
if [ -n "$(git -C "$raiz" status --porcelain -- radio-agenda-cultural-site/themes)" ]; then
  echo "ATENÇÃO: há alterações no tema que ainda não foram commitadas; elas entram no zip."
  avisos=1
fi

# branch local atrás do GitHub
git -C "$raiz" fetch origin --quiet 2>/dev/null || echo "Aviso: não consegui consultar o GitHub (sem internet?)."
if git -C "$raiz" rev-parse --abbrev-ref '@{u}' >/dev/null 2>&1; then
  atras="$(git -C "$raiz" rev-list --count 'HEAD..@{u}')"
  if [ "$atras" -gt 0 ]; then
    echo "ATENÇÃO: sua branch está $atras commit(s) atrás do GitHub. Rode 'git pull' e gere o zip de novo."
    avisos=1
  fi
fi

# na main, mas existe branch de trabalho à frente dela (causa mais comum de zip com versão antiga)
if [ "$branch" = "main" ]; then
  while read -r ref; do
    [ -z "$ref" ] && continue
    adiante="$(git -C "$raiz" rev-list --count "origin/main..$ref" 2>/dev/null || echo 0)"
    if [ "$adiante" -gt 0 ]; then
      echo "ATENÇÃO: você está na main, mas a branch ${ref#origin/} tem $adiante commit(s) a mais."
      echo "         Se o zip deveria ter a versão mais nova, rode: git checkout ${ref#origin/} && git pull"
      avisos=1
    fi
  done <<EOF2
$(git -C "$raiz" for-each-ref --format='%(refname:short)' refs/remotes/origin/claude/ 2>/dev/null)
EOF2
fi
[ "$avisos" -eq 1 ] && echo

# 3. apaga os zips antigos do tema
apagou=0
for antigo in "$temas"/rac-kadence-child*.zip; do
  if [ -e "$antigo" ]; then
    rm -f "$antigo"
    echo "Zip antigo removido: $(basename "$antigo")"
    apagou=1
  fi
done
[ "$apagou" -eq 0 ] && echo "Nenhum zip antigo do tema na pasta."

# 4. cria o zip novo, com a versão no nome
destino="$temas/rac-kadence-child-$versao.zip"
( cd "$temas" && zip -rq "$destino" rac-kadence-child -x "*.DS_Store" )

# 5. destaque final
echo
echo "=================================================="
echo "  ZIP PRONTO PARA UPLOAD"
echo "  VERSÃO DO TEMA: $versao"
echo "  Arquivo: $destino"
echo "  No WordPress, a tela de instalação deve mostrar: Enviado: $versao"
echo "=================================================="

# no Mac, mostra o arquivo no Finder
if [ "$(uname)" = "Darwin" ] && command -v open >/dev/null 2>&1; then
  open -R "$destino" 2>/dev/null || true
fi
