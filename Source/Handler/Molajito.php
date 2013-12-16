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
use CommonApi\Exception\RuntimeException;
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
     * Object containing a single row for using within View
     *
     * @var    array
     * @since  1.0
     */
    protected $row = null;

    /**
     * Schedule Event - anonymous function to FrontController triggerEvent method
     *
     * @var    callable
     * @since  1.0
     */
    protected $triggerEvent;

    /**
     * Exclude tokens from parsing (Head tokens held until end)
     *
     * @var    array
     * @since  1.0
     */
    protected $exclude_tokens = array();

    /**
     * Scheme
     *
     * @var    object
     * @since  1.0
     */
    protected $scheme = null;

    /**
     * Resource
     *
     * @var    object
     * @since  1.0
     */
    protected $resource = null;

    /**
     * Fieldhandler
     *
     * @var    object  CommonApi\Model\FieldhandlerInterface
     * @since  1.0
     */
    protected $fieldhandler = null;

    /**
     * Date Controller
     *
     * @var    object  CommonApi\Controller\DateInterface
     * @since  1.0
     */
    protected $date_controller = null;

    /**
     * Url Controller
     *
     * @var    object  CommonApi\Controller\UrlInterface
     * @since  1.0
     */
    protected $url_controller = null;

    /**
     * Language Instance
     *
     * @var    object CommonApi\Language\LanguageInterface
     * @since  1.0
     */
    protected $language_controller;

    /**
     * Authorisation Controller
     *
     * @var    object  CommonApi\Authorisation\AuthorisationInterface
     * @since  1.0
     */
    protected $authorisation_controller;

    /**
     * Runtime Data
     *
     * @var    array
     * @since  1.0
     */
    protected $runtime_data = array();

    /**
     * Parameters
     *
     * @var    object
     * @since  1.0
     */
    protected $parameters = null;

    /**
     * Model Registry
     *
     * @var    object
     * @since  1.0
     */
    protected $model_registry = null;

    /**
     * Query Results
     *
     * @var    object
     * @since  1.0
     */
    protected $query_results = null;

    /**
     * View Rendered Output
     *
     * @var    string
     * @since  1.0
     */
    protected $rendered_view = null;

    /**
     * Page Rendered Output
     *
     * @var    string
     * @since  1.0
     */
    protected $rendered_page = null;

    /**
     * Constructor
     *
     * @param  string $plugin_name
     * @param  string $event_name
     * @param  array  $data
     *
     * @since  1.0
     */
    public function __construct(
        callable $triggerEvent,
        array $options = array()
    ) {
        $this->triggerEvent   = $triggerEvent;

        foreach ($options as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Get the current value (or default) of the specified property
     *
     * @param   string $key
     *
     * @return  null|mixed
     * @since   1.0
     */
    public function get($key)
    {
        if (isset($this->$key)) {
            return $this->$key;
        }

        return null;
    }

    /**
     * Inclusion of the Theme introduces rendered output parsed for tokens
     *
     * @param   string $this->include_path
     * @param   array  $options
     *
     * @return  string
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function includeTheme($include_path, array $options = array())
    {
        if (isset($options['page_name'])) {
            $page_name = $options['page_name'];
            unset($options['page_name']);
        }

        if (count($options) > 0) {
            foreach ($options as $key => $value) {
                $this->$key = $value;
            }
        }

        $this->include_path = $include_path;

        if (file_exists($this->include_path)) {
        } else {
            throw new RuntimeException('Render Molajito: Theme not found at ' . $this->include_path);
        }

        $this->row            = new stdClass();
        $this->row->page_name = $page_name;

        ob_start();
        include $this->include_path;
        $this->rendered_page = ob_get_contents();
        ob_end_clean();

        return $this->rendered_page;
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
    public function parseTokens(
        $rendered_page,
        array $exclude_tokens = array()
    ) {
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
     * @param   string $include_path
     * @param   array  $query_results
     * @param   array  $options
     *
     * @return  array
     * @since   0.1
     */
    public function renderView($include_path, array $query_results = array(), array $options = array())
    {
        foreach ($options as $key => $value) {
            $this->$key = $value;
        }

        $this->include_path  = $include_path;

        $this->query_results = $query_results;

        if ($this->scheme == 'page') {
            return $this->renderPageView();
        }

        if ($this->scheme == 'wrap') {
            return $this->renderWrapView();
        }

        return $this->renderTemplateView();
    }

    /**
     * Render Page View
     *
     * @return  string
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    protected function renderPageView()
    {
        if (file_exists($this->include_path)) {
        } else {
            throw new RuntimeException ('Render Molajito Page - path not found: ' . $this->include_path);
        }

        ob_start();
        include $this->include_path;
        $rendered_page = ob_get_contents();
        ob_end_clean();

        return $rendered_page;
    }

    /**
     * Render Template View
     *
     * 1. Custom.phtml file - View handles processing of $this->query_results
     *
     * 2. Normal - method loops thru $this->query_results passing in one $row at a time
     *
     *      Head.phtml - first
     *      Body.phtml - each time
     *      Footer.phtml end
     *
     * @return  string
     * @since   1.0
     */
    protected function renderTemplateView()
    {
        /** 1. Initialisation */
        $triggerEvent = $this->triggerEvent;

        /** 2. Template View: Custom handles data */
        if (file_exists($this->include_path . '/Custom.phtml')) {
            ob_start();
            include $this->include_path . '/Custom.phtml';
            $this->rendered_view = ob_get_contents();
            ob_end_clean();
            return $this->rendered_view;
        }

        /** 3. Controller manages loop */
        $total_rows    = count($this->query_results);
        $row_count     = 1;
        $first         = true;
        $even_or_odd   = 'odd';
        $this->rendered_view = '';

        if (count($this->query_results) > 0) {
        } else {
            return array(
                'rendered_view' => $this->rendered_view,
                'runtime_data'  => $this->runtime_data
            );
        }

        foreach ($this->query_results as $row) {

            if ($row_count == $total_rows) {
                $last_row = 1;
            } else {
                $last_row = 0;
            }

            $this->parameters->row_count   = $row_count;
            $this->parameters->even_or_odd = $even_or_odd;
            $this->parameters->total_rows  = $total_rows;
            $this->parameters->last_row    = $last_row;
            $this->parameters->first       = $first;

            if ($first === true) {
                $first = false;

                $trigger_results = $triggerEvent(
                    $event_name = 'onBeforeRenderViewHead',
                    $this->runtime_data,
                    $this->parameters,
                    $this->query = null,
                    $this->model_registry,
                    $this->query_results = $row,
                    $this->rendered_page = null,
                    $this->rendered_view
                );

                foreach ($trigger_results as $key => $value) {
                    if ($key == 'runtime_data') {
                        $this->runtime_data = $value;
                    } elseif ($key == 'parameters') {
                        $this->parameters = $value;
                    } elseif ($key == 'model_registry') {
                        $this->model_registry = $value;
                    } elseif ($key == 'query_results') {
                        $row = $value;
                    } elseif ($key == 'rendered_view') {
                        $this->rendered_view = $value;
                    }
                }

                unset($trigger_results);

                $file = $this->include_path . '/Header.phtml';

                if (file_exists($file)) {
                    ob_start();
                    include $file;
                    $this->rendered_view .= ob_get_contents();
                    ob_end_clean();
                }
            }

            /** Body */
            $trigger_results = $triggerEvent(
                $event_name = 'onBeforeRenderViewItem',
                $this->runtime_data,
                $this->parameters,
                $this->query = null,
                $this->model_registry,
                $this->query_results = $row,
                $this->rendered_page = null,
                $this->rendered_view
            );

            foreach ($trigger_results as $key => $value) {
                if ($key == 'runtime_data') {
                    $this->runtime_data = $value;
                } elseif ($key == 'parameters') {
                    $this->parameters = $value;
                } elseif ($key == 'model_registry') {
                    $this->model_registry = $value;
                } elseif ($key == 'query_results') {
                    $row = $value;
                } elseif ($key == 'rendered_view') {
                    $this->rendered_view = $value;
                }
            }

            unset($trigger_results);

            $file = $this->include_path . '/Body.phtml';
            if (file_exists($file)) {
                ob_start();
                include $file;
                $this->rendered_view .= ob_get_contents();
                ob_end_clean();
            }

            /** Footer */
            if ($last_row == 1) {

                $trigger_results = $triggerEvent(
                    $event_name = 'onBeforeRenderViewFooter',
                    $this->runtime_data,
                    $this->parameters,
                    $this->query = null,
                    $this->model_registry,
                    $this->query_results = $row,
                    $this->rendered_page = null,
                    $this->rendered_view
                );

                foreach ($trigger_results as $key => $value) {
                    if ($key == 'runtime_data') {
                        $this->runtime_data = $value;
                    } elseif ($key == 'parameters') {
                        $this->parameters = $value;
                    } elseif ($key == 'model_registry') {
                        $this->model_registry = $value;
                    } elseif ($key == 'query_results') {
                        $row = $value;
                    } elseif ($key == 'rendered_view') {
                        $this->rendered_view = $value;
                    }
                }

                unset($trigger_results);

                $file = $this->include_path . '/Footer.phtml';

                if (file_exists($file)) {
                    ob_start();
                    include $file;
                    $this->rendered_view .= ob_get_contents();
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

        return $this->rendered_view;
    }

    /**
     * Wrap Template Rendered Output
     *
     * @return  string
     * @since   1.0
     */
    protected function renderWrapView()
    {
        /** Header */
        $row             = $this->query_results[0];
        $this->rendered_output = '';

        $file = $this->include_path . '/Header.phtml';
        if (file_exists($file)) {
            ob_start();
            include $file;
            $this->rendered_output = ob_get_contents();
            ob_end_clean();
        }

        /** Body */
        $file = $this->include_path . '/Body.phtml';
        if (file_exists($file)) {
            ob_start();
            include $file;
            $this->rendered_output .= ob_get_contents();
            ob_end_clean();
        }

        /** Footer */
        $file = $this->include_path . '/Footer.phtml';
        if (file_exists($file)) {
            ob_start();
            include $file;
            $this->rendered_output .= ob_get_contents();
            ob_end_clean();
        }

        return $this->rendered_output;
    }

    /**
     * Replace the token discovered during parsing with the associated rendered output
     *
     * @return  $this
     * @since   1.0
     */
    public function injectRenderedOutput($token, $rendered_view, $rendered_page)
    {
        return str_replace($token, $rendered_view, $rendered_page);
    }
}
