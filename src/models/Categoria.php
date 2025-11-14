<?php
namespace models;

class Categoria {
    private $id;
    private $nombre;
    private $createdAt;
    private $updatedAt;
    private $isDeleted;

    public function __construct($id=null,$nombre=null,$createdAt=null,$updatedAt=null,$isDeleted=false){
        $this->id=$id; $this->nombre=$nombre; $this->createdAt=$createdAt; $this->updatedAt=$updatedAt; $this->isDeleted=$isDeleted;
    }
    public function getId(){ return $this->id; }
    public function __get($n){ return $this->$n ?? null; }
    public function __set($n,$v){ $this->$n = $v; }
}
