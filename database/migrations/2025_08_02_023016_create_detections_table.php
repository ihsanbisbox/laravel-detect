<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up()
    {
        Schema::create('detections', function (Blueprint $table) {
            $table->id();
            $table->string('original_image');
            $table->string('annotated_image')->nullable();
            $table->json('detections_data');
            $table->integer('total_detections')->default(0);
            $table->timestamps();
        });
    }
};