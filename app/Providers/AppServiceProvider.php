<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
<<<<<<< HEAD
use Illuminate\Support\Facades\Schema; //add fixed sql

=======
use Illuminate\Support\Facades\Schema;//add fixed sql
>>>>>>> 98d3df064c2ee7cf7114ece277eae758fefa11a9

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
    public function boot()
    {
<<<<<<< HEAD
        Schema::defaultStringLength(191); //add fixed sql

=======
        //
        Schema::defaultStringLength(191); //add fixed sql
>>>>>>> 98d3df064c2ee7cf7114ece277eae758fefa11a9
    }
}
