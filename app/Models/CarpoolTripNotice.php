<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarpoolTripNotice  extends Model
{
    use HasFactory;
    protected $fillable = [
        'driver_id', 'passenger_id', 'car_pool_trip_id', 'rate', 'comment'
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function passenger()
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }
    public function carTrip()
    {
        return $this->belongsTo(CarpoolTrip::class, 'car_pool_trip_id');
    }
}
