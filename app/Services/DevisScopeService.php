<?php

namespace App\Services;

/**
 * Port PHP de BATITRAVOO-flutter/lib/core/models/devis_scope.dart
 *
 * @phpstan-type DevisRow array<string, mixed>
 */
class DevisScopeService
{
    public const DIRECTION_RECEIVED = 'received';

    public const DIRECTION_SENT = 'sent';

    public const KIND_CATALOG = 'catalog';

    public const KIND_MARKETPLACE = 'marketplace';

    public const KIND_ALL = 'all';

    /**
     * @param  DevisRow  $row
     */
    public function isSelfDealing(array $row): bool
    {
        $clientId = (int) ($row['client_user_id'] ?? 0);
        $userId = (int) ($row['user_id'] ?? 0);

        return $clientId > 0 && $clientId === $userId;
    }

    /**
     * @param  DevisRow  $row
     */
    public function isArtisanBesoinOutboundProposal(array $row): bool
    {
        $ref = trim((string) ($row['order_reference'] ?? ''));
        if (str_starts_with($ref, 'BESOIN-')) {
            return true;
        }
        $li = $row['line_items'] ?? null;
        if (is_array($li) && isset($li['besoin_id'])) {
            return true;
        }

        return false;
    }

    /**
     * @param  DevisRow  $row
     */
    public function isSentMarketplaceRequest(array $row, int $currentUserId): bool
    {
        return (int) ($row['client_user_id'] ?? 0) === $currentUserId
            && (int) ($row['user_id'] ?? 0) !== $currentUserId;
    }

    /**
     * @param  DevisRow  $row
     */
    public function isIncomingRequestForPrestataire(array $row, int $currentUserId): bool
    {
        if ((int) ($row['user_id'] ?? 0) !== $currentUserId) {
            return false;
        }
        if (empty($row['client_user_id'])) {
            return false;
        }
        if ($this->isSelfDealing($row)) {
            return false;
        }
        if ($this->isArtisanBesoinOutboundProposal($row)) {
            return false;
        }

        return true;
    }

    /**
     * @param  DevisRow  $row
     */
    public function isInternalDraft(array $row, int $currentUserId): bool
    {
        return (int) ($row['user_id'] ?? 0) === $currentUserId
            && empty($row['client_user_id']);
    }

    /**
     * @param  DevisRow  $row
     */
    public function isCatalogCartOrder(array $row): bool
    {
        $ref = trim((string) ($row['order_reference'] ?? ''));
        if (str_starts_with($ref, 'PANIER-') || str_starts_with($ref, 'MOB-FOUR-')) {
            return true;
        }
        $title = mb_strtolower((string) ($row['title'] ?? ''));
        if (str_contains($title, 'commande catalogue')) {
            return true;
        }
        $li = $row['line_items'] ?? null;
        if (! is_array($li)) {
            return false;
        }
        if (isset($li['order_lignes']) && is_array($li['order_lignes'])) {
            return true;
        }
        $src = mb_strtolower((string) ($li['source'] ?? ''));
        if (str_contains($src, 'marketplace_cart') || str_contains($src, '_cart')) {
            return true;
        }

        return false;
    }

    /**
     * @param  DevisRow  $row
     */
    public function isSupplierReceivedOrder(array $row, int $currentUserId): bool
    {
        return $this->isIncomingRequestForPrestataire($row, $currentUserId)
            && $this->isCatalogCartOrder($row);
    }

    public function hasProviderResponse(array $row): bool
    {
        $status = (string) ($row['status'] ?? '');

        return in_array($status, ['envoye', 'valide', 'en_cours'], true);
    }

    /**
     * @param  DevisRow  $row
     */
    public function matchesReceivedCategory(array $row, int $currentUserId): bool
    {
        if ($this->isSelfDealing($row)) {
            return false;
        }
        if ($this->isSupplierReceivedOrder($row, $currentUserId)) {
            return false;
        }

        return $this->isIncomingRequestForPrestataire($row, $currentUserId)
            || $this->isInternalDraft($row, $currentUserId);
    }

