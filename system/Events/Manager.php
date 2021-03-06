<?php
/**
 * Events Manager - Manage the Events and dispatch them to listeners.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 * @date December 19th, 2015
 */

namespace Nova\Events;

use Nova\Core\Controller;
use Nova\Core\Event;
use Nova\Events\Listener;


class Manager
{
    private static $instance;

    private $events = array();

    private static $hookPath = 'Nova.Events.Manager.LegacyHook_';


    public function __construct()
    {
        self::$instance =& $this;
    }

    public static function &getInstance()
    {
        if (! self::$instance) {
            $manager = new self();
        }
        else {
            $manager =& self::$instance;
        }

        return $manager;
    }

    public static function initialize()
    {
        // Get the EventManager instance.
        $manager = self::getInstance();

        $manager->sortListeners();
    }

    public static function addListener($name, $callback, $priority = 0)
    {
        // Get the EventManager instance.
        $manager = self::getInstance();

        if(empty($name)) {
            throw new \UnexpectedValueException('The Event Name can not be empty');
        }

        $manager->attach($name, $callback, $priority);
    }

    public static function hasEvent($name)
    {
        // Get the EventManager instance.
        $manager = self::getInstance();

        if(! empty($name)) {
            return $manager->exists($name);
        }

        return false;
    }

    //
    // Hooks Compat/Legacy methods.

    public static function addHook($where, $callback)
    {
        // Get the EventManager instance.
        $manager = self::getInstance();

        if(empty($where)) {
            throw new \UnexpectedValueException('The Hook Name can not be empty');
        }

        $name = self::$hookPath .$where;

        $manager->attach($name, $callback);
    }

    public static function hasHook($where)
    {
        // Get the EventManager instance.
        $manager = self::getInstance();

        if(! empty($where)) {
            $name = self::$hookPath .$where;

            return $manager->exists($name);
        }

        return false;
    }

    public static function runHook($where, $args = '')
    {
        // Get the EventManager instance.
        $manager = self::getInstance();

        if (empty($where)) {
            throw new \UnexpectedValueException('The Hook Name can not be empty');
        }

        // Prepare the parameters.
        $name = self::$hookPath .$where;

        $result = $args;

        // Get the Listerners registered to this Event.
        $listeners = $manager->listeners($name);

        if($listeners === null) {
            // There are no Listeners registered for this Event.
            return false;
        }

        // First, preserve a instance of the Current Controller.
        $controller = Controller::getInstance();

        // Execute every Listener Callback, passing Result as parameter.
        foreach ($listeners as $listener) {
            $result = $manager->invokeObject($listener->callback(), $result);
        }

        // Ensure the restoration of the right Controller instance.
        $controller->setInstance();

        return $result;
    }

    public static function sendEvent($name, $params = array(), &$result = null)
    {
        // Get the EventManager instance.
        $manager = self::getInstance();

        if(empty($name)) {
            throw new \UnexpectedValueException('The Event Name can not be empty');
        }

        if(! $manager->exists($name)) {
            // There are no Listeners registered for this Event.
            return false;
        }

        // Create a new Event.
        $event = new Event($name, $params);

        // Deploy the Event to its Listeners and parse the Result from every one.
        return $manager->notify($event, function($data) use (&$result) {
            if(is_array($result)) {
                $result[] = $data;
            }
            else if(is_string($result)) {
                if(! is_string($data) && ! is_integer($data)) {
                    throw new \UnexpectedValueException('Unsupported Data type while the Result is String');
                }

                $result .= $data;
            }
            else if(is_bool($result)) {
                if(! is_bool($data)) {
                    throw new \UnexpectedValueException('Unsupported Data type while the Result is Boolean');
                }

                $result = $result ? $data : false;
            }
            else if(! is_null($result)) {
                throw new \UnexpectedValueException('Unsupported Result type');
            }
        });
    }

    // Some getters

    public function events()
    {
        return $this->events;
    }

    public function listeners($name)
    {
        if(! empty($name) && isset($this->events[$name])) {
            return $this->events[$name];
        }

        // Let's make Tom happy! ;)
        return null;
    }

    // Confirm if there are one or more Listeners registered to this Event Name.

    public function exists($name)
    {
        if(! empty($name)) {
            return isset($this->events[$name]);
        }

        return false;
    }

