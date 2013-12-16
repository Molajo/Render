<?php
/**
 * Render Service Provider
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Service\Render;

use stdClass;
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
        if (isset($this->options['triggerEvent'])) {
            $triggerEvent = $this->options['triggerEvent'];
        } else {
            throw new RuntimeException('RenderService: Requires triggerEvent be passed in.');
        }

        $options = array();

        if (isset($this->options['resource'])) {
            $options['resource'] = $this->options['resource'];
        } else {
            $options['resource']  = new stdClass();
        }

        if (isset($this->options['fieldhandler'])) {
            $options['fieldhandler']  = $this->options['fieldhandler'];
        } else {
            $options['fieldhandler']  = new stdClass();
        }

        if (isset($this->options['date_controller'])) {
            $options['date_controller']  = $this->options['date_controller'];
        } else {
            $options['date_controller']  = new stdClass();
        }

        if (isset($this->options['url_controller'])) {
            $options['url_controller']  = $this->options['url_controller'];
        } else {
            $options['url_controller']  = new stdClass();
        }

        if (isset($this->options['language_controller'])) {
            $options['language_controller']  = $this->options['language_controller'];
        } else {
            $options['language_controller']  = new stdClass();
        }

        if (isset($this->options['authorisation_controller'])) {
            $options['authorisation_controller']  = $this->options['authorisation_controller'];
        } else {
            $options['authorisation_controller']  = new stdClass();
        }

        if (isset($this->options['runtime_data'])) {
            $options['runtime_data']  = $this->options['runtime_data'];
        } else {
            $options['runtime_data']  = new stdClass();
        }

        if (isset($this->options['parameters'])) {
            $options['parameters']  = $this->options['parameters'];
        } else {
            $options['parameters']  = new stdClass();
        }

        if (isset($this->options['model_registry'])) {
            $options['model_registry']  = $this->options['model_registry'];
        } else {
            $options['model_registry']  = new stdClass();
        }

        if (isset($this->options['query_results'])) {
            $options['query_results']  = $this->options['query_results'];
        } else {
            $options['query_results']  = array();
        }

        if (isset($this->options['rendered_view'])) {
            $options['rendered_view']  = $this->options['rendered_view'];
        } else {
            $options['rendered_view']  = '';
        }

        if (isset($this->options['rendered_page'])) {
            $options['rendered_page']  = $this->options['rendered_page'];
        } else {
            $options['rendered_page']  = '';
        }

        $class = 'Molajo\\Render\\Handler\\Molajito';

        try {
            return new $class ($triggerEvent, $options);
        } catch (Exception $e) {
            throw new RuntimeException
            ('Render: Could not instantiate Handler: ' . $class);
        }
    }
}
