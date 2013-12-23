<?php
/**
 * Adapter for Render Handlers
 *
 * @package    Molajo
 * @copyright  2013 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Render;

use CommonApi\Render\RenderInterface;
use CommonApi\Exception\RuntimeException;

/**
 * Adapter for Render Handlers
 *
 * @package    Molajo
 * @copyright  2013 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @since      1.0
 */
class Adapter
{
    /**
     * Render Handler
     *
     * @var     object  CommonApi\Render\RenderInterface
     * @since  1.0
     */
    protected $render_handler = null;

    /**
     * Class Constructor
     *
     * @param   RenderInterface $render_handler
     *
     * @since   1.0
     */
    public function __construct(
        RenderInterface $render_handler
    ) {
        $this->render_handler = $render_handler;
    }

    /**
     * Inclusion of the Theme introduces rendered output parsed for tokens
     *
     * @return  string
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function render()
    {
        return $this->render_handler->render();
    }
}
