<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('message_id')->nullable()->unsigned();
            $table->integer('contact_id')->nullable()->unsigned();
            $table->integer('user_id')->nullable()->unsigned();
            $table->enum('type', [
                'inbox',
                'outbox'
            ])->default('outbox');
            $table->string('source', 30);
            $table->string('destination', 30);
            $table->text('text');
            $table->text('metadata');
            $table->enum('status', [
                'pending',
                'sent',
                'failed',
                'canceled',
            ]);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
