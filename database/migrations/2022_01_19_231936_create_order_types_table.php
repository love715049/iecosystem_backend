<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateOrderTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $now = Carbon::now();
        DB::table('order_types')->insert([
            ['name' => '法律篇', 'updated_at' => $now, 'created_at' => $now],
            ['name' => '醫療篇', 'updated_at' => $now, 'created_at' => $now],
            ['name' => '經濟篇', 'updated_at' => $now, 'created_at' => $now],
            ['name' => '待產篇', 'updated_at' => $now, 'created_at' => $now],
            ['name' => '出養篇', 'updated_at' => $now, 'created_at' => $now],
            ['name' => '托育篇', 'updated_at' => $now, 'created_at' => $now],
            ['name' => '就學篇', 'updated_at' => $now, 'created_at' => $now],
            ['name' => '就業篇', 'updated_at' => $now, 'created_at' => $now],
            ['name' => '寄養篇', 'updated_at' => $now, 'created_at' => $now],
            ['name' => '其他', 'updated_at' => $now, 'created_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_types');
    }
}
