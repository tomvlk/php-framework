<?php
/**
 * Route - manage a route to an HTTP request and an assigned callback function.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 * @date December 11th, 2015
 */

namespace Nova\Net;

/**
 * The Route class is responsible for routing an HTTP request to an assigned callback function.
 */
class Route
{
    /**
     * @var string HTTP method or 'ANY'
     */
    private $method;

    /**
     * @var string URL pattern
     */
    private $pattern;

    /**
     * @var mixed Callback
     */
    private $callback = null;

    /**
     * @var array Route parameters
     */
    private $params = array();

    /**
     * @var string Matching regular expression
     */
    private $regex;

    /**
     * Constructor.
     *
     * @param string $method HTTP method
     * @param string $pattern URL pattern
     * @param mixed $callback Callback function
     */
    public function __construct($method, $pattern, $callback)
    {
        $this->method = strtoupper($method);

        $this->pattern = ! empty($pattern) ? $pattern : '/';

        $this->callback = $callback;
    }

    /**
     * Checks if a URL and HTTP method matches the Route pattern.
     *
     * @param string $uri Requested URL
     * @param $method
     * @param bool $optionals
     * @return bool Match status
     * @internal param string $pattern URL pattern
     */
    public function match($uri, $method, $optionals = true)
    {
        if (($this->method != $method) && ($this->method != 'ANY')) {
            return false;
        }

        // Exact match Route.
        if ($this->pattern == $uri) {
            return true;
        }

        // Build the regex for matching.
        if (strpos($this->pattern, ':') !== false) {
            $regex = str_replace(array(':any', ':num', ':all'), array('[^/]+', '-?[0-9]+', '.*'), $this->pattern);
        } else {
            $regex = $this->pattern;
        }

        if ($optionals !== false) {
            $searches = array('(/', ')');
            $replaces = array('(?:/', ')?');

            if(is_array($optionals) && ! empty($optionals)) {
                $searches = array_merge(array_keys($optionals),   $searches);
                $replaces = array_merge(array_values($optionals), $replaces);
            }

            $regex = str_replace($searches, $replaces, $regex);
        }

        // Attempt to match the Route and extract the parameters.
        if (preg_match('#^' .$regex .'(?:\?.*)?$#i', $uri, $matches)) {
            // Remove $matched[0] as [1] is the first parameter.
            array_shift($matches);
            // Store the extracted parameters.
            $this->params = $matches;
            // Also, store the compiled regex.
            $this->regex = $regex;

            return true;
        }

        return false;
    }

    //
    // Some Getters

    public function method()
    {
        return $this->method;
    }

    public function pattern()
    {
        return $this->pattern;
    }

    public function callback()
    {
        return $this->callback;
    }

    public function params()
    {
        return $this->params;
    }

    public function regex()
    {
        return $this->regex;
    }
}
