<?php

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Models\Besoin;
use App\Models\Candidature;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArtisanDashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $u = $request->user();
        abort_unless($u->profile_type === User::PROFILE_ARTISAN, 403);

        $period = $request->query('period', 'month');
        if (! in_array($period, ['week', 'month', 'year'], true)) {
            $period = 'month';
        }

        $applicantId = $u->id;
        $now = Carbon::now();
        $end = $now->copy()->endOfDay();

        if ($period === 'year') {
            $rangeStart = $now->copy()->startOfYear();
        } elseif ($period === 'week') {
            $rangeStart = $now->copy()->startOfWeek();
        } else {
            $rangeStart = $now->copy()->startOfMonth();
        }

        $totalCandidatures = (int) Candidature::query()
            ->where('applicant_id', $applicantId)
            ->whereBetween('created_at', [$rangeStart, $end])
            ->count();

        $missionsRecues = (int) Candidature::query()
            ->where('applicant_id', $applicantId)
            ->where('status', 'accepte')
            ->whereBetween('updated_at', [$rangeStart, $end])
            ->count();

        $revenueFcfa = (int) Candidature::query()
            ->where('applicant_id', $applicantId)
            ->where('status', 'accepte')
            ->whereBetween('updated_at', [$rangeStart, $end])
            ->with('besoin')
            ->get()
            ->sum(fn (Candidature $c) => $this->parseBudgetToFcfa($c->besoin?->budget));

        $opportunitesOuvertes = (int) Besoin::query()
            ->where('status', 'open')
            ->whereBetween('created_at', [$rangeStart, $end])
            ->count();

        if ($period === 'year') {
            [$xLabels, $revenuParPeriodeK, $envoyeesParPeriode, $accepteParPeriode] =
                $this->buildYearlySeries($applicantId, $now, $end);
        } elseif ($period === 'week') {
            [$xLabels, $revenuParPeriodeK, $envoyeesParPeriode, $accepteParPeriode] =
                $this->buildWeeklySeries($applicantId, $rangeStart, $end);
        } else {
            [$xLabels, $revenuParPeriodeK, $envoyeesParPeriode, $accepteParPeriode] =
                $this->buildMonthlyLast7DaysSeries($applicantId, $rangeStart, $end, $now);
        }

        return response()->json([
            'period' => $period,
            'chart_main_title' => 'Revenus et candidatures',
            'kpi_labels' => [
                'revenue_fcfa' => 'Chiffre d’affaires (FCFA)',
                'missions_recues' => 'Mes missions',
                'candidatures_envoyees' => 'Candidatures envoyées',
                'opportunites_ouvertes' => 'Opportunités ouvertes',
            ],
            'kpis' => [
                'revenue_fcfa' => $revenueFcfa,
                'missions_recues' => $missionsRecues,
                'candidatures_envoyees' => $totalCandidatures,
                'opportunites_ouvertes' => $opportunitesOuvertes,
            ],
            'charts' => [
                'granularity' => $period === 'year' ? 'month' : 'day',
                'period_label' => match ($period) {
                    'week' => 'Cette semaine',
                    'year' => 'Cette année',
                    default => 'Ce mois-ci',
                },
                'x_labels' => $xLabels,
                'revenu_par_jour_k' => $revenuParPeriodeK,
                'candidatures_envoyees_par_jour' => $envoyeesParPeriode,
                'candidatures_accepte_par_jour' => $accepteParPeriode,
                'dataset_labels' => [
                    'revenue' => 'Revenu (milliers FCFA)',
                    'envoyees' => 'Candidatures envoyées',
                    'acceptees' => 'Candidatures acceptées',
                ],
            ],
        ]);
    }

    /**
     * 7 jours de la semaine en cours (lun → dim).
     *
     * @return array{0: array<int, string>, 1: array<int, float>, 2: array<int, int>, 3: array<int, int>}
     */
    private function buildWeeklySeries(
        int $applicantId,
        Carbon $weekStart,
        Carbon $end
    ): array {
        $xLabels = [];
        $revenuParJourK = [];
        $envoyeesParJour = [];
        $accepteParJour = [];

        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $ds = $day->toDateString();
            $xLabels[] = $this->shortWeekdayFr($day);

            if ($day->gt($end)) {
                $revenuParJourK[] = 0.0;
                $envoyeesParJour[] = 0;
                $accepteParJour[] = 0;

                continue;
            }

            $envoyeesParJour[] = (int) Candidature::query()
                ->where('applicant_id', $applicantId)
                ->whereDate('created_at', $ds)
                ->count();

            $accepteParJour[] = (int) Candidature::query()
                ->where('applicant_id', $applicantId)
                ->where('status', 'accepte')
                ->whereDate('updated_at', $ds)
                ->count();

            $revenueJour = (int) Candidature::query()
                ->where('applicant_id', $applicantId)
                ->where('status', 'accepte')
                ->whereDate('updated_at', $ds)
                ->with('besoin')
                ->get()
                ->sum(fn (Candidature $c) => $this->parseBudgetToFcfa($c->besoin?->budget));

            $revenuParJourK[] = round($revenueJour / 1000, 1);
        }

        return [$xLabels, $revenuParJourK, $envoyeesParJour, $accepteParJour];
    }

    /**
     * 7 derniers jours (dans le mois en cours si on est en début de mois).
     *
     * @return array{0: array<int, string>, 1: array<int, float>, 2: array<int, int>, 3: array<int, int>}
     */
    private function buildMonthlyLast7DaysSeries(
        int $applicantId,
        Carbon $rangeStart,
        Carbon $end,
        Carbon $now
    ): array {
        $chartStart = $rangeStart->copy()->max($now->copy()->subDays(6)->startOfDay());

        $xLabels = [];
        $revenuParJourK = [];
        $envoyeesParJour = [];
        $accepteParJour = [];

        for ($i = 0; $i < 7; $i++) {
            $day = $chartStart->copy()->addDays($i);
            $ds = $day->toDateString();

            $xLabels[] = $this->shortWeekdayFr($day);

            if ($day->gt($end)) {
                $revenuParJourK[] = 0.0;
                $envoyeesParJour[] = 0;
                $accepteParJour[] = 0;

                continue;
            }

            $envoyeesParJour[] = (int) Candidature::query()
                ->where('applicant_id', $applicantId)
                ->whereDate('created_at', $ds)
                ->count();

            $accepteParJour[] = (int) Candidature::query()
                ->where('applicant_id', $applicantId)
                ->where('status', 'accepte')
                ->whereDate('updated_at', $ds)
                ->count();

            $revenueJour = (int) Candidature::query()
                ->where('applicant_id', $applicantId)
                ->where('status', 'accepte')
                ->whereDate('updated_at', $ds)
                ->with('besoin')
                ->get()
                ->sum(fn (Candidature $c) => $this->parseBudgetToFcfa($c->besoin?->budget));

            $revenuParJourK[] = round($revenueJour / 1000, 1);
        }

        return [$xLabels, $revenuParJourK, $envoyeesParJour, $accepteParJour];
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, float>, 2: array<int, int>, 3: array<int, int>}
     */
    private function buildYearlySeries(int $applicantId, Carbon $now, Carbon $end): array
    {
        $yearStart = $now->copy()->startOfYear();
        $monthNames = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

        $xLabels = [];
        $revenuParMoisK = [];
        $envoyeesParMois = [];
        $accepteParMois = [];

        for ($m = 0; $m < 12; $m++) {
            $monthStart = $yearStart->copy()->addMonths($m)->startOfMonth();
            $monthEnd = $yearStart->copy()->addMonths($m)->endOfMonth();
            $xLabels[] = $monthNames[$m];

            if ($monthStart->gt($end)) {
                $envoyeesParMois[] = 0;
                $accepteParMois[] = 0;
                $revenuParMoisK[] = 0.0;

                continue;
            }
            if ($monthEnd->gt($end)) {
                $monthEnd = $end->copy();
            }

            $envoyeesParMois[] = (int) Candidature::query()
                ->where('applicant_id', $applicantId)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();

            $accepteParMois[] = (int) Candidature::query()
                ->where('applicant_id', $applicantId)
                ->where('status', 'accepte')
                ->whereBetween('updated_at', [$monthStart, $monthEnd])
                ->count();

            $revenueMois = (int) Candidature::query()
                ->where('applicant_id', $applicantId)
                ->where('status', 'accepte')
                ->whereBetween('updated_at', [$monthStart, $monthEnd])
                ->with('besoin')
                ->get()
                ->sum(fn (Candidature $c) => $this->parseBudgetToFcfa($c->besoin?->budget));

            $revenuParMoisK[] = round($revenueMois / 1000, 1);
        }

        return [$xLabels, $revenuParMoisK, $envoyeesParMois, $accepteParMois];
    }

    private function shortWeekdayFr(Carbon $d): string
    {
        $map = [
            0 => 'Dim',
            1 => 'Lun',
            2 => 'Mar',
            3 => 'Mer',
            4 => 'Jeu',
            5 => 'Ven',
            6 => 'Sam',
        ];

        return $map[(int) $d->dayOfWeek] ?? $d->format('D');
    }

    private function parseBudgetToFcfa(?string $budget): int
    {
        if ($budget === null || trim($budget) === '') {
            return 0;
        }
        $b = trim($budget);
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*k/i', $b, $m)) {
            return (int) round((float) str_replace(',', '.', $m[1]) * 1000);
        }
        if (preg_match('/(\d+(?:[.,]\d+)?)/', $b, $m)) {
            return (int) round((float) str_replace(',', '.', $m[1]));
        }

        return 0;
    }
}
