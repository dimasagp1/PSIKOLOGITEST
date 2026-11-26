<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('admin_notes');
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->string('education')->nullable()->after('date_of_birth');
            $table->string('department')->nullable()->after('education');
            $table->text('address')->nullable()->after('department');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'date_of_birth', 'education', 'department', 'address']);
        });
    }
};