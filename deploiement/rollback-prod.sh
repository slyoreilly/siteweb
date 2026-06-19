#!/usr/bin/env bash

set -Eeuo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

TAG="${1:?Usage: rollback-prod.sh <commit-ou-tag>}"

BRANCHE_ACTUELLE=$(git -C "${ROOT_DIR}" rev-parse --abbrev-ref HEAD)
COMMIT_ACTUEL=$(git -C "${ROOT_DIR}" rev-parse --short HEAD)

echo ""
echo "=== ROLLBACK PROD ==="
echo "Etat actuel : ${BRANCHE_ACTUELLE} (${COMMIT_ACTUEL})"
echo "Cible       : ${TAG}"
echo ""

read -p "Confirmer le rollback vers ${TAG} ? (oui/non) : " REP
[[ "$REP" == "oui" ]] || exit 0

echo ""
echo "1/3 Checkout ${TAG}"
git -C "${ROOT_DIR}" checkout "${TAG}"

echo ""
echo "2/3 Deploiement"
"${SCRIPT_DIR}/scriptTransfert.sh"

echo ""
echo "3/3 Retour a ${BRANCHE_ACTUELLE}"
git -C "${ROOT_DIR}" checkout "${BRANCHE_ACTUELLE}"

COMMIT_DEPLOYE=$(git -C "${ROOT_DIR}" rev-parse --short "${TAG}" 2>/dev/null || echo "${TAG}")
echo "$(date -u) ${COMMIT_DEPLOYE} ROLLBACK depuis ${COMMIT_ACTUEL}" >> "${SCRIPT_DIR}/deploy-history.log"

echo ""
echo "ROLLBACK TERMINE (deploye: ${TAG}, branche restauree: ${BRANCHE_ACTUELLE})"
