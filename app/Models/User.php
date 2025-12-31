<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Roles
    public const ROLE_ADMIN = 'admin';
    public const ROLE_TECNICO = 'tecnico';
    public const ROLE_ATENCION = 'atencion';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
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

    // Role helpers
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isTecnico(): bool
    {
        return $this->role === self::ROLE_TECNICO;
    }

    public function isAtencion(): bool
    {
        return $this->role === self::ROLE_ATENCION;
    }

    //ticket helpers
    public function createdTickets()
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

}
