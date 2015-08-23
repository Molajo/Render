<?php
/**
 * Abstract Render Adapter
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 */
namespace Molajo\Render\Adapter;

use CommonApi\Render\RenderInterface;

/**
 * Abstract Render Adapter
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class AbstractAdapter implements RenderInterface
{
    /**
     * Render Engine
     *
     * @var    object  CommonApi\Render\RenderInterface
     * @since  1.0
     */
    protected $render_adapter = null;

    /**
     * Constructor
     *
     * @param  $render_adapter  RenderInterface
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
     * @param   array  $data
     *
     * @return  string
     * @since   1.0.0
     */
    public function renderOutput(array $data = array())
    {
        return $this->render_adapter->renderOutput($data);
    }
}
