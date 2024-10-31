<?php
namespace Framework;

use App\Controllers\ErrorController;
use Framework\Middleware\Authorize;
use Framework\Middlware;

class Router{
    protected $routes = [];

    public function registerRoute($method,$uri,$action,$middleware = []){
     
       list($controller,$controllerMethod) =  explode("@",$action);
        $this ->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'controller' => $controller,
            'controllerMethod' => $controllerMethod,
            'middleware' => $middleware
        ];

    }

  

    /**
     * Add a Get route
     * 
     * @param string $uri
     * @param string $controller
     * @param array $middleware
     * @return void 
     */

     public function get($uri, $controller,$middleware =[]){
       $this -> registerRoute("GET",$uri,$controller,$middleware);

     } 

      /**
     * Add a post route
     * 
     * @param string $uri
     * @param string $controller
     * @param  array $middleware
     * @return void 
     */

     public function post($uri, $controller,$middleware = []){
        $this -> registerRoute("POST",$uri,$controller,$middleware);
        
     } 


           /**
     * Add a put route
     * 
     * @param string $uri
     * @param string $controller
     * @param  array $midddleware
     * @return void 
     */

     public function put($uri, $controller,$midddleware = []){
        $this -> registerRoute("PUT",$uri,$controller,$midddleware);
     } 

           /**
     * Add a Delete route
     * 
     * @param string $uri
     * @param string $controller
     * @param array $middleware
     * @return void 
     */

     public function delete($uri, $controller,$midddleware = []){
        $this -> registerRoute("DELETE",$uri,$controller,$midddleware);
        
     } 

     /**
      * @param string $uri
      * @param string $method
      * @return void
      */
      public function route($uri){
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        //check for _method  input
        if($requestMethod === "POST" && isset($_POST["_method"])){
          //overide the request method
          $requestMethod = strtoupper($_POST["_method"]);
        }
        foreach($this -> routes as $route){

           //split the current uri into segments
           $uriSegments = explode('/',trim($uri,'/'));
          

           //split the route URI into segments
           $routeSegments = explode("/",trim($route['uri'],"/"));

           $match = true;

           //check the number of segments
           if(count($uriSegments) === count($routeSegments) && strtoupper($route['method'] === $requestMethod)){
            $params = [];

            $match = true;
            for($i = 0; $i < count($uriSegments);$i++ ){
              //if the uri's do not match and there is no params
              if($routeSegments[$i] !==  $uriSegments[$i] && !preg_match('/\{(.+?)\}/', $routeSegments[$i])){
                $match = false;
                break;



              }
              //check for the params and add to the params array
              if(preg_match('/\{(.+?)\}/', $routeSegments[$i],$matches)){
              $params[$matches[1]] = $uriSegments[$i];
           

              }

            }

            if($match){
                //extract controller and controller 
                foreach($route['middleware'] as $midddleware){
                  (new Authorize()) -> handle($midddleware);
                }
                $controller = "App\\Controllers\\".$route['controller'];
                $controllerMethod = $route['controllerMethod'];

                //instanciate the controller and call the method;
                $controllerInstance = new $controller();
                $controllerInstance -> $controllerMethod($params);
                return;

            }
           }

       


            // if ($route['uri'] === $uri && $route['method'] === $method){
            //     //extract controller and controller method
            //     $controller = "App\\Controllers\\".$route['controller'];
            //     $controllerMethod = $route['controllerMethod'];

            //     //instanciate the controller and call the method;
            //     $controllerInstance = new $controller();
            //     $controllerInstance -> $controllerMethod();

            //     return;

            // }

        }
        ErrorController::notFound();
    
      }
    }

 ?>