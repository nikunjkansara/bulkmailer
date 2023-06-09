<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Clean up all subscriptions!!!!!
        \horsefly\Cashier\Subscription::whereRaw('true')->delete();

        Schema::create('subscription_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uid');
            $table->integer('subscription_id')->unsigned();
            $table->string('type');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable();
            $table->string('status');
            $table->string('description');
            $table->string('title');
            $table->string('amount');
            $table->text('metadata');

            $table->timestamps();

            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription_transactions');
    }
}
