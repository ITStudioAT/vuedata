<?php

namespace Itstudioat\Vuedata\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Itstudioat\Vuedata\VuedataServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Erzeugen der App.vue Datei für die Tests
        copy(__DIR__ . '/vue/App_Original.vue', __DIR__ . '/vue/App.vue');

        Factory::guessFactoryNamesUsing(
            fn(string $modelName) => 'Itstudioat\\Vuedata\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            VuedataServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }
}
