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
use CommonApi\IoC\FactoryBatchInterface;
use CommonApi\IoC\FactoryInterface;
use Molajo\IoC\FactoryMethod\Base as FactoryMethodBase;

/**
 * Render Factory Method
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class RenderFactoryMethod extends FactoryMethodBase implements FactoryInterface, FactoryBatchInterface
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
        $options['product_namespace']        = 'Molajo\\Render\\Driver';

        parent::__construct($options);
    }

    /**
     * Instantiate a new adapter and inject it into the Adapter for the FactoryInterface
     *
     * @return  array
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function setDependencies(array $reflection = array())
    {
        $this->reflection     = array();
        $this->dependencies   = array();
        $options              = array();
        $options['base_path'] = $this->base_path;

        $this->dependencies['Molajito']    = $options;
        $this->dependencies['Runtimedata'] = array();

        return $this->dependencies;
    }

    /**
     * Instantiate Class
     *
     * @return  $this
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function instantiateClass()
    {
        $adapter = $this->getMolajitoAdapter();

        $class = $this->product_namespace;

        try {
            $this->product_result = new $class($adapter);

        } catch (Exception $e) {
            throw new RuntimeException(
                'Render: Could not instantiate Adapter: ' . $class
            );
        }

        return $this;
    }

    /**
     * Set Extension Data for Resource
     *
     * @return  $this
     * @since   1.0
     */
    public function onAfterInstantiation()
    {
        $name         = $this->dependencies['Runtimedata']->resource->extensions->theme->title;
        $include_path = $this->dependencies['Runtimedata']->resource->extensions->theme->path;

        $data                 = array();
        $data['name']         = $name;
        $data['include_path'] = $include_path;
        $data['runtime_data'] = $this->dependencies['Runtimedata'];

        $this->product_result = $this->product_result->renderOutput($include_path, $data);

        return $this;
    }

    /**
     * Instantiate Molajito Adapter
     *
     * @return  $this
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function getMolajitoAdapter()
    {
        $class = 'Molajo\\Render\\Adapter\\Molajito';

        try {
            return new $class($this->dependencies['Molajito']);

        } catch (Exception $e) {
            throw new RuntimeException
            (
                'Render: Could not instantiate Molajito Adapter: ' . $class
            );
        }
    }

    /**
     * Factory Method Controller requests any Products (other than the current product) to be saved
     *
     * @return  array
     * @since   1.0
     */
    public function setContainerEntries()
    {
        $this->set_container_entries['rendered_page'] = $this->product_result;

        return $this->set_container_entries;
    }
}
