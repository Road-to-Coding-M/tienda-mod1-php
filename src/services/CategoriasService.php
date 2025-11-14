<?php

namespace services;

use models\Categoria;
use PDO;

require_once __DIR__ . '/../models/Categoria.php';

class CategoriasService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM categorias WHERE is_deleted = FALSE ORDER BY id ASC";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'rowToCategoria'], $rows);
    }

    private function rowToCategoria(array $row): Categoria
    {
        return new Categoria(
            $row['id'] ?? null,
            $row['nombre'] ?? null,
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null,
            (bool)($row['is_deleted'] ?? false)
        );
    }
}
