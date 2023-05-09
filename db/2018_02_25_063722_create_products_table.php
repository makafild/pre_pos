<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
//		Schema::create('products', function (Blueprint $table) {
//			$table->increments('id');
//
//			$table->string('name_fa');
//			$table->string('name_en')->nullable();
//			$table->text('description')->nullable();
//
//			$table->integer('quotas_master')->default(0);
//			$table->integer('quotas_slave')->default(0);
//			$table->integer('quotas_slave2')->default(0);
//
//			$table->boolean('master_status')->default(false);
//			$table->boolean('slave_status')->default(false);
//			$table->boolean('slave2_status')->default(false);
//
//			$table->integer('per_master')->default(0);
//			$table->integer('per_slave')->default(0);
//
//			$table->integer('master_unit_id')->unsigned()->nullable();
//			$table->foreign('master_unit_id')->references('id')->on('constants');
//
//			$table->integer('slave_unit_id')->unsigned()->nullable();
//			$table->foreign('slave_unit_id')->references('id')->on('constants');
//
//			$table->integer('slave2_unit_id')->unsigned()->nullable();
//			$table->foreign('slave2_unit_id')->references('id')->on('constants');
//
//
//			$table->integer('purchase_price')->default(0);
//			$table->integer('sales_price')->default(0);
//			$table->integer('consumer_price')->default(0);
//			$table->string('discount')->nullable();
//
//			// brand
//			$table->integer('brand_id')->unsigned()->nullable();
//			$table->foreign('brand_id')->references('id')->on('brands');
//
//			// category
//			$table->integer('category_id')->unsigned()->nullable();
//			$table->foreign('category_id')->references('id')->on('categories');
//
//			// photo
//			$table->integer('photo_id')->unsigned()->nullable();
//			$table->foreign('photo_id')->references('id')->on('files');
//
//			// company
//			$table->string('referral_id')->nullable();
//			$table->integer('company_id')->unsigned();
//			$table->foreign('company_id')->references('id')->on('users');
//
//			$table->string('score')->default(0);
//			$table->string('status')->default('available');
//
//			$table->integer('row_version')->default(0);
//			$table->timestamps();
//			$table->softDeletes();
//		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('products');
	}
}
