<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BootstrapController extends Controller
{
    public function home(): View
    {
        return view('bootstrap.home');
    }

    public function services(): View
    {
        return view('bootstrap.services');
    }

    public function createContact(): View
    {
        return view('bootstrap.contact');
    }

    public function storeContact(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        return back()->with('status', 'Thanks. This demo keeps the note on this page and does not send email.');
    }
}
