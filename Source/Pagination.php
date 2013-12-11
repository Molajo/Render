<?php
/**
 * Pagination
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Render;

use Countable;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use CommonApi\Render\PaginationInterface;
use CommonApi\Model\FieldhandlerInterface;

/**
 * Pagination
 *
 * To get "Prev/Next" type pagination, set $per_page to 1
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2013 Amy Stephen. All rights reserved.
 * @since      1.0
 */
class Pagination implements PaginationInterface, IteratorAggregate, ArrayAccess, Countable
{
    /**
     * Data - numerically indexed array items
     *
     * @var    array
     * @since  1.0
     */
    protected $data;

    /**
     * Base URL for pagination page
     *
     * @var    int
     * @since  1.0
     */
    protected $page_url;

    /**
     * URL Filters
     *
     * @var    array
     * @since  1.0
     */
    protected $query_parameters = array();

    /**
     * Total Items (could include more than the pagination set)
     *
     * @var    int
     * @since  1.0
     */
    protected $total_items;

    /**
     * Items per page
     *
     * @var    int
     * @since  1.0
     */
    protected $per_page = 10;

    /**
     * Number of page links to show
     *
     * @var    int
     * @since  1.0
     */
    protected $display_links = 5;

    /**
     * Get the first page number to use when
     * looping through the display page buttons
     *
     * @var    int
     * @since  1.0
     */
    protected $start_display_page;

    /**
     * Get the last page number to use when
     * looping through the display page buttons
     *
     * @var    int
     * @since  1.0
     */
    protected $stop_display_page;

    /**
     * Current Page minus 1
     *
     * @var    int
     * @since  1.0
     */
    protected $page = 0;

    /**
     * Last Page
     *
     * @var    int
     * @since  1.0
     */
    protected $last_page;

    /**
     * Fieldhandler
     *
     * @var    object CommonApi\Model\FieldhandlerInterface
     * @since  1.0
     */
    protected $fieldhandler;

    /**
     * Construct
     *
     * @param  FieldhandlerInterface $fieldhandler     Filter input
     * @param  array                 $data             Data to be displayed (not full results)
     * @param  string                $page_url         URL for page on which paginated appears
     * @param  array                 $query_parameters URL Query Parameters (other than page)
     * @param  int                   $total_items      Total items in full resultset for data
     * @param  int                   $per_page         Number of items per page
     * @param  int                   $display_links    Number of page number "buttons" to show
     * @param  int                   $page             Current page
     *
     * @since  1.0
     */
    public function __construct(
        FieldhandlerInterface $fieldhandler,
        array $data = array(),
        $page_url,
        array $query_parameters = array(),
        $total_items,
        $per_page,
        $display_links,
        $page
    ) {
        $this->data = $data;
        $this->getIterator();

        $this->page_url         = $page_url;
        $this->query_parameters = $query_parameters;

        $this->total_items = $total_items;

        if ((int)$per_page === 0) {
            $per_page = 9999999;
            $page     = 0;
        }
        $this->per_page = $per_page;

        if ((int)$page > $this->total_items) {
            $page = 0;
        }
        $this->page = $page;

        $this->last_page = ceil($this->total_items / $per_page);

        $this->display_links = $display_links;

        $this->start_display_page = 1;
        $this->stop_display_page  = $this->last_page;
        $temp                     = ceil($this->last_page / $this->display_links);
        for ($i = 1; $i < $temp + 1; $i ++) {
            if (($i * $this->display_links) + 1 >= $page
                && $page >= ($i * $this->display_links) - $this->display_links + 1
            ) {

                $this->start_display_page = ($i * $this->display_links) - $this->display_links + 1;
                $this->stop_display_page  = ($i * $this->display_links) + 1;
            }
        }
    }

    /**
     * Get the first page number (always page=1)
     *
     * @return  int
     * @since   1.0
     */
    public function getFirstPage()
    {
        if ((int)$this->last_page === 0) {
            return 0;
        }
        return 1;
    }

    /**
     * Get the page number previous to the first displayed page number link
     *
     * @return  int
     * @since   1.0
     */
    public function getPrevPage()
    {
        if (((int)$this->start_display_page - 1) > 1) {
            return (int)$this->start_display_page;
        }

        return 1;
    }

