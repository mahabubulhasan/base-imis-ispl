<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateTopologyFunction extends Command
{
    protected $signature = 'db:create-topology-function';
    protected $description = 'Creates the fnc_set_insert_road_and_create_topology SQL function in Postgres';

    public function handle()
    {
        try {
            DB::unprepared(config('create_topology.fnc_set_insert_road_and_create_topology'));
            $this->info('Topology function created successfully.');
        } catch (\Exception $e) {
            $this->error('Failed to create topology function: ' . $e->getMessage());
        }
    }
}
