<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyReportCompanyTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_report_company', function (Blueprint $table) {
			$table->integer('company_report_id')->unsigned();
			$table->foreign('company_report_id')->references('id')->on('company_reports');

			$table->integer('company_id')->unsigned();
			$table->foreign('company_id')->references('id')->on('users');

		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('compan_report_company');
    }
}
