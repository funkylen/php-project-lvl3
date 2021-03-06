<?php

namespace App\Http\Controllers;

use App\Services\UrlCheckService;
use Illuminate\Http\RedirectResponse;

class UrlCheckController extends Controller
{
    public function store(UrlCheckService $checkService, int $url): RedirectResponse
    {
        $checkService->checkUrlById($url);

        return back();
    }
}
