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

            $vars = $route->getParametersWithoutDefaults();
            $layoutName = $routes[$path]['layout'];
            $layout = \Pages\Layout::whereName($layoutName)->with('sections')->first();
            
            // Fill each section with blank content
            $content = array();
            foreach($layout->sections as $section) {
                $content[$section->name] = '';
            }

            // Add content to each section
            foreach ($routes[$path]['sections'] as $section => $blocks) {
                
                $content[$section] = '';
                
                foreach ($blocks as $block) {

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

                    if (isset($block['matchRouteParams'])) {
                        foreach ($block['matchRouteParams'] as $key => $param) {

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