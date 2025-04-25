<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarpoolTrip  extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'departure', 'arrival', 'date', 'duration','availableSeats','totalSeats','luggageType',"petsAllowed","smokingAllowed","price"
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function passengers()
    {
        return $this->belongsToMany(User::class, 'carpool_trip_user', 'carpool_trip_id', 'user_id');
    }
    public function notices()
    {
        return $this->hasMany(CarpoolTripNotice::class, 'car_pool_trip_id');
    }
    protected function casts(): array
    {
        return [
            'petsAllowed' => 'bool',
            'smokingAllowed' => 'bool'
        ];
    }
}
