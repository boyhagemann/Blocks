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

					if(isset($block['params'])) {
						foreach($block['params'] as $key => $param) {

							if(is_callable($param)) {
								$vars[$key] = call_user_func_array($param, array($route));
							}
							else {
								$vars[$key] = $param;
							}
						}
					}


					if(isset($block['matchRouteParams'])) {
						foreach($block['matchRouteParams'] as $key => $param) {

							if(!$route->getParameter($param)) {
								throw new \Exception(sprintf('The route does not have the param "%s"', $param));
							}

							$vars[$key] = $route->getParameter($param);
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