<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class WhatsappController extends Controller
{
    public function index(): View
    {
        $settings = [
            'gateway_url' => 'https://wa-gateway.example.com/api/send',
            'api_token' => 'wa_token_1234567890abcdef',
            'recipient_phone' => '+628123456789',
            'connection_status' => 'connected',
        ];

        return view('admin.whatsapp', compact('settings'));
    }
}
