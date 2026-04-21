<?php
// app/Models/ConclusionDescriptiva.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConclusionDescriptiva extends Model
{
    protected $table = 'conclusiones_descriptivas';
    
    protected $fillable = [
        'nota_id',
        'conclusion'
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function nota(): BelongsTo
    {
        return $this->belongsTo(Nota::class, 'nota_id');
    }
}