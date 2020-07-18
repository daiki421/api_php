<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropColumnUsersColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_verified_at');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('email')->default(false);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('email_verified_at')->default(false);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('password')->default(false);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('remember_token')->default(false);
        });
    }
}
