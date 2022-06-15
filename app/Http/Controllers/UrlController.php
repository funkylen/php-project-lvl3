<?php

namespace App\Http\Controllers;

use App\Services\UrlCheckService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UrlController extends Controller
{
    private Builder $urlsTable;
    private Builder $urlChecksTable;

    public function __construct()
    {
        $this->urlsTable = app('db')->table('urls');
        $this->urlChecksTable = app('db')->table('url_checks');
    }

    public function index(): View
    {
        $urls = $this->urlsTable
            ->orderBy('id')
            ->paginate();

        $urlsIds = collect($urls->items())->pluck('id');

        $checks = $this->urlChecksTable
            ->select(
                app('db')
                    ->raw(
                        'MAX(created_at) as latest_created_at, url_id, status_code'
                    )
            )
            ->whereIn('url_id', $urlsIds)
            ->groupBy('url_id')
            ->get()
            ->keyBy('url_id');

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

        $parsedUrl = parse_url($request->url['name']);
        $name = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

        $url = $this->urlsTable->where('name', $name)->first();

        if ($url) {
            flash('Страница уже существует')->info();
            return redirect()->route('urls.show', ['url' => $url->id]);
        }

        $id = $this->urlsTable->insertGetId([
            'name' => $name,
            'created_at' => now(),
        ]);

        $checkService->checkUrlById($id);

        flash('Страница успешно добавлена')->info();
        return redirect()->route('urls.show', ['url' => $id]);
    }

    public function show($url): View
    {
        $url = $this->urlsTable->find($url);

        abort_unless($url, 404);

        $checks = $this->urlChecksTable
            ->where('url_id', '=', $url->id)
            ->latest()
            ->get();

        return view('urls.show', compact('url', 'checks'));
    }
}
