<?php

namespace App\Services\Web;

use Illuminate\Http\Request;

class ManualDevisQuoteBuilder
{
    /**
     * Lignes manuelles + remise / TVA (alignement éditeur mobile).
     *
     * @return array<string, mixed>|null
     */
    public function buildFromRequest(Request $request): ?array
    {
        /** @var array<int, string|null> $labels */
        $labels = $request->input('line_label', []);
        /** @var array<int, int|string|null> $qtys */
        $qtys = $request->input('line_qty', []);
        /** @var array<int, int|string|null> $units */
        $units = $request->input('line_unit_fcfa', []);

        if (! is_array($labels)) {
            return null;
        }

        $lignes = [];
        foreach ($labels as $i => $rawLabel) {
            $label = trim((string) $rawLabel);
            if ($label === '') {
                continue;
            }
            $qty = max(1, (int) ($qtys[$i] ?? 1));
            $unit = max(0, (int) ($units[$i] ?? 0));
            $lineTot = $qty * $unit;
            $lignes[] = [
                'label' => $label,
                'qty' => $qty,
                'unit_price_fcfa' => $unit,
                'line_total_fcfa' => $lineTot,
                'total' => $lineTot,
            ];
        }

        if ($lignes === []) {
            return null;
        }

        $discountPct = max(0, min(100, (int) $request->input('discount_pct', 0)));
        $tvaPct = max(0, min(100, (int) $request->input('tva_pct', 0)));

        $subtotal = 0;
        foreach ($lignes as $row) {
            $subtotal += (int) ($row['line_total_fcfa'] ?? 0);
        }

        $discountFcfa = (int) round($subtotal * $discountPct / 100);
        $afterDiscount = max(0, $subtotal - $discountFcfa);
        $tvaFcfa = (int) round($afterDiscount * $tvaPct / 100);
        $totalGeneral = $afterDiscount + $tvaFcfa;

        return [
            'currency' => 'XOF',
            'source' => 'manual_quote_web',
            'discount_pct' => $discountPct,
            'tva_pct' => $tvaPct,
            'lignes' => $lignes,
            'totals' => [
                'subtotal_fcfa' => $subtotal,
                'discount_fcfa' => $discountFcfa,
                'subtotal_after_discount_fcfa' => $afterDiscount,
                'tva_fcfa' => $tvaFcfa,
                'total_fcfa' => $totalGeneral,
            ],
        ];
    }

    /**
     * Préremplit le formulaire devis à partir des line_items existants.
     *
     * @return list<array{label: string, qty: int, unit: int}>
     */
    public function extractFormLines(mixed $lineItems): array
    {
        if (! is_array($lineItems)) {
            return [['label' => '', 'qty' => 1, 'unit' => 0]];
        }

        $maps = [];
        if (isset($lineItems['lignes']) && is_array($lineItems['lignes']) && $lineItems['lignes'] !== []) {
            foreach ($lineItems['lignes'] as $item) {
                if (is_array($item)) {
                    $maps[] = $item;
                }
            }
        }
        if ($maps === [] && isset($lineItems['order_lignes']) && is_array($lineItems['order_lignes'])) {
            foreach ($lineItems['order_lignes'] as $item) {
                if (is_array($item)) {
                    $maps[] = $item;
                }
            }
        }

        if ($maps === []) {
            return [['label' => '', 'qty' => 1, 'unit' => 0]];
        }

        $lines = [];
        foreach ($maps as $m) {
            $label = trim((string) ($m['label'] ?? $m['description'] ?? $m['title'] ?? $m['service'] ?? ''));
            if ($label === '') {
                continue;
            }
            $qty = max(1, (int) ($m['qty'] ?? $m['quantity'] ?? $m['quantite'] ?? 1));
            $unit = max(0, (int) ($m['unit_price_fcfa'] ?? $m['unit_price'] ?? $m['prix_unitaire'] ?? 0));
            $lines[] = ['label' => $label, 'qty' => $qty, 'unit' => $unit];
        }

        return $lines !== [] ? $lines : [['label' => '', 'qty' => 1, 'unit' => 0]];
    }
}
