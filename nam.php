<?php
namespace sigareng\Nam;
/**
 * @method static Nam get(string $route, Callable $callback)
 * @method static Nam post(string $route, Callable $callback)
 * @method static Nam put(string $route, Callable $callback)
 * @method static Nam delete(string $route, Callable $callback)
 * @method static Nam options(string $route, Callable $callback)
 * @method static Nam head(string $route, Callable $callback)
 */
class Nam
{
    public static $halts = false;
    public static $routes = array();
    public static $methods = array();
    public static $callbacks = array();
    public static $maps = array();
    public static $patterns = array(
        ':any' => '[^/]+',
        ':num' => '[0-9]+',
        ':all' => '.*'
    );
    public static $error_callback;
    public static $root;
    public static $base = false;
    public static $basepath;
    // public static $root = '/maze/ahu/';
    /**
     * Defines a route w/ callback and method
     */
    public static function hello()
    {
        echo "hello Nam";
    }
    public static function setbase($value='')
    {
        self::$basepath = $value;
        self::$base=true;
    }
    public static function __callstatic($method, $params)
    {
        if ($method == 'map')
        {
            $maps = array_map('strtoupper', $params[0]);
            $uri = strpos($params[1], '/') === 0 ? $params[1] : '/' . $params[1];
            if (self::$base) {
            $uri = strpos($params[1], '/') === 0 ? '/'.self::$basepath.''.$params[1] : '/'.self::$basepath.'/' . $params[1];
            }
            $callback = $params[2];
        }
        else
        {
            $maps = null;
            $uri = strpos($params[0], '/') === 0 ? $params[0] : '/' . $params[0];
            $callback = $params[1];
            if (self::$base) {
            $uri = strpos($params[0], '/') === 0 ? '/'.self::$basepath.''.$params[0] : '/'.self::$basepath.'/' . $params[0];
            }
        }
        array_push(self::$maps, $maps);
        array_push(self::$routes, $uri);
        array_push(self::$methods, strtoupper($method));
        array_push(self::$callbacks, $callback);
    }
    /**
     * Defines callback if route is not found
     */
    public static function error($callback)
    {
        self::$error_callback = $callback;
    }
    public static function haltOnMatch($flag = true)
    {
        self::$halts = $flag;
    }
    private function routebase($router)
    {
        return (self::$basepath.''.$router);
    }
    /**
     * Runs the callback for the given request
     */
    public static function dispatch()
    {
        $uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        if (!empty(self::$root) && self::$root !== '/')
        {
            self::$root = rtrim(self::$root, '/');
            if (self::$root === $uri)
            {
                $uri = '/';
            }
            else
            {
                // Remove the root directory from uri, remove only the first occurrence
                $uri = substr_replace($uri, '', strpos($uri, self::$root) , strlen(self::$root));
            }
        }
        $method = $_SERVER['REQUEST_METHOD'];
        $searches = array_keys(static ::$patterns);
        $replaces = array_values(static ::$patterns);
        $found_route = false;
        self::$routes = preg_replace('/\/+/', '/', self::$routes);
        // Check if route is defined without regex
        if (in_array($uri, self::$routes))
        {
            $route_pos = array_keys(self::$routes, $uri);
            foreach ($route_pos as $route)
            {
                // Using an ANY option to match both GET and POST requests
                if (self::$methods[$route] == $method || self::$methods[$route] == 'ANY' || (!empty(self::$maps[$route]) && in_array($method, self::$maps[$route])))
                {
                    $found_route = true;
                    // If route is not an object
                    if (!is_object(self::$callbacks[$route]))
                    {
                        // Grab all parts based on a / separator
                        $parts = explode('/', self::$callbacks[$route]);
                        // Collect the last index of the array
                        $last = end($parts);
                        // Grab the controller name and method call
                        $segments = explode('@', $last);
                        // Instanitate controller
                        $controller = new $segments[0]();
                        // Call method
                        $controller->{$segments[1]}();
                        if (self::$halts) return;
                    }
                    else
                    {
                        // Call closure
                        call_user_func(self::$callbacks[$route]);
                        if (self::$halts) return;
                    }
                }
            }
        }
        else
        {
            // Check if defined with regex
            $pos = 0;
            foreach (self::$routes as $route)
            {
                if (strpos($route, ':') !== false)
                {
                    $route = str_replace($searches, $replaces, $route);

                }
                if (preg_match('#^' . $route . '$#', $uri, $matched))
                {
                    if (self::$methods[$pos] == $method || self::$methods[$pos] == 'ANY' || (!empty(self::$maps[$pos]) && in_array($method, self::$maps[$pos])))
                    {
                        $found_route = true;
                        // Remove $matched[0] as [1] is the first parameter.
                        array_shift($matched);
                        if (!is_object(self::$callbacks[$pos]))
                        {
                            // Grab all parts based on a / separator
                            $parts = explode('/', self::$callbacks[$pos]);
                            // Collect the last index of the array
                            $last = end($parts);
                            // Grab the controller name and method call
                            $segments = explode('@', $last);
                            // Instanitate controller
                            $controller = new $segments[0]();
                            // Fix multi parameters
                            if (!method_exists($controller, $segments[1]))
                            {
                                echo "controller and action not found";
                            }
                            else
                            {
                                call_user_func_array(array(
                                    $controller,
                                    $segments[1]
                                ) , $matched);
                            }
                            if (self::$halts) return;
                        }
                        else
                        {
                            call_user_func_array(self::$callbacks[$pos], $matched);
                            if (self::$halts) return;
                        }
                    }
                }
                $pos++;
            }
        }
        // Run the error callback if the route was not found
        if ($found_route == false)
        {
            if (!self::$error_callback)
            {
                self::$error_callback = function ()
                {
                    header($_SERVER['SERVER_PROTOCOL'] . " 404 Not Found");
                    echo '404----';
                    echo rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) , '/');
                };
            }
            else
            {
                if (is_string(self::$error_callback))
                {
                    self::get($_SERVER['REQUEST_URI'], self::$error_callback);
                    self::$error_callback = null;
                    self::dispatch();
                    return;
                }
            }
            call_user_func(self::$error_callback);
        }
    }

    public static function render($file,array $import=array()) {
        if ($import) {
        $data= $import;
        }
        // echo count($import);
        include($file);
    }
}