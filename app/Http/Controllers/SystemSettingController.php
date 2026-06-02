<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\SmsService;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index()
    {
        $smsBalance = null;

        if (Setting::bool('sms_enabled') && Setting::get('sms_token')) {
            $smsBalance = app(SmsService::class)->getBalance();
        }

        return view('settings.index', compact('smsBalance'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'church_name' => 'required|string|max:255',
            'church_tagline' => 'nullable|string|max:255',
            'church_phone' => 'nullable|string|max:50',
            'church_email' => 'nullable|email|max:255',
            'church_address' => 'nullable|string|max:500',
            'church_pastor' => 'nullable|string|max:255',
            'sms_sender_id' => 'nullable|string|max:11',
            'sms_token' => 'nullable|string|max:500',
            'sms_enabled' => 'nullable|boolean',
            'currency' => 'required|string|max:10',
            'timezone' => 'required|string|max:100',
            'date_format' => 'required|string|max:20',
        ]);

        $churchKeys = ['church_name', 'church_tagline', 'church_phone', 'church_email', 'church_address', 'church_pastor'];
        foreach ($churchKeys as $key) {
            Setting::set($key, $validated[$key] ?? '', 'church');
        }

        Setting::set('sms_sender_id', $validated['sms_sender_id'] ?? 'TAG UPENDO', 'sms');
        Setting::set('sms_enabled', $request->boolean('sms_enabled'), 'sms');

        if ($request->filled('sms_token')) {
            Setting::set('sms_token', $validated['sms_token'], 'sms');
        }

        Setting::set('currency', $validated['currency'], 'general');
        Setting::set('timezone', $validated['timezone'], 'general');
        Setting::set('date_format', $validated['date_format'], 'general');

        return redirect()
            ->route('settings.index')
            ->with('success', 'Mipangilio imesasishwa.');
    }
}
