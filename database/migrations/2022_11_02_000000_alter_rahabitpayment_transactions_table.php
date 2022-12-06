<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


use Rahabit\Payment\Traits\PaymentDatabase;

class AlterRahabitPaymentTransactionsTable extends Migration
{
    use PaymentDatabase;

    public function up()
    {
        Schema::table($this->getTable(), function (Blueprint $table) {
            $table->bigInteger('user_id')->nullable()->after('card_number');
            $table->string('full_name')->nullable()->after('user_id');
            $table->string('email')->nullable()->after('full_name');
            $table->json('gateway_data')->nullable()->after('extra');
        });
    }

    public function down()
    {
        Schema::table($this->getTable(), function ($table) {
            $table->dropColumn('email');
        });
        Schema::table($this->getTable(), function ($table) {
            $table->dropColumn('full_name');
        });
        Schema::table($this->getTable(), function ($table) {
            $table->dropColumn('gateway_data');
        });
    }
}
