<?php
/**
 * Abstract Render Handler
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Render\Handler;

use CommonApi\Render\RenderInterface;

/**
 * Abstract Render Handler
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2013 Amy Stephen. All rights reserved.
 * @since      1.0
 */
abstract class AbstractHandler implements RenderInterface
{
    /**
     * Exclude tokens from parsing (Head tokens held until end)
     *
     * @var    array
     * @since  1.0
     */
    protected $exclude_tokens = array();

    /**
     * Schedule Event - anonymous function to FrontController triggerEvent method
     *
     * @var    callable
     * @since  1.0
     */
    protected $triggerEvent;

    /**
     * Constructor
     *
     * @param  array  $sequence
     * @param  array  $final
     * @param  object $resources
     * @param  object $extensions
     *
     * @since   1.0
     */
    public function __construct(
        array $exclude_tokens = array(),
        callable $triggerEvent = null
    ) {
        $this->exclude_tokens = $exclude_tokens;
        $this->triggerEvent   = $triggerEvent;
    }
}
