@php
    $initials = static function (?string $name): string {
        $name = trim((string) $name);
        if ($name === '') {
            return '?';
        }
        $parts = preg_split('/\s+/u', $name) ?: [];
        if (count($parts) >= 2) {
            return mb_strtoupper(mb_substr($parts[0], 0, 1).mb_substr($parts[1], 0, 1));
        }

        return mb_strtoupper(mb_substr($name, 0, 1));
    };
    $formatDate = function ($iso) {
        if (empty($iso)) return '—';
        try {
            $c = \Carbon\Carbon::parse($iso)->locale('fr');
            if ($c->isToday()) return $c->translatedFormat('H:i');
            if ($c->isYesterday()) return 'Hier, ' . $c->translatedFormat('H:i');
            return $c->translatedFormat('d M, H:i');
        } catch (\Throwable) { return (string) $iso; }
    };
    $peerName = null;
    if (($peerId ?? 0) > 0) {
        foreach ($conversations as $p) {
            if ((int) ($p['id'] ?? 0) === (int) ($peerId ?? 0)) {
                $peerName = $p['name'] ?? null;
                break;
            }
        }
    }
@endphp

<div class="msn"
     id="msn-root"
     data-send-url="{{ route('app.'.$profileSlug.'.messages.send') }}"
     data-thread-url="{{ route('app.'.$profileSlug.'.messages.thread') }}"
     data-messages-url="{{ route('app.'.$profileSlug.'.messages') }}"
     data-user-id="{{ auth()->id() }}"
     data-peer-id="{{ (int) ($peerId ?? 0) }}">
    @php
        $msnShowFilters = ($profileSlug ?? '') === 'particulier';
        $msnFilterCounts = [
            'tous' => count($conversations ?? []),
            'entreprise' => 0,
            'fournisseurs' => 0,
            'artisans' => 0,
            'particulier' => 0,
        ];
        foreach ($conversations ?? [] as $msnPeer) {
            $msnPt = (string) ($msnPeer['profile_type'] ?? '');
            $msnKey = match ($msnPt) {
                'entrepreneur_batiment' => 'entreprise',
                'entreprise_fournisseur' => 'fournisseurs',
                'artisan' => 'artisans',
                'particulier' => 'particulier',
                default => null,
            };
            if ($msnKey !== null) {
                $msnFilterCounts[$msnKey]++;
            }
        }
        $msnChipLabel = static function (string $base, string $key) use ($msnFilterCounts): string {
            $n = (int) ($msnFilterCounts[$key] ?? 0);

            return $key === 'tous' || $n > 0 ? $base.' ('.$n.')' : $base;
        };
    @endphp
    <aside class="msn__sidebar" aria-label="Conversations">
        <div class="msn__sidebar-head">
            <h2 class="msn__sidebar-title">Discussions</h2>
        </div>
        @if ($msnShowFilters)
            <div class="msn__filters app-chip-row devis-chips__row" role="group" aria-label="Filtrer les conversations" id="msn-filters">
                <button type="button" class="app-chip is-active" data-msn-filter="tous">{{ $msnChipLabel('Tous', 'tous') }}</button>
                <button type="button" class="app-chip" data-msn-filter="entreprise">{{ $msnChipLabel('Entreprise', 'entreprise') }}</button>
                <button type="button" class="app-chip" data-msn-filter="fournisseurs">{{ $msnChipLabel('Fournisseurs', 'fournisseurs') }}</button>
                <button type="button" class="app-chip" data-msn-filter="artisans">{{ $msnChipLabel('Artisans', 'artisans') }}</button>
                <button type="button" class="app-chip" data-msn-filter="particulier">{{ $msnChipLabel('Particulier', 'particulier') }}</button>
            </div>
        @endif
        <div class="msn__thread-list" id="msn-thread-list">
            @forelse ($conversations as $p)
                @php
                    $pid = (int) ($p['id'] ?? 0);
                    $isActive = ($peerId ?? 0) === $pid;
                    $pt = (string) ($p['profile_type'] ?? '');
                    $msnFilterKey = match ($pt) {
                        'entrepreneur_batiment' => 'entreprise',
                        'entreprise_fournisseur' => 'fournisseurs',
                        'artisan' => 'artisans',
                        'particulier' => 'particulier',
                        default => '',
                    };
                @endphp
                <a href="{{ route('app.'.$profileSlug.'.messages') }}?peer_id={{ $pid }}"
                   class="msn__thread {{ $isActive ? 'is-active' : '' }}"
                   data-peer-id="{{ $pid }}"
                   data-peer-name="{{ e($p['name'] ?? '') }}"
                   data-profile-filter="{{ $msnFilterKey }}">
                    <span class="msn__avatar" aria-hidden="true">{{ $initials($p['name'] ?? '') }}</span>
                    <span class="msn__thread-body">
                        <span class="msn__thread-name">{{ $p['name'] ?? '—' }}</span>
                        <span class="msn__thread-meta">{{ $p['profile_type'] ?? '' }}</span>
                    </span>
                </a>
            @empty
                <p class="msn__empty-sidebar app-muted">Aucune conversation. Ouvrez une fiche prestataire ou une annonce pour démarrer un échange.</p>
            @endforelse
        </div>
    </aside>

    <section class="msn__panel" id="msn-panel" aria-label="Fil de messages">
        @if (($peerId ?? 0) > 0)
            <header class="msn__chatbar">
                <span class="msn__avatar msn__avatar--lg" aria-hidden="true">{{ $initials($peerName) }}</span>
                <div class="msn__chatbar-info">
                    <span class="msn__chatbar-name" id="msn-peer-name">{{ $peerName ?? 'Contact' }}</span>
                    <span class="msn__chatbar-sub">Message privé BatiTravoo</span>
                </div>
            </header>

            <div class="msn__stream" id="msn-stream" role="log" aria-live="polite">
                @if (! empty($thread['data']))
                    @foreach ($thread['data'] as $m)
                        @php $isMe = (int) ($m['sender_id'] ?? 0) === (int) auth()->id(); @endphp
                        <div class="msn__row {{ $isMe ? 'msn__row--me' : 'msn__row--them' }}">
                            @unless ($isMe)
                                <span class="msn__avatar msn__avatar--xs" aria-hidden="true">{{ $initials($peerName) }}</span>
                            @endunless
                            <div class="msn__bubble {{ $isMe ? 'msn__bubble--sent' : 'msn__bubble--recv' }}">
                                <div class="msn__bubble-text">{{ $m['body'] ?? '—' }}</div>
                                @if (! empty($m['attachment_url']))
                                    <a href="{{ $m['attachment_url'] }}" class="msn__attach" target="_blank" rel="noopener">@include('app.partials.app-nav-icon', ['name' => 'paperclip'])<span>Pièce jointe</span></a>
                                @endif
                                <time class="msn__time" datetime="{{ $m['created_at'] ?? '' }}">{{ $formatDate($m['created_at'] ?? '') }}</time>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="msn__empty-stream app-muted" id="msn-empty-hint">Pas encore de message. Envoyez le premier.</p>
                @endif
            </div>

            <form method="post" action="{{ route('app.'.$profileSlug.'.messages.send') }}" enctype="multipart/form-data" class="msn__composer" id="msn-composer-form">
                @csrf
                <input type="hidden" name="receiver_id" value="{{ $peerId }}" id="msn-receiver-id">
                <div class="msn__composer-inner">
                    <label class="msn__visually-hidden" for="msg_body">Message</label>
                    <textarea name="body" id="msg_body" rows="1" maxlength="20000" placeholder="Écrire un message…" class="msn__input"></textarea>
                    <label class="msn__clip" title="Joindre un fichier">
                        <input type="file" name="attachment" id="msg_attachment" class="msn__clip-input" accept="image/*,.pdf,.doc,.docx">
                        <span class="msn__clip-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
                        </span>
                    </label>
                    <button type="submit" class="msn__send" aria-label="Envoyer" id="msn-send-btn">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                    </button>
                </div>
                <div class="msn__composer-error-wrap" id="msn-composer-errors"></div>
            </form>
        @else
            <div class="msn__placeholder" id="msn-placeholder">
                <div class="msn__placeholder-icon" aria-hidden="true">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                </div>
                <p class="msn__placeholder-title">Sélectionnez une discussion</p>
                <p class="app-muted">Choisissez un contact dans la liste pour afficher vos messages.</p>
            </div>
        @endif
    </section>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var root = document.getElementById('msn-root');
    if (!root) return;

    var panel = document.getElementById('msn-panel');
    var threadList = document.getElementById('msn-thread-list');
    var sendUrl = root.dataset.sendUrl;
    var threadUrl = root.dataset.threadUrl;
    var messagesUrl = root.dataset.messagesUrl;
    var userId = parseInt(root.dataset.userId, 10) || 0;
    var currentPeerId = parseInt(root.dataset.peerId, 10) || 0;
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrf ? csrf.getAttribute('content') : '';
    var sending = false;

    function initials(name) {
        name = (name || '').trim();
        if (!name) return '?';
        var parts = name.split(/\s+/).filter(Boolean);
        if (parts.length >= 2) {
            return (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase();
        }
        return name.charAt(0).toUpperCase();
    }

    function formatDate(iso) {
        if (!iso) return '—';
        try {
            var d = new Date(iso);
            if (isNaN(d.getTime())) return iso;
            var now = new Date();
            var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
            var time = pad(d.getHours()) + ':' + pad(d.getMinutes());
            var sameDay = d.toDateString() === now.toDateString();
            var yesterday = new Date(now);
            yesterday.setDate(yesterday.getDate() - 1);
            if (sameDay) return time;
            if (d.toDateString() === yesterday.toDateString()) return 'Hier, ' + time;
            var months = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
            return d.getDate() + ' ' + months[d.getMonth()] + ', ' + time;
        } catch (e) {
            return iso;
        }
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function scrollStream() {
        var stream = document.getElementById('msn-stream');
        if (stream) stream.scrollTop = stream.scrollHeight;
    }

    function setActiveThread(peerId) {
        if (!threadList) return;
        threadList.querySelectorAll('.msn__thread').forEach(function (link) {
            var id = parseInt(link.dataset.peerId, 10);
            link.classList.toggle('is-active', id === peerId);
        });
    }

    function renderMessageRow(msg, peerName) {
        var isMe = parseInt(msg.sender_id, 10) === userId;
        var row = document.createElement('div');
        row.className = 'msn__row ' + (isMe ? 'msn__row--me' : 'msn__row--them');

        if (!isMe) {
            var av = document.createElement('span');
            av.className = 'msn__avatar msn__avatar--xs';
            av.setAttribute('aria-hidden', 'true');
            av.textContent = initials(peerName);
            row.appendChild(av);
        }

        var bubble = document.createElement('div');
        bubble.className = 'msn__bubble ' + (isMe ? 'msn__bubble--sent' : 'msn__bubble--recv');

        if (msg.body) {
            var text = document.createElement('div');
            text.className = 'msn__bubble-text';
            text.textContent = msg.body;
            bubble.appendChild(text);
        }

        if (msg.attachment_url) {
            var attach = document.createElement('a');
            attach.href = msg.attachment_url;
            attach.className = 'msn__attach';
            attach.target = '_blank';
            attach.rel = 'noopener';
            attach.innerHTML = '<span>Pièce jointe</span>';
            bubble.appendChild(attach);
        }

        var time = document.createElement('time');
        time.className = 'msn__time';
        time.setAttribute('datetime', msg.created_at || '');
        time.textContent = formatDate(msg.created_at);
        bubble.appendChild(time);

        row.appendChild(bubble);
        return row;
    }

    function renderMessages(messages, peerName) {
        var stream = document.createElement('div');
        stream.className = 'msn__stream';
        stream.id = 'msn-stream';
        stream.setAttribute('role', 'log');
        stream.setAttribute('aria-live', 'polite');

        if (!messages || !messages.length) {
            var empty = document.createElement('p');
            empty.className = 'msn__empty-stream app-muted';
            empty.id = 'msn-empty-hint';
            empty.textContent = 'Pas encore de message. Envoyez le premier.';
            stream.appendChild(empty);
        } else {
            messages.forEach(function (msg) {
                stream.appendChild(renderMessageRow(msg, peerName));
            });
        }

        return stream;
    }

    function renderComposer(peerId) {
        var form = document.createElement('form');
        form.className = 'msn__composer';
        form.id = 'msn-composer-form';
        form.setAttribute('enctype', 'multipart/form-data');
        form.innerHTML =
            '<input type="hidden" name="_token" value="' + escapeHtml(csrfToken) + '">' +
            '<input type="hidden" name="receiver_id" value="' + peerId + '" id="msn-receiver-id">' +
            '<div class="msn__composer-inner">' +
                '<label class="msn__visually-hidden" for="msg_body">Message</label>' +
                '<textarea name="body" id="msg_body" rows="1" maxlength="20000" placeholder="Écrire un message…" class="msn__input"></textarea>' +
                '<label class="msn__clip" title="Joindre un fichier">' +
                    '<input type="file" name="attachment" id="msg_attachment" class="msn__clip-input" accept="image/*,.pdf,.doc,.docx">' +
                    '<span class="msn__clip-icon" aria-hidden="true">' +
                        '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>' +
                    '</span>' +
                '</label>' +
                '<button type="submit" class="msn__send" aria-label="Envoyer" id="msn-send-btn">' +
                    '<svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>' +
                '</button>' +
            '</div>' +
            '<div class="msn__composer-error-wrap" id="msn-composer-errors"></div>';
        return form;
    }

    function renderChatPanel(peer, messages) {
        var peerName = peer.name || 'Contact';
        panel.innerHTML = '';

        var header = document.createElement('header');
        header.className = 'msn__chatbar';
        header.innerHTML =
            '<span class="msn__avatar msn__avatar--lg" aria-hidden="true">' + escapeHtml(initials(peerName)) + '</span>' +
            '<div class="msn__chatbar-info">' +
                '<span class="msn__chatbar-name" id="msn-peer-name">' + escapeHtml(peerName) + '</span>' +
                '<span class="msn__chatbar-sub">Message privé BatiTravoo</span>' +
            '</div>';
        panel.appendChild(header);
        panel.appendChild(renderMessages(messages, peerName));
        panel.appendChild(renderComposer(peer.id));

        scrollStream();
    }

    function showComposerError(message) {
        var wrap = document.getElementById('msn-composer-errors');
        if (!wrap) return;
        wrap.innerHTML = message
            ? '<div class="app-error msn__composer-error" role="alert">' + escapeHtml(message) + '</div>'
            : '';
    }

    function loadThread(peerId, peerNameHint) {
        if (!peerId || peerId === currentPeerId) return;

        panel.innerHTML = '<p class="msn__loading app-muted" style="padding:1.5rem;text-align:center;">Chargement…</p>';

        fetch(threadUrl + '?peer_id=' + encodeURIComponent(peerId), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
        .then(function (result) {
            if (!result.ok) {
                throw new Error(result.data.message || 'Impossible de charger la discussion.');
            }
            currentPeerId = peerId;
            root.dataset.peerId = String(peerId);
            setActiveThread(peerId);
            renderChatPanel(result.data.peer || { id: peerId, name: peerNameHint || 'Contact' }, result.data.messages || []);
            var url = messagesUrl + (messagesUrl.indexOf('?') >= 0 ? '&' : '?') + 'peer_id=' + peerId;
            history.pushState({ peerId: peerId }, '', url);
        })
        .catch(function (err) {
            panel.innerHTML =
                '<div class="msn__placeholder">' +
                    '<p class="app-error" style="padding:1.5rem;">' + escapeHtml(err.message || 'Erreur de chargement.') + '</p>' +
                '</div>';
        });
    }

    function appendMessage(msg, peerName) {
        var stream = document.getElementById('msn-stream');
        if (!stream) return;

        var hint = document.getElementById('msn-empty-hint');
        if (hint) hint.remove();

        stream.appendChild(renderMessageRow(msg, peerName));
        scrollStream();
    }

    function handleSend(event) {
        var form = event.target.closest('#msn-composer-form');
        if (!form || sending) return;
        event.preventDefault();

        var bodyInput = form.querySelector('#msg_body');
        var fileInput = form.querySelector('#msg_attachment');
        var bodyVal = (bodyInput && bodyInput.value) ? bodyInput.value.trim() : '';
        var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;

        if (!bodyVal && !hasFile) {
            showComposerError('Saisissez un message ou joignez un fichier.');
            return;
        }

        showComposerError('');
        sending = true;
        var sendBtn = form.querySelector('#msn-send-btn');
        if (sendBtn) sendBtn.disabled = true;

        var formData = new FormData(form);

        fetch(sendUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
        .then(function (result) {
            if (!result.ok) {
                var errMsg = result.data.message
                    || (result.data.errors && result.data.errors.body && result.data.errors.body[0])
                    || 'Envoi impossible.';
                showComposerError(errMsg);
                return;
            }

            var peerNameEl = document.getElementById('msn-peer-name');
            var peerName = peerNameEl ? peerNameEl.textContent : '';
            if (result.data.data) {
                appendMessage(result.data.data, peerName);
            }

            if (bodyInput) bodyInput.value = '';
            if (fileInput) fileInput.value = '';
            showComposerError('');
        })
        .catch(function () {
            showComposerError('Erreur réseau. Réessayez.');
        })
        .finally(function () {
            sending = false;
            if (sendBtn) sendBtn.disabled = false;
        });
    }

    if (threadList) {
        threadList.addEventListener('click', function (event) {
            var link = event.target.closest('.msn__thread');
            if (!link) return;
            event.preventDefault();
            var peerId = parseInt(link.dataset.peerId, 10);
            if (!peerId) return;
            loadThread(peerId, link.dataset.peerName || '');
        });
    }

    panel.addEventListener('submit', handleSend);

    window.addEventListener('popstate', function (event) {
        var peerId = event.state && event.state.peerId ? parseInt(event.state.peerId, 10) : 0;
        if (peerId > 0) {
            currentPeerId = 0;
            loadThread(peerId, '');
        } else {
            currentPeerId = 0;
            root.dataset.peerId = '0';
            setActiveThread(0);
            panel.innerHTML =
                '<div class="msn__placeholder" id="msn-placeholder">' +
                    '<div class="msn__placeholder-icon" aria-hidden="true">' +
                        '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>' +
                    '</div>' +
                    '<p class="msn__placeholder-title">Sélectionnez une discussion</p>' +
                    '<p class="app-muted">Choisissez un contact dans la liste pour afficher vos messages.</p>' +
                '</div>';
        }
    });

    if (currentPeerId > 0) {
        history.replaceState({ peerId: currentPeerId }, '', window.location.href);
    }

    var filtersBar = document.getElementById('msn-filters');
    var activeMsnFilter = 'tous';

    function applyMsnFilter(filter) {
        activeMsnFilter = filter || 'tous';
        if (filtersBar) {
            filtersBar.querySelectorAll('[data-msn-filter]').forEach(function (btn) {
                btn.classList.toggle('is-active', btn.dataset.msnFilter === activeMsnFilter);
            });
        }
        if (!threadList) return;
        var visible = 0;
        threadList.querySelectorAll('.msn__thread').forEach(function (link) {
            var key = link.dataset.profileFilter || '';
            var show = activeMsnFilter === 'tous' || key === activeMsnFilter;
            link.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        var emptyEl = document.getElementById('msn-filter-empty');
        if (activeMsnFilter !== 'tous' && visible === 0) {
            if (!emptyEl) {
                emptyEl = document.createElement('p');
                emptyEl.id = 'msn-filter-empty';
                emptyEl.className = 'msn__empty-sidebar app-muted';
                emptyEl.textContent = 'Aucune conversation pour ce filtre.';
                threadList.appendChild(emptyEl);
            }
        } else if (emptyEl) {
            emptyEl.remove();
        }
    }

    if (filtersBar) {
        filtersBar.addEventListener('click', function (event) {
            var btn = event.target.closest('[data-msn-filter]');
            if (!btn) return;
            event.preventDefault();
            applyMsnFilter(btn.dataset.msnFilter);
        });
        applyMsnFilter('tous');
    }

    scrollStream();
});
</script>
@endpush