    /**
     * @param  DevisRow  $row
     */
    public function matchesSentCategory(array $row, int $currentUserId): bool
    {
        if ($this->isSelfDealing($row)) {
            return false;
        }
        if ($this->isSentMarketplaceRequest($row, $currentUserId)) {
            return true;
        }

        return (int) ($row['user_id'] ?? 0) === $currentUserId
            && ! empty($row['client_user_id'])
            && $this->isArtisanBesoinOutboundProposal($row);
    }

    /**
     * @param  DevisRow  $row
     */
    public function getSubjectKind(array $row): string
    {
        if (empty($row['client_user_id'])) {
            return 'brouillon_interne';
        }
        if ($this->isArtisanBesoinOutboundProposal($row)) {
            return 'besoin_opportunite';
        }

        $li = $row['line_items'] ?? null;
        if (is_array($li)) {
            if (isset($li['order_lignes']) && is_array($li['order_lignes'])) {
                return 'commande_produits';
            }
            $src = mb_strtolower((string) ($li['source'] ?? ''));
            if (str_contains($src, 'marketplace_request')) {
                return 'demande_devis';
            }
            if (str_contains($src, 'marketplace_cart') || str_contains($src, '_cart')) {
                return 'commande_produits';
            }
            if (str_contains($src, 'marketplace_service') || str_contains($src, 'services_selection')) {
                return 'service';
            }
            if (isset($li['besoin_id'])) {
                return 'besoin_opportunite';
            }
            if (isset($li['lignes']) && is_array($li['lignes'])) {
                foreach ($li['lignes'] as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    if (isset($item['product_id'])) {
                        return 'commande_produits';
                    }
                    if (isset($item['service_id'])) {
                        return 'service';
                    }
                }
            }
        }

        $ref = trim((string) ($row['order_reference'] ?? ''));
        if (str_starts_with($ref, 'PANIER-') || str_starts_with($ref, 'MOB-FOUR-') || str_starts_with($ref, 'BESOIN-')) {
            return str_starts_with($ref, 'BESOIN-') ? 'besoin_opportunite' : 'commande_produits';
        }

        $title = mb_strtolower((string) ($row['title'] ?? ''));
        if (str_contains($title, 'commande catalogue')) {
            return 'commande_produits';
        }
        if (str_contains($title, 'demande devis')) {
            return 'demande_devis';
        }
        if (str_starts_with($title, 'demande —') || str_contains($title, 'demande marketplace') || str_contains($title, 'prestation')) {
            return 'service';
        }
        if (str_contains($title, 'besoin') || str_contains($title, 'opportunit')) {
            return 'besoin_opportunite';
        }

        if (! empty($row['is_marketplace_request'])) {
            if ($this->isCatalogCartOrder($row)) {
                return 'commande_produits';
            }
            $pt = (string) ($row['prestataire_profile_type'] ?? '');
            if ($pt === 'entreprise_fournisseur') {
                return 'demande_devis';
            }

            return 'service';
        }

        if (is_array($li) && isset($li['lignes'])) {
            return 'devis_chantier';
        }

        return 'devis_chantier';
    }

    public function subjectKindLabel(string $kind): string
    {
        return match ($kind) {
            'commande_produits' => 'Commande produits',
            'demande_devis' => 'Demande de devis',
            'besoin_opportunite' => 'Besoin / opportunité',
            'service' => 'Service',
            'brouillon_interne' => 'Brouillon interne',
            default => 'Devis chantier',
        };
    }

