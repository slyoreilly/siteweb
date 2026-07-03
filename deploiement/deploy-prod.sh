#!/usr/bin/env bash

set -Eeuo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

mode_simulation="false"
if [[ "${1:-}" == "--dry-run" ]]; then
  mode_simulation="true"
fi

echo ""
echo "=== DEPLOIEMENT PROD ==="

COMMIT=$(git -C "${ROOT_DIR}" rev-parse --short HEAD)
BRANCH=$(git -C "${ROOT_DIR}" rev-parse --abbrev-ref HEAD)

echo "Commit : $COMMIT"
echo "Branche : $BRANCH"

if [[ "$mode_simulation" == "true" ]]; then
  echo "(mode simulation — aucun upload reel)"
fi

echo ""
read -p "Continuer vers PROD ? (oui/non) : " REP

[[ "$REP" == "oui" ]] || exit 0

echo ""
echo "1/4 Transfert"

if [[ "$mode_simulation" == "true" ]]; then
  "${SCRIPT_DIR}/scriptTransfert.sh" --dry-run
else
  "${SCRIPT_DIR}/scriptTransfert.sh"
fi

echo ""
echo "2/4 Smoke tests"

"${SCRIPT_DIR}/smoke-tests.sh"

echo ""
echo "3/4 Rapport"

echo ""
echo "Version :"
curl -s https://syncstats.com/.deploy-version.json

echo ""

echo "Verifier rapidement :"

echo "https://syncstats.com"
echo "https://syncstats.com/admin"
echo "https://syncstats.com/api"

echo ""
echo "4/4 Historique"

if [[ "$mode_simulation" == "false" ]]; then
  echo "$(date -u) $COMMIT SUCCESS" >> "${SCRIPT_DIR}/deploy-history.log"
fi

echo ""
echo "DEPLOIEMENT TERMINE"
