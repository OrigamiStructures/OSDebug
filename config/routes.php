<?php
use Cake\Routing\Router;

Router::plugin('OSDebug', function ($routes) {
    $routes->fallbacks('InflectedRoute');
});
