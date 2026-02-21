<?php
    declare(strict_types=1);

    namespace Application\Core;    

    class App
    {       
        public function __construct(            
            private ?Container $container = null,
            private string $controller = "", 
            private string $method = "index",
            private string $route = "",
            private array $protectedRoutes = [
                'AdminController'  => 'auth'
            ]                     
        )
        {           
            $this->container = new Container();                     
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
                session_start();

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
                
                /** Manage Middlewares */
                if(isset($this->protectedRoutes[$this->controller])) {
                    $middleware = $this->container->getMiddleware($this->protectedRoutes[$this->controller]);            
                    $middleware->handle();
                }
                                                
                $controller = $this->container->get($controller_path);

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

            } catch(\Throwable $th) {
                \Application\Core\ErrorHandler::handle($th, $controller ?? null);
            }                                            
        }        
    }    
?>