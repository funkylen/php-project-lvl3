<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use DiDom\Document;

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
        $latestChecksQuery = $this->urlChecksTable
            ->select(app('db')->raw('MAX(created_at) as latest_created_at, url_id, status_code'))
            ->groupBy('url_id');

        $urls = $this->urlsTable
            ->joinSub($latestChecksQuery, 'latest_checks', 'urls.id', '=', 'latest_checks.url_id')
            ->join('url_checks', 'latest_checks.url_id', '=', 'url_checks.url_id')
            ->whereColumn('latest_checks.latest_created_at', '=', 'url_checks.created_at')
            ->select(
                'urls.*',
                'latest_checks.latest_created_at as latest_check_created_at',
                'latest_checks.status_code as latest_check_status_code'
            )
            ->orderBy('id')
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

        $id = $this->urlsTable->insertGetId([
            'name' => $name,
            'created_at' => now(),
        ]);

        $this->checkUrlById($id);

        flash('Страница успешно добавлена')->info();
        return redirect()->route('urls.show', ['url' => $id]);
    }

    public function show($url): View
    {
        $url = $this->findUrlById($url);

        $checks = $this->urlChecksTable->where('url_id', '=', $url->id)->latest()->get();

        return view('urls.show', compact('url', 'checks'));
    }

    private function findUrlById($id)
    {
        $url = $this->urlsTable->find($id);

        if (!$url) {
            abort(404);
        }

        return $url;
    }

    public function check($id)
    {
        $this->checkUrlById($id);

        return back();
    }

    private function checkUrlById($id)
    {
        $url = $this->findUrlById($id);

        try {
            $response = Http::get($url->name);

            $document = new Document($response->body());

            $this->urlChecksTable->insert([
                'url_id' => $id,
                'status_code' => $response->status(),
                'created_at' => now(),
                'title' => optional($document->first('title'), fn ($node) => $node->text()),
                'h1' => optional($document->first('h1'), fn ($node) => $node->text()),
                'description' => optional(
                    $document->first('meta[name="description"]'),
                    fn ($node) => $node->attr('content')
                ),
            ]);
        } catch (ConnectionException $exception) {
            flash($exception->getMessage())->error();
        }
    }
}
