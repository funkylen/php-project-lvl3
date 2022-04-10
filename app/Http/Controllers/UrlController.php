<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UrlController extends Controller
{
    private Builder $table;

    public function __construct()
    {
        $this->table = DB::table('urls');
    }

    public function index(): View
    {
        $urls = $this->table->paginate();

        return view('urls.index', compact('urls'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make(
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

        $url = $this->table->where('name', $name)->first();

        if ($url) {
            flash('Страница уже существует')->info();
            return redirect()->route('urls.show', ['url' => $url->id]);
        }

        $id = $this->table->insertGetId([
            'name' => $name,
            'created_at' => now(),
        ]);

        flash('Страница успешно добавлена')->info();
        return redirect()->route('urls.show', ['url' => $id]);
    }

    public function show($url): View
    {
        $url = $this->table->find($url);

        return view('urls.show', compact('url'));
    }
}
