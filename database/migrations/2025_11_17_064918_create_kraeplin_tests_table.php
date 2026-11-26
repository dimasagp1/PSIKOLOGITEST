<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kraeplin_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('test_data'); // Menyimpan data tes
            $table->integer('total_score');
            $table->integer('correct_answers');
            $table->integer('wrong_answers');
            $table->integer('completion_time'); // dalam detik
            $table->json('analysis_results'); // Hasil analisis
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('kraeplin_tests');
    }
};