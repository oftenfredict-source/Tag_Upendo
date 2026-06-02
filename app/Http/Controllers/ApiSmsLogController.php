<?php

namespace App\Http\Controllers;

use App\Services\SmsService;
use Illuminate\Http\Request;

class ApiSmsLogController extends Controller
{
    public function index(SmsService $smsService)
    {
        // Fetch logs directly from NextSMS API
        $response = $smsService->getLogs();

        $logs = [];
        if ($response['success']) {
            $logs = $response['logs'];
        }

        return view('sms_logs.index', compact('logs'));
    }
}
