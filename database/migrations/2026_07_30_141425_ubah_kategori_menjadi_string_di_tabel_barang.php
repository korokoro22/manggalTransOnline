<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('barang', function (Blueprint $table) {
            // Mengubah tipe kolom dari enum ke string (varchar)
            $table->string('kategori')->change();
        });
    }

    public function down()
    {
        Schema::table('barang', function (Blueprint $table) {
            // Kembalikan ke enum jika di-rollback (opsional, sesuaikan dengan enum lama Anda)
            $table->enum('kategori', ['oli_mesin', 'filter_solar', 'item_bebas'])->change();
        });
    }
};