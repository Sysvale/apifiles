<?php

namespace jedsonmelo\ApiFiles\Tests;

use jedsonmelo\ApiFiles\ApiFilesServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
	protected function getPackageProviders($app)
	{
		return [
			ApiFilesServiceProvider::class,
		];
	}
}
