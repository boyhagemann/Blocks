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


			$vars = $route->getParametersWithoutDefaults();

			foreach($routes[$path] as $section => $blocks) {
				$content[$section] = '';
				foreach($blocks as $block) {

					if(isset($block['vars'])) {
						foreach($block['vars'] as $key => $var) {

							if(is_callable($var)) {
								$vars[$key] = call_user_func_array($var, array($route));
							}
						}
					}
					$controller = $block['controller'];
					$content[$section] .= Layout::dispatch($controller, $vars);
				}
			}

			return View::make('layouts.default', $content);
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