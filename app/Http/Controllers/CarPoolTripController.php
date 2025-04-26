<?php

namespace App\Http\Controllers;

use App\Models\CarpoolTrip;
use App\Models\Image;
use Illuminate\Http\Request;
use App\Models\Property;

use Illuminate\Support\Facades\File;
use App\Models\CarPoolTripDto;
use App\Models\CarpoolTripBooking;
use App\Models\CarpoolTripBookingDto;
use App\Models\CarpoolTripNotice;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CarPoolTripController extends Controller
{
    public function index(Request $request)
    {
        $carpoolTrips = CarpoolTrip::where("user_id", "!=", auth()->id())->with('driver')
            ->when($request->filled('departure'), fn($q) => $q->whereRaw('LOWER(departure) = ?', [Str::lower($request->departure)]))
            ->when($request->filled('arrival'), fn($q) => $q->whereRaw('LOWER(arrival) = ?', [Str::lower($request->arrival)]))
            ->when($request->filled('availableSeats'), fn($q) => $q->where('availableSeats', '>=', $request->availableSeats))
            ->when($request->filled('date'), function ($q) use ($request) {
                $selectedDate = Carbon::parse($request->date)->startOfDay();
                $today = now()->startOfDay();

                $q->whereDate('date', $selectedDate->toDateString());

                // Si la date est aujourd'hui, on vérifie aussi l'heure
                if ($selectedDate->equalTo($today)) {
                    $q->whereTime('date', '>=', now()->format('H:i:s'));
                }

                return $q;
            })
            ->orderBy('date', 'asc')
            ->get();

        // Map vers DTO
        $dtos = $carpoolTrips->map(function ($trip) {
            $start = Carbon::parse($trip->date);
            $hours = floor($trip->duration);
            $minutes = ($trip->duration - $hours) * 60;

            $end = $start->copy()->addHours($hours)->addMinutes($minutes);

            return new CarPoolTripDto(
                id: $trip->id,
                user_id: $trip->user_id,
                departure: $trip->departure,
                arrival: $trip->arrival,
                date: Carbon::parse($trip->date)->toDateString(),
                start: $start->format('H:i'),
                end: $end->format('H:i'),
                price: $trip->price,
                petsAllowed: $trip->petsAllowed,
                smokingAllowed: $trip->smokingAllowed,
                driverFullName: trim(($trip->driver->firstName ?? '') . ' ' . ($trip->driver->lastName ?? '')),
                rate: $trip->driver->rate ?? 0,
                duration: $trip->duration,
                driverGender: $trip->driver->gender
            );
        });

        // Return the transformed DTOs as JSON
        return response()->json($dtos);
    }
    public function getById($id)
    {
        $carpoolTrip = CarpoolTrip::where('id', '=', $id)->with(['driver', 'passengers'])->first();

        // Map vers DTO
        $start = Carbon::parse($carpoolTrip->date);
        $hours = floor($carpoolTrip->duration);
        $minutes = ($carpoolTrip->duration - $hours) * 60;

        $end = $start->copy()->addHours($hours)->addMinutes($minutes);
        $passengers = $carpoolTrip->passengers->map(function ($passenger) {
            return [
                'fullName' => trim(($passenger->firstName ?? '') . ' ' . ($passenger->lastName ?? '')),
                'gender' => $passenger->gender,
            ];
        })->toArray();
        $dto = new CarPoolTripDto(
            id: $carpoolTrip->id,
            user_id: $carpoolTrip->user_id,
            departure: $carpoolTrip->departure,
            arrival: $carpoolTrip->arrival,
            date: Carbon::parse($carpoolTrip->date)->toDateString(),
            start: $start->format('H:i'),
            end: $end->format('H:i'),
            price: $carpoolTrip->price,
            petsAllowed: $carpoolTrip->petsAllowed,
            smokingAllowed: $carpoolTrip->smokingAllowed,
            driverFullName: trim(($carpoolTrip->driver->firstName ?? '') . ' ' . ($carpoolTrip->driver->lastName ?? '')),
            passengers: $passengers,
            rate: $carpoolTrip->driver->rate ?? 0,
            duration: $carpoolTrip->duration,
            driverGender:$carpoolTrip->driver->gender

        );

        // Return the transformed DTOs as JSON
        return response()->json($dto);
    }


    public function store(Request $request)
    {
        $data = $request->all();
        $data['user_id'] = auth()->id();
        $data['date'] = Carbon::parse($data['date'])->format('Y-m-d H:i:s');
        $carpoolTrip = CarpoolTrip::create($data);
        return response()->json($carpoolTrip, 201);
    }
    public function addBookingRequest(Request $request)
    {
        $data = $request->all();
        $data['passenger_id'] = auth()->id();

        DB::beginTransaction(); // 🔁 Début de la transaction

        try {
            $carpoolTrip = CarpoolTrip::where("id", $data['car_pool_trip_id'])->lockForUpdate()->first();

            if (!$carpoolTrip) {
                throw new \Exception("Trajet non trouvé.");
            }

            if ($carpoolTrip->availableSeats < $data['total_seats']) {
                throw new \Exception("Nombre de places insuffisantes.");
            }

            // Mise à jour des places disponibles
            $carpoolTrip->availableSeats -= $data['total_seats'];
            $carpoolTrip->save();

            // Création de la réservation
            $carpoolTripBooking = CarpoolTripBooking::create($data);

            DB::commit(); // Tout est bon, on valide les changements

            return response()->json($carpoolTripBooking, 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Une erreur, on annule tout

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function getMyBookingRequests()
    {
        $bookings = CarpoolTripBooking::with(['passenger', 'carTrip.driver'])
            ->where('driver_id', auth()->id())
            ->where('status', '=', 'pending')
            ->get();
        $dtos = $bookings->map(function ($booking) {
            $start = Carbon::parse($booking->carTrip->date);
            $hours = floor($booking->carTrip->duration);
            $minutes = ($booking->carTrip->duration - $hours) * 60;

            $end = $start->copy()->addHours($hours)->addMinutes($minutes);

            return new CarPoolTripBookingDto(
                id: $booking->id,
                carPoolTripId : $booking->carTrip->id,
                passengerId : $booking->passenger->id,
                driverId : auth()->id(),
                departure: $booking->carTrip->departure,
                arrival: $booking->carTrip->arrival,
                date: Carbon::parse($booking->carTrip->date)->toDateString(),
                start: $start->format('H:i'),
                end: $end->format('H:i'),
                totalPrice: $booking->carTrip->price * $booking->total_seats,
                status: $booking->status,
                totalSeats: $booking->total_seats,
                driverFullName: trim(($trip->driver->firstName ?? '') . ' ' . ($trip->driver->lastName ?? '')),
                passengerFullName: trim(($booking->passenger->firstName ?? '') . ' ' . ($booking->passenger->lastName ?? '')),
                gender:$booking->passenger->gender
            );
        });

        return response()->json($dtos);
    }

    public function getMyBookings()
    {
        $bookings = CarpoolTripBooking::with(['passenger', 'carTrip.driver'])
            ->where('passenger_id', auth()->id())
            ->get();
        $existingNotices = CarpoolTripNotice::where('passenger_id', auth()->id())->get()
            ->pluck('car_pool_trip_id')
            ->toArray();

        $dtos = $bookings->map(function ($booking) use ($existingNotices) {
            $start = Carbon::parse($booking->carTrip->date);
            $hours = floor($booking->carTrip->duration);
            $minutes = ($booking->carTrip->duration - $hours) * 60;

            $end = $start->copy()->addHours($hours)->addMinutes($minutes);
            $canComment = $booking->status === 'confirmed'
                && now()->greaterThan($end)
                && !in_array($booking->carTrip->id, $existingNotices);
            return new CarPoolTripBookingDto(
                id: $booking->id,
                carPoolTripId : $booking->carTrip->id,
                passengerId : auth()->id(),
                driverId : $booking->carTrip->driver->id,
                departure: $booking->carTrip->departure,
                arrival: $booking->carTrip->arrival,
                date: Carbon::parse($booking->carTrip->date)->toDateString(),
                start: $start->format('H:i'),
                end: $end->format('H:i'),
                totalPrice: $booking->carTrip->price * $booking->total_seats,
                status: $booking->status,
                totalSeats: $booking->total_seats,
                driverFullName: trim(($booking->driver->firstName ?? '') . ' ' . ($booking->driver->lastName ?? '')),
                passengerFullName: trim(($booking->passenger->firstName ?? '') . ' ' . ($booking->passenger->lastName ?? '')),
                canComment: $canComment,
                gender: $booking->driver->gender
            );
        });
        return response()->json($dtos);
    }
    public function acceptBookingRequest($id)
    {
        $carpoolTripBookingRequest = CarpoolTripBooking::where("id", $id)->lockForUpdate()->first();

        try {

            if (!$carpoolTripBookingRequest) {
                throw new \Exception("Reservation non trouvée.");
            }
            $carpoolTrip = CarpoolTrip::where("id", $carpoolTripBookingRequest->car_pool_trip_id)->lockForUpdate()->first();

            if (!$carpoolTrip) {
                throw new \Exception("Trajet non trouvé.");
            }

            // Mise à jour des places disponibles
            $carpoolTrip->passengers()->syncWithoutDetaching([$carpoolTripBookingRequest->passenger_id]);
            $carpoolTrip->save();
            $carpoolTripBookingRequest->status = 'confirmed';
            $carpoolTripBookingRequest->save();

            DB::commit(); // Tout est bon, on valide les changements

            return response()->json($carpoolTripBookingRequest, 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Une erreur, on annule tout

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function refuseBookingRequest($id)
    {
        $carpoolTripBookingRequest = CarpoolTripBooking::where("id", $id)->lockForUpdate()->first();

        DB::beginTransaction(); // Début de la transaction

        try {

            if (!$carpoolTripBookingRequest) {
                throw new \Exception("Reservation non trouvée.");
            }
            $carpoolTrip = CarpoolTrip::where("id", $carpoolTripBookingRequest->car_pool_trip_id)->lockForUpdate()->first();

            if (!$carpoolTrip) {
                throw new \Exception("Trajet non trouvé.");
            }

            // Mise à jour des places disponibles
            $carpoolTrip->availableSeats += $carpoolTripBookingRequest->total_seats;
            $carpoolTrip->save();
            $carpoolTripBookingRequest->status = 'refused';
            $carpoolTripBookingRequest->save();

            DB::commit(); // Tout est bon, on valide les changements

            return response()->json($carpoolTripBookingRequest, 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Une erreur, on annule tout

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
