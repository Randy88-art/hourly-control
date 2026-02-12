<?php
    declare(strict_types=1);

    namespace Application\Core;

    use Application\Controller\hourlycontrol\HourlyController;
    use Application\Database\Connection;
    use Application\model\classes\Query;
    use Application\model\classes\QueryHourlyControl;
    use Application\model\classes\Validate;

    class App
    {       
        public function __construct(
            private ?Connection $dbcon = null,
            private string $controller = "", 
            private string $method = "index",
            private string $route = "",
            private array $dependencies = [],           
        )
        {
            $this->dbcon = new Connection(include DB_CONFIG_FILE);            

            $this->dependencies['validate']             = new Validate;
            $this->dependencies['query']                = new Query($this->dbcon->getConnection());
            $this->dependencies['query_hourly_control'] = new QueryHourlyControl($this->dbcon->getConnection());
            $this->dependencies['hourly_control']       = new HourlyController(
                $this->dependencies['validate'], 
                $this->dependencies['query_hourly_control'],
                $this->dbcon->getConnection()
            );
        }
        
        private function splitUrl(): array|string {           
            $url = $_SERVER['REQUEST_URI'] === '/' ? 'home' : $_SERVER['REQUEST_URI'];
            $url = explode('/', trim($url, "/")); 
            
            if(empty($url[0])) {
                array_shift($url);                
                return $url;
            }                                 

            return $url;
        }

        public function router(): void {            
            try {
                $url = $this->splitUrl();

                // Test diferent options to configure to Controller                         
                if(count($url) == 1 && !empty($url[0])){
                    $this->controller = ucfirst($url[0]);
                    $this->method = "index";
                }
                else if(count($url) == 2) {
                    $this->controller = ucfirst($url[0]);
                    $this->method = $url[1];                     
                }
                else if(count($url) > 2) {            
                    if(!empty($url) && preg_match('/^([0-9]){1,5}$/', $url[count($url) - 1])) {
                        $id = $url[count($url) - 1];                                                                     
                        array_pop($url);                                                     
                    }
                    
                    foreach ($url as $key => $value) {
                        if($key == count($url) - 2) break;
                        $this->route .= $value . "/";            
                    }                          
        
                    $this->controller = ucfirst($url[count($url) - 2]);
                    $this->method = $url[count($url) - 1];                                                       
                } 

                // Build the Controller
                $this->route = "/Application/Controller/" . $this->route;        
                $this->controller = $this->controller . "Controller";

                $file_name = SITE_ROOT . "/.." . $this->route . $this->controller . ".php";
                
                if(file_exists($file_name)) {                    
                    $controller_path = str_replace('/', '\\', $this->route) . $this->controller;                                                         
                } 
                else {                    
                    $this->controller = "ErrorController";
                    $controller_path = '\Application\Controller\\' . ucfirst($this->controller);                                                        
                } 
                                
                $controller = $this->createController($controller_path);

                /** select method */
                if(count($url) > 0) {                    
                    if(method_exists($controller, $this->method)) {                        
                        array_shift($url);
                    }
                    else {
                        $this->method = "index";
                    }
                }

                $params = isset($id) ? [$id] : [];

                call_user_func_array([$controller, $this->method], $params);

            } catch (\Throwable $th) {
                \Application\Core\ErrorHandler::handle($th, $controller ?? null);
            }                                
            
        }

        private function createController(string $className): object
        { 
            $controllerMaps = [
                "\Application\Controller\LoginController" => fn() => new $className(
                    $this->dependencies['validate'],
                    $this->dependencies['query']
                ),
                "\Application\Controller\RegisterController" => fn() => new $className(
                    $this->dependencies['validate'],
                    $this->dependencies['query']
                ),
                "\Application\Controller\admin\AdminController" => fn() => new $className(
                    $this->dependencies['validate'],
                    $this->dependencies['query']
                ),
                "\Application\Controller\HomeController" => fn() => new $className(
                    $this->dependencies['validate'],
                    $this->dependencies['query_hourly_control'],
                    $this->dependencies['hourly_control']
                ),
                "\Application\Controller\admin\SearchController" => fn() => new $className(
                    $this->dependencies['validate'],
                    $this->dependencies['query_hourly_control']
                ),
                "\Application\Controller\hourlycontrol\HourlyController" => fn() => new $className(
                    $this->dependencies['validate'],
                    $this->dependencies['query_hourly_control'],
                    $this->dbcon->getConnection()
                ),
                "\Application\Controller\projects\ProjectController" => fn() => new $className(
                    $this->dependencies['validate'],
                    $this->dependencies['query']
                ),
                "\Application\Controller\Tasks\TaskController" => fn() => new $className(
                    $this->dependencies['validate'],
                    $this->dependencies['query']
                ),
                "\Application\Controller\ErrorController" => fn() => new $className()
            ];

            if(isset($controllerMaps[$className])) return $controllerMaps[$className]();

            return new $className;
        }
    }    
?>