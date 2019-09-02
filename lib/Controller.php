<?php
namespace Stoodle;

use Context;
use PageLayout;
use RuntimeException;

class Controller extends \StudipController
{
    protected $_autobind = true;

    /**
     * Constructs the controller and provide translations methods.
     *
     * @param object $dispatcher
     */
    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);

        $this->plugin = $dispatcher->current_plugin;

        // Localization
        $this->_ = function ($string) use ($dispatcher) {
            return call_user_func_array(
                [$dispatcher->current_plugin, '_'],
                func_get_args()
            );
        };

        $this->_n = function ($string0, $tring1, $n) use ($dispatcher) {
            return call_user_func_array(
                [$dispatcher->current_plugin, '_n'],
                func_get_args()
            );
        };
    }

    /**
     * Intercepts all non-resolvable method calls in order to correctly handle
     * calls to _ and _n.
     *
     * @param string $method
     * @param array  $arguments
     * @return mixed
     * @throws RuntimeException when method is not found
     */
    public function __call($method, $arguments)
    {
        $variables = get_object_vars($this);
        if (isset($variables[$method]) && is_callable($variables[$method])) {
            return call_user_func_array($variables[$method], $arguments);
        }

        return parent::__call($method, $arguments);
    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $layout = $this->get_template_factory()->open('layout.php');
        $layout->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        $this->set_layout($layout);
    }

    protected function setPageTitle($title)
    {
        $title = vsprintf($title, array_slice(func_get_args(), 1));

        PageLayout::setTitle(Context::getHeaderLine() . ' - ' . $title);
    }
}
