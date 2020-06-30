<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateRolesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('roles', function(Blueprint $table)
		{
            $table->bigIncrements('id');
            $table->string('name')->unique();
			$table->string('description')->nullable();
			$table->timestamps();
            $table->json('extra_info')->nullable();
        });

        // Create default master role
        $extra_info = [];
        $permissions = json_decode(file_get_contents(base_path('resources/json/roles_permissions.json')));
        $permissions = $permissions->permissions;

        foreach($permissions as $permission) {
            $extra_info[]['permission'] = [
                'name' => $permission,
                'enabled' => true
            ];
        }

        DB::table('roles')->insert([
            [
                'name' => 'master',
                'description' => 'Master role with allmighty privileges',
                'extra_info' => '{ "permissions": ' . json_encode($extra_info) . ' }'
            ],
        ]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('roles');
	}
}
