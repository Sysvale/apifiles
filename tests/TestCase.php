<?php

namespace Sysvale\ApiFiles\Tests;

use Sysvale\ApiFiles\ApiFilesServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
	protected function getPackageProviders($app)
	{
		return [
			ApiFilesServiceProvider::class,
		];
	}
}
