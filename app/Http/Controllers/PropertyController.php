<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::where('user_id', Auth::id())->withCount('rooms')->latest()->get();
        return view('properties.index', compact('properties'));
    }

    public function create()
    {
        return view('properties.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
        ]);
        $validated['user_id'] = Auth::id();
        Property::create($validated);
        return redirect()->route('properties.index')->with('success', 'Properti berhasil ditambahkan');
    }

    public function edit(Property $property)
    {
        if($property->user_id !== Auth::id()) {
            abort(403);
        }
        return view('properties.edit', compact('property'));
    }

    public function update(Request $request, Property $property)
    {
        if($property->user_id !== Auth::id()){
            abort(403);
        }
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
        ]);
        $property->update($validated);
        return redirect()->route('properties.index')->with('success', 'Properti berhasil diupdate');
    }

    public function destroy(Property $property)
    {
        if($property->user_id !== Auth::id())
        {
            abort(403);
        }
        $property->delete();
        return redirect()->route('properties.index')->with('success', 'Properti berhasil dihapus');
    }
}
