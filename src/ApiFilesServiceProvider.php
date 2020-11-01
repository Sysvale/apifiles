<?php

namespace jedsonmelo\ApiFiles;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client as Guzzle;
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
		$this->app->bind(ClientInterface::class, function ($app) {
			$access_token = config('apifiles.access_token');

			return new Guzzle([
				'base_uri' => config('apifiles.url'),
				'headers' => [
					'Authorization' => "Bearer $access_token",
				],
			]);
		});

		$this->app->singleton('apifiles', ApiFiles::class);
	}
}
