<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FundController extends Controller
{
    public function requests(Request $request): View
    {
        return view('admin.funds.requests', [
            'search' => trim($request->string('q')->toString()),
        ]);
    }

    public function address(): View
    {
        return view('admin.funds.address');
    }

    public function updateAddress(): RedirectResponse
    {
        return back()->with('status', 'The address form is on screen. Saving is not connected yet.');
    }

    public function send(): View
    {
        return view('admin.funds.send');
    }

    public function storeSend(): RedirectResponse
    {
        return back()->with('status', 'The send form is on screen. Sending is not connected yet.');
    }

    public function history(Request $request): View
    {
        return view('admin.funds.history', [
            'search' => trim($request->string('q')->toString()),
        ]);
    }
}
