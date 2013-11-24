<?php
/**
 * Molajito Handler for Render
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Render\Handler;

use stdClass;
use CommonApi\Render\RenderInterface;
use Exception\Render\RenderException;
use CommonApi\Language\LanguageInterface;

/**
 * Molajito Handler for Render
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2013 Amy Stephen. All rights reserved.
 * @since      1.0
 */
class Molajito extends AbstractHandler implements RenderInterface
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
     * Language Controller
     *
     * @var    object  CommonApi\Language\LanguageInterface
     * @since  1.0
     */
    protected $language_controller;

    /**
     * Constructor
     *
     * @since   1.0
     */
    public function __construct(
        callable $triggerEvent,
        LanguageInterface $language_controller
    ) {
        $this->triggerEvent        = $triggerEvent;
        $this->language_controller = $language_controller;
    }

    /**
     * Inclusion of the Theme introduces rendered output parsed for tokens
     *
     * @param object $runtime_data
     *
     * @return  string
     * @since   1.0
     * @throws  \Exception\Render\RenderException
     */
    public function includeTheme($runtime_data)
    {
        if (file_exists($runtime_data->resource->theme->include_path)) {
            $file = $runtime_data->resource->theme->include_path;
        } else {
            throw new RenderException('Render Molajito: Theme '
            . $runtime_data->resource->theme->title
            . ' not found at '
            . $runtime_data->resource->theme->path);
        }

        $row            = new stdClass();
        $row->page_name = $runtime_data->resource->page->title;

        ob_start();
        include $file;
        $rendered_page = ob_get_contents();
        ob_end_clean();

        return $rendered_page;
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
        if (count($exclude_tokens) > 0) {
            foreach ($exclude_tokens as $exclude) {
                $this->exclude_tokens[] = trim(strtolower($exclude));
            }
            $this->exclude_tokens = $exclude_tokens;
        }

        $matches          = array();
        $tokens_to_render = array();

        preg_match_all('#<include(.*)\/>#iU', $rendered_page, $matches);

        if (count($matches) == 0) {
            return $tokens_to_render;
        }

        foreach ($matches[1] as $parsed_token) {

            $token               = new stdClass();
            $token->type         = '';
            $token->name         = '';
            $token->wrap         = '';
            $token->attributes   = array();
            $token->replace_this = '<include' . $parsed_token . '/>';

            $token_elements = array();
            $pieces         = explode(' ', $parsed_token);

            if (count($pieces) > 0) {
                foreach ($pieces as $piece) {
                    if (trim($piece) == '') {
                    } else {
                        $token_elements[] = $piece;
                    }
                }
            }

            if (count($token_elements) > 0) {

                $count_attributes = 0;
                $first            = true;

                foreach ($token_elements as $part) {

                    $pair = explode('=', $part);

                    if ($first === true) {
                        $first = false;

                        if (count($pair) == 1) {
                            $token->type = 'template';
                            $token->name = trim(strtolower($part));
                        } else {
                            $token->type = trim(strtolower($pair[0]));
                            $token->name = trim(strtolower($pair[1]));
                        }

                    } elseif (count($pair) == 2 && $pair[0] == 'wrap') {
                        $token->wrap = $pair[1];

                    } else {
                        $count_attributes ++;
                        $token->attributes[$pair[0]] = $pair[1];
                    }
                }

                if (count($this->exclude_tokens) > 0) {
                    $temp             = $tokens_to_render;
                    $tokens_to_render = array();
                    foreach ($temp as $object) {
                        if (in_array($object->type, $this->exclude_tokens)) {
                        } else {
                            $tokens_to_render[] = $object;
                        }
                    }
                }

                $tokens_to_render[] = $token;
            }
        }

        if (count($this->exclude_tokens) > 0) {
            $temp             = $tokens_to_render;
            $tokens_to_render = array();
            foreach ($temp as $object) {
                if (in_array($object->name, $this->exclude_tokens)) {
                } else {
                    $tokens_to_render[] = $object;
                }
            }
        }

        return $tokens_to_render;
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
        if ($runtime_data->render->scheme == 'page') {
            return $this->renderPageView($runtime_data);
        }

        if ($runtime_data->render->scheme == 'wrap') {
            return $this->renderWrapView($runtime_data, $query_results);
        }

        return $this->renderTemplateView($runtime_data, $parameters, $query_results, $model_registry);
    }

    /**
     * Render Page View
     *
     * @param   object $runtime_data
     *
     * @return  array
     * @since   1.0
     * @throws  \Exception\Render\RenderException
     */
    protected function renderPageView($runtime_data)
    {
        $file = $runtime_data->render->extension->include_path;

        if (file_exists($file)) {
        } else {
            throw new RenderException ('Render Molajito Page - path not found: ' . $file);
        }

        ob_start();
        include $file;
        $rendered_page = ob_get_contents();
        ob_end_clean();

        return array(
            'rendered_view'  => $rendered_page,
            'runtime_data'   => $runtime_data
        );
    }

    /**
     * Render Template View
     *
     * 1. Custom.phtml file - View handles processing of $query_results
     *
     * 2. Normal - method loops thru $query_results passing in one $row at a time
     *
     *      Head.phtml - first
     *      Body.phtml - each time
     *      Footer.phtml end
     *
     * @param   object      $runtime_data
     * @param   object      $parameters
     * @param   array       $query_results
     * @param   null|object $model_registry
     *
     * @return  string
     * @since   1.0
     * @throws  \Exception\Render\RenderException
     */
    protected function renderTemplateView($runtime_data, $parameters,
        array $query_results = array(), $model_registry = null)
    {
        /** 1. Initialisation */
        $triggerEvent = $this->triggerEvent;

        /** 2. Template View: Custom handles data */
        if (file_exists($runtime_data->render->extension->include_path . '/Custom.phtml')) {
            ob_start();
            include $runtime_data->render->extension->include_path . '/Custom.phtml';
            $rendered_view = ob_get_contents();
            ob_end_clean();
            return array(
                'rendered_view'  => $rendered_view,
                'runtime_data'   => $runtime_data
            );
        }

        /** 3. Controller manages loop */
        $total_rows    = count($query_results);
        $row_count     = 1;
        $first         = true;
        $even_or_odd   = 'odd';
        $rendered_view = '';

        if (count($query_results) > 0) {
        } else {
            return array(
                'rendered_view'  => $rendered_view,
                'runtime_data'   => $runtime_data
            );
        }

        foreach ($query_results as $row) {

            if ($row_count == $total_rows) {
                $last_row = 1;
            } else {
                $last_row = 0;
            }

            $parameters->row_count   = $row_count;
            $parameters->even_or_odd = $even_or_odd;
            $parameters->total_rows  = $total_rows;
            $parameters->last_row    = $last_row;
            $parameters->first       = $first;

            if ($first === true) {
                $first = false;

                $trigger_results = $triggerEvent(
                    $event_name = 'onBeforeRenderViewHead',
                    $runtime_data,
                    $parameters,
                    $query = null,
                    $model_registry,
                    $query_results = $row,
                    $rendered_page = null,
                    $rendered_view
                );

                foreach ($trigger_results as $key => $value) {
                    if ($key == 'runtime_data') {
                        $runtime_data = $value;
                    } elseif ($key == 'parameters') {
                        $parameters = $value;
                    } elseif ($key == 'model_registry') {
                        $model_registry = $value;
                    } elseif ($key == 'query_results') {
                        $row = $value;
                    } elseif ($key == 'rendered_view') {
                        $rendered_view  = $value;
                    }
                }

                unset($trigger_results);

                $file = $runtime_data->render->extension->include_path . '/Header.phtml';

                if (file_exists($file)) {
                    ob_start();
                    include $file;
                    $rendered_view .= ob_get_contents();
                    ob_end_clean();
                }
            }

            /** Body */
            $trigger_results = $triggerEvent(
                $event_name = 'onBeforeRenderViewItem',
                $runtime_data,
                $parameters,
                $query = null,
                $model_registry,
                $query_results = $row,
                $rendered_page = null,
                $rendered_view
            );

            foreach ($trigger_results as $key => $value) {
                if ($key == 'runtime_data') {
                    $runtime_data = $value;
                } elseif ($key == 'parameters') {
                    $parameters = $value;
                } elseif ($key == 'model_registry') {
                    $model_registry = $value;
                } elseif ($key == 'query_results') {
                    $row = $value;
                } elseif ($key == 'rendered_view') {
                    $rendered_view  = $value;
                }
            }

            unset($trigger_results);

            $file = $runtime_data->render->extension->include_path . '/Body.phtml';
            if (file_exists($file)) {
                ob_start();
                include $file;
                $rendered_view .= ob_get_contents();
                ob_end_clean();
            }

            /** Footer */
            if ($last_row == 1) {

                $trigger_results = $triggerEvent(
                    $event_name = 'onBeforeRenderViewFooter',
                    $runtime_data,
                    $parameters,
                    $query = null,
                    $model_registry,
                    $query_results = $row,
                    $rendered_page = null,
                    $rendered_view
                );

                foreach ($trigger_results as $key => $value) {
                    if ($key == 'runtime_data') {
                        $runtime_data = $value;
                    } elseif ($key == 'parameters') {
                        $parameters = $value;
                    } elseif ($key == 'model_registry') {
                        $model_registry = $value;
                    } elseif ($key == 'query_results') {
                        $row = $value;
                    } elseif ($key == 'rendered_view') {
                        $rendered_view  = $value;
                    }
                }

                unset($trigger_results);

                $file = $runtime_data->render->extension->include_path . '/Footer.phtml';

                if (file_exists($file)) {
                    ob_start();
                    include $file;
                    $rendered_view .= ob_get_contents();
                    ob_end_clean();
                }
            }

            if ($even_or_odd == 'odd') {
                $even_or_odd = 'even';
            } else {
                $even_or_odd = 'odd';
            }

            $row_count ++;
            $first = 0;
        }

        return array(
            'rendered_view'  => $rendered_view,
            'runtime_data'   => $runtime_data
        );
    }

    /**
     * Wrap Template Rendered Output
     *
     * @param   object $runtime_data
     * @param   array  $query_results contains one column $row->content
     *
     * @return  string
     * @since   1.0
     * @throws  \Exception\Render\RenderException
     */
    protected function renderWrapView($runtime_data, array $query_results)
    {
        /** Header */
        $row             = $query_results[0];
        $rendered_output = '';

        $file = $runtime_data->render->extension->include_path . '/Header.phtml';
        if (file_exists($file)) {
            ob_start();
            include $file;
            $rendered_output = ob_get_contents();
            ob_end_clean();
        }

        /** Body */
        $file = $runtime_data->render->extension->include_path . '/Body.phtml';
        if (file_exists($file)) {
            ob_start();
            include $file;
            $rendered_output .= ob_get_contents();
            ob_end_clean();
        }

        /** Footer */
        $file = $runtime_data->render->extension->include_path . '/Footer.phtml';
        if (file_exists($file)) {
            ob_start();
            include $file;
            $rendered_output .= ob_get_contents();
            ob_end_clean();
        }

        return array(
            'rendered_view'  => $rendered_output,
            'runtime_data'   => $runtime_data
        );
    }

    /**
     * Replace the token discovered during parsing with the associated rendered output
     *
     * @param   string $token
     * @param   string $rendered_view
     * @param   string $rendered_view
     *
     * @return  $this
     * @since   1.0
     * @throws  \Exception\Render\RenderException
     */
    public function injectRenderedOutput($token, $rendered_view, $rendered_page)
    {
        return str_replace($token, $rendered_view, $rendered_page);
    }
}
