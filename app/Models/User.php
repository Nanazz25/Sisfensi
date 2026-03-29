<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Teacher;
use App\Models\PesertaDidik;
use App\Models\Assessment;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'initial_password',
        'password_changed',
        'role'
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function pesertaDidik()
    {
        return $this->hasOne(PesertaDidik::class);
    }
    public function getInitialsAttribute()
    {
        $words = explode(' ', $this->name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[count($words) - 1], 0, 1));
        }
        return strtoupper(substr($words[0], 0, 1));
    }

    public function getAvatarUrlAttribute()
    {
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&background=00bcd4&color=fff&bold=true&size=512";
    }
    public function receivedAssessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'evaluatee_id');
    }

    public function givenAssessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'evaluator_id');
    }
}
