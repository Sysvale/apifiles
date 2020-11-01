<?php

namespace jedsonmelo\ApiFiles;

use Illuminate\Support\ServiceProvider;

class ApiFilesBaseServiceProvider extends ServiceProvider
{
	public function boot()
	{
		if ($this->app->runningInConsole()) {
			$this->registerPublishing();
		}
	}

	public function register()
	{
		$this->mergeConfigFrom(__DIR__.'/../config/apifiles.php', 'apifiles');
	}

	protected function registerPublishing()
	{
		$this->publishes([
			__DIR__.'/../config/apifiles.php' => config_path('apifiles.php'),
		], 'apifiles-config');
	}
}
