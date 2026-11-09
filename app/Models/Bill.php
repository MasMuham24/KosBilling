<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'room_id',
        'bill_month',
        'base_amount',
        'additional_charge',
        'total_amount',
        'status',
        'due_date',
        'snap_token',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'additional_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Build automated reminder message template for WhatsApp dispatch.
     */
    public function buildReminderMessage(): string
    {
        $tenantName = $this->tenant?->name ?? 'Penghuni';
        $propertyName = $this->room?->property?->name ?? 'Kost';
        $roomNumber = $this->room?->room_number ?? '-';
        $period = \Carbon\Carbon::parse($this->bill_month . '-01')->translatedFormat('F Y');
        $dueDate = \Carbon\Carbon::parse($this->due_date)->translatedFormat('d F Y');
        $totalFormatted = 'Rp ' . number_format($this->total_amount, 0, ',', '.');
        $invoiceUrl = route('invoice.show', $this->id);

        return "Halo {$tenantName},\n\n"
            . "Berikut adalah rincian tagihan sewa kos untuk {$propertyName} (Kamar {$roomNumber}):\n"
            . "• Periode: {$period}\n"
            . "• Jatuh Tempo: {$dueDate}\n"
            . "• Total Tagihan: {$totalFormatted}\n\n"
            . "Silakan lihat invoice dan lakukan pembayaran melalui tautan berikut:\n"
            . "{$invoiceUrl}\n\n"
            . "Terima kasih.";
    }

    /**
     * Build automated digital receipt message for WhatsApp dispatch after payment.
     */
    public function buildReceiptMessage(): string
    {
        $tenantName = $this->tenant?->name ?? 'Penghuni';
        $billCode = "BILL-#{$this->id}";
        $propertyName = $this->room?->property?->name ?? 'Kost';
        $roomNumber = $this->room?->room_number ?? '-';
        $period = \Carbon\Carbon::parse($this->bill_month . '-01')->translatedFormat('F Y');
        $rawMethod = $this->payment?->payment_method ?? $this->payment?->payment_type ?? 'Midtrans';
        $paymentMethod = match (strtolower($rawMethod)) {
            'qris' => 'QRIS',
            'gopay' => 'GoPay',
            'shopeepay' => 'ShopeePay',
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Kartu Kredit',
            'manual_transfer' => 'Transfer Manual',
            default => strtoupper(str_replace('_', ' ', $rawMethod)),
        };
        $amountFormatted = 'Rp ' . number_format($this->total_amount, 0, ',', '.');
        $paymentDate = ($this->payment?->verified_at ?? now())->translatedFormat('d F Y, H:i') . ' WIB';
        $invoiceUrl = route('invoice.show', $this->id);

        return "🧾 *BUKTI PEMBAYARAN RESMI (LUNAS)*\n\n"
            . "Halo {$tenantName},\n"
            . "Terima kasih! Pembayaran sewa kos Anda telah berhasil diverifikasi oleh sistem.\n\n"
            . "• No. Tagihan: {$billCode}\n"
            . "• Properti: {$propertyName} (Kamar {$roomNumber})\n"
            . "• Periode Sewa: {$period}\n"
            . "• Metode Pembayaran: {$paymentMethod}\n"
            . "• Jumlah Dibayar: {$amountFormatted}\n"
            . "• Waktu Pembayaran: {$paymentDate}\n"
            . "• Status: LUNAS\n\n"
            . "Rincian invoice resmi dapat diakses dan diunduh melalui tautan berikut:\n"
            . "{$invoiceUrl}\n\n"
            . "Simpan bukti pembayaran ini sebagai tanda bukti transaksi yang sah.";
    }
}
