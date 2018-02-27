<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExtraFieldsToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table)
        {

            $table->string('tc',11)->nullable();
            $table->string('tax_no',20)->nullable();
            $table->string('gsm_phone',20)->nullable();
            $table->string('phone',20)->nullable();
            $table->text('address')->nullable();
            $table->string('avatar',50)->default("no_avatar.png");
            $table->integer('user_type')->default(5);
            $table->json('operations')->nullable();
            $table->integer('created_by')->default(0);


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table)
        {

            $table->dropColumn('tc');
            $table->dropColumn('tax_no');
            $table->dropColumn('gsm_phone');
            $table->dropColumn('phone');
            $table->dropColumn('address');
            $table->dropColumn('avatar');
            $table->dropColumn('user_type');
            $table->dropColumn('operations');
            $table->dropColumn('created_by');
        });
    }
}
