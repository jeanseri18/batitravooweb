<?php

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Models\InAppNotification;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function conversationPartners(Request $request): JsonResponse
    {
        $u = $request->user();
        $out = Message::query()->where('sender_id', $u->id)->pluck('receiver_id');
        $in = Message::query()->where('receiver_id', $u->id)->pluck('sender_id');
        $ids = $out->merge($in)->unique()->filter(fn ($id) => (int) $id > 0)->values();
        if ($ids->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $peers = User::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get()
            ->map(fn (User $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'profile_type' => $p->profile_type,
                'unread_count' => (int) Message::query()
                    ->where('receiver_id', $u->id)
                    ->where('sender_id', $p->id)
                    ->whereNull('read_at')
                    ->count(),
            ]);

        return response()->json([
            'data' => $peers,
            'meta' => [
                'unread_count' => $this->totalUnreadFor($u),
            ],
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $u = $request->user();

        return response()->json([
            'unread_count' => $this->totalUnreadFor($u),
        ]);
    }

    private function totalUnreadFor(User $u): int
    {
        return (int) Message::query()
            ->where('receiver_id', $u->id)
            ->whereNull('read_at')
            ->count();
    }

    public function index(Request $request): JsonResponse
    {
        $u = $request->user();
        $peerId = (int) $request->query('peer_id', 0);
        abort_unless($peerId > 0, 404);

        $q = Message::query()
            ->where(function ($b) use ($u, $peerId) {
                $b->where('sender_id', $u->id)->where('receiver_id', $peerId);
            })
            ->orWhere(function ($b) use ($u, $peerId) {
                $b->where('sender_id', $peerId)->where('receiver_id', $u->id);
            });

        $items = $q->orderBy('id')->get()->map(fn (Message $m) => $this->row($m));

        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $u = $request->user();
        $data = $request->validate([
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
            'body' => ['nullable', 'string', 'max:20000'],
            'attachment' => ['nullable', 'file', 'max:12288'],
        ]);

        $receiverId = (int) $data['receiver_id'];
        $bodyTrim = trim((string) ($data['body'] ?? ''));

        $uploaded = $request->file('attachment');
        $attachmentPath = null;
        $attachmentOriginal = null;
        if ($uploaded !== null && $uploaded->isValid()) {
            $attachmentPath = $uploaded->store('message-attachments', 'public');
            $attachmentOriginal = $uploaded->getClientOriginalName();
        }

        if ($bodyTrim === '' && $attachmentPath === null) {
            return response()->json(['message' => 'Message ou pièce jointe requis.'], 422);
        }

        if ($receiverId === (int) $u->id) {
            return response()->json(['message' => 'Destinataire invalide.'], 422);
        }

        $other = User::query()->findOrFail($receiverId);
        if ($other->isAdmin() || ! $other->is_active) {
            return response()->json(['message' => 'Destinataire indisponible.'], 422);
        }

        $m = Message::query()->create([
            'sender_id' => $u->id,
            'receiver_id' => $receiverId,
            'body' => $bodyTrim,
            'attachment_path' => $attachmentPath,
            'attachment_original_name' => $attachmentOriginal,
        ]);

        $preview = $bodyTrim !== ''
            ? (mb_strlen($bodyTrim) > 120 ? mb_substr($bodyTrim, 0, 117).'…' : $bodyTrim)
            : ($attachmentOriginal ?? 'Pièce jointe');
        InAppNotification::query()->create([
            'user_id' => (int) $data['receiver_id'],
            'type' => InAppNotification::TYPE_MESSAGE,
            'title' => 'Nouveau message',
            'body' => $u->name.' : '.$preview,
            'data' => [
                'message_id' => $m->id,
                'sender_id' => $u->id,
            ],
        ]);

        return response()->json(['data' => $this->row($m)], 201);
    }

    public function markRead(Request $request): JsonResponse
    {
        $u = $request->user();
        $peerId = (int) $request->input('peer_id', 0);
        abort_unless($peerId > 0, 404);

        Message::query()
            ->where('receiver_id', $u->id)
            ->where('sender_id', $peerId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function row(Message $m): array
    {
        $attachmentUrl = null;
        if ($m->attachment_path) {
            $attachmentUrl = storage_public_url($m->attachment_path);
        }

        return [
            'id' => $m->id,
            'sender_id' => $m->sender_id,
            'receiver_id' => $m->receiver_id,
            'body' => $m->body ?? '',
            'attachment_url' => $attachmentUrl,
            'attachment_original_name' => $m->attachment_original_name,
            'read_at' => $m->read_at?->toIso8601String(),
            'created_at' => $m->created_at?->toIso8601String(),
        ];
    }
}
