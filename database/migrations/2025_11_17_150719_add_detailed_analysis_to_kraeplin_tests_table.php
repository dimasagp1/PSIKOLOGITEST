<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('kraeplin_tests', function (Blueprint $table) {
            $table->json('detailed_analysis')->nullable()->after('analysis_results');
            $table->decimal('speed_factor', 8, 2)->nullable();
            $table->decimal('rhythm_factor', 8, 2)->nullable();
            $table->decimal('ausdeur_factor', 8, 2)->nullable();
            $table->decimal('accuracy_rate', 8, 2)->nullable();
            $table->decimal('consistency_rate', 8, 2)->nullable();
            $table->decimal('stability_rate', 8, 2)->nullable();
            $table->decimal('endurance_rate', 8, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('kraeplin_tests', function (Blueprint $table) {
            $table->dropColumn([
                'detailed_analysis',
                'speed_factor',
                'rhythm_factor',
                'ausdeur_factor',
                'accuracy_rate',
                'consistency_rate',
                'stability_rate',
                'endurance_rate'
            ]);
        });
    }
};