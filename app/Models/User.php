<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser; // <--- AJOUTÉ
use Filament\Panel; // <--- AJOUTÉ
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser // <--- MODIFIÉ
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Cette fonction est la clé ! Elle autorise l'accès au panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Pour l'instant, on autorise tout le monde pour débloquer ton accès.
        return true; 
    }
}
