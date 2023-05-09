<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');

            // email and mobile
            $table->string('email')->unique()->nullable();
			$table->string('mobile_number')->unique();
			$table->boolean('mobile_number_confirmation')->default(false);

			// Company data
			$table->string('name_fa')->nullable();
			$table->string('name_en')->nullable();
			$table->string('economic_code')->nullable();
			$table->string('api_url')->nullable();

			// points
			$table->string('score')->default(0);

			// Person data
			$table->string('first_name')->nullable();
			$table->string('last_name')->nullable();
			$table->string('national_id')->nullable();

			$table->string('kind')->default(\App\Models\User\User::KIND_CUSTOMER);
			$table->string('status')->default('active');

			// Creator
			$table->integer('creator_id')->unsigned()->nullable();
			$table->foreign('creator_id')->references('id')->on('users');

			// Company
			$table->integer('company_id')->unsigned()->nullable();
			$table->foreign('company_id')->references('id')->on('users');

			// Category
			$table->integer('category_id')->unsigned()->nullable();
			$table->foreign('category_id')->references('id')->on('constants');


			// photo
			$table->integer('photo_id')->unsigned()->nullable();
			$table->foreign('photo_id')->references('id')->on('files');


			$table->integer('introducer_code_id')->unsigned()->nullable();

			// security
			$table->string('password');
			$table->rememberToken();

			$table->integer('row_version')->default(0);

			// Date
			$table->timestamp('end_at')->nullable();
			$table->timestamps();
			$table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
