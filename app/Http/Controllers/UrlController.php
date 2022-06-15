<?php

namespace App\Http\Controllers;

use App\Services\UrlCheckService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UrlController extends Controller
{
    public function index(): View
    {
        $urls = app('db')
            ->table('urls')
            ->orderBy('id')
            ->paginate();

        /** @var array $urlsItems */
        $urlsItems = $urls->items();

        $checks = collect($urlsItems)->mapWithKeys(function ($url) {
            $check = app('db')
                ->table('url_checks')
                ->where('url_id', $url->id)
                ->latest()
                ->select('url_id', 'created_at', 'status_code')
                ->first();

            return [$url->id => $check];
        });

        return view('urls.index', compact('urls', 'checks'));
    }

    public function store(Request $request, UrlCheckService $checkService): RedirectResponse
    {
        $validator = app('validator')->make(
            $request->all(),
            [
                'url.name' => 'required|url|max:255',
            ]
        );

        if ($validator->fails()) {
            flash('Неккоректный URL.')->error();
            return back()->withErrors($validator);
        }

        $parsedUrl = parse_url($request->get('url')['name']);
        $name = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

        $url = app('db')->table('urls')->where('name', $name)->first();

        if ($url) {
            flash('Страница уже существует')->info();
            return redirect()->route('urls.show', ['url' => $url->id]);
        }

        $id = app('db')->table('urls')->insertGetId([
            'name' => $name,
            'created_at' => now(),
        ]);

        $checkService->checkUrlById($id);

        flash('Страница успешно добавлена')->info();
        return redirect()->route('urls.show', ['url' => $id]);
    }

    public function show(int $url): View
    {
        $url = app('db')->table('urls')->find($url);

        abort_unless($url, 404);

        $checks = app('db')
            ->table('url_checks')
            ->where('url_id', $url->id)
            ->latest()
            ->get();

        return view('urls.show', compact('url', 'checks'));
    }
}
