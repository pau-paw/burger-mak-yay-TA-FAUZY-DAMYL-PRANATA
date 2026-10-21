<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): \Illuminate\Foundation\Application
    {
        $app = parent::createApplication();
        if ($app['config']->get('database.default') !== 'sqlite' || $app['config']->get('database.connections.sqlite.database') !== ':memory:') {
            throw new \LogicException('Pengujian dibatalkan: hanya database SQLite :memory: yang diizinkan. Jangan gunakan database toko.');
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Ephemeral test-only key: no real application secret is distributed.
        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
    }
}
