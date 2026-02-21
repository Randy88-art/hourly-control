<?php
declare(strict_types=1);

namespace Application\Core;

use Application\Controller\hourlycontrol\HourlyController;
use Application\Database\Connection;
use Application\model\classes\Query;
use Application\model\classes\QueryHourlyControl;
use Application\model\classes\Validate;

final class Container
{
    public function __construct(
        private ?Connection $dbcon = null,
        private array $services     = [],
        private array $factories    = [],
        private array $middlewares  = []
    )
    {
        $this->dbcon = new Connection(include DB_CONFIG_FILE);
        $this->registerBaseServices();
        $this->registerControllerFactories();
        $this->registerMiddlewares();
    }

    /** Resgister Middlewares */
    private function registerMiddlewares(): void
    {
        $this->middlewares['auth']  = fn() => new \Application\Middlewares\AuthMiddleware();
        $this->middlewares['admin'] = fn() => new \Application\Middlewares\RoleMiddeleware([
            'ROLE_ADMIN'
        ]);
    }

    public function getMiddleware(string $name): object
    {
        if(isset($this->middlewares[$name])) {
            return $this->middlewares[$name]();
        }

        throw new \Exception("Middleware $name not found.", 1);        
    }

    /** 
     * We define the base services (tools)
     */
    private function registerBaseServices(): void 
    {
        $this->services['query'] = function() {            
            return new Query($this->dbcon->getConnection());
        };

        $this->services['validate'] = function() {
            return new Validate;
        };

        $this->services['query_hourly_control'] = function() {
            return new QueryHourlyControl($this->dbcon->getConnection());
        };

        $this->services['hourly_control'] = function() {
            return new HourlyController(
                $this->get('validate'),
                $this->get('query_hourly_control'),
                $this->dbcon->getConnection()
            );
        };
    }

    /** 
     * We define how to build every controller 
     */
    private function registerControllerFactories(): void
    {
        $this->factories["\Application\Controller\LoginController"] = fn() => new \Application\Controller\LoginController(
            $this->get('validate'),
            $this->get('query')
        );

        $this->factories["\Application\Controller\HomeController"] = fn() => new \Application\Controller\HomeController(
            $this->get('validate'),
            $this->get('query_hourly_control'),
            $this->get('hourly_control')
        );

        $this->factories["\Application\Controller\admin\AdminController"] = fn() => new \Application\Controller\admin\AdminController(
            $this->get('validate'),
            $this->get('query')
        );

        $this->factories["\Application\Controller\hourlycontrol\HourlyController"] = fn() => new \Application\Controller\hourlycontrol\HourlyController(
            $this->get('validate'),
            $this->get('query_hourly_control'),
            $this->dbcon->getConnection()
        );

        $this->factories["\Application\Controller\projects\ProjectController"] = fn() => new \Application\Controller\projects\ProjectController(
            $this->get('validate'),
            $this->get('query')
        );

        $this->factories["\Application\Controller\Tasks\TaskController"] = fn() => new \Application\Controller\Tasks\TaskController(
            $this->get('validate'),
            $this->get('query')
        );

        $this->factories["\Application\Controller\RegisterController"] = fn() => new \Application\Controller\RegisterController(
            $this->get('validate'),
            $this->get('query')
        );

        $this->factories["\Application\Controller\admin\SearchController"] = fn() => new \Application\Controller\admin\SearchController(
            $this->get('validate'),
            $this->get('query_hourly_control')
        );
    }

    /** We get a service or build a controller */
    public function get(string $id): object
    {
        // If it's a controller defined in our factories
        if(isset($this->factories[$id])) {
            return $this->factories[$id]();
        }

        // If it's a base service
        if(isset($this->services[$id])) {
            if(is_callable($this->services[$id])) {
                $this->services[$id] = $this->services[$id]();
            }

            return $this->services[$id];
        }

        if(class_exists($id)) {
            return new $id();
        }

        throw new \Exception("Service or Controller not found: $id", 1);        
    }
}
