<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    use CreatesApplication;

    protected function getFixturePath(string $filename): string
    {
        $pathParts = ['tests', 'Fixtures', $filename];

        return base_path(implode(DIRECTORY_SEPARATOR, $pathParts));
    }
}
