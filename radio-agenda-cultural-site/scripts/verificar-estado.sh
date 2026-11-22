#!/usr/bin/env bash
# Mostra, em português claro, se o seu computador, a branch de trabalho e a main estão em dia.
# NÃO muda nada (só lê; o único comando de rede é "git fetch", que apenas baixa as novidades do GitHub).
#
# Responde:
#   1. Há alterações no seu computador que ainda não foram commitadas?
#   2. Sua cópia local está igual à do GitHub (nada para enviar nem para baixar)?
#   3. A branch de trabalho tem commits que AINDA NÃO estão na main (ou seja, falta fazer o merge)?
#   4. Que versão do tema cada lugar (seu computador, branch, main) vai gerar no zip?
#
# Uso (de qualquer pasta dentro do projeto):
#   ./radio-agenda-cultural-site/scripts/verificar-estado.sh
set -uo pipefail

RAMO="claude/amazing-davinci-glkobd"   # branch de trabalho combinada com o Claude
raiz="$(git rev-parse --show-toplevel)"
cd "$raiz" || exit 1
estilo="radio-agenda-cultural-site/themes/rac-kadence-child/style.css"

echo "Baixando as novidades do GitHub (git fetch)..."
if ! git fetch origin --quiet 2>/dev/null; then
  echo "ATENÇÃO: não consegui falar com o GitHub (sem internet ou sem permissão). O resultado abaixo pode estar desatualizado."
fi

versao_de() { git show "$1:$estilo" 2>/dev/null | grep -i '^Version:' | awk '{print $2}'; }

atual="$(git branch --show-current)"
v_local="$(grep -i '^Version:' "$estilo" 2>/dev/null | awk '{print $2}')"
v_main="$(versao_de origin/main)"
v_ramo="$(versao_de "origin/$RAMO")"
[ -z "$v_main" ] && v_main="?"; [ -z "$v_ramo" ] && v_ramo="?"; [ -z "$v_local" ] && v_local="?"

echo
echo "=============================================================="
echo " ESTADO DO PROJETO"
echo "=============================================================="
echo " Branch em que você está agora ..: ${atual:-"(nenhuma)"}"
echo " Último commit aqui .............: $(git log --oneline -1)"
echo

problemas=0

# 1) alterações não commitadas (ignora .DS_Store, que o Mac cria sozinho)
mudancas="$(git status --porcelain | grep -v '\.DS_Store' || true)"
if [ -n "$mudancas" ]; then
  problemas=1
  echo "[!] 1. Há alterações aqui que NÃO foram commitadas (entram no zip, mas não estão no GitHub):"
  echo "$mudancas" | head -15 | sed 's/^/        /'
  [ "$(echo "$mudancas" | wc -l)" -gt 15 ] && echo "        ... e mais"
  echo "        -> Me mande esta lista no chat para eu incorporar ao tema (ou descarte se foi sem querer)."
else
  echo "[ok] 1. Nenhuma alteração solta no seu computador."
fi
echo

# 2) local x GitHub (mesma branch)
if git rev-parse --verify --quiet '@{u}' >/dev/null; then
  contagem="$(git rev-list --left-right --count 'HEAD...@{u}')"
  a_enviar="$(echo "$contagem" | awk '{print $1}')"
  a_baixar="$(echo "$contagem" | awk '{print $2}')"
  if [ "$a_enviar" = "0" ] && [ "$a_baixar" = "0" ]; then
    echo "[ok] 2. Sua cópia está igual à do GitHub para esta branch."
  else
    problemas=1
    [ "$a_baixar" != "0" ] && echo "[!] 2. O GitHub tem $a_baixar commit(s) que você ainda não baixou -> rode: git pull"
    [ "$a_enviar" != "0" ] && echo "[!] 2. Você tem $a_enviar commit(s) que ainda não foram enviados ao GitHub -> me avise (eu envio, ou rode: git push)"
  fi
else
  echo "[!] 2. Esta branch não está ligada a uma do GitHub (nada para comparar)."
  problemas=1
fi
echo

# 3) branch de trabalho x main
if git rev-parse --verify --quiet "origin/$RAMO" >/dev/null; then
  faltam_na_main="$(git rev-list --count "origin/main..origin/$RAMO")"
  main_na_frente="$(git rev-list --count "origin/$RAMO..origin/main")"
  if [ "$faltam_na_main" = "0" ]; then
    echo "[ok] 3. A main já contém TUDO da branch de trabalho (merge feito)."
  else
    problemas=1
    echo "[!] 3. A branch de trabalho tem $faltam_na_main commit(s) que AINDA NÃO estão na main."
    echo "        -> O merge (Pull Request) ainda não foi feito. Faça o merge no navegador antes de usar a main."
  fi
  if [ "$main_na_frente" != "0" ]; then
    echo "     (A main tem $main_na_frente commit(s) que a branch ainda não tem — acontece depois de um merge. Peça ao Claude para reiniciar a branch a partir da main.)"
  fi
else
  echo "[?] 3. Não encontrei a branch $RAMO no GitHub."
fi
echo

# 4) versões
echo " VERSÃO DO TEMA que cada lugar vai gerar no zip:"
echo "   - o que está na sua pasta agora ..: $v_local"
echo "   - branch de trabalho (GitHub) ....: $v_ramo"
echo "   - main (GitHub) ..................: $v_main"
echo
echo "=============================================================="
if [ "$problemas" = "0" ] && [ "$v_main" = "$v_ramo" ]; then
  echo " TUDO EM DIA: main, branch e seu computador estão iguais. Pode gerar o zip."
  echo " Versão do zip: $v_local"
else
  echo " ATENÇÃO: há pendências (veja os itens marcados com [!] acima)."
  echo " Para gerar o zip da versão MAIS NOVA agora, use a branch:  git checkout $RAMO && git pull"
fi
echo "=============================================================="
