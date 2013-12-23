<?php
/**
 * Render Service Provider
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2013 Amy Stephen. All rights reserved.
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
 * @copyright  2013 Amy Stephen. All rights reserved.
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
        $options['service_namespace']        = null;

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

        $this->dependencies['Fieldhandler']  = array();
        $this->dependencies['Date']          = array();
        $this->dependencies['Url']           = array();
        $this->dependencies['Language']      = array();
        $this->dependencies['Authorisation'] = array();
        $this->dependencies['Resource']      = array();
        $this->dependencies['Runtimedata']   = array();
        $this->dependencies['Eventcallback']  = array();

        return $this->dependencies;
    }

    /**
     * Set Dependency values
     *
     * @param   array $dependency_instances (ignored in Service Item Adapter, based in from handler)
     *
     * @return  $this
     * @since   1.0
     */
    public function onBeforeInstantiation(array $dependency_instances = null)
    {
        parent::onBeforeInstantiation($dependency_instances);

        $exclude_tokens = array();
        $x              = $this->dependencies['Resource']
            ->get('xml:///Molajo//Application//Parse_final.xml')->include;
        foreach ($x as $y) {
            $exclude_tokens[] = (string)$y;
        }
        $this->dependencies['ExcludeTokens'] = $exclude_tokens;

        return $this;
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
        $handler = $this->getAdapterHandler();

        $class = 'Molajo\\Render\\Adapter';

        try {
            $this->service_instance = new $class($handler);

        } catch (Exception $e) {
            throw new RuntimeException
            ('Render: Could not instantiate Handler: ' . $class);
        }

        return $this;
    }

    /**
     * Get Filesystem Adapter, inject with specific Filesystem Adapter Handler
     *
     * @param   object $handler
     *
     * @return  object
     * @since   1.0
     * @throws  ServiceProviderInterface
     */
    protected function getAdapterHandler()
    {
        $class = 'Molajo\\Render\\Handler\\Molajito';

        try {
            return new $class (
                $this->dependencies['Fieldhandler'],
                $this->dependencies['Date'],
                $this->dependencies['Url'],
                $this->dependencies['Language'],
                $this->dependencies['Authorisation'],
                $this->dependencies['Resource'],
                $this->dependencies['Runtimedata'],
                $this->dependencies['Eventcallback'],
                $this->dependencies['ExcludeTokens']
            );
        } catch (Exception $e) {
            throw new RuntimeException
            ('Render: Could not instantiate Handler: ' . $class);
        }
    }
}
