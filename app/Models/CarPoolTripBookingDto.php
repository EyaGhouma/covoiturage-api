<?php

namespace App\Models;


class CarPoolTripBookingDto
{
    public int $id;
    public int $carPoolTripId;
    public int $passengerId;
    public int $driverId;
    public string $departure;
    public string $arrival;
    public string $date;
    public string $start;
    public string $end;
    public float $totalPrice;
    public string $driverFullName;
    public string $passengerFullName;
    public string $status;
    public int $totalSeats;
    public bool $canComment;

    // Constructor to initialize the DTO
    public function __construct(
        int $id,
        int $carPoolTripId,
        int $passengerId,
        int $driverId,
        string $departure,
        string $arrival,
        string $date,
        string $start,
        string $end,
        float $totalPrice,
        string $driverFullName,
        string $passengerFullName,
        string $status,
        int $totalSeats,
        bool $canComment = false
    ) {
        $this->id = $id;
        $this->carPoolTripId = $carPoolTripId;
        $this->passengerId = $passengerId;
        $this->driverId = $driverId;
        $this->departure = $departure;
        $this->arrival = $arrival;
        $this->date = $date;
        $this->start = $start;
        $this->end = $end;
        $this->totalPrice = $totalPrice;
        $this->passengerFullName = $passengerFullName;
        $this->status = $status;
        $this->driverFullName = $driverFullName;
        $this->totalSeats = $totalSeats;
        $this->canComment = $canComment;
    }
}
