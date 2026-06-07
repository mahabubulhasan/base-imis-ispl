<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $renames = [
            'Export SW Organizations to CSV' => 'Export SW Organizations to Excel',
            'Export SW Workers to CSV' => 'Export SW Workers to Excel',
            'Export SW Vehicles to CSV' => 'Export SW Vehicles to Excel',
            'Export SW STS to CSV' => 'Export SW STS to Excel',
            'Export SW Landfills to CSV' => 'Export SW Landfills to Excel',
            'Export SW Complaints to CSV' => 'Export SW Complaints to Excel',
            'Export SW Attendance Logs to CSV' => 'Export SW Attendance Logs to Excel',
            'Export SW STS Logs to CSV' => 'Export SW STS Logs to Excel',
            'Export SW Landfill Logs to CSV' => 'Export SW Landfill Logs to Excel',
            'Export SW Waste Processing to CSV' => 'Export SW Waste Processing to Excel',
            'Export SW Bill Collection Payments to CSV' => 'Export SW Bill Collection Payments to Excel',
            'Import SW Bill Collection Payments From CSV' => 'Import SW Bill Collection Payments From Excel',
        ];

        foreach ($renames as $old => $new) {
            DB::table('permissions')->where('name', $old)->update(['name' => $new]);
        }

        $newPermissions = [
            ['group' => 'SW Service Provider Organizations', 'type' => 'Import', 'name' => 'Import SW Organizations From Excel'],
            ['group' => 'SW Service Provider Workers', 'type' => 'Import', 'name' => 'Import SW Workers From Excel'],
            ['group' => 'SW Service Provider Vehicles', 'type' => 'Import', 'name' => 'Import SW Vehicles From Excel'],
            ['group' => 'SW Waste Bins', 'type' => 'Export', 'name' => 'Export SW Waste Bins to Excel'],
            ['group' => 'SW Waste Bins', 'type' => 'Import', 'name' => 'Import SW Waste Bins From Excel'],
            ['group' => 'SW STS', 'type' => 'Import', 'name' => 'Import SW STS From Excel'],
            ['group' => 'SW Landfills', 'type' => 'Import', 'name' => 'Import SW Landfills From Excel'],
            ['group' => 'SW Attendance Logs', 'type' => 'Import', 'name' => 'Import SW Attendance Logs From Excel'],
            ['group' => 'SW STS Logs', 'type' => 'Import', 'name' => 'Import SW STS Logs From Excel'],
            ['group' => 'SW Landfill Logs', 'type' => 'Import', 'name' => 'Import SW Landfill Logs From Excel'],
            ['group' => 'SW Waste Processing', 'type' => 'Import', 'name' => 'Import SW Waste Processing From Excel'],
            ['group' => 'SW Complaints', 'type' => 'Import', 'name' => 'Import SW Complaints From Excel'],
        ];

        foreach ($newPermissions as $perm) {
            if (DB::table('permissions')->where('name', $perm['name'])->exists()) {
                continue;
            }
            DB::table('permissions')->insert([
                'name' => $perm['name'],
                'guard_name' => 'web',
                'group' => $perm['group'],
                'type' => $perm['type'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $renames = array_flip([
            'Export SW Organizations to CSV' => 'Export SW Organizations to Excel',
            'Export SW Workers to CSV' => 'Export SW Workers to Excel',
            'Export SW Vehicles to CSV' => 'Export SW Vehicles to Excel',
            'Export SW STS to CSV' => 'Export SW STS to Excel',
            'Export SW Landfills to CSV' => 'Export SW Landfills to Excel',
            'Export SW Complaints to CSV' => 'Export SW Complaints to Excel',
            'Export SW Attendance Logs to CSV' => 'Export SW Attendance Logs to Excel',
            'Export SW STS Logs to CSV' => 'Export SW STS Logs to Excel',
            'Export SW Landfill Logs to CSV' => 'Export SW Landfill Logs to Excel',
            'Export SW Waste Processing to CSV' => 'Export SW Waste Processing to Excel',
            'Export SW Bill Collection Payments to CSV' => 'Export SW Bill Collection Payments to Excel',
            'Import SW Bill Collection Payments From CSV' => 'Import SW Bill Collection Payments From Excel',
        ]);

        foreach ($renames as $old => $new) {
            DB::table('permissions')->where('name', $old)->update(['name' => $new]);
        }

        DB::table('permissions')->whereIn('name', [
            'Import SW Organizations From Excel',
            'Import SW Workers From Excel',
            'Import SW Vehicles From Excel',
            'Export SW Waste Bins to Excel',
            'Import SW Waste Bins From Excel',
            'Import SW STS From Excel',
            'Import SW Landfills From Excel',
            'Import SW Attendance Logs From Excel',
            'Import SW STS Logs From Excel',
            'Import SW Landfill Logs From Excel',
            'Import SW Waste Processing From Excel',
            'Import SW Complaints From Excel',
        ])->delete();
    }
};
