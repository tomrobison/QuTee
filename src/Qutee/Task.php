<?php

namespace Qutee;

use Qutee\Queue;

/**
 * Task
 *
 * @author anorgan
 */
class Task
{
    /**
     * Default name of the method to run the task
     */
    const DEFAULT_METHOD_NAME = 'run';

    /**
     *
     * @var string
     */
    protected $_name;

    /**
     *
     * @var string
     */
    protected $_methodName;

    /**
     *
     * @var array
     */
    protected $_data;

    /**
     *
     * @var boolean
     */
    protected $_is_reserved = false;

    /**
     *
     * @param string $name
     *
     * @param array $data
     */
    public function __construct($name = null, $data = array(), $methodName = null)
    {
        if (null !== $name) {
            $this->setName($name);
        }

        if (null !== $data) {
            $this->setData($data);
        }

        if (null !== $methodName) {
            $this->setMethodName($methodName);
        }
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     *
     * @param string $name
     *
     * @return Task
     */
    public function setName($name)
    {
        // Name can hold method name in it
        if (strpos($name, '::')) {
            list($name, $methodName) = explode('::', $name);
        }

        // Validate name
        if (!preg_match('/^[a-zA-Z0-9\/\\\ _-]+$/', $name)) {
            throw new \InvalidArgumentException('Name can be only alphanumerics, spaces, underscores and dashes');
        }

        if (isset($methodName)) {
            $this->setMethodName($methodName);
        }

        $this->_name = $name;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getMethodName()
    {
        if ($this->_methodName === null) {
            $data = $this->getData();
            if (isset($data['method']) && strlen($data['method'])) {
                $this->_methodName = $data['method'];
            } else {
                $this->_methodName = self::DEFAULT_METHOD_NAME;
            }
        }

        return $this->_methodName;
    }

    /**
     *
     * @param string $methodName
     * @return \Qutee\Task
     *
     * @throws \InvalidArgumentException
     */
    public function setMethodName($methodName)
    {
        // validate name
        if (!preg_match('/^[a-z][a-zA-Z0-9_]+$/', $methodName)) {
            throw new \InvalidArgumentException('Method name can be only alphanumerics and underscores');
        }

        $this->_methodName = $methodName;

        return $this;
    }

    /**
     *
     * @return string
     * @throws Exception
     */
    public function getClassName()
    {
        if ($this->_name === null) {
            throw new Exception('Name not set, can not create class name');
        }

        if (strpos($this->_name, '\\') !== false) {
            // FQCN?
            $className = $this->_name;
        } elseif (strpos($this->_name, '/') !== false) {
            // Forward slash FQCN?
            $className = str_replace('/', '\\', $this->_name);
        } else {
            $className = str_replace(array('-','_'), ' ', strtolower($this->_name));
            $className = str_replace(' ', '', ucwords($className));
        }

        return $className;
    }

    /**
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     *
     * @param array $data
     *
     * @return Task
     */
    public function setData(array $data)
    {
        $this->_data = $data;

        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function isReserved()
    {
        return $this->_is_reserved;
    }

    /**
     *
     * @param boolean $state
     *
     * @return Task
     */
    public function setReserved($state)
    {
        $this->_is_reserved = $state;

        return $this;
    }

    /**
     * Unserialized task should not be reserved
     *
     * @return array
     */
    public function __sleep()
    {
        return array('_name', '_data', '_methodName');
    }

    /**
     *
     * @param string $name
     * @param array $data
     *
     * @return Task
     */
    public static function create($name, $data = null, $methodName = null)
    {
        $queue  = new Queue;
        $task   = new self($name, $data, $methodName);
        $queue->addTask($task);

        return $task;
    }
}