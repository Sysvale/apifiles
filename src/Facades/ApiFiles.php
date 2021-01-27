<?php

namespace Sysvale\ApiFiles\Facades;

use Illuminate\Support\Facades\Facade;
use Tests\Fakes\ApiFilesFake;

class ApiFiles extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'apifiles';
	}

	public static function fake()
	{
		static::swap(new ApiFilesFake);
	}
}
