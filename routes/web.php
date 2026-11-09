<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\TenantController;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        $user = Auth::user();
        $properties = Property::where('user_id', $user->id)->withCount('rooms')->latest()->get();
        $propertyIds = $properties->pluck('id');
        $rooms = Room::whereIn('property_id', $propertyIds)->with(['property', 'activeTenant'])->latest()->get();

        $totalProperties = $properties->count();
        $totalRooms = $rooms->count();
        $occupiedRooms = $rooms->where('status', 'occupied')->count();
        $availableRooms = $rooms->where('status', 'available')->count();
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0;
        $monthlyRevenue = $rooms->where('status', 'occupied')->sum('monthly_price');
        $potentialRevenue = $rooms->sum('monthly_price');

        $tenants = Tenant::whereHas('room', function ($q) use ($propertyIds) {
            $q->whereIn('property_id', $propertyIds);
        })->with(['room.property'])->latest()->get();
        $activeTenants = $tenants->where('status', 'active');
        $totalTenants = $activeTenants->count();

        return view('dashboard', compact(
            'properties',
            'rooms',
            'tenants',
            'activeTenants',
            'totalProperties',
            'totalRooms',
            'occupiedRooms',
            'availableRooms',
            'totalTenants',
            'occupancyRate',
            'monthlyRevenue',
            'potentialRevenue'
        ));
    })->name('dashboard');
    Route::resource('properties', PropertyController::class);
    Route::resource('rooms', RoomController::class);
    Route::resource('tenants', TenantController::class);
    Route::post('bills/generate', [BillController::class, 'generate'])->name('bills.generate');
    Route::post('bills/{bill}/mark-as-paid', [BillController::class, 'markAsPaid'])->name('bills.mark_as_paid');
    Route::post('/bills/{bill}/send-reminder', [BillController::class, 'sendReminder'])->name('bills.sendReminder');
    Route::resource('bills', BillController::class);
});

// Route akses publik untuk pembayaran/invoice penyewa (bisa dibuka via tautan WhatsApp)
Route::get('pay/{bill}', [BillController::class, 'show'])->name('bills.pay');
Route::get('invoice/{bill}', [BillController::class, 'show'])->name('invoice.show');
Route::post('pay/{bill}/success', [BillController::class, 'paymentSuccess'])->name('bills.payment_success');
