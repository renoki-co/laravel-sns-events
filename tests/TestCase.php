<?php

namespace Rennokki\LaravelSnsEvents\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Rennokki\LaravelSnsEvents\Concerns\GeneratesSnsMessages;

class TestCase extends Orchestra
{
    use GeneratesSnsMessages;

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Rennokki\LaravelSnsEvents\LaravelSnsEventsServiceProvider::class,
            TestServiceProvider::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');
    }
}
