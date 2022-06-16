<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UrlCheckControllerTest extends TestCase
{
    public function testStore(): void
    {
        $urlName = 'http://example.com';

        $fakeResponseBody = file_get_contents($this->getFixturePath('example.html'));

        Http::fake([
            $urlName => Http::response($fakeResponseBody),
        ]);

        $id = app('db')->table('urls')->insertGetId([
            'name' => $urlName,
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
