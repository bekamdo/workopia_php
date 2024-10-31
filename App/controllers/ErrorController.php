<?php

namespace App\Controllers;

class ErrorController{
   public static function notFound($message = "Resource not Found"){
    loadView("error",[
        'status' => '404',
        'message' => $message
    ]);
       
    }
    public static function unauthorized($message = "You are not authorised to view this"){
        loadView("error",[
            'status' => '403',
            'message' => $message
        ]);
           
        }
   
}


?>