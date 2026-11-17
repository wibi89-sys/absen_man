<?php

namespace App\Providers;

use App\Models\SchoolSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            try {
                if (Schema::hasTable('school_settings')) {
                    $view->with('schoolSetting', SchoolSetting::current());
                } else {
                    $view->with('schoolSetting', new SchoolSetting([
                        'school_name' => 'MADRASAH ONE',
                        'school_slogan' => 'Integrated Student Monitoring System',
                        'principal_name' => 'Dr. H. Ahmad Fauzi, M.Pd.',
                        'enable_demo_mode' => true,
                    ]));
                }
            } catch (\Throwable) {
                $view->with('schoolSetting', new SchoolSetting([
                    'school_name' => 'MADRASAH ONE',
                    'school_slogan' => 'Integrated Student Monitoring System',
                    'principal_name' => 'Dr. H. Ahmad Fauzi, M.Pd.',
                    'enable_demo_mode' => true,
                ]));
            }
        });
    }
}
