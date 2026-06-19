#!/bin/bash
set -e

cd "$(dirname "$0")/.."

mkdir -p dolibarr/logs

if [ -f dolibarr/logs/facturation_syncstats.log ]; then

tail -n 3000 \
dolibarr/logs/facturation_syncstats.log \
> dolibarr/logs/facturation_syncstats.log.tmp

mv \
dolibarr/logs/facturation_syncstats.log.tmp \
dolibarr/logs/facturation_syncstats.log

fi

exec >> dolibarr/logs/facturation_syncstats.log 2>&1

echo ""
echo "===== $(date '+%Y-%m-%d %H:%M:%S') ====="

mois=$(date +%m)
annee=$(date +%Y)

if [ -n "${1:-}" ]; then
periode_debut="$1"
periode_fin="$2"

echo "Periode forcee : $periode_debut -> $periode_fin"

else


if [ "$mois" = "01" ]; then

annee=$((annee - 1))
periode_debut="${annee}-11-01"
periode_fin="${annee}-12-31"

elif [ "$mois" = "03" ] || 
[ "$mois" = "05" ] || 
[ "$mois" = "07" ] || 
[ "$mois" = "09" ] || 
[ "$mois" = "11" ]; then

periode_debut=$(date -d "$(date +%Y-%m-01) -2 month" +%Y-%m-01)
periode_fin=$(date -d "$(date +%Y-%m-01) -1 day" +%Y-%m-%d)

else

echo "Aucune facturation prevue ce mois."
exit 0

fi

fi

echo "Periode facturee : $periode_debut -> $periode_fin"

export WORK_ENV=production

php dolibarr/syncstats_dolibarr_billing.php \
--periode_debut="$periode_debut" \
--periode_fin="$periode_fin" \
--mode=brouillon \
--verbose=1

echo "Execution terminee."
echo ""
echo "===== RESUME ====="

echo "Date : $(date '+%Y-%m-%d %H:%M:%S')"

echo "Factures creees :"
grep -c "Facture creee:" dolibarr/logs/facturation_syncstats.log || true

echo "Doublons detectes :"
grep -c "Anti-doublon:" dolibarr/logs/facturation_syncstats.log || true

echo "Erreurs :"
grep -c "ERROR" dolibarr/logs/facturation_syncstats.log || true

echo "===== FIN ====="