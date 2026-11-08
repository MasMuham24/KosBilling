<?php

namespace App\Services;

use App\Models\Bill;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function createSnapToken(Bill $bill): string
    {
        if ($bill->snap_token) {
            return $bill->snap_token;
        }
        $orderId = 'BILL-'.$bill->id.'-'.time();
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $bill->total_amount,
            ],
            'customer_details' => [
                'first_name' => $bill->tenant->name,
                'phone' => $bill->tenant->whatsapp_number,
            ],
            'item_details' => [
                [
                    'id' => 'ROOM-'.$bill->room_id,
                    'price' => (int) $bill->base_amount,
                    'quantity' => 1,
                    'name' => 'Sewa Kamar '.$bill->room->room_number,
                ],
            ],
        ];
        if ($bill->additional_charge > 0) {
            $params['item_details'][] = [
                'id' => 'ADDITIONAL-'.$bill->id,
                'price' => (int) $bill->additional_charge,
                'quantity' => 1,
                'name' => 'Biaya Tambahan / Utilitas',
            ];
        }
        $snapToken = Snap::getSnapToken($params);
        $bill->update([
            'snap_token' => $snapToken,
        ]);
        return $snapToken;
    }
}
