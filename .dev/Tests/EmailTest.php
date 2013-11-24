<?php
/**
 * Render Test
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Render\Test;

/**
 * Render Test
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2013 Amy Stephen. All rights reserved.
 * @since      1.0
 */
class RenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Render Object
     */
    protected $languageInstance;

    /**
     * @var Render Object
     */
    protected $Render_folder;

    /**
     * Initialises Adapter
     */
    protected function setUp()
    {
        $class = 'Molajo\\Render\\Adapter';

        $language_type  = 'PhpMailerType';
        $language_class = 'Phpmailer\\phpmailer';
        $options        = array();

        $this->languageInstance = new $class($language_type, $language_class, $options);

        return;
    }

    /**
     * Create a Render entry or set a parameter value
     *
     * @covers Molajo\Render\Type\FileRender::set
     */
    public function testSet()
    {
        $this->languageInstance->set('to', 'AmyStephen@gmail.com,Fname Lname');
        $this->languageInstance->set('from', 'AmyStephen@gmail.com,Fname Lname');
        $this->languageInstance->set('reply_to', 'AmyStephen@gmail.com,FName LName');
        $this->languageInstance->set('cc', 'AmyStephen@gmail.com,FName LName');
        $this->languageInstance->set('bcc', 'AmyStephen@gmail.com,FName LName');
        $this->languageInstance->set('subject', 'Welcome to our Site');
        $this->languageInstance->set('body', '<h2>Stuff goes here</h2>');
        $this->languageInstance->set('mailer_html_or_text', 'html');

        $this->languageInstance->send();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
}
