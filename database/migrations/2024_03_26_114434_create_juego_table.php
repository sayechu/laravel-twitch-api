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
        $sql = "
            CREATE TABLE JUEGO (
                position SERIAL,
                gameId INT PRIMARY KEY,
                gameName VARCHAR(255)
            )
        ";

        DB::statement($sql);
    } 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS JUEGO");
    }
};