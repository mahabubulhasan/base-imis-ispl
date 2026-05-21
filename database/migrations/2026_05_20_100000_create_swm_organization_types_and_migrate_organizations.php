<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swm.organization_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('code')->nullable()->unique();
            $table->softDeletes();
            $table->timestamps();
        });

        $now = now();
        $types = [
            ['code' => 'private', 'name' => 'Private'],
            ['code' => 'government', 'name' => 'Government'],
            ['code' => 'other', 'name' => 'Others'],
        ];

        foreach ($types as $type) {
            DB::table('swm.organization_types')->insert([
                'name' => $type['name'],
                'code' => $type['code'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('swm.organizations', function (Blueprint $table) {
            $table->foreignId('organization_type_id')
                ->nullable()
                ->after('contact_number')
                ->constrained('swm.organization_types')
                ->restrictOnDelete();
        });

        $codeToId = DB::table('swm.organization_types')
            ->whereNull('deleted_at')
            ->pluck('id', 'code');

        foreach ($codeToId as $code => $typeId) {
            DB::table('swm.organizations')
                ->where('organization_category', $code)
                ->update(['organization_type_id' => $typeId]);
        }

        $defaultTypeId = $codeToId['other'] ?? $codeToId->first();
        if ($defaultTypeId) {
            DB::table('swm.organizations')
                ->whereNull('organization_type_id')
                ->update(['organization_type_id' => $defaultTypeId]);
        }

        DB::statement('ALTER TABLE swm.organizations ALTER COLUMN organization_type_id SET NOT NULL');

        Schema::table('swm.organizations', function (Blueprint $table) {
            $table->dropColumn(['organization_category', 'organization_category_other']);
        });
    }

    public function down(): void
    {
        Schema::table('swm.organizations', function (Blueprint $table) {
            $table->string('organization_category')->nullable()->after('contact_number');
            $table->string('organization_category_other')->nullable()->after('organization_category');
        });

        $idToCode = DB::table('swm.organization_types')
            ->whereNotNull('code')
            ->pluck('code', 'id');

        foreach ($idToCode as $typeId => $code) {
            DB::table('swm.organizations')
                ->where('organization_type_id', $typeId)
                ->update(['organization_category' => $code]);
        }

        Schema::table('swm.organizations', function (Blueprint $table) {
            $table->dropForeign(['organization_type_id']);
            $table->dropColumn('organization_type_id');
        });

        Schema::dropIfExists('swm.organization_types');
    }
};
