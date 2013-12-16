<?php
/**
 * Adapter for Render
 *
 * @package    Molajo
 * @copyright  2013 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Render;

use CommonApi\Render\RenderInterface;
use CommonApi\Exception\RuntimeException;

/**
 * Adapter for Render
 *
 * @package    Molajo
 * @copyright  2013 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @since      1.0
 */
class Adapter implements RenderInterface
{
    /**
     * Render Adapter
     *
     * @var     object  CommonApi\Render\RenderInterface
     * @since  1.0
     */
    protected $adapter = null;

    /**
     * Get the current value (or default) of the specified property
     *
     * @param   string $key
     *
     * @return  mixed
     * @since   1.0
     */
    public function get($key)
    {
        return $this->adapter->get($key);
    }

    /**
     * Class Constructor
     *
     * @param   RenderInterface $adapter
     *
     * @since   1.0
     */
    public function __construct(
        RenderInterface $adapter
    ) {
        $this->adapter = $adapter;
    }

    /**
     * Inclusion of the Theme introduces rendered output parsed for tokens
     *
     * @param   string $include_path
     * @param   array  $options
     *
     * @return  string
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function includeTheme($include_path, array $options = array())
    {
        return $this->adapter->includeTheme($include_path, $options);
    }

    /**
     * Parse rendered output looking for tokens (excluded tokens are head, held until all body rendered)
     *
     * @param   string $rendered_page
     * @param   array  $exclude_tokens
     *
     * @return  array
     * @since   1.0
     */
    public function parseTokens($rendered_page, array $exclude_tokens = array())
    {
        return $this->adapter->parseTokens($rendered_page, $exclude_tokens);
    }

    /**
     * Render output for tag discovered in parsing
     *
     * @param   string $include_path
     * @param   array  $query_results
     * @param   array  $options
     *
     * @return  array
     * @since   0.1
     */
    public function renderView($include_path, array $query_results = array(), array $options = array())
    {
        return $this->adapter->renderView($include_path, $query_results, $options);
    }

    /**
     * Replace the token discovered during parsing with the associated rendered output
     *
     * @param   string $token
     * @param   string $rendered_view
     * @param   string $rendered_page
     *
     * @return  $this
     * @since   1.0
     */
    public function injectRenderedOutput($token, $rendered_view, $rendered_page)
    {
        return $this->adapter->injectRenderedOutput($token, $rendered_view, $rendered_page);
    }
}
