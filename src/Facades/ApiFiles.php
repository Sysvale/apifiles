<?php

namespace jedsonmelo\ApiFiles\Facades;

use Illuminate\Support\Facades\Facade;

class ApiFiles extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'apifiles';
	}
}
