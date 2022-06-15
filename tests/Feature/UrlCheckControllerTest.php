<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UrlCheckControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $body = file_get_contents($this->getFixturePath('example.html'));

        Http::fake([
            'http://example.com' => Http::response($body),
        ]);
    }

    public function testStore(): void
    {
        $id = app('db')->table('urls')->insertGetId([
            'name' => 'http://example.com',
            'created_at' => now(),
        ]);

        $response = $this->post(route('urls.checks.store', ['url' => $id]));

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
