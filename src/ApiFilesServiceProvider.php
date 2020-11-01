<?php

namespace jedsonmelo\ApiFiles;

use Illuminate\Support\ServiceProvider;

class ApiFilesServiceProvider extends ServiceProvider
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

		$this->app->singleton('apifiles', function ($app) {
			return new ApiFiles();
		});
	}

	protected function registerPublishing()
	{
		$this->publishes([
			__DIR__.'/../config/apifiles.php' => config_path('apifiles.php'),
		], 'apifiles-config');
	}
}
