#!/usr/bin/env bash
# Gera o .zip do tema filho para instalar no WordPress e MOSTRA de onde ele veio
# (branch, último commit e versão), para evitar subir uma versão antiga sem perceber.
#
# Uso (dentro da pasta do projeto):
#   ./radio-agenda-cultural-site/scripts/gerar-zip-tema.sh            # salva em ~/Downloads
#   ./radio-agenda-cultural-site/scripts/gerar-zip-tema.sh ~/Desktop  # salva em outra pasta
set -euo pipefail

raiz="$(git rev-parse --show-toplevel)"
temas="$raiz/radio-agenda-cultural-site/themes"
tema="$temas/rac-kadence-child"

branch="$(git -C "$raiz" branch --show-current)"
commit="$(git -C "$raiz" log --oneline -1)"
versao="$(grep -i '^Version:' "$tema/style.css" | awk '{print $2}')"
destino_dir="${1:-$HOME/Downloads}"
destino="$destino_dir/rac-kadence-child.zip"

echo "Branch ........: $branch"
echo "Último commit .: $commit"
echo "Versão do tema : $versao"

# alerta se houver arquivos do tema alterados e não commitados
if [ -n "$(git -C "$raiz" status --porcelain -- radio-agenda-cultural-site/themes)" ]; then
  echo "ATENÇÃO: há alterações no tema que ainda não foram commitadas; elas entram no zip."
fi

# alerta se a branch local está atrás do GitHub
git -C "$raiz" fetch origin --quiet 2>/dev/null || true
if upstream="$(git -C "$raiz" rev-parse --abbrev-ref '@{u}' 2>/dev/null)"; then
  atras="$(git -C "$raiz" rev-list --count 'HEAD..@{u}')"
  if [ "$atras" -gt 0 ]; then
    echo "ATENÇÃO: sua branch está $atras commit(s) atrás de $upstream. Rode 'git pull' e gere o zip de novo."
  fi
fi

mkdir -p "$destino_dir"
rm -f "$destino"
( cd "$temas" && zip -rq "$destino" rac-kadence-child -x "*.DS_Store" )

echo
echo "Zip criado: $destino  (versão $versao)"
echo "No WordPress, a versão exibida ao instalar deve ser $versao."
