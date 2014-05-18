<?php
/**
 * Render Test
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Render\Test;

use CommonApi\Render\RenderInterface;
use Molajo\Render\Adapter\Molajito;
use Molajo\Render\Adapter\Mustache;
use Molajo\Render\Adapter\Twig;
use Molajo\Render\Driver;
use Exception;

/**
 * Render Test
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class MolajitoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Molajito Renderer
     *
     * @return  $this
     * @since   1.0
     */
    public function testMolajito()
    {

        $instance = new Driver(new Molajito(new MockRender));

        $file  = __DIR__ . '/RenderTest.php';
        $data  = array();
        $stuff = $instance->renderOutput($file, $data);
        $this->assertEquals('stuff', $stuff);

        return $this;
    }

    /**
     * Test Mustache Renderer
     *
     * @return  $this
     * @since   1.0
     */
    public function testMustache()
    {
        $instance = new Driver(new Mustache(new MockRender));

        $file  = __DIR__ . '/RenderTest.php';
        $data  = array();
        $stuff = $instance->renderOutput($file, $data);
        $this->assertEquals('stuff', $stuff);

        return $this;
    }

    /**
     * Test Twig Renderer
     *
     * @return  $this
     * @since   1.0
     */
    public function testTwig()
    {
        $instance = new Driver(new Twig(new MockRender));

        $file  = __DIR__ . '/RenderTest.php';
        $data  = array();
        $stuff = $instance->renderOutput($file, $data);
        $this->assertEquals('stuff', $stuff);

        return $this;
    }

    /**
     * @expectedException        \CommonApi\Exception\RuntimeException
     * @expectedExceptionMessage Render Driver render Method Failed: Boom.
     *
     * @return  $this
     * @since   1.0
     */
    public function testRenderOutputException()
    {
        $instance = new Driver(new Twig(new MockRender));

        $file  = 'xxx.php';
        $data  = array();
        $stuff = $instance->renderOutput($file, $data);
        $this->assertEquals('stuff', $stuff);

        return $this;
    }
}

class MockRender implements RenderInterface
{
    /**
     * Render output for specified file and data
     *
     * @param   string $include_path
     * @param   array  $data
     *
     * @return  string
     * @since   1.0
     */
    public function renderOutput($include_path, array $data = array())
    {
        if ($include_path === 'xxx.php') {
            throw new Exception('Boom.');
        }
        return 'stuff';
    }
}
