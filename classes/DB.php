<?php

namespace classes;


class DB{


    private $last_id;
    private $error;
    private $conn;
    private $host = 'localhost';
    private $dbname = ''; //nome base dados
    private $user = ''; //utilizador acesso base dados
    private $password = ''; //palavra-passe base dados

    public function __construct(){

       
        try{
            $this->conn = new \PDO("mysql:host=".$this->host.";dbname=".$this->dbname, $this->user, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
       

        }

        catch(\PDOException $e){

           die($e->getMessage());
        }

    }


    public function beginTransaction(){

        $this->conn->beginTransaction();
    }


    public function commit(){

        $this->conn->commit();
    }


    public function rollback(){

        $this->conn->rollback();
    }



   public function Query($query) {



        try{
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
        }

        catch(\PDOException $e){
           
           die($e->getMessage());

        }


        $array = array();

        while($row = $stmt->fetchObject()){

            $array[] = $row;

        }

        return $array;

    }



    public function Select($table=null, $condition=null, $columns=null){


        if(!empty($table)){


            $colNames = !empty($columns) ? (
                            is_array($columns) ? (
                                count($columns) > 1 ? implode(",", $columns) : $columns[0])
                                 : $columns 
                        ) : '*';

        try{

            if(isset($condition) && !empty($condition)){
                foreach($condition as $ind => $val){
                 $where[] = "{$ind}".(is_null($val) ? " IS NULL " : " = ?");   
                }

             }else{

                $where = '';
             }

             $clausule = !empty($where) ? "WHERE ". implode(' AND ', $where) : '';

            
            $query = "SELECT ".$colNames." FROM ".$table." ".$clausule;
            $stmt = $this->conn->prepare($query);

            if(!empty($condition)){

                $k = 1;
                foreach ($condition as $key => $value) {
                    $stmt->bindValue($k, $value);
                    $k++;
                }
            }
            
            $stmt->execute();

        }

        catch(\PDOException $e){

           die($e->getMessage());

        }

        $array = array();

        while($row = $stmt->fetchObject()){
            $array[] = $row;
        }


        if(!empty($array)){

            return $array;

        }else{

            return null;
        }

        }


    }


    public function Insert($obj, $table) 
    {
        try
        {
            $query = "INSERT INTO {$table} (".implode(",",array_keys((array) $obj)).") VALUES 
            ('".implode("','",array_values((array) $obj))."')";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(array('widgets'));
            
            $this->error = false;
            $this->last_id = $this->Last($table);
            return array('success'=>true, 'feedback'=>'', 'last_id'=>$this->last_id);


        }
        catch(\PDOException $e)
        {
            $this->error = true;

            return array('success'=>false);


           die($e->getMessage() ." " . $query);
        }

    }



    public function Update($obj, $condition, $table){

        try {
            foreach($obj as $ind => $val){
             $dados[] = "{$ind} = ".(is_null($val) ? " NULL " : "'{$val}'");   
            }

            foreach($condition as $ind => $val){
             $where[] = "{$ind}".(is_null($val) ? " IS NULL " : " = '{$val}'");   
            }

            $sql = "UPDATE {$table} SET ". implode(',', $dados)." WHERE ". implode(' AND ', $where);
            
            $state = $this->conn->prepare($sql);
            $state->execute(array('widgets'));
            


        } catch (\PDOException $ex){

            die ($ex->getMessage(). "Comando: ". $sql);

        }

        return array('success'=>true, 'id'=>$this->Last($table));
    }




    public function Delete($condition, $table){
      
        try{
            foreach ($condition as $index => $val)
            {
                $where[] = "{$index}". (is_null($val) ? " IS NULL " : " = '{$val}'");
            }

            $sql = "DELETE FROM {$table} WHERE ". implode(' AND ', $where);
            $state = $this->conn->prepare($sql);
            $state->execute(array('widgets'));

            return array('success'=>true, 'feedback'=>'', 'codigo'=>$this->Last($table));

        }

        catch(\PDOException $e){
           
           return false;
           
           die($e->getMessage());
        }


    }




    public function Last($table){
        
        try{
            $stmt = $this->conn->prepare("SELECT last_insert_id() AS last FROM {$table}");
            $stmt->execute();
            $stmt = $stmt->fetchObject();
        }

        catch(\PDOException $e){

           die($e->getMessage());

        }

        return @$stmt->last;
    }





    public function LastPosition($field, $table, $args){

        try{   

            foreach ($args as $index => $val)
            {
                $where[] = "{$index}". (is_null($val) ? " IS NULL " : " = '{$val}'");
            }


            $stmt = $this->conn->prepare("SELECT MAX({$field}) AS position FROM {$table} WHERE ".implode(' AND ', $where)."");
            $stmt->execute();
            $stmt = $stmt->fetchObject();
        }

        catch(\PDOException $e)
        {
           die($e->getMessage());
        }

        return @$stmt->position;


    }




    public function prepare($query){

        return $this->conn->prepare($query);

    }


    public function success(){

        return !$this->error ? true : false;
    }


    public function lastId(){

        return $this->last_id;
    }

}

?>
