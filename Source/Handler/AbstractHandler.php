<?php
/**
 * Abstract Render Handler
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Render\Handler;

use CommonApi\Render\RenderInterface;

/**
 * Abstract Render Handler
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0
 */
class AbstractHandler implements RenderInterface
{
    /**
     * Render Handler
     *
     * @var     object  CommonApi\Render\RenderInterface
     * @since  1.0
     */
    protected $renderer = null;

    /**
     * Constructor
     *
     * @param  $renderer  RenderInterface
     *
     * @since  1.0
     */
    public function __construct(
        RenderInterface $renderer
    ) {
        $this->renderer = $renderer;
    }

    /**
     * Render Output
     *
     * @return  string
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function render()
    {
        return $this->renderer->render();
    }
}
