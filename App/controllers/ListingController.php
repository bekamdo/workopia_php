<?php

namespace App\Controllers;

use ErrorException;
use Framework\Database;
use Framework\Session;
use Framework\Validation;
use Framework\Authorization;

class ListingController{
    protected $db;
    public function __construct(){
        $config= require  basePath('/config/db.php');
        $this -> db = new Database($config);

    }

    public function index(){
        $listings = $this -> db -> query("SELECT * FROM listings ORDER BY created_at DESC") -> fetchAll();



       loadView("listings/index",['listings' => $listings]);

    }

    public function create(){
        loadView('listings/create');
    }

public function show($params){
$id = $params["id"] ?? "";

$params=['id'=> $id];

$listing  = $this -> db -> query("SELECT * FROM listings where id =:id",$params) -> fetch();

//check if listing exists

if(!$listing){
    ErrorController::notFound("Listing not found");
    return;
}
loadView("listings/show",['listing' => $listing]);
}

/**
 * store data in the database
 * @return  void
 */

 public function store(){
    
   $allowedFields = ['title', 'description', 'salary', 'tags', 'company', 'address', 'city', 'state', 'phone', 'email', 'requirements', 'benefits'];
   $newListingData = array_intersect_key($_POST, array_flip($allowedFields));
   $newListingData['user_id'] = Session::get('user')['id'];

   $newListingData = array_map('sanitize',$newListingData);

   $requiredFields = ["title","description","email","salary","city","state"];
   $errors = [];
   foreach($requiredFields as $field){
    if(empty($newListingData[$field]) || !Validation::string($newListingData[$field])){
        $errors[$field] = ucfirst($field). 'is required';
    }
}

    if(!empty($errors)){
        // Reload views with errors
        loadView('listings/create',[
            'errors' => $errors,
            'listing' => $newListingData
        ]);

    }else{
        //submit data
        // $this-> db-> query('INSERT INTO listings (title,description,salary,tags,company,
        // address,city,state,phone,email,requirements,benefits,user_id) values(
        // :title,:description,:salary,:tags,:company,:address,:city,:state,:phone,
        // :email,:requirements,:benefits,:user_id)',$newListingData);

        $fields = [];

        foreach($newListingData as $field => $value){
           $fields[] = $field;
        }
        $fields = implode(', ',$fields);
        $values = [];

        foreach($newListingData as $field => $value){
            //convert empty strings to null;
            if($value === ""){
                $newListingData[$field] = null;
            }
            $values[] = ":". $field;

        }
        $values = implode(', ',$values);

        $query = "INSERT INTO listings({$fields}) VALUES({$values})";

        $this -> db -> query($query,$newListingData);
        redirect("/workopia/public/listings");
        
    }

   
  
 }

 /**
  * delete a listing 
  * @param array $params
  * @return  void
  */

  public function destroy($params){
    $id = $params['id'];
    $params = ['id' => $id];
    $listing = $this->db->query("SELECT * FROM listings WHERE id = :id",$params)-> fetch();
    //check if listing exists

    if(!$listing){
        ErrorController::notFound("Listing was not found");
        return;
    }
    //authorization
    if(!Authorization::isOwner($listing -> user_id)){
        $_SESSION['error_message'] = "You are not authorized to delete this message";
       return  redirect("/workopia/public/listings/" . $id);

    }
   $this-> db-> query("DELETE FROM listings where id= :id",$params);
   //set flash message 
   $_SESSION['success_message'] = 'Listing deleted successfully';
   redirect("/workopia/public/listings/");


  }
  public function edit($params){
    
    $id = $params["id"] ?? "";
    
    $params=['id'=> $id];
    
    $listing  = $this -> db -> query("SELECT * FROM listings where id =:id",$params) -> fetch();
     
    
    //check if listing exists
    
    if(!$listing){
        ErrorController::notFound("Listing not found");
        return;
    }
       //authorization
       if(!Authorization::isOwner($listing -> user_id)){
        $_SESSION['error_message'] = "You are not authorized to delete this message";
       return  redirect("/workopia/public/listings/" . $id);

    }
 
    loadView("listings/edit",['listing' => $listing]);
    }

    /**
     * update a listing
     * @param array $params
     * @return void
     */

     public function update($params){
        $id = $params["id"] ?? "";
    
        $params=['id'=> $id];
        
        $listing  = $this -> db -> query("SELECT * FROM listings where id =:id",$params) -> fetch();
        
        //check if listing exists
        
        if(!$listing){
            ErrorController::notFound("Listing not found");
            return;
        }
            //authorization
    if(!Authorization::isOwner($listing -> user_id)){
        $_SESSION['error_message'] = "You are not authorized to update this listing";
       return  redirect("/workopia/public/listings/" . $id);

    }
        $allowedFields = ['title', 'description', 'salary', 'tags', 'company', 'address', 'city', 'state', 'phone', 'email', 'requirements', 'benefits'];
     
        $updateValues = [];

        $updateValues = array_intersect_key($_POST, array_flip($allowedFields));
       
        
        $updateValues = array_map('sanitize',$updateValues);
     

        $requiredFields= ['title','description','salary','email','city','state'];

        $errors = [];

        foreach($requiredFields as $field){
            if(empty($updateValues[$field]) || !Validation::string($updateValues[$field])){
                $errors[$field] = ucfirst($field) .' is required';
            }
        }
            if(!empty($errors)){
                loadView('listings/edit',['listing'=> $listing,'errors' => $errors]);
                exit;
            }else{
                $updateFields = [];
                foreach(array_keys($updateValues) as $field){
                    $updateFields[] = "{$field} = :{$field}";
                  
                }
            $updateFields = implode(', ',$updateFields);
           
           $updateQuery = "UPDATE listings SET $updateFields WHERE  id = :id";
           $updateValues['id'] = $id;
           $this -> db -> query($updateQuery,$updateValues);

           $_SESSION['success_message'] = "Listing Updated";
           redirect("/workopia/public/listings/".$id);
            
        }
     
    
       
     }
     /**
      * search listings by keywords and location
      * @return void
      */
      public function search(){
      $keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : "";
      $location = isset($_GET['location']) ? trim($_GET['location']) : "";

      $query = "SELECT * FROM listings WHERE (title 
      LIKE :keywords
      OR description LIKE :keywords 
      OR tags LIKE :keywords
      OR company LIKE :keywords) AND (city LIKE :location OR state LIKE :location)";

      $params = [
        'keywords' => "%{$keywords}%",
        'location' =>  "%{$location}%",
      ];

      $listings = $this -> db -> query($query,$params)-> fetchAll();

      loadView("/listings/index",['listings' => $listings,'keywords' => $keywords,'location' => 'location']);
      }

    
}


?>