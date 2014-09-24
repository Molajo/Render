<?php
/**
 * Proxy Class for Render Engine Adapters
 *
 * @package    Molajo
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Render;

use CommonApi\Render\RenderInterface;
use CommonApi\Exception\RuntimeException;
use Exception;

/**
 * Proxy Class for Render Engine Adapters
 *
 * @package    Molajo
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @since      1.0.0
 */
class Driver implements RenderInterface
{
    /**
     * Render Adapter
     *
     * @var    object  CommonApi\Render\RenderInterface
     * @since  1.0
     */
    protected $render_adapter = null;

    /**
     * Class Constructor
     *
     * @param  RenderInterface $render_adapter
     *
     * @since  1.0
     */
    public function __construct(
        RenderInterface $render_adapter
    ) {
        $this->render_adapter = $render_adapter;
    }

    /**
     * Render output for specified file and data
     *
     * @param   string $include_path
     * @param   array  $data
     *
     * @return  string
     * @since   1.0
     */
    public function renderOutput($include_path, array $data = array())
    {
        try {
            return $this->render_adapter->renderOutput($include_path, $data);

        } catch (Exception $e) {

            throw new RuntimeException('Render Driver render Method Failed: ' . $e->getMessage());
        }
    }
}