    /**
     * @param  DevisRow  $row
     */
    public function orderNeedsSupplierQuote(array $row): bool
    {
        $lineItems = $row['line_items'] ?? null;
        if ($lineItems === null) {
            return true;
        }
        if (is_array($lineItems) && isset($lineItems['lignes']) && is_array($lineItems['lignes'])) {
            if ($lineItems['lignes'] === []) {
                return true;
            }
            foreach ($lineItems['lignes'] as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $total = $item['line_total_fcfa'] ?? $item['total'] ?? null;
                $unit = $item['unit_price_fcfa'] ?? $item['unit_price'] ?? $item['prix_unitaire'] ?? null;
                if ($this->amountIsZeroOrMissing($total) || $this->amountIsZeroOrMissing($unit)) {
                    return true;
                }
            }

            return false;
        }
        if (is_array($lineItems) && isset($lineItems['order_lignes']) && is_array($lineItems['order_lignes'])) {
            foreach ($lineItems['order_lignes'] as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $total = $item['line_total_fcfa'] ?? $item['total'] ?? $item['prix_total'] ?? null;
                $unit = $item['unit_price_fcfa'] ?? $item['unit_price'] ?? $item['prix_unitaire'] ?? null;
                if ($this->amountIsZeroOrMissing($total) || $this->amountIsZeroOrMissing($unit)) {
                    return true;
                }
            }

            return $lineItems['order_lignes'] === [];
        }

        return true;
    }

    /**
     * @param  list<DevisRow>  $rows
     * @return list<DevisRow>
     */
    public function filterForProfile(
        array $rows,
        int $currentUserId,
        string $profileSlug,
        ?string $direction = null,
        ?string $kind = null,
        bool $supplierOrdersOnly = false,
        ?string $statusFilter = null,
        ?string $missionFilter = null,
    ): array {
        $filtered = array_values(array_filter($rows, function (array $row) use (
            $currentUserId,
            $profileSlug,
            $direction,
            $kind,
            $supplierOrdersOnly,
            $statusFilter,
            $missionFilter,
        ) {
            if ($this->isSelfDealing($row)) {
                return false;
            }

            if ($supplierOrdersOnly) {
                return $this->isSupplierReceivedOrder($row, $currentUserId);
            }

            if ($direction === self::DIRECTION_RECEIVED) {
                if ($profileSlug === 'particulier') {
                    if ((int) ($row['client_user_id'] ?? 0) !== $currentUserId
                        || (int) ($row['user_id'] ?? 0) === $currentUserId) {
                        return false;
                    }
                } elseif ($profileSlug === 'artisan') {
                    if (! $this->isIncomingRequestForPrestataire($row, $currentUserId)) {
                        return false;
                    }
                } elseif (! $this->matchesReceivedCategory($row, $currentUserId)) {
                    return false;
                }
            }

            if ($direction === self::DIRECTION_SENT) {
                if ($profileSlug === 'particulier') {
                    if (! $this->isSentMarketplaceRequest($row, $currentUserId)) {
                        return false;
                    }
                } elseif (! $this->matchesSentCategory($row, $currentUserId)) {
                    return false;
                }
            }

            if ($kind === self::KIND_CATALOG && ! $this->isCatalogCartOrder($row)) {
                return false;
            }
            if ($kind === self::KIND_MARKETPLACE && $this->isCatalogCartOrder($row)) {
                return false;
            }

            if ($statusFilter !== null && $statusFilter !== '') {
                if ((string) ($row['status'] ?? '') !== $statusFilter) {
                    return false;
                }
            }

            if ($missionFilter !== null && $missionFilter !== '' && $missionFilter !== 'all') {
                $status = (string) ($row['status'] ?? '');

                return match ($missionFilter) {
                    'negotiation' => in_array($status, ['envoye', 'en_cours', 'non_traite'], true),
                    'won' => $status === 'valide',
                    'lost' => $status === 'rejete',
                    default => true,
                };
            }

            return true;
        }));

        return $filtered;
    }

