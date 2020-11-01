<?php

namespace jedsonmelo\ApiFiles\Tests;

use jedsonmelo\ApiFiles\ApiFilesBaseServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
	protected function getPackageProviders($app)
	{
		return [
			ApiFilesBaseServiceProvider::class,
		];
	}
}
