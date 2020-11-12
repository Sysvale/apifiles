<?php

namespace Sysvale\ApiFiles;

use Sysvale\ApiFiles\ApiFilesClient;
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

		$this->registerFacades();
	}

	protected function registerPublishing()
	{
		$this->publishes([
			__DIR__.'/../config/apifiles.php' => config_path('apifiles.php'),
		], 'apifiles-config');
	}

	protected function registerFacades()
	{
		$this->app->bind(ApiFilesClient::class, function ($app) {
			$access_token = config('apifiles.access_token');

			return new ApiFilesClient([
				'base_uri' => config('apifiles.url'),
				'headers' => [
					'Authorization' => "Bearer $access_token",
				],
			]);
		});

		$this->app->singleton('apifiles', ApiFiles::class);
	}
}
