<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('automotive_detailings', function (Blueprint $table) {
            $table->string('cnpj', 14)->nullable()->unique();
        });

        DB::table('automotive_detailings')->update([
            'postal_code' => DB::raw("replace(postal_code, '-', '')"),
        ]);
    }

    public function down(): void
    {
        Schema::table('automotive_detailings', function (Blueprint $table) {
            $table->dropColumn('cnpj');
        });
    }
};
