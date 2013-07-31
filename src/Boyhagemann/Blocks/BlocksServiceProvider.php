<?php namespace Boyhagemann\Blocks;

use Illuminate\Support\ServiceProvider;

use Config, Route, View, Layout;

class BlocksServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->package('blocks', 'blocks');
	}

	public function boot()
	{
		$routes = Config::get('blocks');

		Route::filter('blocks', function($route) use ($routes) {

			$path = $route->getPath();

			if(!isset($routes[$path])) {
				return;
			}

			foreach($routes[$path] as $section => $controllers) {
				$blocks[$section] = '';
				foreach($controllers as $controller) {
					$blocks[$section] .= Layout::dispatch($controller);
				}
			}

			return View::make('layouts.default', $blocks);
		});

		Route::when('*', 'blocks');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}