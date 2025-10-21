<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class RunTopologyUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $roadCode;

    public function __construct($roadCode)
    {
        $this->roadCode = $roadCode;
    }

    public function handle()
    {
       DB::statement("SELECT fnc_set_insert_road_and_create_topology()");
        \Log::info('Running topology update for road code: ' . $this->roadCode);

    }
}
