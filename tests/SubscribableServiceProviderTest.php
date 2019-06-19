<?php

namespace YlsIdeas\SubscribableNotifications\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\File;

class SubscribableServiceProviderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->cleanUp();
    }

    public function tearDown(): void
    {
        $this->cleanUp();
        parent::tearDown();
    }

    /** @test */
    public function it_can_publish_views()
    {
        $this->assertFalse(File::exists(resource_path('views/vendor/subscriber')));

        $this->artisan('vendor:publish', [
            '--tag' => 'subscriber-views',
            '--force' => true,
        ]);

        $this->assertTrue(File::exists(resource_path('views/vendor/subscriber')));
    }

    /** @test */
    public function it_can_publish_an_application_service_provider()
    {
        $this->assertFalse(File::exists(app_path('Providers/SubscribableServiceProvider.php')));

        $this->artisan('vendor:publish', [
            '--tag' => 'subscriber-provider',
            '--force' => true,
        ]);

        $this->assertTrue(File::exists(app_path('Providers/SubscribableServiceProvider.php')));
    }

    protected function cleanUp()
    {
        if (File::exists(resource_path('views/vendor/subscriber'))) {
            File::deleteDirectory(resource_path('views/vendor/subscriber'));
        }

        if (File::exists(resource_path('lang/vendor/subscriber'))) {
            File::deleteDirectory(resource_path('lang/vendor/subscriber'));
        }

        if (File::exists(app_path('Providers/SubscribableServiceProvider.php'))) {
            File::delete(app_path('Providers/SubscribableServiceProvider.php'));
        }
    }
}
