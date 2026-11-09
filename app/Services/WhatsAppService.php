<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppService
{
    protected string $gatewayUrl;

    public function __construct()
    {
        $this->gatewayUrl = rtrim(env('LOCAL_WA_GATEWAY_URL', 'http://127.0.0.1:3000'), '/');
    }

    /**
     * Dispatch WhatsApp message through the local Baileys gateway.
     */
    public function sendMessage(string $target, string $message): bool
    {
        try {
            $response = Http::timeout(10)->post("{$this->gatewayUrl}/api/send-message", [
                'target' => $target,
                'message' => $message,
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('WhatsApp Gateway responded with non-2xx status', [
                'target' => $target,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;
        } catch (Throwable $e) {
            Log::error('Failed to communicate with WhatsApp Gateway', [
                'target' => $target,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

