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
        Schema::table('blood_issues', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->nullable(false)->default(0.00)->change();
        });
    }
};