    /**
     * @param  DevisRow  $row
     */
    public function enrichRow(array $row, int $currentUserId, string $direction = self::DIRECTION_RECEIVED): array
    {
        $kind = $this->getSubjectKind($row);
        $row['subject_kind'] = $kind;
        $row['subject_kind_label'] = $this->subjectKindLabel($kind);
        $row['is_catalog_order'] = $this->isCatalogCartOrder($row);
        $row['is_supplier_received_order'] = $this->isSupplierReceivedOrder($row, $currentUserId);
        $row['needs_supplier_quote'] = $this->orderNeedsSupplierQuote($row);
        $row['has_provider_response'] = $this->hasProviderResponse($row);
        $row['counterparty_name'] = $direction === self::DIRECTION_SENT
            ? ($row['prestataire_name'] ?? $row['client_name'] ?? '—')
            : ($row['client_name'] ?? $row['prestataire_name'] ?? '—');
        $row['total_fcfa_display'] = $this->extractTotalFcfa($row);

        return $row;
    }

    /**
     * @param  DevisRow  $row
     */
    public function extractTotalFcfa(array $row): ?int
    {
        $li = $row['line_items'] ?? null;
        if (! is_array($li)) {
            return null;
        }
        if (isset($li['totals']['total_fcfa'])) {
            return (int) $li['totals']['total_fcfa'];
        }
        if (isset($li['totals']['subtotal_fcfa'])) {
            return (int) $li['totals']['subtotal_fcfa'];
        }

        return null;
    }

    /**
     * Décompte par filtre statut (hors filtre statut actif).
     *
     * @param  list<DevisRow>  $rows
     * @return array<string, int>
     */
    public function countByStatusChip(
        array $rows,
        int $currentUserId,
        string $profileSlug,
        ?string $direction = null,
        ?string $kind = null,
        bool $supplierOrdersOnly = false,
        ?string $missionFilter = null,
    ): array {
        $base = $this->filterForProfile(
            $rows,
            $currentUserId,
            $profileSlug,
            $direction,
            $kind,
            $supplierOrdersOnly,
            null,
            $missionFilter,
        );
        $counts = ['' => count($base)];
        foreach (['non_traite', 'en_cours', 'envoye', 'valide', 'rejete'] as $status) {
            $counts[$status] = count(array_filter(
                $base,
                static fn (array $r) => (string) ($r['status'] ?? '') === $status,
            ));
        }

        return $counts;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function statusChipFilters(): array
    {
        return [
            ['value' => '', 'label' => 'Tous'],
            ['value' => 'non_traite', 'label' => 'En attente'],
            ['value' => 'en_cours', 'label' => 'En cours'],
            ['value' => 'envoye', 'label' => 'Réponse reçue'],
            ['value' => 'valide', 'label' => 'Validé'],
            ['value' => 'rejete', 'label' => 'Refusé'],
        ];
    }

    /**
     * Filtres statut — écran « Mes devis » / « Mes commandes » fournisseur (prestataire).
     *
     * @return list<array{value: string, label: string}>
     */
    public function supplierStatusChipFilters(): array
    {
        return [
            ['value' => '', 'label' => 'Tous'],
            ['value' => 'non_traite', 'label' => 'En attente'],
            ['value' => 'en_cours', 'label' => 'En cours'],
            ['value' => 'envoye', 'label' => 'Réponse envoyée'],
            ['value' => 'valide', 'label' => 'Validé'],
            ['value' => 'rejete', 'label' => 'Refusé'],
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function missionChipFilters(): array
    {
        return [
            ['value' => 'all', 'label' => 'Tous'],
            ['value' => 'negotiation', 'label' => 'En négociation'],
            ['value' => 'won', 'label' => 'Gagnées'],
            ['value' => 'lost', 'label' => 'Perdues'],
        ];
    }

    private function amountIsZeroOrMissing(mixed $v): bool
    {
        if ($v === null) {
            return true;
        }
        if (is_numeric($v)) {
            return (float) $v <= 0;
        }
        $n = (int) preg_replace('/[^\d]/', '', (string) $v);

        return $n <= 0;
    }
}
