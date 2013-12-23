<?php
/**
 * Molajito Render Handler
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Render\Handler;

use stdClass;
use CommonApi\Render\RenderInterface;
use CommonApi\Controller\UrlInterface;
use CommonApi\Controller\DateInterface;
use CommonApi\Language\LanguageInterface;
use CommonApi\Exception\RuntimeException;
use CommonApi\Model\FieldhandlerInterface;
use CommonApi\Authorisation\AuthorisationInterface;

/**
 * Molajito Render Handler
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
     * Schedule Event - anonymous function to FrontController event_callback method
     *
     * @var    callable
     * @since  1.0
     */
    protected $event_callback;

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
     * Path to Include File
     *
     * @var    array
     * @since  1.0
     */
    protected $include_path = array();

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
     * Object containing a single row for using within View
     *
     * @var    array
     * @since  1.0
     */
    protected $row = null;

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
     * @param  FieldhandlerInterface  $fieldhandler
     * @param  DateInterface          $date_controller
     * @param  UrlInterface           $url_controller
     * @param  LanguageInterface      $language_controller
     * @param  AuthorisationInterface $authorisation_controller
     * @param  object                 $resource
     * @param  object                 $runtime_data
     * @param  callable               $event_callback
     * @param  array                  $exclude_tokens
     *
     * @since  1.0
     */
    public function __construct(
        FieldhandlerInterface $fieldhandler,
        DateInterface $date_controller,
        UrlInterface $url_controller,
        LanguageInterface $language_controller,
        AuthorisationInterface $authorisation_controller,
        $resource,
        $runtime_data,
        callable $event_callback = null,
        array $exclude_tokens = array()
    ) {
        $this->fieldhandler             = $fieldhandler;
        $this->date_controller          = $date_controller;
        $this->url_controller           = $url_controller;
        $this->language_controller      = $language_controller;
        $this->authorisation_controller = $authorisation_controller;
        $this->resource                 = $resource;
        $this->runtime_data             = $runtime_data;
        $this->event_callback           = $event_callback;
        $this->exclude_tokens           = $exclude_tokens;
    }

    /**
     * Render Application
     *
     * @return  $this
     * @since   1.0
     */
    public function render()
    {
        /** Step 1. Retrieve Rendering Starter information from Route */
        $this->getResourceExtensions();

        /** Step 2. Schedule onBeforeParse Event */
        $options                 = $this->initializeOptions();
        $options['runtime_data'] = $this->runtime_data;
        $this->scheduleEvent('onBeforeParse', $options);

        /** Step 3. Render Theme */
        $this->include_path = $this->runtime_data->resource->theme->include_path;
        $page_name          = $this->runtime_data->resource->page->id;
        $this->includeTheme($page_name);

        /** Step 4: Process Body */
        $this->renderLoop($this->exclude_tokens);

        /** Step 5. Schedule onBeforeParseHead Event */
        $options                  = $this->initializeOptions();
        $options['runtime_data']  = $this->runtime_data;
        $options['rendered_page'] = $this->rendered_page;
        $this->scheduleEvent('onBeforeParseHead', $options);

        /** Step 6: Render Loop for Head */
        $this->renderLoop();

        /** Step 7. Schedule onAfterRender Event */
        $options                  = $this->initializeOptions();
        $options['runtime_data']  = $this->runtime_data;
        $options['rendered_page'] = $this->rendered_page;
        $this->scheduleEvent('onAfterRender', $options);

        /** Step 8. Done */
        return $this;
    }

    /**
     * Before Parsing
     *
     * @return  $this
     * @since   1.0
     */
    protected function getResourceExtensions()
    {
        /** Step 1. Get Resource Extensions */
        $runtime_data = $this->runtime_data;

        $runtime_data->resource->theme
            = $this->resource->get(
            'Theme:///Molajo//Theme//'
            . $runtime_data->resource->parameters->theme_id
        );

        $runtime_data->resource->page
            = $this->resource->get(
            'Page:///Molajo//View//Page//'
            . $runtime_data->resource->parameters->page_view_id
        );

        $runtime_data->resource->template
            = $this->resource->get(
            'Template:///Molajo//View//Template//'
            . $runtime_data->resource->parameters->template_view_id
        );

        $runtime_data->resource->wrap
            = $this->resource->get(
            'Wrap:///Molajo//View//Wrap//'
            . $runtime_data->resource->parameters->wrap_view_id
        );

        $this->runtime_data = $runtime_data;

        return $this;
    }

    /**
     * Render Loop - Process the Body, then Process the Head
     *
     * @param   array $exclude_tokens
     *
     * @return  $this
     * @throws  \CommonApi\Exception\RuntimeException
     * @since   1.0
     */
    protected function renderLoop(array $exclude_tokens = array())
    {
        /** Step 1. Initialise */
        $stop_loop_count = $this->runtime_data->reference_data->stop_loop_count;
        $complete        = false;
        $loop_counter    = 0;

        while ($complete === false) {

            /** Step 2. Counter */
            $loop_counter ++;

            /** Step 3. Parse Output for Tokens */
            $tokens = $this->parseTokens($exclude_tokens);

            if (is_array($tokens) && count($tokens) > 0) {
            } else {
                $complete = true;
                break;
            }

            /** Step 4. Render Output for Tokens */
            foreach ($tokens as $token) {
                $this->renderToken($token);
            }

            if ($loop_counter > $stop_loop_count) {
                throw new RuntimeException
                ('Frontcontroller Renderloop: Maximum loop count exceeded: ' . $loop_counter);
            }

            continue;
        }

        /** Step 12. Complete */
        return $this;
    }

    /**
     * Render Token
     *
     * @param   object $token
     *
     * @return  $this
     * @since   1.0
     */
    protected function renderToken($token)
    {
//        echo 'TOKEN NAME ' . $token->name . '<br />';

        /** Step 1. Initialise */
        $this->rendered_view = '';

        /** Step 2. Get Rendering Extension */
        $this->getExtension($token);

        /** Step 3. Get Query Data for Rendering Extension */
        $this->getData($token);

        /** Step 4. Schedule onBeforeRenderView Event */
        $options                   = $this->initializeOptions();
        $options['runtime_data']   = $this->runtime_data;
        $options['parameters']     = $this->parameters;
        $options['model_registry'] = $this->model_registry;
        $options['query_results']  = $this->query_results;
        $options['rendered_view']  = $this->rendered_view;
        $options['rendered_page']  = $this->rendered_page;
        $this->scheduleEvent('onBeforeRenderView', $options);

        /** Step 5. Render View */
        $this->rendered_view = '';

        $this->include_path = $this->runtime_data->render->extension->include_path;

        if ($this->runtime_data->render->scheme == 'page') {
            $this->renderPageView();

        } else {
            $this->renderTemplateView();

            if ($token->wrap == '') {
            } else {
                $this->renderWrapView($token->wrap);
            }
        }

        /** Step 6. Schedule onAfterRenderView Event */
        $options                   = $this->initializeOptions();
        $options['runtime_data']   = $this->runtime_data;
        $options['parameters']     = $this->parameters;
        $options['model_registry'] = $this->model_registry;
        $options['query_results']  = $this->query_results;
        $options['rendered_view']  = $this->rendered_view;
        $options['rendered_page']  = $this->rendered_page;

        $this->scheduleEvent('onAfterRenderView', $options);

        /** Step 7. Inject Rendered Output */
        $this->rendered_page = str_replace($token->replace_this, $this->rendered_view, $this->rendered_page);

        return $this;
    }

    /**
     * Get Data required to render token
     *
     * @param   object $token
     *
     * @return  $this
     * @since   1.0
     */
    protected function getExtension($token)
    {
        $runtime_data = $this->runtime_data;

        $runtime_data->render        = new stdClass();
        $runtime_data->render->token = $token;

        $scheme = ucfirst(strtolower($token->type));

        if ($scheme == 'Page' || $scheme == 'Wrap') {
            $model = $scheme . ':///Molajo//View//' . $scheme . '//' . ucfirst(strtolower($token->name));
        } else {
            $model = 'Template' . ':///Molajo//View//Template//' . ucfirst(strtolower($token->name));
        }

        $runtime_data->render->scheme    = strtolower($scheme);
        $runtime_data->render->extension = $this->resource->get($model);

        $this->runtime_data = $runtime_data;

        return $this;
    }

    /**
     * Get Data required to render token
     *
     * @return  array
     * @since   1.0
     */
    protected function getData()
    {
        $query_results  = array();
        $model_registry = array();

        $runtime_data = $this->runtime_data;

        $scheme = $runtime_data->render->scheme;

        if (strtolower($scheme) == 'page') {

        } elseif (isset($runtime_data->render->extension->parameters->model_type)
            && isset($runtime_data->render->extension->parameters->model_name)
        ) {
            $model_type = strtolower($runtime_data->render->extension->parameters->model_type);
            $model_name = strtolower($runtime_data->render->extension->parameters->model_name);

            // primary uses resource data
            if ($model_name == 'primary') {
                if (is_array($runtime_data->resource->data)) {
                    $query_results  = $runtime_data->resource->data;
                    $model_registry = $runtime_data->resource->model_registry;
                } else {
                    $query_results  = array($runtime_data->resource->data);
                    $model_registry = $runtime_data->resource->model_registry;
                }

                // no data (likely will use $runtime_data in custom.phtml view
            } elseif (trim($model_name) == '') {


                // runtime_data (application parameters, plugin_data, etc. push into array)
            } elseif ($model_type == 'runtime_data') {

                if (isset($runtime_data->$model_name)) {
                    $query_results = $runtime_data->$model_name;
                }

                // Ex. $this->runtime_data->plugin_data->grid_toolbar
            } elseif ($model_type == 'plugin_data') {

                if (isset($runtime_data->plugin_data->$model_name)) {
                    $query_results = $runtime_data->plugin_data->$model_name;
                }

            } else {
                // Query_results built in onBeforeExecute
                if (isset($runtime_data->plugin_data->$model_name->data)) {
                    $query_results = $runtime_data->plugin_data->$model_name->data;
                } else {
                    $query_results = array();
                }
                if (isset($runtime_data->plugin_data->$model_name->model_registry)) {
                    $model_registry = $runtime_data->plugin_data->$model_name->model_registry;
                }
            }
        } else {
            echo '<pre>in getData bad 2 - no model_name at all ... hmmmm';
            var_dump($runtime_data->render->extension);
            die;
        }

        if (isset($query_results->parameters)) {
            $parameters = $query_results->parameters;
            unset($query_results->parameters);

        } else {
            $parameters = new stdClass();
        }

        if (is_array($query_results)) {
        } else {
            $query_results = array($query_results);
        }

        $this->query_results  = $query_results;
        $this->model_registry = $model_registry;
        $this->parameters     = $parameters;

        return $this;
    }

    /**
     * Inclusion of the Theme introduces rendered output parsed for tokens
     *
     * @param   string $page_name
     *
     * @return  $this
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function includeTheme($page_name)
    {
        if (file_exists($this->include_path)) {
        } else {
            throw new RuntimeException
            ('Render Molajito: Theme not found at ' . $this->include_path);
        }

        $this->row            = new stdClass();
        $this->row->page_name = $page_name;

        ob_start();
        include $this->include_path;
        $this->rendered_page = ob_get_contents();
        ob_end_clean();

        return $this;
    }

    /**
     * Parse rendered output looking for tokens (excluded tokens are head, held until all body rendered)
     *
     * @param   array $exclude_tokens
     *
     * @return  array
     * @since   1.0
     */
    public function parseTokens(array $exclude_tokens = array())
    {
        if (count($exclude_tokens) > 0) {
            foreach ($exclude_tokens as $exclude) {
                $this->exclude_tokens[] = trim(strtolower($exclude));
            }
            $this->exclude_tokens = $exclude_tokens;
        }

        $matches          = array();
        $tokens_to_render = array();

        preg_match_all('#<include(.*)\/>#iU', $this->rendered_page, $matches);

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
            throw new RuntimeException
            ('Render Molajito Page - path not found: ' . $this->include_path);
        }

        ob_start();
        include $this->include_path;
        $this->rendered_view = ob_get_contents();
        ob_end_clean();

        return $this;
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
     * @return  $this
     * @since   1.0
     */
    protected function renderTemplateView()
    {
        $this->rendered_view = '';

        /** 1. Template View: Custom handles data */
        if (file_exists($this->include_path . '/Custom.phtml')) {
            ob_start();
            include $this->include_path . '/Custom.phtml';
            $this->rendered_view = ob_get_contents();
            ob_end_clean();

            return $this;
        }

        /** 2. Pushes each row from query results into View */
        $total_rows          = count($this->query_results);
        $row_count           = 1;
        $first               = true;
        $even_or_odd         = 'odd';
        $this->rendered_view = '';

        if (count($this->query_results) > 0) {
        } else {
            return $this;
        }

        foreach ($this->query_results as $this->row) {

            if ($row_count == $total_rows) {
                $last_row = 1;
            } else {
                $last_row = 0;
            }

            $this->row->row_count   = $row_count;
            $this->row->even_or_odd = $even_or_odd;
            $this->row->total_rows  = $total_rows;
            $this->row->last_row    = $last_row;
            $this->row->first       = $first;

            if ($first === true) {
                $first = false;

                $options                   = $this->initializeOptions();
                $options['runtime_data']   = $this->runtime_data;
                $options['parameters']     = $this->parameters;
                $options['model_registry'] = $this->model_registry;
                $options['query_results']  = $this->row;
                $options['rendered_view']  = $this->rendered_view;
                $options['rendered_page']  = $this->rendered_page;

                $this->scheduleEvent('onBeforeRenderViewHead', $options);

                $file = $this->include_path . '/Header.phtml';

                if (file_exists($file)) {
                    ob_start();
                    include $file;
                    $this->rendered_view .= ob_get_contents();
                    ob_end_clean();
                }
            }

            /** Body */
            $options                   = $this->initializeOptions();
            $options['runtime_data']   = $this->runtime_data;
            $options['parameters']     = $this->parameters;
            $options['model_registry'] = $this->model_registry;
            $options['query_results']  = $this->row;
            $options['rendered_view']  = $this->rendered_view;
            $options['rendered_page']  = $this->rendered_page;

            $this->scheduleEvent('onBeforeRenderViewItem', $options);

            $file = $this->include_path . '/Body.phtml';
            if (file_exists($file)) {
                ob_start();
                include $file;
                $this->rendered_view .= ob_get_contents();
                ob_end_clean();
            }

            /** Footer */
            if ($last_row == 1) {

                $options                   = $this->initializeOptions();
                $options['runtime_data']   = $this->runtime_data;
                $options['parameters']     = $this->parameters;
                $options['model_registry'] = $this->model_registry;
                $options['query_results']  = $this->row;
                $options['rendered_view']  = $this->rendered_view;
                $options['rendered_page']  = $this->rendered_page;

                $this->scheduleEvent('onBeforeRenderViewFooter', $options);

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
     * @param   string $wrap
     *
     * @return  string
     * @since   1.0
     */
    protected function renderWrapView($wrap)
    {
        $model                                 = 'Wrap' . ':///Molajo//View//Wrap//' . ucfirst(strtolower($wrap));
        $this->runtime_data->render->scheme    = 'wrap';
        $this->runtime_data->render->extension = $this->resource->get($model);
        $this->include_path                    = $this->runtime_data->render->extension->include_path;

        $this->row           = new stdClass();
        $this->row->title    = '';
        $this->row->subtitle = '';
        $this->row->content  = $this->rendered_view;

        /** Header */
        $this->rendered_view = '';

        $file = $this->include_path . '/Header.phtml';

        if (file_exists($file)) {
            ob_start();
            include $file;
            $this->rendered_view = ob_get_contents();
            ob_end_clean();
        }

        /** Body */
        $file = $this->include_path . '/Body.phtml';
        if (file_exists($file)) {
            ob_start();
            include $file;
            $this->rendered_view .= ob_get_contents();
            ob_end_clean();
        }

        /** Footer */
        $file = $this->include_path . '/Footer.phtml';
        if (file_exists($file)) {
            ob_start();
            include $file;
            $this->rendered_view .= ob_get_contents();
            ob_end_clean();
        }

        return $this;
    }

    /**
     * Register Extension Plugins for Event
     *
     * @return  array
     * @since   1.0
     */
    protected function getPluginList()
    {
        $model_registry_name = $this->get('model_registry_name');

        $modelPlugins = array();

        if ((int)$this->registry->get($model_registry_name, 'process_events') > 0) {
            $modelPlugins = $this->registry->get($model_registry_name, 'plugins');

            if (is_array($modelPlugins)) {
            } else {
                $modelPlugins = array();
            }
        }

        $templatePlugins = array();

        if ((int)$this->registry->get($model_registry_name, 'process_template_plugins') > 0) {
            $name = $this->registry->get($model_registry_name, 'template_view_path_node');
            if ($name == '') {
            } else {
                $templatePlugins = $this->registry->get(ucfirst(strtolower($name)) . 'Templates', 'plugins');

                if (is_array($templatePlugins)) {
                } else {
                    $templatePlugins = array();
                }
            }
        }

        $plugins = array_merge($modelPlugins, $templatePlugins);
        if (is_array($plugins)) {
        } else {
            $plugins = array();
        }

        $page_type = $this->get('catalog_page_type');
        if ($page_type == '') {
        } else {
            $plugins[] = 'Pagetype' . strtolower($page_type);
        }

        $template = $this->get('template_view_path_node');
        if ($template == '') {
        } else {
            $plugins[] = $template;
        }

        if ((int)$this->registry->get($model_registry_name, 'process_events') == 0
            && count($plugins) == 0
        ) {
            $this->plugins;

            return array();
        }

        $plugins[] = 'Application';

        return $plugins;
    }

    /**
     * Initialise Options Array for Event
     *
     * @return  array
     * @since   1.0
     */
    protected function initializeOptions()
    {
        $options                   = array();
        $options['runtime_data']   = null;
        $options['parameters']     = null;
        $options['query']          = null;
        $options['model_registry'] = null;
        $options['query_results']  = null;
        $options['rendered_view']  = null;
        $options['rendered_page']  = null;

        return $options;
    }

    /**
     * Schedule the Render Event
     *
     * @param   string $event_name
     * @param   array  $options
     *
     * @return  $this
     * @since   1.0
     */
    protected function scheduleEvent($event_name, $options)
    {
        $schedule_event = $this->event_callback;

        $event_results = $schedule_event($event_name, $options);

        if (count($event_results) == 0) {
            return $this;
        }

        foreach ($event_results as $key => $value) {
            if ($key == 'runtime_data') {
                $this->runtime_data = $value;

            } elseif ($key == 'parameters') {
                $this->parameters = $value;

            } elseif ($key == 'model_registry') {
                $this->model_registry = $value;

            } elseif ($key == 'query_results') {
                $query_results = $value;
                if (is_array($query_results)) {
                } else {
                    if ($query_results === null || trim($query_results) == '') {
                        $query_results = array();
                    } else {
                        $query_results = array($query_results);
                    }
                }
                $this->row = $query_results;

            } elseif ($key == 'rendered_page') {
                $this->rendered_page = $value;

            } elseif ($key == 'rendered_view') {
                $this->rendered_view = $value;
            }
        }

        return $this;
    }
}
