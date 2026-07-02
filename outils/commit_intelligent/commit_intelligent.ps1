# Contrat canonique d'encodage (ADR SYNC_ADR_ENCODAGE_001) : UTF-8 explicite, sans BOM.
# Rend la console et le process Python deterministes, hors locale/codepage OEM.
try {
    $utf8SansBom = New-Object System.Text.UTF8Encoding($false)
    [Console]::OutputEncoding = $utf8SansBom
    [Console]::InputEncoding = $utf8SansBom
} catch { }
$OutputEncoding = New-Object System.Text.UTF8Encoding($false)
$env:PYTHONUTF8 = "1"
$env:PYTHONIOENCODING = "utf-8"

$ErrorActionPreference = "Stop"

$dossierScript = $PSScriptRoot
$racineDepot = (Resolve-Path (Join-Path $dossierScript "..\..")).Path
$scriptPython = Join-Path $racineDepot "outils\commit_intelligent\commit_intelligent.py"

if (-not (Get-Command py -ErrorAction SilentlyContinue)) {
    Write-Error "Le lanceur Python 'py' est introuvable. Installez Python pour Windows ou ajoutez le lanceur 'py' au PATH."
    exit 127
}

if (-not (Test-Path -LiteralPath $scriptPython)) {
    Write-Error "Script Python introuvable: $scriptPython"
    exit 1
}

Set-Location -LiteralPath $racineDepot
& py $scriptPython
$codeRetour = $LASTEXITCODE

if ($null -eq $codeRetour) {
    $codeRetour = 0
}

exit $codeRetour
