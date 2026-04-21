<?php
// app/Models/Apoderado.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Apoderado extends Model
{
    
    protected $table = 'apoderados';
    
    protected $fillable = [
        'dni',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'direccion',
        'sexo',
        'telefono',
        'email',
        'parentesco',
        'recibe_notificaciones'
    ];
    
    protected $casts = [
        'recibe_notificaciones' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    // Constantes
    const SEXO_MASCULINO = 'M';
    const SEXO_FEMENINO = 'F';
    
    const SEXOS = [
        self::SEXO_MASCULINO => 'Masculino',
        self::SEXO_FEMENINO => 'Femenino',
    ];
    
    const PARENTESCO_PADRE = 'PADRE';
    const PARENTESCO_MADRE = 'MADRE';
    const PARENTESCO_TUTOR = 'TUTOR';
    const PARENTESCO_HERMANO = 'HERMANO';
    const PARENTESCO_ABUELO = 'ABUELO';
    const PARENTESCO_TIO = 'TIO';
    const PARENTESCO_OTRO = 'OTRO';
    
    const PARENTESCOS = [
        self::PARENTESCO_PADRE => 'Padre',
        self::PARENTESCO_MADRE => 'Madre',
        self::PARENTESCO_TUTOR => 'Tutor Legal',
        self::PARENTESCO_HERMANO => 'Hermano/a',
        self::PARENTESCO_ABUELO => 'Abuelo/a',
        self::PARENTESCO_TIO => 'Tío/a',
        self::PARENTESCO_OTRO => 'Otro',
    ];
    
    // Relaciones
    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(Alumno::class, 'alumno_apoderado', 'apoderado_id', 'alumno_id')
                    ->withPivot('parentesco', 'recibe_notificaciones', 'es_tutor', 'puede_retirar')
                    ->withTimestamps();
    }
    
    public function user()
    {
        return $this->morphOne(User::class, 'userable');
    }
    
    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'apoderado_id');
    }
    
    // Scopes
    public function scopeRecibeNotificaciones($query)
    {
        return $query->where('recibe_notificaciones', true);
    }
    
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nombres', 'LIKE', "%{$search}%")
              ->orWhere('apellido_paterno', 'LIKE', "%{$search}%")
              ->orWhere('apellido_materno', 'LIKE', "%{$search}%")
              ->orWhere('dni', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%")
              ->orWhere('telefono', 'LIKE', "%{$search}%");
        });
    }
    
    // Accessors
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->apellido_paterno} {$this->apellido_materno}, {$this->nombres}";
    }
    
    public function getNombreCompletoInversoAttribute(): string
    {
        return "{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}";
    }
    
    public function getSexoNombreAttribute(): string
    {
        return self::SEXOS[$this->sexo] ?? 'No especificado';
    }
    
    public function getParentescoNombreAttribute(): string
    {
        return self::PARENTESCOS[$this->parentesco] ?? $this->parentesco;
    }
    
    public function getCantidadAlumnosAttribute(): int
    {
        return $this->alumnos()->count();
    }
    
    // Helper methods
    public function esPadre(): bool
    {
        return $this->parentesco === self::PARENTESCO_PADRE;
    }
    
    public function esMadre(): bool
    {
        return $this->parentesco === self::PARENTESCO_MADRE;
    }
    
    public function esTutor(): bool
    {
        return $this->parentesco === self::PARENTESCO_TUTOR;
    }
    
    public function activarNotificaciones(): void
    {
        $this->update(['recibe_notificaciones' => true]);
    }
    
    public function desactivarNotificaciones(): void
    {
        $this->update(['recibe_notificaciones' => false]);
    }
}