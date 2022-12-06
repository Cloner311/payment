<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Illuminate\Support\Facades\Schema;
use Rahabit\Payment\Traits\PaymentDatabase;
use Rahabit\Payment\Models\RahabitPaymentTransaction;
use Rahabit\Payment\Helpers\Currency;

class CreateRahabitPaymentTransactionsTable extends Migration
{
    use PaymentDatabase;

    public function up()
    {
        Schema::create($this->getTable(), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->nullable()->index();
            $table->unsignedBigInteger('payable_id')->nullable();
            $table->string('payable_type')->nullable();
            $table->string('gateway')->index();
            $table->unsignedDecimal('amount', 16, 4);
            $table->string('currency')->default(Currency::IRR);
            $table->unsignedTinyInteger('status')->default(RahabitPaymentTransaction::T_INIT);
            $table->string('tracking_code')->nullable()->index();
            $table->string('reference_number')->nullable()->index();
            $table->string('card_number')->nullable();
            $table->string('mobile')->nullable();
            $table->string('description')->nullable();
            $table->text('errors')->nullable();
            $table->text('extra')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->getTable());
    }
}
