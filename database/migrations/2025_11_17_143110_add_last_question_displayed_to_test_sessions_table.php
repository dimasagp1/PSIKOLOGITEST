<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('test_sessions', function (Blueprint $table) {
            $table->timestamp('last_question_displayed_at')->nullable();
            $table->integer('auto_skip_interval')->default(15); // detik
            $table->integer('total_test_time')->default(720); // 12 menit dalam detik
        });
    }

    public function down()
    {
        Schema::table('test_sessions', function (Blueprint $table) {
            $table->dropColumn(['last_question_displayed_at', 'auto_skip_interval', 'total_test_time']);
        });
    }
};