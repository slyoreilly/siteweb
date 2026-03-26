#!/usr/bin/env bash

set -Eeuo pipefail

fichier_env=".env.transfert"
mode_simulation="false"

if [[ "${1:-}" == "--dry-run" ]]; then
  mode_simulation="true"
fi

if [[ -f "$fichier_env" ]]; then
  set -a
  # shellcheck disable=SC1090
  source "$fichier_env"
  set +a
fi

: "${DEPLOY_HOST:?DEPLOY_HOST manquant (ex: ftp.syncstats.com)}"
: "${DEPLOY_USER:?DEPLOY_USER manquant}"
: "${DEPLOY_PASSWORD:?DEPLOY_PASSWORD manquant}"

DEPLOY_PROTOCOL="${DEPLOY_PROTOCOL:-ftps}"
DEPLOY_PORT="${DEPLOY_PORT:-21}"
DEPLOY_REMOTE_DIR="${DEPLOY_REMOTE_DIR:-/public_html}"
DEPLOY_SSL_VERIFY="${DEPLOY_SSL_VERIFY:-true}"
DEPLOY_PARALLEL="${DEPLOY_PARALLEL:-2}"
DEPLOY_DELETE="${DEPLOY_DELETE:-true}"

echo "Deploiement demarre (hote=${DEPLOY_HOST}, protocole=${DEPLOY_PROTOCOL}, simulation=${mode_simulation})"

if [[ "$DEPLOY_PROTOCOL" != "ftps" && "$DEPLOY_PROTOCOL" != "ftp" ]]; then
  echo "DEPLOY_PROTOCOL invalide: ${DEPLOY_PROTOCOL} (valeurs permises: ftps, ftp)" >&2
  exit 1
fi

if [[ "$DEPLOY_PROTOCOL" == "ftp" ]]; then
  echo "Avertissement: FTP non chiffre actif (DEPLOY_PROTOCOL=ftp)." >&2
fi

if [[ "$DEPLOY_SSL_VERIFY" == "true" ]]; then
  reglage_certificat="true"
else
  reglage_certificat="false"
fi

option_simulation=""
if [[ "$mode_simulation" == "true" ]]; then
  option_simulation="--dry-run"
fi

option_suppression="--delete"
if [[ "$DEPLOY_DELETE" != "true" ]]; then
  option_suppression=""
fi

lftp <<EOF
set cmd:fail-exit true
set xfer:clobber true
set net:max-retries 2
set net:timeout 20
set net:reconnect-interval-base 5
set net:reconnect-interval-max 20
set ssl:verify-certificate ${reglage_certificat}

open -u "${DEPLOY_USER}","${DEPLOY_PASSWORD}" ${DEPLOY_PROTOCOL}://${DEPLOY_HOST}:${DEPLOY_PORT}

mirror -R -c --verbose=2 --parallel=${DEPLOY_PARALLEL} ${option_suppression} ${option_simulation} \
  --exclude-glob .git/ \
  --exclude-glob .git/** \
  --exclude-glob .vs/ \
  --exclude-glob .vscode/ \
  --exclude-glob .settings/ \
  --exclude-glob .sass-cache/ \
  --exclude-glob backUp/ \
  --exclude-glob backUp/** \
  --exclude-glob node_modules/ \
  --exclude-glob node_modules/** \
  --exclude-glob vendor/bin/ \
  --exclude-glob vendor/bin/** \
  --exclude-glob scriptsphp/defenvvar.php \
  --exclude-glob php.ini \
  --exclude-glob .htaccess \
  --exclude-glob .env \
  --exclude-glob .env.* \
  --exclude-glob "*.log" \
  --exclude-glob "**/error_log" \
  . ${DEPLOY_REMOTE_DIR}

bye
EOF

echo "Deploiement termine avec succes."
