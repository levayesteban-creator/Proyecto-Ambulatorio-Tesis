<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    // Permitimos que se guarde el nombre del rol
    protected $fillable = ['name'];

    /**
     * Un Rol tiene muchos Usuarios (Un médico, muchos usuarios con rol médico)
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
