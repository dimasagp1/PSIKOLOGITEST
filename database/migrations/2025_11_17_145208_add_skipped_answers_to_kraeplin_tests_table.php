<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('kraeplin_tests', function (Blueprint $table) {
            $table->integer('skipped_answers')->default(0)->after('wrong_answers');
        });
    }

    public function down()
    {
        Schema::table('kraeplin_tests', function (Blueprint $table) {
            $table->dropColumn('skipped_answers');
        });
    }
};