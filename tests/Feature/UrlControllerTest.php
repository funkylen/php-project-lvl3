<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UrlControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'http://example.com' => Http::response('
            <html>
                <head>
                    <title>Title</title>
                    <meta name="description" content="Description" />
                </head>
                <body>
                    <h1>H1</h1>
                </body>
            </html>
            ', 200),
        ]);
    }

    public function testIndex(): void
    {
        $response = $this->get(route('urls.index'));
        $response->assertOk();
    }

    public function testStore(): void
    {
        $urlName = 'http://example.com';

        $body = [
            'url' => ['name' => $urlName],
        ];

        $response = $this->post(route('urls.store'), $body);

        $response->assertSessionHasNoErrors();

        $response->assertRedirect();

        $this->assertDatabaseHas('urls', [
            'name' => $urlName,
        ]);

        $this->assertDatabaseHas('url_checks', [
            'url_id' => app('db')->table('urls')->first()->id,
            'status_code' => 200,
            'h1' => 'H1',
            'title' => 'Title',
            'description' => 'Description',
        ]);
    }

    public function testShow(): void
    {
        $id = app('db')->table('urls')->insertGetId([
            'name' => 'http://example.com',
            'created_at' => now(),
        ]);

        $response = $this->get(route('urls.show', $id));

        $response->assertOk();
    }

    public function testUrlCheck(): void
    {
        $id = app('db')->table('urls')->insertGetId([
            'name' => 'http://example.com',
            'created_at' => now(),
        ]);

        $response = $this->post(route('urls.check', $id));

        $response->assertSessionHasNoErrors();

        $response->assertRedirect();

        $this->assertDatabaseHas('url_checks', [
            'url_id' => $id,
            'status_code' => 200,
            'h1' => 'H1',
            'title' => 'Title',
            'description' => 'Description',
        ]);
    }
}
