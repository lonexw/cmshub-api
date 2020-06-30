<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Request $request)
    {
        $projectId = $request->input('Project-Id');
        if ($projectId) {
            Config::set('lighthouse.schema.register',  base_path('graphql/cust/schema' . $projectId . '.graphql'));
        }
    }
}
