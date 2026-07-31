<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(string $locale)
    {
        if (! in_array($locale, ['en', 'sw'], true)) {
            abort(400);
        }

        session(['locale' => $locale]);

        return back();
    }
}
