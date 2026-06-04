<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtisanBusinessCard extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'display_name',
        'profession',
        'experience_text',
        'price_on_request',
        'price_on_quote',
        'price_text',
        'services',
        'avail_immediate',
        'avail_appointment',
        'avail_unavailable',
        'location_text',
        'portfolio_path',
        'portfolio_paths',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_on_request' => 'boolean',
            'price_on_quote' => 'boolean',
            'avail_immediate' => 'boolean',
            'avail_appointment' => 'boolean',
            'avail_unavailable' => 'boolean',
            'services' => 'array',
            'portfolio_paths' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
