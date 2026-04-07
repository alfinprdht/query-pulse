<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('query_pulse', function (Blueprint $table) {
            $table->index('url')->name('query_pulse_url_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('query_pulse', function (Blueprint $table) {
            $table->dropIndex('query_pulse_url_index');
        });
    }
};
