@if (session('status'))
    <div class="app-alert app-alert--success app-mb-md" role="status">{{ session('status') }}</div>
@endif
@if ($errors->has('document_upload'))
    <div class="app-alert app-alert--error app-mb-md" role="alert">{{ $errors->first('document_upload') }}</div>
@endif

@if (! empty($documentUploadKinds))
    <div class="app-card app-mt">
        <h2 class="app-section-title">Déposer un document</h2>
        <p class="app-muted app-mb-md">Même fonctionnalité que sur l’application mobile (pièces justificatives, RCCM, etc.).</p>
        <form method="post" action="{{ route('app.'.$profileSlug.'.documents.store') }}" enctype="multipart/form-data" class="app-form-stack">
            @csrf
            <div class="app-field">
                <label for="doc-kind">Type de document</label>
                <select name="kind" id="doc-kind" required>
                    <option value="">— Choisir —</option>
                    @foreach ($documentUploadKinds as $kind)
                        <option value="{{ $kind['code'] }}" @selected(old('kind') === $kind['code'])>{{ $kind['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="app-field">
                <label for="doc-file">Fichier (PDF, image… max 10 Mo)</label>
                <input type="file" name="file" id="doc-file" required accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
            </div>
            <button type="submit" class="app-btn app-btn--inline">Envoyer</button>
        </form>
    </div>
@endif

@if (! empty($documentsList) && count($documentsList))
    <div class="app-card app-mt">
        <ul class="app-doc-list">
            @foreach ($documentsList as $doc)
                <li class="app-doc-list__item">
                    <div>
                        <strong>{{ $doc['title'] ?? 'Document' }}</strong>
                        @if (! empty($doc['subtitle']))
                            <span class="app-muted app-text-sm">{{ $doc['subtitle'] }}</span>
                        @endif
                    </div>
                    <div class="app-flex-between-wrap app-gap-sm">
                        @if (! empty($doc['has_file']) && ! empty($doc['file_url']))
                            <a href="{{ $doc['file_url'] }}" class="app-text-link" target="_blank" rel="noopener">Télécharger</a>
                        @else
                            <span class="app-muted">—</span>
                        @endif
                        @if (! empty($documentUploadKinds) && ! empty($doc['id']))
                            <form method="post" action="{{ route('app.'.$profileSlug.'.documents.destroy', ['document' => $doc['id']]) }}" onsubmit="return confirm('Supprimer ce document ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="app-text-link app-text-link--danger">Supprimer</button>
                            </form>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@else
    <div class="app-card app-mt">
        <p class="app-muted app-mb-0">Aucun document pour l’instant.@if (! empty($documentUploadKinds)) Utilisez le formulaire ci-dessus pour déposer vos pièces.@endif</p>
    </div>
@endif
