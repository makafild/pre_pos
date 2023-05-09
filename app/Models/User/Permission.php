<?php

namespace App\Models\User;

/**
 * Class Permission
 *
 * @package App\Models\User
 *
 * @property integer $id
 * @property string  $namespace
 * @property string  $namespace_translate
 * @property string  $controller
 * @property string  $controller_translate
 * @property string  $permission
 * @property string  $permission_translate
 * @property string  $name
 * @property string  $guard_name
 * @property Role[]  $roles
 * @property User[]  $users
 */
class Permission extends \Spatie\Permission\Models\Permission
{
	protected $appends = ['namespace_translate', 'controller_translate', 'permission_translate'];


	public function getNamespaceTranslateAttribute()
	{
		return trans("translate.user.role.namespace.{$this->namespace}");
	}

	public function getControllerTranslateAttribute()
	{
		return trans("translate.user.role.controller.{$this->controller}");
	}

	public function getPermissionTranslateAttribute()
	{
		return trans("translate.user.permission.{$this->permission}");
	}
}