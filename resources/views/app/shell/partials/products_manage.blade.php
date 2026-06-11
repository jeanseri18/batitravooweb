@php
    $mp = $profileSlug;
    $productStatus = static function (?string $s): string {
        return match ($s) {
            'draft' => 'Brouillon',
            'pending' => 'En validation',
            'approved' => 'Validé',
            'rejected' => 'Refusé',
            default => $s ?? '—',
        };
    };
@endphp

<div class="app-page-stack--split">
<div class="app-manage-toolbar app-card">
    <a href="{{ route('app.'.$mp.'.marketplace') }}" class="app-btn app-btn--secondary app-btn--sm">Marketplace</a>
    <a href="{{ route('app.'.$mp.'.messages') }}" class="app-btn app-btn--secondary app-btn--sm">Chat</a>
    <a href="{{ route('app.fournisseur.commandes') }}" class="app-btn app-btn--secondary app-btn--sm">Mes commandes</a>
    <a href="{{ route('app.fournisseur.devis') }}" class="app-btn app-btn--secondary app-btn--sm">Mes devis</a>
</div>

<div class="app-card">
    <div class="app-page-head">
        <div class="app-page-head__main">
            <h2 class="app-page-head__title">Catalogue produits</h2>
            <p class="app-page-head__desc">Gérez vos articles, stocks et statuts de publication.</p>
        </div>
        <div class="app-page-head__actions">
            <a href="{{ route('app.'.$mp.'.products.create') }}" class="app-btn app-btn--inline app-btn--sm">Nouveau produit</a>
        </div>
    </div>
    @if (! empty($productsList) && count($productsList))
        <div class="app-table-wrap">
            <table class="app-table app-table--bordered">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Vues</th>
                        <th>Statut</th>
                        <th class="app-table__col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productsList as $row)
                        @php $pid = (int) ($row['id'] ?? 0); @endphp
                        <tr>
                            <td><strong>{{ $row['title'] ?? '—' }}</strong></td>
                            <td class="app-muted">{{ $row['category']['name'] ?? '—' }}</td>
                            <td>{{ $row['price_display_fr'] ?? '—' }}</td>
                            <td>{{ (int) ($row['stock_units'] ?? 0) }}</td>
                            <td class="app-muted">{{ (int) ($row['views_count'] ?? 0) }}</td>
                            <td><span class="app-pill">{{ $productStatus($row['status'] ?? null) }}</span></td>
                            <td class="app-table__col-actions">
                                @if ($pid > 0)
                                    @include('app.shell.partials.table_actions_dropdown', [
                                        'menuId' => 'product-actions-'.$pid,
                                        'actions' => [
                                            ['type' => 'link', 'label' => 'Fiche publique', 'href' => route('app.'.$mp.'.marketplace.product', ['product' => $pid])],
                                            ['type' => 'link', 'label' => 'Modifier', 'href' => route('app.'.$mp.'.products.edit', ['product' => $pid])],
                                            ['type' => 'form', 'label' => 'Supprimer', 'action' => route('app.'.$mp.'.products.destroy', ['product' => $pid]), 'method' => 'DELETE', 'confirm' => 'Supprimer ce produit ?', 'danger' => true],
                                        ],
                                    ])
                                @else
                                    <span class="app-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="app-muted app-mb-sm">Aucune référence pour le moment.</p>
        <a href="{{ route('app.'.$mp.'.products.create') }}" class="app-btn app-btn--inline">Ajouter un produit</a>
    @endif
</div>
</div>
