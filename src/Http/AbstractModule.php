<?php

namespace Atom\Framework\Http;

use Atom\DI\Container;
use Atom\Event\Contracts\EventDispatcherContract;
use Atom\Event\EventDispatcher;
use Atom\Routing\Contracts\RouterContract;
use Atom\Routing\Router;
use Atom\Framework\Contracts\ModuleContract;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @property String MODULE_NAME
 * @property String MODULE_DESCRIPTION
 */
abstract class AbstractModule implements ModuleContract
{
    /**
     * @var String
     */
    protected string $moduleName;
    /**
     * @var string
     */
    protected string $moduleDescription;
    /**
     * @var RouterContract | Router
     */
    protected $router;
    /**
     * @var ContainerInterface | Container
     */
    protected $container;

    /**
     * @var EventDispatcherContract | EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var RequestHandler | RequestHandlerInterface
     */
    private $requestHandler;

    public function __construct(
        RouterContract $router,
        Container $container,
        EventDispatcherContract $eventDispatcher,
        RequestHandler $requestHandler
    ) {
        $this->router = $router;
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
        $this->requestHandler = $requestHandler;
    }

    /**
     * @return void
     */
    public function bootstrap()
    {

        $this->setup($this->container);
        $this->routes($this->router);
        $this->events($this->eventDispatcher);
    }

    abstract protected function routes(RouterContract $router);

    public function getModuleName(): string
    {
        return $this->moduleName ?? self::class;
    }

    public function getModuleDescription(): string
    {
        return $this->moduleDescription ?? self::class;
    }

    protected function events(EventDispatcherContract $eventDispatcher)
    {
    }

    private function setup(ContainerInterface $container)
    {
    }
}