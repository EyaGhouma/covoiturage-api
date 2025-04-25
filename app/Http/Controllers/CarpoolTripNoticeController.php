<?php
namespace App\Http\Controllers;

use App\Models\CarpoolTripNotice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CarpoolTripNoticeController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'driver_id' => 'required|exists:users,id',
            'car_pool_trip_id' => 'required|exists:carpool_trips,id',
            'rate' => 'required|numeric|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $data['passenger_id'] = Auth::id();

        DB::beginTransaction();

        try {
            // 1. Ajouter l'avis
            $notice = CarpoolTripNotice::create($data);

            // 2. Mettre à jour les infos du driver
            $driver = User::findOrFail($data['driver_id']);

            $driver->rateCount = $driver->rateCount ?? 0;
            $driver->rate = $driver->rate ?? 0;

            $driver->rateCount += 1;
            $driver->rate = ($driver->rate + $data['rate']) / $driver->rateCount;

            $driver->save();

            DB::commit();

            return response()->json([
                'message' => 'Commentaire ajouté avec succès.',
                'notice' => $notice,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Erreur lors de l\'ajout du commentaire.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
