<?php

namespace App\Services;

use DiDom\Document;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class UrlCheckService
{
    public function checkUrlById($id): void
    {
        $url = app('db')->table('urls')->find($id);

        abort_unless($url, 404);

        try {
            $response = Http::get($url->name);

            $document = new Document($response->body());

            app('db')->table('url_checks')->insert([
                'url_id' => $id,
                'status_code' => $response->status(),
                'created_at' => now(),
                'title' => optional(
                    $document->first('title'),
                    fn($node) => $node->text()
                ),
                'h1' => optional(
                    $document->first('h1'),
                    fn($node) => $node->text()
                ),
                'description' => optional(
                    $document->first('meta[name="description"]'),
                    fn($node) => $node->attr('content')
                ),
            ]);
        } catch (ConnectionException $exception) {
            flash($exception->getMessage())->error();
        }
    }
}
