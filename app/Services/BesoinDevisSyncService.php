<?php

namespace App\Services;

use App\Models\Besoin;
use App\Models\Candidature;
use App\Models\Devis;
use App\Models\InAppNotification;
use App\Models\User;

/**
 * Synchronise candidature et besoin lorsque le client valide / refuse un devis lié à une opportunité (BESOIN-{id}).
 */
class BesoinDevisSyncService
{
    public function syncAfterDevisStatusChange(Devis $devis, string $newStatus, string $previousStatus): void
    {
        if ($newStatus === $previousStatus) {
            return;
        }

        $besoinId = $this->resolveBesoinIdFromDevis($devis);
        if ($besoinId === null) {
            return;
        }

        $candidature = Candidature::query()
            ->where('besoin_id', $besoinId)
            ->where('applicant_id', $devis->user_id)
            ->first();

        if ($candidature === null) {
            return;
        }

        if ($newStatus === 'valide') {
            $candidature->status = 'accepte';
            $candidature->save();

            Besoin::query()
                ->where('id', $besoinId)
                ->whereIn('status', ['open', 'in_progress'])
                ->update(['status' => 'in_progress']);

            Candidature::query()
                ->where('besoin_id', $besoinId)
                ->where('id', '!=', $candidature->id)
                ->where('status', 'recu')
                ->update(['status' => 'rejete']);

            $besoin = Besoin::query()->find($besoinId);
            if ($besoin !== null) {
                InAppNotification::query()->create([
                    'user_id' => $devis->user_id,
                    'type' => InAppNotification::TYPE_CANDIDATURE,
                    'title' => 'Candidature acceptée',
                    'body' => 'Votre proposition de devis a été acceptée pour « '.($besoin->title ?? 'le besoin').' ».',
                    'data' => [
                        'besoin_id' => $besoinId,
                        'candidature_id' => $candidature->id,
                        'devis_id' => $devis->id,
                    ],
                ]);
            }

            return;
        }

        if ($newStatus === 'rejete') {
            $candidature->status = 'rejete';
            $candidature->save();
        }
    }

    public function resolveBesoinIdFromDevis(Devis $devis): ?int
    {
        $ref = trim((string) ($devis->order_reference ?? ''));
        if (preg_match('/^BESOIN-(\d+)$/i', $ref, $m) !== 1) {
            return null;
        }

        $id = (int) $m[1];

        return $id > 0 ? $id : null;
    }
}
