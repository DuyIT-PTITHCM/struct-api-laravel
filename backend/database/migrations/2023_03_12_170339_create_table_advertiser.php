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
        Schema::create('advertiser', function (Blueprint $table) {
            $table->id();
            $table->string('hash')->unique();
            $table->string('email')->unique();
            $table->string('password')->nullable(false);
            $table->string('name');
            $table->string('profile')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->float('balance')->default(0);
            $table->unsignedBigInteger('parent_id')->default(0);
            $table->string('report_email');
            $table->string('registration_no')->unique();
            $table->string('phone')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('postcode')->nullable();
            $table->dateTime('connected_at')->nullable();
            $table->string('partner_id');
            $table->string('representative')->nullable();
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->tinyInteger('state')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertiser', function (Blueprint $table) {
            //
        });
    }
};
