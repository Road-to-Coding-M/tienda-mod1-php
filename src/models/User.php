<?php
namespace models;

class User {
    private $id;
    private $username;
    private $password;
    private $nombre;
    private $apellidos;
    private $email;
    private $roles = [];
    private $createdAt;
    private $updatedAt;
    private $isDeleted;

    public function __construct($id=null,$username=null,$password=null,$nombre=null,$apellidos=null,$email=null,$roles=[],$createdAt=null,$updatedAt=null,$isDeleted=false){
        $this->id=$id; $this->username=$username; $this->password=$password; $this->nombre=$nombre; $this->apellidos=$apellidos;
        $this->email=$email; $this->roles=$roles; $this->createdAt=$createdAt; $this->updatedAt=$updatedAt; $this->isDeleted=$isDeleted;
    }
    public function hasRole($r){ return in_array($r, $this->roles); }
    public function __get($n){ return $this->$n ?? null; }
    public function __set($n,$v){ $this->$n = $v; }
}
