<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'address',
        'phoneNumber',
        'password',
        'rate',
        'rateCount',
        'isActive',
        'isDrivingLicenseVerified'
    ];

    public function carpoolTripsAsDriver()
    {
        return $this->hasMany(CarpoolTrip::class, 'user_id');
    }

    public function carpoolTripsAsPassenger()
    {
        return $this->belongsToMany(CarpoolTrip::class, 'carpool_trip_user', 'user_id', 'carpool_trip_id');
    }

    public function noticesAsDriver()
    {
        return $this->hasMany(CarpoolTripNotice::class, 'driver_id');
    }

    public function noticesAsPassenger()
    {
        return $this->hasMany(CarpoolTripNotice::class, 'passenger_id');
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
            'isActive' => 'bool',
            'isDrivingLicenseVerified' => 'bool'
        ];
    }
}
