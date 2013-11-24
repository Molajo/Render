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
use Exception\Render\RenderException;

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
     * Render Theme and its include Page
     *
     * @param   object $runtime_data
     *
     * @since   1.0
     * @return  string
     */
    public function includeTheme($runtime_data)
    {
        return $this->adapter->includeTheme($runtime_data);
    }

    /**
     * Parse rendered output looking for tokens (excluded tokens are head, held until all body rendered)
     *
     * @param   string $rendered_page
     * @param   array  $exclude_tokens
     *
     * @return  array
     * @since   1.0
     * @throws  \Exception\Render\RenderException
     */
    public function parseTokens($rendered_page, array $exclude_tokens = array())
    {
        return $this->adapter->parseTokens($rendered_page, $exclude_tokens);
    }

    /**
     * Render output for tag discovered in parsing
     *
     * @param   object      $runtime_data
     * @param   object      $parameters
     * @param   array       $query_results
     * @param   null|object $model_registry
     *
     * @return  array
     * @since   1.0
     * @throws  \Exception\Render\RenderException
     */
    public function renderView($runtime_data, $parameters, array $query_results = array(), $model_registry = null)
    {
        return $this->adapter->renderView($runtime_data, $parameters, $query_results, $model_registry);
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
     * @throws  \Exception\Render\RenderException
     */
    public function injectRenderedOutput($token, $rendered_view, $rendered_page)
    {
        return $this->adapter->injectRenderedOutput($token, $rendered_view, $rendered_page);
    }
}
