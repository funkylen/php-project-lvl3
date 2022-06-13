<?php

namespace App\Http\Controllers;

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
        $latestCheck = $this->urlChecksTable
            ->latest()
            ->limit(1);

        $urls = $this->urlsTable
            ->leftJoinSub($latestCheck, 'latest_check', function ($join) {
                $join->on('urls.id', '=', 'latest_check.url_id');
            })
            ->select('urls.*', 'latest_check.created_at as latest_check_at')
            ->paginate();

        return view('urls.index', compact('urls'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = app('validator')->make(
            $request->all(),
            [
                'url.name' => 'required|url|max:255',
            ]
        );

        if ($validator->fails()) {
            flash('Неккоректный URL.')->error();
            return back();
        }

        $parsedUrl = parse_url($request->url['name']);
        $name = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

        $url = $this->urlsTable->where('name', $name)->first();

        if ($url) {
            flash('Страница уже существует')->info();
            return redirect()->route('urls.show', ['url' => $url->id]);
        }

        $now = now();

        $id = $this->urlsTable->insertGetId([
            'name' => $name,
            'created_at' => $now,
        ]);

        $this->urlChecksTable->insert([
            'url_id' => $id,
            'created_at' => $now,
        ]);

        flash('Страница успешно добавлена')->info();
        return redirect()->route('urls.show', ['url' => $id]);
    }

    public function show($url): View
    {
        $url = $this->urlsTable->find($url);
        $checks = $this->urlChecksTable->where('url_id', '=', $url->id)->latest()->get();

        return view('urls.show', compact('url', 'checks'));
    }

    public function check($id)
    {
        $this->urlChecksTable->insert([
            'url_id' => $id,
            'created_at' => now(),
        ]);

        return back();
    }
}