    /**
     * Attach the Callback with its associated Event parameter.
     *
     * @param  string $name name of the Event
     * @param  object $callback Callback executed on Event deploying
     * @param  integer $priority priority
     */
    public function attach($name, $callback, $priority = 0)
    {
        if(empty($name)) {
            throw new \UnexpectedValueException('The Event Name can not be empty');
        }

        if(! array_key_exists($name, $this->events)) {
            $this->events[$name] = array();
        }

        $listeners =& $this->events[$name];

        $listeners[] = new Listener($name, $callback, $priority);
    }

    /**
     * Dettach a Listener from the specified Event.
     *
     * @param  string $name name of the Event
     * @param  object $callback Callback executed on Event deploying
     */
    public function dettach($name, $callback)
    {
        if(empty($name) || ! $this->exists($name)) {
            return false;
        }

        $listeners =& $this->events[$name];

        $listeners = array_filter($listeners, function($listener) use ($callback) {
            return ($listener->callback() !== $callback);
        });

        return true;
    }

    public function clear($name = null)
    {
        if($name !== null) {
            // Is wanted to clear the Listeners from a specific Event.
            unset($this->events[$name]);
        }
        else {
            // Clear the entire Events list.
            $this->events = array();
        }
    }

    /**
     * Trigger an Event deploying to the Listeners registered for it.
     *
     * @param  string $name name of the event
     * @param  array  $params array of parameters
     * @param  string $callback callback invoked after Event deploying
     */
    public function trigger($name, $params = array(), $callback = null)
    {
        if(empty($name)) {
            throw new \UnexpectedValueException('The Event Name can not be empty');
        }

        // Create a new Event.
        $event = new Event($name, $params);

        // Deploy the Event notification to Listeners.
        return $this->notify($event, $callback);
    }

    /**
     * Notify event
     *
     * @param $event
     * @param null|callable $callback
     */
    public function notify($event, $callback = null)
    {
        $name = $event->name();

        if(! $this->exists($name)) {
            // There are no Listeners to observe this type of Event.
            return false;
        }

        // Get the Listerners registered to this Event.
        $listeners = $this->events[$name];

        // First, preserve a instance of the Current Controller.
        $controller = Controller::getInstance();

        // Deploy the Event to every Listener registered.
        foreach ($listeners as $listener) {
            // Invoke the Listener's Callback with the Event as parameter.
            $result = $this->invokeObject($listener->callback(), $event);

            if ($callback) {
                // Invoke the Callback with the Result as parameter.
                $this->invokeCallback($callback, $result);
            }
        }

        // Ensure the restoration of the right Controller instance.
        $controller->setInstance();

        return true;
    }

    /**
     * Invoke the Object Callback with its associated parameters.
     *
     * @param callable $callback
     * @param mixed|null $param
     * @return bool|mixed
     * @throws
     * @internal param object $event Event parameter
     */
    protected function invokeObject($callback, $param)
    {
        if (is_object($callback)) {
            // Call the Closure.
            return call_user_func($callback, $param);
        }

        // Call the object Class and its Method.
        $segments = explode('@', $callback);

        $className = $segments[0];
        $method    = $segments[1];

        // Check first if the Class exists.
        if (!class_exists($className)) {
            throw new \Exception("Class not found: $className");
        }

        // Initialize the Class.
        $object = new $className();

        // The called Method should be defined in the called Class, not in one of its parents.
        if (! in_array(strtolower($method), array_map('strtolower', get_class_methods($object)))) {
            throw new \Exception("Method not found: $className@$method");
        }

        if($object instanceof Controller) {
            // We are going to call-out a Controller; special setup is required.
            // The Controller instance should be properly initialized before executing its Method.
            $object->initialize($className, $method);
        }

        // Execute the Object's Method and return the result.
        return call_user_func(array($object, $method), $param);
    }

    protected function invokeCallback($callback, $param)
    {
        if (is_callable($callback)) {
            // Call the Closure.
            return call_user_func($callback, $param);
        }

        throw new \UnexpectedValueException('Unsupported Callback type');
    }

    protected function sortListeners()
    {
        $events = array();

        foreach($this->events as $name => $listeners) {
            usort($listeners, function($a, $b) {
                return ($a->priority() - $b->priority());
            });

            $events[$name] = $listeners;
        }

        $this->events = $events;
    }
}
