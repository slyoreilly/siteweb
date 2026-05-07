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
DEPLOY_TLS_MODE="${DEPLOY_TLS_MODE:-explicit}"
DEPLOY_PARALLEL="${DEPLOY_PARALLEL:-2}"
DEPLOY_DELETE="${DEPLOY_DELETE:-false}"
DEPLOY_MODE="${DEPLOY_MODE:-controlled}"

echo "Deploiement demarre (hote=${DEPLOY_HOST}, protocole=${DEPLOY_PROTOCOL}, simulation=${mode_simulation})"

if [[ "$DEPLOY_PROTOCOL" != "ftps" && "$DEPLOY_PROTOCOL" != "ftp" ]]; then
  echo "DEPLOY_PROTOCOL invalide: ${DEPLOY_PROTOCOL} (valeurs permises: ftps, ftp)" >&2
  exit 1
fi

if [[ "$DEPLOY_TLS_MODE" != "explicit" && "$DEPLOY_TLS_MODE" != "implicit" && "$DEPLOY_TLS_MODE" != "off" ]]; then
  echo "DEPLOY_TLS_MODE invalide: ${DEPLOY_TLS_MODE} (valeurs permises: explicit, implicit, off)" >&2
  exit 1
fi

if [[ "$DEPLOY_SSL_VERIFY" == "true" ]]; then
  reglage_certificat="true"
else
  reglage_certificat="false"
fi

url_connexion="ftp://${DEPLOY_HOST}:${DEPLOY_PORT}"
reglage_ftp_ssl_allow="yes"
reglage_ftp_ssl_force="yes"

if [[ "$DEPLOY_TLS_MODE" == "implicit" ]]; then
  url_connexion="ftps://${DEPLOY_HOST}:${DEPLOY_PORT}"
  reglage_ftp_ssl_allow="yes"
  reglage_ftp_ssl_force="yes"
elif [[ "$DEPLOY_TLS_MODE" == "off" ]]; then
  url_connexion="ftp://${DEPLOY_HOST}:${DEPLOY_PORT}"
  reglage_ftp_ssl_allow="no"
  reglage_ftp_ssl_force="no"
  echo "Avertissement: TLS desactive (connexion FTP non chiffree)." >&2
fi

option_simulation=""
if [[ "$mode_simulation" == "true" ]]; then
  option_simulation="--dry-run"
fi

option_suppression="--delete"
if [[ "$DEPLOY_DELETE" != "true" ]]; then
  option_suppression=""
fi

if [[ "$DEPLOY_MODE" != "controlled" && "$DEPLOY_MODE" != "full" ]]; then
  echo "DEPLOY_MODE invalide: ${DEPLOY_MODE} (valeurs permises: controlled, full)" >&2
  exit 1
fi

if [[ "$DEPLOY_MODE" == "full" ]]; then
  lftp <<EOF
set cmd:fail-exit true
set xfer:clobber true
set net:max-retries 2
set net:timeout 20
set net:reconnect-interval-base 5
set net:reconnect-interval-max 20
set ssl:verify-certificate ${reglage_certificat}
set ftp:ssl-allow ${reglage_ftp_ssl_allow}
set ftp:ssl-force ${reglage_ftp_ssl_force}

open -u "${DEPLOY_USER}","${DEPLOY_PASSWORD}" ${url_connexion}

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
else
  lftp <<EOF
set cmd:fail-exit true
set xfer:clobber true
set net:max-retries 2
set net:timeout 20
set net:reconnect-interval-base 5
set net:reconnect-interval-max 20
set ssl:verify-certificate ${reglage_certificat}
set ftp:ssl-allow ${reglage_ftp_ssl_allow}
set ftp:ssl-force ${reglage_ftp_ssl_force}

open -u "${DEPLOY_USER}","${DEPLOY_PASSWORD}" ${url_connexion}

mirror -R -c --verbose=2 --parallel=${DEPLOY_PARALLEL} ${option_suppression} ${option_simulation} --include-glob index.html --exclude-glob "*" . ${DEPLOY_REMOTE_DIR}
mirror -R -c --verbose=2 --parallel=${DEPLOY_PARALLEL} ${option_suppression} ${option_simulation} phpobjects ${DEPLOY_REMOTE_DIR}/phpobjects
mirror -R -c --verbose=2 --parallel=${DEPLOY_PARALLEL} ${option_suppression} ${option_simulation} mobile ${DEPLOY_REMOTE_DIR}/mobile
mirror -R -c --verbose=2 --parallel=${DEPLOY_PARALLEL} ${option_suppression} ${option_simulation} ligues ${DEPLOY_REMOTE_DIR}/ligues
mirror -R -c --verbose=2 --parallel=${DEPLOY_PARALLEL} ${option_suppression} ${option_simulation} images ${DEPLOY_REMOTE_DIR}/images
mirror -R -c --verbose=2 --parallel=${DEPLOY_PARALLEL} ${option_suppression} ${option_simulation} admin ${DEPLOY_REMOTE_DIR}/admin
mirror -R -c --verbose=2 --parallel=${DEPLOY_PARALLEL} ${option_suppression} ${option_simulation} stats2 ${DEPLOY_REMOTE_DIR}/stats2
mirror -R -c --verbose=2 --parallel=${DEPLOY_PARALLEL} ${option_suppression} ${option_simulation} scripts ${DEPLOY_REMOTE_DIR}/scripts
mirror -R -c --verbose=2 --parallel=${DEPLOY_PARALLEL} ${option_suppression} ${option_simulation} style ${DEPLOY_REMOTE_DIR}/style
mirror -R -c --verbose=2 --parallel=${DEPLOY_PARALLEL} ${option_suppression} ${option_simulation} --exclude-glob detectAppChange.php syncscript ${DEPLOY_REMOTE_DIR}/syncscript
mirror -R -c --verbose=2 --parallel=${DEPLOY_PARALLEL} ${option_suppression} ${option_simulation} --exclude-glob defenvvar.php scriptsphp ${DEPLOY_REMOTE_DIR}/scriptsphp
mirror -R -c --verbose=2 --parallel=${DEPLOY_PARALLEL} ${option_suppression} ${option_simulation} zadmin ${DEPLOY_REMOTE_DIR}/zadmin
mirror -R -c --verbose=2 --parallel=${DEPLOY_PARALLEL} ${option_suppression} ${option_simulation} zstats ${DEPLOY_REMOTE_DIR}/zstats
mirror -R -c --verbose=2 --parallel=${DEPLOY_PARALLEL} ${option_suppression} ${option_simulation} zuser ${DEPLOY_REMOTE_DIR}/zuser
mirror -R -c --verbose=2 --parallel=${DEPLOY_PARALLEL} ${option_suppression} ${option_simulation} zdoc ${DEPLOY_REMOTE_DIR}/zdoc
mirror -R -c --verbose=2 --parallel=${DEPLOY_PARALLEL} ${option_suppression} ${option_simulation} zarbitre ${DEPLOY_REMOTE_DIR}/zarbitre
mirror -R -c --verbose=2 --parallel=${DEPLOY_PARALLEL} ${option_suppression} ${option_simulation} api ${DEPLOY_REMOTE_DIR}/api

bye
EOF
fi

echo "Deploiement termine avec succes."
