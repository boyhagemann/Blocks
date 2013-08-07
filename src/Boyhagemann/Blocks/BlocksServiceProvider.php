<?php

namespace Boyhagemann\Blocks;

use Illuminate\Support\ServiceProvider;
use Config,
    Route,
    View,
    App;

class BlocksServiceProvider extends ServiceProvider
{
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
        Route::filter('blocks', function($route) {

            $routes = Config::get('blocks');
            $path = $route->getPath();

            if (!isset($routes[$path])) {
                return;
            }

            if (!isset($routes[$path]['layout'])) {
                throw new \Exception(sprintf('The route does not have a layout'));
            }

            if (!isset($routes[$path]['sections'])) {
                return;
            }

            $layoutName = $routes[$path]['layout'];

            // Add content to each section
            foreach ($routes[$path]['sections'] as $section => $blocks) {
                
                $content[$section] = '';
                
                foreach ($blocks as $block) {

                    $vars = $route->getParametersWithoutDefaults();
            
                    if (isset($block['params'])) {
                        foreach ($block['params'] as $key => $param) {

                            if (is_callable($param)) {
                                $vars[$key] = call_user_func_array($param, array($route));
                            }
                            else {
                                $vars[$key] = $param;
                            }
                        }
                    }

                    if (isset($block['match'])) {
                        foreach ($block['match'] as $key => $param) {

                            if (!$route->getParameter($param)) {
                                throw new \Exception(sprintf('The route does not have the param "%s"', $param));
                            }

                            $vars[$key] = $route->getParameter($param);
                        }
                    }

                    $content[$section] .= App::make('DeSmart\Layout\Layout')->dispatch($block['controller'], $vars);
                }
            }

            return View::make($layoutName, $content);
        });

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