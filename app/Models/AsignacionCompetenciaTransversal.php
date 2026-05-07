<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionCompetenciaTransversal extends Model
{
    protected $table = 'ct_asignaciones';
    
    protected $fillable = [
        'competencia_transversal_id',
        'user_id',
    ];
    
    public function competenciaTransversal(): BelongsTo
    {
        return $this->belongsTo(CompetenciaTransversal::class, 'competencia_transversal_id');
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
