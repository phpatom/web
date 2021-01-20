<?php


namespace Atom\Framework;

use Atom\Kernel\Kernel;
use Atom\Kernel\Contracts\ServiceProviderContract;
use Atom\Kernel\Env\Env;
use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use Atom\DI\Exceptions\StorageNotFoundException;
use Atom\Event\Exceptions\ListenerAlreadyAttachedToEvent;
use Atom\Routing\CanRegisterRoute;
use Atom\Routing\Route;
use Atom\Routing\Router;
use Atom\Framework\Events\ServiceProviderFailed;
use Exception;

class Application extends Kernel
{
    use CanRegisterRoute {
        create as public route;
    }

    /**
     * @var RequestHandler
     */
    private $requestHandler;

    /**
     * @var Router
     */
    private $router;

    /**
     * @param string $appDir
     * @param string|null $env
     * @return Application
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws ListenerAlreadyAttachedToEvent
     * @throws NotFoundException
     * @throws StorageNotFoundException
     */
    public static function create(string $appDir, string $env = Env::DEV): Application
    {
        return (new self($appDir, $env))->use(new WebServiceProvider());
    }

    /**
     * @param string $appDir
     * @return Application
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws ListenerAlreadyAttachedToEvent
     * @throws NotFoundException
     * @throws StorageNotFoundException
     */
    public static function prod(string $appDir): Application
    {
        return self::create($appDir, Env::PRODUCTION);
    }

    /**
     * @param string $appDir
     * @return Application
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws ListenerAlreadyAttachedToEvent
     * @throws NotFoundException
     * @throws StorageNotFoundException
     */
    public static function dev(string $appDir): Application
    {
        return self::create($appDir);
    }

    /**
     * @param string $appDir
     * @return Application
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws ListenerAlreadyAttachedToEvent
     * @throws NotFoundException
     * @throws StorageNotFoundException
     */
    public static function test(string $appDir): Application
    {
        return self::create($appDir, Env::TESTING);
    }

    /**
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws Exceptions\RequestHandlerException
     * @throws NotFoundException
     * @throws StorageNotFoundException
     */
    public function run()
    {
        $this->requestHandler()->run();
    }

    /**
     * @param ServiceProviderContract $serviceProvider
     * @return Kernel|Application
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws ListenerAlreadyAttachedToEvent
     * @throws NotFoundException
     * @throws StorageNotFoundException
     */
    public function use(ServiceProviderContract $serviceProvider): Kernel
    {
        try {
            return parent::use($serviceProvider);
        } catch (Exception $exception) {
            $this->eventDispatcher()->dispatch(new ServiceProviderFailed($serviceProvider, $exception));
            throw $exception;
        }
    }


    /**
     * @return mixed|void
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws StorageNotFoundException
     */
    public function requestHandler(): RequestHandler
    {
        if ($this->requestHandler == null) {
            $this->requestHandler = $this->container()->get(RequestHandler::class);
        }
        return $this->requestHandler;
    }

    /**
     * @return Router
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws StorageNotFoundException
     */
    public function router(): Router
    {
        if ($this->router == null) {
            $this->router = $this->container()->get(Router::class);
        }
        return $this->router;
    }

    /**
     * @param array $modules
     * @return Application
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws StorageNotFoundException
     */
    public function withModules(array $modules): self
    {
        foreach ($modules as $module) {
            $this->requestHandler()->addModule($module);
        }
        return $this;
    }

    /**
     * @param string $prefix
     * @param callable $callable
     * @param $handler
     * @return Application
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws StorageNotFoundException
     */
    public function group(string $prefix, callable $callable, $handler = null): self
    {
        $this->router()->group($prefix, $callable, $handler);
        return $this;
    }


    /**
     * @param $middleware
     * @return Application
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws Exceptions\RequestHandlerException
     * @throws NotFoundException
     * @throws StorageNotFoundException
     */
    public function add($middleware): Application
    {
        $this->requestHandler()->add($middleware);
        return $this;
    }

    /**
     * @param $middleware
     * @return Application
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws Exceptions\RequestHandlerException
     * @throws NotFoundException
     * @throws StorageNotFoundException
     */
    public function load($middleware): Application
    {
        $this->requestHandler()->load($middleware);
        return $this;
    }

    /**
     * @param $module
     * @return Application
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws StorageNotFoundException
     */
    public function addModule($module): Application
    {
        $this->requestHandler()->addModule($module);
        return $this;
    }

    /**
     * @param Route $route
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws StorageNotFoundException
     */
    protected function registerRoute(Route $route)
    {
        $this->router()->add($route);
    }
}