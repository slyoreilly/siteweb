<?php
declare(strict_types=1);

// Validation de transport préparatoire seulement : aucune autorisation métier,
// connexion SQL ou écriture. Le destinataire reste bloqué indépendamment du résultat.
function publicationDecoderCommande(string $corps): array
{
    if (strlen($corps) > 1048576) throw new InvalidArgumentException('COMMANDE_TROP_VOLUMINEUSE');
    $commande = json_decode($corps, true, 64, JSON_THROW_ON_ERROR);
    $cle = is_array($commande) ? ($commande['cleIdempotence'] ?? null) : null;
    // Limite .NET String.Length : unités UTF-16, pas octets UTF-8.
    $longueur = is_string($cle) ? preg_match_all('/./us', $cle)
        + preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $cle) : 0;
    if (!is_array($commande) || !is_string($commande['cleIdempotence'] ?? null)
        || !preg_match('/(*UCP)\S/u', $cle) || $longueur > 128
        || !is_string($commande['empreinte'] ?? null)
        || !preg_match('/^[A-F0-9]{64}$/D', $commande['empreinte'])
        || !is_string($commande['planJson'] ?? null)) throw new InvalidArgumentException('COMMANDE_INVALIDE');
    // Hacher les octets exacts .NET AVANT parsing ; jamais de resérialisation PHP.
    if (!hash_equals($commande['empreinte'], strtoupper(hash('sha256', $commande['planJson'])))) {
        throw new InvalidArgumentException('EMPREINTE_INVALIDE');
    }
    $plan = json_decode($commande['planJson'], true, 64, JSON_THROW_ON_ERROR);
    if (!is_array($plan) || ($plan['Version'] ?? null) !== 'publication-equipe-v1') {
        throw new InvalidArgumentException('VERSION_INCOMPATIBLE');
    }
    return [$commande, $plan];
}
