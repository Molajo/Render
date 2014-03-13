<?php
/**
 * Render Factory Method
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Factories\Render;

use Exception;
use CommonApi\Exception\RuntimeException;
use CommonApi\IoC\FactoryMethodInterface;
use CommonApi\IoC\FactoryMethodBatchSchedulingInterface;
use Molajo\IoC\FactoryBase;

/**
 * Render Factory Method
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0
 */
class RenderFactoryMethod extends FactoryBase implements FactoryMethodInterface, FactoryMethodBatchSchedulingInterface
{
    /**
     * Constructor
     *
     * @param  $options
     *
     * @since  1.0
     */
    public function __construct(array $options = array())
    {
        $options['product_name']             = basename(__DIR__);
        $options['store_instance_indicator'] = true;
        $options['product_namespace']        = 'Molajo\\Render\\Adapter';

        parent::__construct($options);
    }

    /**
     * Instantiate a new handler and inject it into the Adapter for the FactoryMethodInterface
     * Retrieve a list of Interface dependencies and return the data ot the controller.
     *
     * @return  array
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException;
     */
    public function setDependencies(array $reflection = null)
    {
        $this->reflection   = array();
        $this->dependencies = array();

        $this->dependencies['Molajito'] = array();

        return $this->dependencies;
    }

    /**
     * Instantiate Class
     *
     * @return  $this
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException;
     */
    public function instantiateClass()
    {
        $class = $this->product_namespace;

        try {
            $this->product_result = new $class($this->getMolajitoHandler());

        } catch (Exception $e) {
            throw new RuntimeException
            ('Render: Could not instantiate Adapter: ' . $class);
        }

        return $this;
    }

    /**
     * Instantiate Molajito Handler
     *
     * @return  $this
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException;
     */
    public function getMolajitoHandler()
    {
        $class = 'Molajo\\Render\\Handler\\Molajito';

        try {
            return new $class($this->dependencies['Molajito']);

        } catch (Exception $e) {
            throw new RuntimeException
            ('Render: Could not instantiate Molajito Adapter: ' . $class);
        }
    }
}
