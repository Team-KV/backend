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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string("first_name");
            $table->string("last_name");
            $table->date("date_born")->nullable();
            $table->tinyInteger("sex")->nullable();
            $table->float("height")->nullable();
            $table->float("weight")->nullable();
            $table->string("personal_information_number")->unique()->nullable();
            $table->integer("insurance_company")->nullable();
            $table->string("phone")->nullable();
            $table->string('contact_email')->nullable();
            $table->string("street")->nullable();
            $table->string("city")->nullable();
            $table->string("postal_code")->nullable();
            $table->string("sport")->nullable();
            $table->string("past_illnesses")->nullable();
            $table->string("injuries_suffered")->nullable();
            $table->text("note")->nullable();
            $table->text("anamnesis")->nullable();
            $table->foreignId('client_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
};
