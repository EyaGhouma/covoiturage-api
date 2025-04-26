<?php

namespace App\Models;


class CarPoolTripDto
{
    public int $id;
    public int $user_id;
    public string $departure;
    public string $arrival;
    public string $date;
    public string $start;
    public string $end;
    public float $price;
    public bool $petsAllowed;
    public bool $smokingAllowed;
    public string $driverFullName;
    public float $rate;
    public string $driverGender;
    public ?array $passengers;
    public ?float $duration;


    // Constructor to initialize the DTO
    public function __construct(
        int $id,
        int $user_id,
        string $departure,
        string $arrival,
        string $date,
        string $start,
        string $end,
        float $price,
        bool $petsAllowed,
        bool $smokingAllowed,
        string $driverFullName,
        float $rate,
        float $duration,
        string $driverGender,
        array $passengers = []
    ) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->departure = $departure;
        $this->arrival = $arrival;
        $this->date = $date;
        $this->start = $start;
        $this->end = $end;
        $this->price = $price;
        $this->petsAllowed = $petsAllowed;
        $this->smokingAllowed = $smokingAllowed;
        $this->driverFullName = $driverFullName;
        $this->rate = $rate;
        $this->passengers = $passengers;
        $this->duration = $duration;
        $this->driverGender = $driverGender;
    }
}
