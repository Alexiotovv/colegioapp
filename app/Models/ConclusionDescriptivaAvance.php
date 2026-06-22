<?php
// app/Models/ConclusionDescriptivaAvance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConclusionDescriptivaAvance extends Model
{
    protected $table = 'conclusiones_descriptivas_avance';
    
    protected $fillable = [
        'nota_avance_id',
        'conclusion'
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function notaAvance(): BelongsTo
    {
        return $this->belongsTo(AvanceNota::class, 'nota_avance_id');
    }
}