<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UrlControllerTest extends TestCase
{
    private int $urlId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->urlId = app('db')->table('urls')->insertGetId([
            'name' => 'http://example.com',
            'created_at' => now(),
        ]);
    }

    public function testIndex(): void
    {
        app('db')->table('url_checks')->insert([
            [
                'url_id' => 1,
                'status_code' => 403,
                'created_at' => now()->subDays(4),
            ],
            [
                'url_id' => 1,
                'status_code' => 200,
                'created_at' => now()->subDays(2),
            ],
        ]);

        $response = $this->get(route('urls.index'));

        $response->assertOk();
    }

    public function testStore(): void
    {
        $urlName = 'http://store-example.com';

        $fakeResponseBody = file_get_contents($this->getFixturePath('example.html'));

        Http::fake([
            $urlName => Http::response($fakeResponseBody),
        ]);

        $body = [
            'url' => ['name' => $urlName],
        ];

        $response = $this->post(route('urls.store'), $body);

        $response->assertSessionHasNoErrors();

        $response->assertRedirect();

        $this->assertDatabaseHas('urls', [
            'name' => $urlName,
        ]);
    }

    public function testStoreWithEmptyName(): void
    {
        $response = $this->post(route('urls.store'));

        $response->assertSessionHasErrors();
    }

    public function testShow(): void
    {
        $response = $this->get(route('urls.show', $this->urlId));

        $response->assertOk();
    }
}
