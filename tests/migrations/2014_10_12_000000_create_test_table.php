<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('wk-core.table.user'), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create(config('wk-core.table.site.sites'), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('serial')->nullable();
            $table->string('language', 5)->default('zh_tw');
            $table->json('language_supported')->nullable();
            $table->string('timezone')->default('Asia/Taipei');
            $table->json('area_supported')->nullable();
            $table->boolean('is_main')->default(0);
            $table->boolean('is_enabled')->default(0);

            $table->timestampsTz();
            $table->softDeletes();

            $table->index('serial');
            $table->index('identifier');
            $table->index('is_enabled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('wk-core.table.site.sites'));
        Schema::dropIfExists(config('wk-core.table.user'));
    }
}
