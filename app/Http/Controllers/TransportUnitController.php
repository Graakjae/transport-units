<?php

namespace App\Http\Controllers;

use App\Models\TransportUnit;
use Illuminate\Http\Request;

class TransportUnitController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');
        $query = TransportUnit::query();

        if ($type) {
            $query->where('type', $type);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:trailer,truck',
        ]);

        $transportUnit = TransportUnit::create($validated);
        return response()->json($transportUnit, 201);
    }
}

