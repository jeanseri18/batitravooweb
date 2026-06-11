<?php

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Models\Devis;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Tableau de bord particulier — devis passés en tant que client ([client_user_id]).
 */
class ParticulierDashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $u = $request->user();
        abort_unless($u->profile_type === User::PROFILE_PARTICULIER, 403);

        $period = $request->query('period', 'month');
        if (! in_array($period, ['week', 'month', 'year'], true)) {
            $period = 'month';
        }

        $now = Carbon::now();

        if ($period === 'year') {
            $rangeStart = $now->copy()->startOfYear();
            $prevAnchor = $now->copy()->subYear();
            $prevRangeStart = $prevAnchor->copy()->startOfYear();
        } elseif ($period === 'week') {
            $rangeStart = $now->copy()->startOfWeek();
            $prevAnchor = $now->copy()->subWeek();
            $prevRangeStart = $prevAnchor->copy()->startOfWeek();
        } else {
            $rangeStart = $now->copy()->startOfMonth();
            $prevAnchor = $now->copy()->subMonth();
            $prevRangeStart = $prevAnchor->copy()->startOfMonth();
        }
        $rangeEnd = $now->copy()->endOfDay();
        $prevRangeEnd = $prevAnchor->copy()->endOfDay();

        $cid = $u->id;

        $base = Devis::query()->where('client_user_id', $cid);

        $btpCur = $this->countTowardProfile($base, User::PROFILE_ENTREPRENEUR_BATIMENT, $rangeStart, $rangeEnd);
        $btpPrev = $this->countTowardProfile($base, User::PROFILE_ENTREPRENEUR_BATIMENT, $prevRangeStart, $prevRangeEnd);

        $artCur = $this->countTowardProfile($base, User::PROFILE_ARTISAN, $rangeStart, $rangeEnd);
        $artPrev = $this->countTowardProfile($base, User::PROFILE_ARTISAN, $prevRangeStart, $prevRangeEnd);

        $cmdCur = (clone $base)->whereBetween('created_at', [$rangeStart, $rangeEnd])->count();
        $cmdPrev = (clone $base)->whereBetween('created_at', [$prevRangeStart, $prevRangeEnd])->count();

        $valCur = $this->sumClientValideCa($cid, $rangeStart, $rangeEnd);
        $valPrev = $this->sumClientValideCa($cid, $prevRangeStart, $prevRangeEnd);

        $weekStart = $now->copy()->locale('fr')->startOfWeek(Carbon::MONDAY)->startOfDay();

        $charts = [
            'week_labels' => ['Lun', 'Mar', 'Merc', 'Jeu', 'Ven', 'Sam', 'Dim'],
            'btp' => $this->weekPairs($cid, User::PROFILE_ENTREPRENEUR_BATIMENT, $weekStart),
            'artisan' => $this->weekPairs($cid, User::PROFILE_ARTISAN, $weekStart),
            'commandes_par_jour' => $this->weekDailyCounts($cid, $weekStart),
            'valeur_par_jour_fcfa' => $this->weekDailyValeur($cid, $weekStart),
        ];

        return response()->json([
            'period' => $period,
            'kpi_labels' => [
                'prestations_btp' => 'Prestations BTP',
                'demandes_artisans' => 'Demandes artisans',
                'nombre_commandes' => 'Nombre de commandes',
                'valeur_commandes_fcfa' => 'Valeur des commandes (FCFA)',
            ],
            'kpis' => [
                'prestations_btp' => $this->kpiPayload($btpCur, $btpPrev),
                'demandes_artisans' => $this->kpiPayload($artCur, $artPrev),
                'nombre_commandes' => $this->kpiPayload($cmdCur, $cmdPrev),
                'valeur_commandes_fcfa' => $this->kpiPayloadMoney($valCur, $valPrev),
            ],
            'charts' => array_merge($charts, [
                'panel_titles' => [
                    'btp' => 'BTP — demandes et prestations acceptées',
                    'artisan' => 'Artisans — demandes et prestations acceptées',
                    'commandes' => 'Commandes par jour',
                    'ca' => 'Valeur des commandes validées (FCFA)',
                ],
                'dataset_labels' => [
                    'demandes' => 'Demandes',
                    'prestations_acceptees' => 'Prestations acceptées',
                    'commandes' => 'Commandes',
                    'fcfa' => 'FCFA',
                ],
            ]),
        ]);
    }

    private function countTowardProfile($baseQuery, string $profile, Carbon $start, Carbon $end): int
    {
        return (clone $baseQuery)
            ->whereBetween('created_at', [$start, $end])
            ->whereHas('user', fn ($q) => $q->where('profile_type', $profile))
            ->count();
    }

    /**
     * @return array{demandes_totales: float[], prestations_acceptees: float[]}
     */
    private function weekPairs(int $clientId, string $profile, Carbon $weekStart): array
    {
        $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();
        $rows = Devis::query()
            ->where('client_user_id', $clientId)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->whereHas('user', fn ($q) => $q->where('profile_type', $profile))
            ->get(['created_at', 'status']);

        $dem = array_fill(0, 7, 0.0);
        $acc = array_fill(0, 7, 0.0);

        foreach ($rows as $d) {
            $idx = $this->dayIndexInWeek($weekStart, $d->created_at);
            if ($idx === null) {
                continue;
            }
            $dem[$idx]++;
            if (in_array($d->status, ['valide', 'envoye'], true)) {
                $acc[$idx]++;
            }
        }

        return [
            'demandes_totales' => $dem,
            'prestations_acceptees' => $acc,
        ];
    }

    private function weekDailyCounts(int $clientId, Carbon $weekStart): array
    {
        $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();
        $rows = Devis::query()
            ->where('client_user_id', $clientId)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->get(['created_at']);

        $counts = array_fill(0, 7, 0.0);
        foreach ($rows as $d) {
            $idx = $this->dayIndexInWeek($weekStart, $d->created_at);
            if ($idx === null) {
                continue;
            }
            $counts[$idx]++;
        }

        return $counts;
    }

    /**
     * @return float[]
     */
    private function weekDailyValeur(int $clientId, Carbon $weekStart): array
    {
        $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();
        $rows = Devis::query()
            ->where('client_user_id', $clientId)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->where('status', 'valide')
            ->get(['created_at', 'line_items']);

        $sums = array_fill(0, 7, 0.0);
        foreach ($rows as $d) {
            $idx = $this->dayIndexInWeek($weekStart, $d->created_at);
            if ($idx === null) {
                continue;
            }
            $sums[$idx] += (float) $this->sumLineItemsTotal($d->line_items);
        }

        return $sums;
    }

    private function dayIndexInWeek(Carbon $weekStart, $createdAt): ?int
    {
        $ws = $weekStart->copy()->startOfDay();
        $day = Carbon::parse($createdAt)->startOfDay();
        if ($day->lt($ws)) {
            return null;
        }
        $idx = (int) $ws->diffInDays($day);
        if ($idx < 0 || $idx > 6) {
            return null;
        }

        return $idx;
    }

    private function sumClientValideCa(int $clientId, Carbon $start, Carbon $end): int
    {
        $rows = Devis::query()
            ->where('client_user_id', $clientId)
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'valide')
            ->get(['line_items']);

        $sum = 0;
        foreach ($rows as $d) {
            $sum += $this->sumLineItemsTotal($d->line_items);
        }

        return $sum;
    }

    /**
     * @param  array<string, mixed>|null  $items
     */
    private function sumLineItemsTotal(?array $items): int
    {
        if ($items === null || $items === []) {
            return 0;
        }

        if (isset($items['totals']) && is_array($items['totals'])) {
            $t = $items['totals']['subtotal_fcfa'] ?? null;
            if ($t !== null) {
                return (int) $t;
            }
        }

        if (isset($items['lignes']) && is_array($items['lignes'])) {
            $t = 0;
            foreach ($items['lignes'] as $line) {
                if (! is_array($line)) {
                    continue;
                }
                $t += (int) ($line['line_total_fcfa'] ?? $line['total'] ?? 0);
            }

            return $t;
        }

        $t = 0;
        foreach ($items as $line) {
            if (! is_array($line)) {
                continue;
            }
            $t += (int) ($line['total'] ?? $line['line_total_fcfa'] ?? 0);
        }

        return $t;
    }

    /**
     * @return array{value: int, trend_pct: int, trend_up: bool}
     */
    private function kpiPayload(int $current, int $previous): array
    {
        if ($previous === 0) {
            return [
                'value' => $current,
                'trend_pct' => $current > 0 ? 100 : 0,
                'trend_up' => $current >= $previous,
            ];
        }

        $pct = (int) round(abs($current - $previous) / $previous * 100);

        return [
            'value' => $current,
            'trend_pct' => min(max($pct, 0), 999),
            'trend_up' => $current >= $previous,
        ];
    }

    /**
     * @return array{value: int, trend_pct: int, trend_up: bool}
     */
    private function kpiPayloadMoney(int $current, int $previous): array
    {
        return $this->kpiPayload($current, $previous);
    }
}
