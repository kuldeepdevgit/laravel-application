<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AiController extends Controller
{
    public function home(): View
    {
        return view('ai.home');
    }

    public function product(): View
    {
        return view('ai.product');
    }

    public function pricing(): View
    {
        return view('ai.pricing');
    }

    public function workspace(Request $request): View
    {
        return view('ai.workspace', [
            'prompt' => $request->string('q')->trim()->toString(),
            'hideFooter' => true,
        ]);
    }
}
