<?php

namespace SensioLabs\PageObjectExtension\PageObject;

use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Session;
use SensioLabs\PageObjectExtension\Context\PageFactoryInterface;

abstract class Page extends DocumentElement
{
    /**
     * @var PageFactoryInterface $pageFactory
     */
    private $pageFactory = null;

    /**
     * @var array $parameters
     */
    private $parameters = array();

    /**
     * @param Session              $session
     * @param PageFactoryInterface $pageFactory
     * @param array                $parameters
     */
    public function __construct(Session $session, PageFactoryInterface $pageFactory, array $parameters = array())
    {
        parent::__construct($session);

        $this->pageFactory = $pageFactory;
        $this->parameters = $parameters;
    }

    /**
     * @param string $path
     *
     * @return Page
     */
    public function open($path)
    {
        $path = $this->makeSurePathIsAbsolute($path);

        $this->getSession()->visit($path);

        return $this;
    }

    /**
     * @param string $name
     * @param array  $arguments
     */
    public function __call($name, $arguments)
    {
        $message = sprintf('"%s" method is not available on the %s', $name, $this->getName());

        throw new \BadMethodCallException($message);
    }

    /**
     * @param string $name
     *
     * @return Page
     */
    protected function getPage($name)
    {
        return $this->pageFactory->createPage($name);
    }

    /**
     * @param string $name
     *
     * @return Element
     */
    protected function getElement($name)
    {
        return $this->pageFactory->createElement($name);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    protected function getParameter($name)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return preg_replace('/^.*\\\(.*?)$/', '$1', get_called_class());
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function makeSurePathIsAbsolute($path)
    {
        $baseUrl = rtrim($this->getParameter('base_url'), '/').'/';

        return 0 !== strpos($path, 'http') ? $baseUrl.ltrim($path, '/') : $path;
    }
}
