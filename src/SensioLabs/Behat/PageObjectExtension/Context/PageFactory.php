<?php

namespace SensioLabs\Behat\PageObjectExtension\Context;

use Behat\Mink\Mink;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\InlineElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class PageFactory implements PageFactoryInterface
{
    /**
     * @var Mink $mink
     */
    private $mink = null;

    /**
     * @var array $parameters
     */
    private $pageParameters = array();

    /**
     * @var string $pageNamespace
     */
    private $pageNamespace = '\\';

    /**
     * @var string $elementNamespace
     */
    private $elementNamespace = '\\';

    /**
     * @var Mink  $mink
     * @var array $pageParameters
     */
    public function __construct(Mink $mink, array $pageParameters)
    {
        $this->mink = $mink;
        $this->pageParameters = $pageParameters;
    }

    /**
     * @param string $namespace
     */
    public function setPageNamespace($namespace)
    {
        $this->pageNamespace = rtrim($namespace, '\\').'\\';
    }

    /**
     * @param string $namespace
     */
    public function setElementNamespace($namespace)
    {
        $this->elementNamespace = rtrim($namespace, '\\').'\\';
    }

    /**
     * @param string $name
     *
     * @return Page
     */
    public function createPage($name)
    {
        $pageClass = $this->getPageObjectNamespace() . 'Page\\' .$this->classifyName($name);

        if (!class_exists($pageClass) && $this->pageNamespace) {
            $pageClass = $this->pageNamespace . $this->classifyName($name);
        }

        if (!class_exists($pageClass)) {
            throw new \LogicException(sprintf('"%s" page not recognised. "%s" class not found.', $name, $pageClass));
        }

        return new $pageClass($this->mink->getSession(), $this, $this->pageParameters);
    }

    /**
     * @param string $name
     *
     * @return Element
     */
    public function createElement($name, $selector = null)
    {
        $elementClass = $this->getPageObjectNamespace() . 'Element\\' .$this->classifyName($name);

        if (!class_exists($elementClass) && $this->elementNamespace) {
            $elementClass = $this->elementNamespace . $this->classifyName($name);
        }

        if (!class_exists($elementClass)) {
            throw new \LogicException(sprintf('"%s" element not recognised. "%s" class not found.', $name, $elementClass));
        }

        return new $elementClass($this->mink->getSession(), $this, $selector);
    }

    /**
     * @param array|string $selector
     *
     * @return InlineElement
     */
    public function createInlineElement($selector)
    {
        return new InlineElement($selector, $this->mink->getSession(), $this);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function classifyName($name)
    {
        return str_replace(' ', '', ucwords($name));
    }

    /**
     * @return string|null
     */
    private function getPageObjectNamespace()
    {
        $backtrace = debug_backtrace();

        foreach ($backtrace as $index) {
            if ( ! isset($index['object'])) {
                continue;
            }

            if ( ! $index['object'] instanceof PageObjectContext) {
                continue;
            }

            $namespace = get_class($index['object']);
            $position  = strrpos($namespace, '\\');

            return ($position !== false ? substr($namespace, 0, $position) : '') .'\\';
        }

        return null;
    }
}
