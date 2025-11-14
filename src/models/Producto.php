<?php
namespace models;

class Producto {
    private $id;
    private $uuid;
    private $marca;
    private $modelo;
    private $precio;
    private $stock;
    private $imagen;
    private $categoriaId;
    private $categoria_nombre;
    private $createdAt;
    private $updatedAt;
    private $isDeleted;

    public function __construct($id=null,$uuid=null,$marca=null,$modelo=null,$precio=0.0,$stock=0,$imagen=null,$categoriaId=null,$categoria_nombre=null,$createdAt=null,$updatedAt=null,$isDeleted=false){
        $this->id=$id; $this->uuid=$uuid; $this->marca=$marca; $this->modelo=$modelo; 
        $this->precio=$precio; $this->stock=$stock; $this->imagen=$imagen; $this->categoriaId=$categoriaId; $this->categoria_nombre=$categoria_nombre;
        $this->createdAt=$createdAt; $this->updatedAt=$updatedAt; $this->isDeleted=$isDeleted;
    }
    public function getId(){ return $this->id; }
    public function __get($n){ return $this->$n ?? null; }
    public function __set($n,$v){ $this->$n = $v; }
}
