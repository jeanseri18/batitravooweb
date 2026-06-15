@extends('app.layouts.shell')

@section('content')
    @php
        /** @var array<string, mixed> $marketplaceItem */
        $item = $marketplaceItem;
        $kind = $marketplaceDetailKind;
        $ownerId = isset($item['owner']['id']) ? (int) $item['owner']['id'] : (isset($item['user_id']) ? (int) $item['user_id'] : null);
        $me = auth()->id();
        $canContact = $ownerId !== null && $ownerId !== (int) $me;
        $ownerPt = $item['owner']['profile_type'] ?? '';
        $msgLabel = match ($ownerPt) {
            'artisan' => 'Écrire à l\'artisan',
            'entreprise_fournisseur' => 'Contacter le fournisseur',
            'entrepreneur_batiment' => 'Écrire au prestataire BTP',
            default => 'Envoyer un message',
        };
        $backQs = request()->query();
        $backUrl = route('app.'.$profileSlug.'.marketplace');
        if ($backQs !== []) {
            $backUrl .= '?'.http_build_query($backQs);
        }
    @endphp

    <div class="app-mockup-page" data-mockup-page="marketplace-show">
    <div class="mp-detail-back">
        <a href="{{ $backUrl }}" class="app-text-link">← Retour aux annonces</a>
    </div>

    <article class="app-card mp-detail">
        @if ($kind === 'product')
            @php
                $img = $item['image_url'] ?? null;
                $title = $item['title'] ?? '—';
            @endphp
            <div class="mp-detail__media">
                @if (! empty($img))
                    <img src="{{ $img }}" alt="" class="mp-detail__img">
                @else
                    <div class="mp-card__placeholder mp-card__placeholder--product mp-detail__placeholder">
                        <span>{{ mb_strtoupper(mb_substr($title, 0, 1)) }}</span>
                    </div>
                @endif
                @if (! empty($item['price_display_fr']))
                    <span class="mp-detail__price">{{ $item['price_display_fr'] }}</span>
                @endif
            </div>
            <div class="mp-detail__body">
                @if (! empty($item['category']['name']))
                    <p class="mp-card__category">{{ $item['category']['name'] }}</p>
                @endif
                <h2 class="mp-detail__title">{{ $title }}</h2>
                @if (! empty($item['description']))
                    <div class="mp-detail__desc app-readable">{!! nl2br(e($item['description'])) !!}</div>
                @endif
                @if (isset($item['stock_units']))
                    <p class="app-muted app-mt-sm">Stock indicatif : {{ (int) $item['stock_units'] }} unité(s)</p>
                @endif
                @if (! empty($item['views_count']))
                    <p class="app-muted app-mt-sm">{{ (int) $item['views_count'] }} vues</p>
                @endif
                @php
                    $productId = (int) ($item['id'] ?? 0);
                    $stockUnits = (int) ($item['stock_units'] ?? 0);
                    $canCart = in_array($profileSlug, ['particulier', 'batiment'], true)
                        && $ownerId
                        && (int) $me !== (int) $ownerId
                        && $productId > 0
                        && $stockUnits > 0;
                @endphp
                @if ($canCart)
                    <div class="app-mt-md app-cart-add">
                        <form method="post" action="{{ route('app.'.$profileSlug.'.cart.add') }}" class="app-cart-add__form">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $productId }}">
                            <label for="mp-add-qty" class="app-muted app-text-sm">Quantité</label>
                            <input type="number" name="qty" id="mp-add-qty" class="app-input" value="1" min="1" max="{{ $stockUnits }}" required>
                            <button type="submit" class="app-btn app-btn--sm">Ajouter au panier</button>
                        </form>
                        <p class="app-muted app-text-sm app-mb-0 app-mt-sm">
                            <a href="{{ route('app.'.$profileSlug.'.cart') }}" class="app-text-link">Voir le panier</a>
                        </p>
                    </div>
                @endif
                @if ($profileSlug === 'particulier' && $ownerId)
                    <p class="app-mt-sm" style="margin-bottom:0;">
                        <a href="{{ route('app.particulier.devis.create', ['owner_user_id' => $ownerId, 'title' => 'Demande — '.$title]) }}" class="app-btn app-btn--secondary app-btn--sm">Demander un devis</a>
                    </p>
                @endif
            </div>
        @elseif ($kind === 'service')
            @php
                $img = $item['image_url'] ?? null;
                $title = $item['title'] ?? '—';
                $pricing = $item['pricing'] ?? [];
                $priceLine = $pricing['detail_fr'] ?? $pricing['title_fr'] ?? ($item['price_fixed_label'] ?? '');
            @endphp
            <div class="mp-detail__media">
                @if (! empty($img))
                    <img src="{{ $img }}" alt="" class="mp-detail__img">
                @else
                    <div class="mp-card__placeholder mp-card__placeholder--service mp-detail__placeholder">
                        <span>{{ mb_strtoupper(mb_substr($title, 0, 1)) }}</span>
                    </div>
                @endif
            </div>
            <div class="mp-detail__body">
                @if (! empty($item['category']['name']))
                    <p class="mp-card__category">{{ $item['category']['name'] }}</p>
                @endif
                <h2 class="mp-detail__title">{{ $title }}</h2>
                @if (! empty($item['location']))
                    <p class="mp-card__location mp-detail__location">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 1118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $item['location'] }}
                    </p>
                @endif
                @if ($priceLine !== '')
                    <p class="mp-detail__pricing">{{ $priceLine }}</p>
                @endif
                @if (! empty($item['description']))
                    <div class="mp-detail__desc app-readable">{!! nl2br(e($item['description'])) !!}</div>
                @endif
                @if (! empty($item['rating']) && (float) $item['rating'] > 0)
                    <p class="app-muted">Note : {{ number_format((float) $item['rating'], 1, ',', ' ') }} / 5 @if (! empty($item['review_count'])) ({{ (int) $item['review_count'] }} avis) @endif</p>
                @endif
                @if ($profileSlug === 'particulier' && $ownerId)
                    <p class="app-mt-sm" style="margin-bottom:0;">
                        <a href="{{ route('app.particulier.devis.create', ['owner_user_id' => $ownerId, 'title' => 'Demande — '.$title]) }}" class="app-btn app-btn--secondary app-btn--sm">Demander un devis</a>
                    </p>
                @endif
            </div>
        @else
            @php
                $img = $item['image_url'] ?? null;
                $title = $item['title'] ?? '—';
            @endphp
            <div class="mp-detail__media">
                @if (! empty($img))
                    <img src="{{ $img }}" alt="" class="mp-detail__img">
                @else
                    <div class="mp-card__placeholder mp-card__placeholder--besoin mp-detail__placeholder">
                        <span>{{ mb_strtoupper(mb_substr($title, 0, 1)) }}</span>
                    </div>
                @endif
                @if (! empty($item['budget']))
                    <span class="mp-detail__price mp-detail__price--budget">{{ $item['budget'] }}</span>
                @endif
            </div>
            <div class="mp-detail__body">
                <h2 class="mp-detail__title">{{ $title }}</h2>
                @if (! empty($item['place']))
                    <p class="mp-card__location mp-detail__location">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 1118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $item['place'] }}
                    </p>
                @endif
                @if (! empty($item['start_label']) || ! empty($item['duration']) || ! empty($item['short_date']))
                    <p class="app-muted">
                        @foreach (array_filter([$item['start_label'] ?? null, $item['duration'] ?? null, $item['short_date'] ?? null]) as $bit)
                            <span class="mp-detail__meta-bit">{{ $bit }}</span>
                        @endforeach
                    </p>
                @endif
                @if (! empty($item['description']))
                    <div class="mp-detail__desc app-readable">{!! nl2br(e($item['description'])) !!}</div>
                @endif
                @if (isset($item['candidature_count']))
                    <p class="app-muted app-mt-sm">{{ (int) $item['candidature_count'] }} réponse(s) à ce besoin</p>
                @endif
                @if (! empty($besoinAlreadyApplied ?? false))
                    <p class="app-muted app-mt-sm app-mb-0">Vous avez déjà répondu à ce besoin.</p>
                @elseif (($item['status'] ?? '') !== 'open' && ! empty($item['status']))
                    <p class="app-muted app-mt-sm app-mb-0">Ce besoin n’accepte plus de nouvelles candidatures (statut : {{ $item['status'] }}).</p>
                @endif
            </div>
        @endif

        @if (! empty($item['owner']))
            @php
                $ownerRoleLabel = match ($ownerPt) {
                    'artisan' => 'Artisan',
                    'entrepreneur_batiment' => 'Entreprise BTP',
                    'entreprise_fournisseur' => 'Fournisseur',
                    default => 'Prestataire',
                };
            @endphp
            <div class="mp-detail__owner">
                <h3 class="mp-detail__owner-title">{{ $ownerRoleLabel }}</h3>
                @php
                    $ownerDisplay = trim((string) ($item['owner']['display_name'] ?? ''));
                    if ($ownerDisplay === '') {
                        $ownerDisplay = trim((string) ($item['owner']['company_name'] ?? ''));
                    }
                    if ($ownerDisplay === '') {
                        $ownerDisplay = trim((string) ($item['owner']['name'] ?? ''));
                    }
                @endphp
                <p class="mp-detail__owner-name">{{ $ownerDisplay !== '' ? $ownerDisplay : '—' }}</p>
                @if (! empty($item['owner']['activity_type']))
                    <p class="app-muted app-mt-sm"><strong>Expertise :</strong> {{ $item['owner']['activity_type'] }}</p>
                @endif
                @if (! empty($item['owner']['description']))
                    <div class="app-mt-sm app-readable">
                        <strong>Description</strong>
                        <p class="app-muted app-mb-0">{!! nl2br(e($item['owner']['description'])) !!}</p>
                    </div>
                @endif
                @if (! empty($item['owner']['company_address']))
                    <p class="app-muted app-readable app-mt-sm">{{ $item['owner']['company_address'] }}</p>
                @endif
            </div>
        @endif
    </article>

    @if ($kind === 'besoin' && ! empty($besoinShowApplicantForms ?? false))
        @php
            $bid = (int) ($item['id'] ?? 0);
            $ownerName = $item['owner']['name'] ?? '';
            $defaultDisplay = old('display_name', auth()->user()->name ?? '');
        @endphp
        @if (! ($besoinIsArtisanApplicant ?? false))
            <div class="app-card app-mt">
                <h3 class="app-section-title">Répondre au besoin</h3>
                <p class="app-muted app-mb-md app-text-sm">Envoyez une candidature avec un court message. Une seule réponse par besoin.</p>
                @error('besoin_apply')
                    <div class="app-alert app-alert--error app-mb-md" role="alert">{{ $message }}</div>
                @enderror
                <form method="post" action="{{ route('app.'.$profileSlug.'.marketplace.besoin.candidature', ['besoin' => $bid]) }}" class="app-form-stack">
                    @csrf
                    <input type="hidden" name="besoin_id" value="{{ $bid }}">
                    <div class="app-field">
                        <label for="cand-display">Nom affiché</label>
                        <input type="text" name="display_name" id="cand-display" value="{{ $defaultDisplay }}" maxlength="255" autocomplete="name">
                    </div>
                    <div class="app-field">
                        <label for="cand-prof">Métier / spécialité</label>
                        <input type="text" name="profession" id="cand-prof" value="{{ old('profession') }}" maxlength="255" placeholder="Ex. électricité, gros œuvre…">
                    </div>
                    <div class="app-field">
                        <label for="cand-msg">Message</label>
                        <textarea name="message" id="cand-msg" rows="5" maxlength="10000" placeholder="Présentez votre proposition…">{{ old('message') }}</textarea>
                    </div>
                    <button type="submit" class="app-btn app-btn--inline">Envoyer la candidature</button>
                </form>
            </div>
        @else
            @php
                $defaultCvMessage = old('message', 'Bonjour, je souhaite vous proposer mes services pour ce besoin. Vous trouverez mon profil et ma carte de visite sur Batitravoo.');
                $defaultShortMessage = old('message', 'Bonjour, je suis disponible pour ce chantier et je serais ravi d’échanger avec vous.');
            @endphp
            <div class="app-card app-mt" id="besoin-postuler">
                <h3 class="app-section-title">Postuler à cette opportunité</h3>
                <p class="app-muted app-mb-md app-text-sm">Choisissez comment répondre : un <strong>devis</strong> chiffré, votre <strong>CV</strong> (candidature avec profil) ou un <strong>message</strong> court. Une seule réponse par besoin.</p>
                @error('besoin_apply')
                    <div class="app-alert app-alert--error app-mb-md" role="alert">{{ $message }}</div>
                @enderror
                @error('besoin_devis')
                    <div class="app-alert app-alert--error app-mb-md" role="alert">{{ $message }}</div>
                @enderror
                <nav class="mp-tabs devis-tabs--pill app-mb-md" role="tablist" aria-label="Mode de réponse">
                    <button type="button" class="mp-tab is-active" data-postuler-tab="devis" role="tab" aria-selected="true">Devis</button>
                    <button type="button" class="mp-tab" data-postuler-tab="cv" role="tab" aria-selected="false">CV</button>
                    <button type="button" class="mp-tab" data-postuler-tab="message" role="tab" aria-selected="false">Message</button>
                </nav>
                <div data-postuler-panel="devis">
                    <p class="app-muted app-mb-md app-text-sm">Proposition chiffrée avec lignes et montants.</p>
                    <form method="post" action="{{ route('app.'.$profileSlug.'.marketplace.besoin.devis', ['besoin' => $bid]) }}" class="app-form-stack">
                        @csrf
                        <div class="app-field">
                            <label for="adv-title">Titre du devis</label>
                            <input type="text" name="title" id="adv-title" required maxlength="255" value="{{ old('title', 'Proposition — '.($item['title'] ?? '')) }}">
                        </div>
                        <div class="app-field">
                            <label for="adv-client">Client (nom affiché)</label>
                            <input type="text" name="client_name" id="adv-client" required maxlength="255" value="{{ old('client_name', $ownerName) }}">
                        </div>
                        <div class="app-field">
                            <label for="adv-place">Lieu / chantier</label>
                            <input type="text" name="place" id="adv-place" maxlength="255" value="{{ old('place', $item['place'] ?? '') }}">
                        </div>
                        <div class="app-field">
                            <label for="adv-contact">Contact</label>
                            <input type="text" name="contact" id="adv-contact" maxlength="255" value="{{ old('contact', auth()->user()->phone ?? '') }}" placeholder="Téléphone ou email">
                        </div>
                        <div class="app-field">
                            <label for="adv-amt">Montant total (FCFA)</label>
                            <input type="number" name="amount_fcfa" id="adv-amt" min="0" step="1" value="{{ old('amount_fcfa') }}" placeholder="Optionnel — laissez vide pour un devis sans montant">
                        </div>
                        <div class="app-field">
                            <label for="adv-lbl">Libellé de la ligne (si montant)</label>
                            <input type="text" name="line_label" id="adv-lbl" maxlength="255" value="{{ old('line_label', 'Prestation') }}">
                        </div>
                        <div class="app-field">
                            <label for="adv-notes">Notes</label>
                            <textarea name="notes" id="adv-notes" rows="4" maxlength="10000">{{ old('notes') }}</textarea>
                        </div>
                        <button type="submit" class="app-btn app-btn--inline">Envoyer le devis</button>
                    </form>
                </div>
                <div data-postuler-panel="cv" hidden>
                    <p class="app-muted app-mb-md app-text-sm">Envoyez votre candidature avec votre profil / carte de visite.</p>
                    <form method="post" action="{{ route('app.'.$profileSlug.'.marketplace.besoin.candidature', ['besoin' => $bid]) }}" class="app-form-stack">
                        @csrf
                        <input type="hidden" name="besoin_id" value="{{ $bid }}">
                        <div class="app-field">
                            <label for="cand-cv-display">Nom affiché</label>
                            <input type="text" name="display_name" id="cand-cv-display" value="{{ $defaultDisplay }}" maxlength="255" autocomplete="name">
                        </div>
                        <div class="app-field">
                            <label for="cand-cv-prof">Métier / spécialité</label>
                            <input type="text" name="profession" id="cand-cv-prof" value="{{ old('profession') }}" maxlength="255" placeholder="Ex. plomberie, électricité…">
                        </div>
                        <div class="app-field">
                            <label for="cand-cv-msg">Présentation (CV)</label>
                            <textarea name="message" id="cand-cv-msg" rows="5" maxlength="10000" placeholder="Présentez votre expérience et vos références…">{{ $defaultCvMessage }}</textarea>
                        </div>
                        <button type="submit" class="app-btn app-btn--inline">Envoyer mon CV</button>
                    </form>
                </div>
                <div data-postuler-panel="message" hidden>
                    <p class="app-muted app-mb-md app-text-sm">Court message de présentation au client.</p>
                    <form method="post" action="{{ route('app.'.$profileSlug.'.marketplace.besoin.candidature', ['besoin' => $bid]) }}" class="app-form-stack">
                        @csrf
                        <input type="hidden" name="besoin_id" value="{{ $bid }}">
                        <div class="app-field">
                            <label for="cand-msg-display">Nom affiché</label>
                            <input type="text" name="display_name" id="cand-msg-display" value="{{ $defaultDisplay }}" maxlength="255">
                        </div>
                        <div class="app-field">
                            <label for="cand-msg-prof">Métier</label>
                            <input type="text" name="profession" id="cand-msg-prof" value="{{ old('profession') }}" maxlength="255">
                        </div>
                        <div class="app-field">
                            <label for="cand-msg-body">Message</label>
                            <textarea name="message" id="cand-msg-body" rows="4" maxlength="10000" placeholder="Votre message au porteur du besoin…">{{ $defaultShortMessage }}</textarea>
                        </div>
                        <button type="submit" class="app-btn app-btn--secondary app-btn--inline">Envoyer le message</button>
                    </form>
                </div>
            </div>
            @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var root = document.getElementById('besoin-postuler');
                    if (!root) return;
                    var tabs = root.querySelectorAll('[data-postuler-tab]');
                    var panels = root.querySelectorAll('[data-postuler-panel]');
                    tabs.forEach(function (tab) {
                        tab.addEventListener('click', function () {
                            var key = tab.getAttribute('data-postuler-tab');
                            tabs.forEach(function (t) {
                                t.classList.toggle('is-active', t === tab);
                                t.setAttribute('aria-selected', t === tab ? 'true' : 'false');
                            });
                            panels.forEach(function (p) {
                                var show = p.getAttribute('data-postuler-panel') === key;
                                if (show) {
                                    p.removeAttribute('hidden');
                                } else {
                                    p.setAttribute('hidden', 'hidden');
                                }
                            });
                        });
                    });
                });
            </script>
            @endpush
        @endif
    @endif

    <div class="mp-detail-actions">
        @if ($canContact)
            <a href="{{ route('app.'.$profileSlug.'.messages', ['peer_id' => $ownerId]) }}" class="app-btn">{{ $msgLabel ?? 'Envoyer un message' }}</a>
        @else
            <p class="app-muted app-mb-0">C’est votre publication ou contact non disponible.</p>
        @endif
        @if ($profileSlug === 'particulier')
            <a href="{{ route('app.particulier.devis', ['direction' => 'sent', 'kind' => 'marketplace']) }}" class="app-btn app-btn--secondary">Mes devis</a>
            <a href="{{ route('app.particulier.devis', ['direction' => 'sent', 'kind' => 'catalog']) }}" class="app-btn app-btn--secondary">Mes commandes</a>
        @else
            <a href="{{ route('app.'.$profileSlug.'.devis') }}" class="app-btn app-btn--secondary">Voir mes devis</a>
        @endif
    </div>
    </div>{{-- .app-mockup-page --}}
@endsection
