<?php

namespace Shemi\Translator;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class ManagerServiceProvider extends ServiceProvider
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	protected $commends = [
		'reset',
		'import',
		'find',
		'find',
		'export',
		'clean',
	];

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// Register the config publish path
		$configPath = __DIR__ . '/../config/translator.php';
		$this->mergeConfigFrom($configPath, 'translator');
		$this->publishes([$configPath => config_path('translator.php')], 'config');

		$this->app->singleton('laravel-translator', function() {
			return $this->app->make('Shemi\Translator\Manager');
		});

		$this->registerCommends();
	}

	/**
	 * Bootstrap the application events.
	 *
	 * @param  \Illuminate\Routing\Router $router
	 *
	 * @return void
	 */
	public function boot(Router $router)
	{

		$resourcesPath = __DIR__ . '/../resources';

		$this->loadViewsFrom("{$resourcesPath}/views", 'translator');
		$this->publishes([
			"{$resourcesPath}/views" => base_path('resources/views/vendor/laravel-translator'),
		], 'views');

		$this->publishes([
			"{$resourcesPath}/assets" => public_path('translator'),
		], 'assets');

		$migrationPath = __DIR__ . '/../database/migrations';
		$this->publishes([
			$migrationPath => base_path('database/migrations'),
		], 'migrations');

		$config = $this->app['config']->get('laravel-translator.route', []);
		$config['namespace'] = 'Shemi\Translator\Http\Controllers';

		$router->group($config, function($router) {
			require "Http/routs.php";
		});
	}

	private function registerCommends()
	{
		foreach( $this->commends as $commend ) {
			$commandClassName = "\\Shemi\\Translator\\Console\\" . studly_case("{$commend}_command");

			$this->app->singleton("command.laravel-translator.$commend", function() use ($commandClassName) {
				return new $commandClassName(app('laravel-translator'));
			});

			$this->commands("command.laravel-translator.$commend");
		}

	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'laravel-translator',
			'command.laravel-translator.reset',
			'command.laravel-translator.import',
			'command.laravel-translator.find',
			'command.laravel-translator.export',
			'command.laravel-translator.clean',
		];
	}

}