    /**
     * Get the first page number to use when looping through the display page number buttons
     *
     * @return  int
     * @since   1.0
     */
    public function getStartDisplayPage()
    {
        return $this->start_display_page;
    }

    /**
     * Get the current page number
     *
     * @return  int
     * @since   1.0
     */
    public function getCurrentPage()
    {
        return (int)$this->page + 1;
    }

    /**
     * Get the last page number to use when looping through the display page number buttons
     *
     * @return  int
     * @since   1.0
     */
    public function getStopDisplayPage()
    {
        return $this->stop_display_page;
    }

    /**
     * Get the page number following the last displayed page number link
     *
     * @return  int
     * @since   1.0
     */
    public function getNextPage()
    {
        if ((int)$this->stop_display_page > (int)$this->last_page) {
            return (int)$this->last_page;
        }

        return (int)$this->stop_display_page;
    }

    /**
     * Get the final page number
     *
     * @return  int
     * @since   1.0
     */
    public function getLastPage()
    {
        return (int)$this->last_page;
    }

    /**
     * Get data paginated
     *
     * @return  array
     * @since   1.0
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the total number of items in the recordset (not just those displayed on the page)
     *
     * @return  int
     * @since   1.0
     */
    public function getTotalItems()
    {
        return (int)$this->total_items;
    }

    /**
     * Get the URL for the specified key
     *
     *  - Use a numeric page number
     *  - Or, use a specific key value: first, previous, current, next, or last
     *
     * @param   mixed $page
     *
     * @return  string
     * @since   1.0
     */
    public function getPageUrl($page)
    {
        if (strtolower($page) == 'first') {
            $page = $this->getFirstPage();
        } elseif (strtolower($page) == 'previous') {
            $page = $this->getPrevPage();
        } elseif (strtolower($page) == 'current') {
            $page = $this->getCurrentPage();
        } elseif (strtolower($page) == 'next') {
            $page = $this->getNextPage();
        } elseif (strtolower($page) == 'last') {
            $page = $this->getLastPage();
        } else {
            $page = (int)$page;
        }

        if ($page < 1) {
            $page = 1;
        }

        if ($page > $this->getLastPage()) {
            $page = $this->getLastPage();
        }

        $url = $this->page_url . '?page=' . (int)$page;

//todo add filter

        if (is_array($this->query_parameters) && count($this->query_parameters) > 0) {
            foreach ($this->query_parameters as $key => $value) {
                $url = $url
                    . '&'
                    . $this->query_parameters[$key]
                    . '='
                    . $this->query_parameters[$value];
            }
        }

        return $url;
    }

    /**
     * Implements PHP's IteratorAggregate::getIterator()
     *
     * @return  $this
     * @since   1.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Set the array item for the specified key and value
     *
     * @param   int   $key
     * @param   mixed $value
     *
     * @return  $this
     * @since   1.0
     */
    public function offsetSet($key, $value)
    {
        $key = (int)$key;

        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Set the array item for the specified key and value
     * Implements PHP's ArrayAccess::offsetExists()
     *
     * @param   int $key
     *
     * @return  boolean
     * @since   1.0
     */
    public function offsetExists($key)
    {
        $key = (int)$key;

        if (array_key_exists($key, $this->data)) {
            return true;
        }

        return false;
    }

    /**
     * Get the array item specified by the key value
     * Implements PHP's ArrayAccess::offsetSet()
     *
     * @param   int $key
     *
     * @return  null|mixed
     * @since   1.0
     */
    public function offsetGet($key)
    {
        $key = (int)$key;

        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        return null;
    }

    /**
     * Unset the array item for the specified key
     * Implements PHP's ArrayAccess::pageUnset()
     *
     * @param   int $key
     *
     * @return  $this
     * @since   1.0
     */
    public function offsetUnset($key)
    {
        $key = (int)$key;

        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
        }

        return $this;
    }

    /**
     * Determines if array has values or is empty
     *
     * @return  int
     * @since   1.0
     */
    public function isEmpty()
    {
        if (count($this->data) === 0) {
            return true;
        }

        return false;
    }

    /**
     * Count displayable items in array
     * Implements PHP's Countable::count()
     *
     * @return  boolean
     * @since   1.0
     */
    public function count()
    {
        return count($this->data);
    }
}