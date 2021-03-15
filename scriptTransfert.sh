echo "Le script de copie va démarrer"
lftp -e "open -u syncsta1,uVTEhAil ftp://ftp.syncstats.com
set ssl:verify-certificate false
set ftp:ssl-allow no
mirror -cR index.html /public_html/index.html
mirror -cR phpobjects /public_html/phpobjects
mirror -cR mobile /public_html/mobile
mirror -cR ligues /public_html/ligues
mirror -cR images /public_html/images
mirror -cR scripts /public_html/scripts
mirror -cR admin /public_html/admin
mirror -cR stats2 /public_html/stats2
mirror -cR scripts /public_html/scripts
mirror -cR style /public_html/style
mirror -cR scripts /public_html/scripts
mirror -cR -x syncscript/detectAppChange.php syncscript /public_html/syncscript
mirror -cR -x scriptsphp/defenvvar.php scriptsphp /public_html/scriptsphp
mirror -cR zadmin /public_html/zadmin
mirror -cR zstats /public_html/zstats
mirror -cR zuser /public_html/zuser
mirror -cR zdoc /public_html/zdoc
mirror -cR zarbitre /public_html/zarbitre"
echo "Script de copie terminé."
