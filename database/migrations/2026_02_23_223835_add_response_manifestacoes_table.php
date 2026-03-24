<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('manifestacoes', function(Blueprint $table){
            $table->unsignedBigInteger('id_response')->nullable();
            $table->foreign("id_response")->references("id_manifestacoes")->on("manifestacoes")->onUpdate("cascade")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manifestacoes', function(Blueprint $table){
            $table->dropColumn("id_response");
        });
    }
};
