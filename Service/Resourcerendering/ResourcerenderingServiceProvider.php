<?php
/**
 * Resourcesrendering Service Provider
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Service\Resourcerendering;

use Exception;
use Molajo\IoC\AbstractServiceProvider;
use CommonApi\IoC\ServiceProviderInterface;
use CommonApi\Exception\RuntimeException;

/**
 * Resourcesrendering Service Provider
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2013 Amy Stephen. All rights reserved.
 * @since      1.0
 */
class ResourcerenderingServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    /**
     * Constructor
     *
     * @param  array $options
     *
     * @since  1.0
     */
    public function __construct(array $options = array())
    {
        $options['service_name'] = basename(__DIR__);

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
        parent::setDependencies($reflection);

        $options                          = array();
        $this->dependencies['Resource']  = $options;
        $this->dependencies['Extensions'] = $options;

        return $this->dependencies;
    }

    /**
     * Set Dependencies for Instantiation
     *
     * @return  array
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException;
     */
    public function onBeforeInstantiation(array $dependency_instances = null)
    {
        parent::onBeforeInstantiation($dependency_instances);

        $filename = BASE_FOLDER . '/vendor/molajo/resource/Source/Files/Output/ResourceMap.json';
        $resource_map = $this->readFile($filename);
        $scheme       = $this->createScheme();

        $handler_instance['ThemeHandler']
            = $this->createHandler(
            'ThemeHandler',
            BASE_FOLDER,
            $resource_map,
            array(),
            $scheme->getScheme('Theme')->include_file_extensions
        );
        $handler_instance['PageHandler']
            = $this->createHandler(
            'PageHandler',
            BASE_FOLDER,
            $resource_map,
            array(),
            $scheme->getScheme('Page')->include_file_extensions
        );
        $handler_instance['TemplateHandler']
            = $this->createHandler(
            'TemplateHandler',
            BASE_FOLDER,
            $resource_map,
            array(),
            $scheme->getScheme('Template')->include_file_extensions
        );
        $handler_instance['WrapHandler']
            = $this->createHandler(
            'WrapHandler',
            BASE_FOLDER,
            $resource_map,
            array(),
            $scheme->getScheme('Wrap')->include_file_extensions
        );
        $handler_instance['MenuitemHandler']
            = $this->createHandler(
            'MenuitemHandler',
            BASE_FOLDER,
            $resource_map,
            array(),
            $scheme->getScheme('Menuitem')->include_file_extensions
        );
        return $this->dependencies;
    }

    /**
     * Create Handler Instance
     *
     * @param   string $handler
     * @param   string $base_path
     * @param   array  $resource_map
     * @param   array  $namespace_prefixes
     * @param   array  $valid_file_extensions
     * @param   bool   $extensions
     *
     * @return  mixed
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException;
     */
    protected function createHandler(
        $handler,
        $base_path,
        $resource_map,
        $namespace_prefixes,
        $valid_file_extensions
    ) {
        $class = 'Molajo\\Resource\\Handler\\' . $handler;

        try {
            $handler_instance = new $class (
                $base_path,
                $resource_map,
                $namespace_prefixes,
                $valid_file_extensions,
                $this->dependencies['Extensions'],
                $this->dependencies['Resource']
            );
        } catch (Exception $e) {
            throw new RuntimeException ('Resources Handler ' . $handler
            . ' Exception during Instantiation: ' . $e->getMessage());
        }

        $this->dependencies['Resource']->setHandlerInstance($handler, $handler_instance);

        return $handler_instance;
    }

    /**
     * Create Scheme Instance
     *
     * @return  object
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException;
     */
    protected function createScheme()
    {
        $class = 'Molajo\\Resource\\Scheme';

        $input = BASE_FOLDER . '/vendor/molajo/resource/Source/Files/Input/SchemeArray.json';

        try {
            $scheme = new $class ($input);
        } catch (Exception $e) {
            throw new RuntimeException ('Resources Scheme ' . $class
            . ' Exception during Instantiation: ' . $e->getMessage());
        }

        return $scheme;
    }

    /**
     * Read File
     *
     * @param  string $file_name
     *
     * @return array
     * @since  1.0
     */
    protected function readFile($file_name)
    {
        $temp_array = array();

        if (file_exists($file_name)) {
        } else {
            return array();
        }

        $input = file_get_contents($file_name);

        $temp = json_decode($input);

        if (count($temp) > 0) {
            $temp_array = array();
            foreach ($temp as $key => $value) {
                $temp_array[$key] = $value;
            }
        }

        return $temp_array;
    }
}
