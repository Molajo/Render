<?php
/**
 * Render Service Provider
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Service\Render;

use Exception;
use Molajo\IoC\AbstractServiceProvider;
use CommonApi\IoC\ServiceProviderInterface;
use CommonApi\Exception\RuntimeException;

/**
 * Render Service Provider
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0
 */
class RenderServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
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
        $options['service_name']             = basename(__DIR__);
        $options['store_instance_indicator'] = true;
        $options['service_namespace']        = 'Molajo\\Render\\Adapter';

        parent::__construct($options);
    }

    /**
     * Instantiate a new handler and inject it into the Adapter for the ServiceProviderInterface
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
    public function instantiateService()
    {
        $class = $this->service_namespace;

        try {
            $this->service_instance = new $class($this->getMolajitoHandler());

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
