<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    const ADMIN = 1;
    const COORDINATOR = 2;
    const DOCTOR = 3;
    const NURSE = 4;

    protected $fillable = ['name', 'descripcion'];

    /**
     * Un Rol tiene muchos Usuarios (Un médico, muchos usuarios con rol médico)
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
