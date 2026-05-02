<?php

namespace App\Models;

use A17\Twill\Models\Behaviors\HasFiles;
use A17\Twill\Models\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFiles;

    public $filesParams = ['video_file'];

    protected $fillable = [
        'published',
        'title',
        'band',
        'nr_componenti',
        'eta_media',
        'citta',
        'genere',
        'durata',
        'referente',
        'telefono',
        'email',
        'video_link',
        'privacy',
        'event_id',
        'data_iscrizione',
    ];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'privacy' => 'boolean',
            'nr_componenti' => 'integer',
            'durata' => 'integer',
            'data_iscrizione' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    protected function videoFileUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->file('video_file') ?: null,
        );
    }
}
