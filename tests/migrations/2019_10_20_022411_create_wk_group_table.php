<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateWkGroupTable extends Migration
{
    public function up()
    {
        Schema::create(config('wk-core.table.group.groups'), function (Blueprint $table) {
            $table->uuid('id');
            $table->nullableUuidMorphs('host');
            $table->uuid('user_id')->nullable();
            $table->string('serial')->nullable();
            $table->string('identifier');
            $table->text('script_head')->nullable();
            $table->text('script_footer')->nullable();
            $table->json('options')->nullable();
            $table->unsignedBigInteger('order')->nullable();
            $table->boolean('is_highlighted')->default(0);
            $table->boolean('is_enabled')->default(0);

            $table->timestampsTz();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')
                  ->on(config('wk-core.table.user'))
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->primary('id');
            $table->index('user_id');
            $table->index('serial');
            $table->index('identifier');
            $table->index('is_highlighted');
            $table->index('is_enabled');
            $table->index(['host_type', 'host_id', 'is_enabled']);
            $table->index(['host_type', 'host_id', 'is_highlighted']);
        });
        if (!config('wk-group.onoff.core-lang_core')) {
            Schema::create(config('wk-core.table.group.groups_lang'), function (Blueprint $table) {
                $table->uuid('id');
                $table->uuidMorphs('morph');
                $table->uuid('user_id')->nullable();
                $table->string('code');
                $table->string('key');
                $table->longText('value')->nullable();
                $table->boolean('is_current')->default(1);

                $table->timestampsTz();
                $table->softDeletes();

                $table->foreign('user_id')->references('id')
                    ->on(config('wk-core.table.user'))
                    ->onDelete('set null')
                    ->onUpdate('cascade');

                $table->primary('id');
            });
        }
        Schema::create(config('wk-core.table.group.groups_morphs'), function (Blueprint $table) {
            $table->uuid('group_id')->nullable();
            $table->uuidMorphs('morph');

            $table->foreign('group_id')->references('id')
                  ->on(config('wk-core.table.group.groups'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->index(['group_id', 'morph_type', 'morph_id']);
        });
    }

    public function down() {
        Schema::dropIfExists(config('wk-core.table.group.groups_morphs'));
        Schema::dropIfExists(config('wk-core.table.group.groups_lang'));
        Schema::dropIfExists(config('wk-core.table.group.groups'));
    }
}
