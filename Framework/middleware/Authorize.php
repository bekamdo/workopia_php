<?php
namespace Framework\Middleware;

use Framework\Session;


 class Authorize{
    /**
     * check if the user is authenticated
     * @return  bool
     */

     public function isAuthenticated(){
      return Session::has('user');
     }

    /**
     * Handle the users requests
     * 
     * @param  string $role
     * @return  bool
     */
    public function handle($role){
      if($role === "guest" && $this-> isAuthenticated()){
        return redirect("/workopia/public/");
      }elseif($role === "auth" && !$this-> isAuthenticated()){
         return redirect("/workopia/public/auth/login/");

      }
        
    }
 }



?>