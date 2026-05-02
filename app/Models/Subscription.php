<?php

namespace App\Models;

use A17\Twill\Models\Behaviors\HasFiles;
use A17\Twill\Models\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Subscription extends Model
{
    use HasFiles;

    protected $fillable = [
        'published',
        'band',
        'nr_componenti',
        'eta_media',
        'citta',
        'genere',
        'durata',
        'referente',
        'telefono',
        'email',
        'video_file_path',
        'video_link',
        'privacy',
        'evento',
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

    protected function videoFileUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->video_file_path ? Storage::url($this->video_file_path) : null,
        );
    }
}
