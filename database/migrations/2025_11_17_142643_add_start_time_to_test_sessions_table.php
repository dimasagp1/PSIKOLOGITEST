<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('test_sessions', function (Blueprint $table) {
            $table->timestamp('current_question_start_time')->nullable();
            $table->integer('timeout_count')->default(0);
        });
    }

    public function down()
    {
        Schema::table('test_sessions', function (Blueprint $table) {
            $table->dropColumn(['current_question_start_time', 'timeout_count']);
        });
    }
};