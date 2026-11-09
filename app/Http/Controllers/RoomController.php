<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $userProperties = Property::where('user_id', Auth::id())->pluck('id');
        $query = Room::whereIn('property_id', $userProperties)->with('property', 'activeTenant');
        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }
        $rooms = $query->latest()->get();
        $properties = Property::where('user_id', Auth::id())->get();
        return view('rooms.index', compact('rooms', 'properties'));
    }

    public function create()
    {
        $properties = Property::where('user_id', Auth::id())->get();
        if ($properties->isEmpty()) {
            return redirect()->route('properties.create')->with('error', 'Silakan buat properti kos terlebih dahulu sebelum menambahkan kamar.');
        }
        return view('rooms.create', compact('properties'));
    }

    public function store(Request $request)
    {
        $userPropertyIds = Property::where('user_id', Auth::id())->pluck('id')->toArray();
        $validated = $request->validate([
            'property_id' => ['required', 'in:' . implode(',', $userPropertyIds)],
            'room_number' => ['required', 'string', 'max:50'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,occupied'],
        ]);
        Room::create($validated);
        return redirect()->route('rooms.index')->with('success', 'Kamar berhasil ditambahkan.');
    }

    public function edit(Room $room)
    {
        if ($room->property->user_id !== Auth::id()) {
            abort(403);
        }

        $properties = Property::where('user_id', Auth::id())->get();
        return view('rooms.edit', compact('room', 'properties'));
    }

    public function update(Request $request, Room $room)
    {
        if ($room->property->user_id !== Auth::id()) {
            abort(403);
        }
        $userPropertyIds = Property::where('user_id', Auth::id())->pluck('id')->toArray();
        $validated = $request->validate([
            'property_id' => ['required', 'in:' . implode(',', $userPropertyIds)],
            'room_number' => ['required', 'string', 'max:50'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,occupied'],
        ]);
        $room->update($validated);
        return redirect()->route('rooms.index')->with('success', 'Kamar berhasil diupdate.');
    }

    public function destroy(Room $room)
    {
        if ($room->property->user_id !== Auth::id()) {
            abort(403);
        }
        $room->delete();
        return redirect()->route('rooms.index')->with('success', 'Kamar berhasil dihapus.');
    }
}
