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
        $this->dependencies['Eventcallback'] = array();

        return $this->dependencies;
    }

    /**
     * Set Dependency values
     *
     * @param   array $dependency_values (ignored in Service Item Adapter, based in from handler)
     *
     * @return  $this
     * @since   1.0
     */
    public function onBeforeInstantiation(array $dependency_values = null)
    {
        parent::onBeforeInstantiation($dependency_values);

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
     * Get Pagination Handler
     *
     * @param   object $handler
     *
     * @return  object
     * @since   1.0
     * @throws  ServiceProviderInterface
     */
    protected function getAdapterHandler()
    {
        $class = 'Molajo\\Render\\Handler\\Pagination';

        $additional_rendering_properties = array(
            'runtime_data',
            'parameters',
            'query',
            'model_registry',
            'row',
            'include_path'
        );

        $event_option_keys                   = array(
            'runtime_data',
            'parameters',
            'query',
            'model_registry',
            'row',
            'rendered_view',
            'rendered_page'
        );
        $options                             = array();
        $options['fieldhandler']             = $this->dependencies['Fieldhandler'];
        $options['date_controller']          = $this->dependencies['Date'];
        $options['url_controller']           = $this->dependencies['Url'];
        $options['language_controller']      = $this->dependencies['Language'];
        $options['authorisation_controller'] = $this->dependencies['Authorisation'];

        try {
            return new $class (
                $this->dependencies['Resource'],
                $this->dependencies['Runtimedata'],
                $this->dependencies['Eventcallback'],
                $this->dependencies['ExcludeTokens'],
                $options,
                $additional_rendering_properties
            );
        } catch (Exception $e) {
            throw new RuntimeException
            ('Render: Could not instantiate Handler: ' . $class);
        }
    }
}
