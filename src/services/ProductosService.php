<?php
namespace services;

use models\Producto;

class ProductosService
{
    private \PDO $db;
    public function __construct(\PDO $db){ $this->db = $db; }

    /** List with category name */
    public function findAllWithCategoryName(): array
    {
        $sql = "SELECT p.*, c.nombre AS categoria_nombre
                  FROM productos p
             LEFT JOIN categorias c ON c.id = p.categoria_id
                 WHERE p.is_deleted = FALSE
              ORDER BY p.id ASC";
        $rows = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        return array_map([$this,'rowToProducto'], $rows);
    }

    /** Get by id with category name */
    public function findById(int $id): ?Producto
    {
        $sql = "SELECT p.*, c.nombre AS categoria_nombre
                  FROM productos p
             LEFT JOIN categorias c ON c.id = p.categoria_id
                 WHERE p.id = :id AND p.is_deleted = FALSE
                 LIMIT 1";
        $st = $this->db->prepare($sql);
        $st->execute([':id'=>$id]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        return $row ? $this->rowToProducto($row) : null;
    }

    /** Get all ids */
    public function getAllIds(): array
    {
        $sql = "SELECT id FROM productos ORDER BY id ASC";
        return $this->db->query($sql)->fetchAll(\PDO::FETCH_COLUMN);
    }

    /** Insert (uuid in DB; imagen nullable). Return new id. */
    public function save(Producto $p): int
    {
        // Find next available ID
        $ids = $this->getAllIds();
        $nextId = 1;
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id !== $nextId) {
                break;
            }
            $nextId++;
        }

        $sql = "INSERT INTO productos
                  (id, uuid, marca, modelo, precio, stock, imagen, categoria_id, created_at, updated_at, is_deleted)
                VALUES
                  (:id, gen_random_uuid(), :marca, :modelo, :precio, :stock, :imagen, :categoria_id, NOW(), NOW(), FALSE)";
        $st = $this->db->prepare($sql);
        $st->execute([
            ':id'           => $nextId,
            ':marca'        => $p->marca,
            ':modelo'       => $p->modelo,
            ':precio'       => $p->precio,
            ':stock'        => $p->stock,
            ':imagen'       => $p->imagen,
            ':categoria_id' => $p->categoriaId,
        ]);
        return $nextId;
    }

    /** Update (no descripcion) */
    public function update(Producto $p): void
    {
        $sql = "UPDATE productos
                   SET marca=:marca,
                       modelo=:modelo,
                       precio=:precio,
                       stock =:stock,
                       imagen=:imagen,
                       categoria_id=:categoria_id,
                       updated_at=NOW()
                 WHERE id=:id";
        $st = $this->db->prepare($sql);
        $st->execute([
            ':marca'        => $p->marca,
            ':modelo'       => $p->modelo,
            ':precio'       => $p->precio,
            ':stock'        => $p->stock,
            ':imagen'       => $p->imagen,
            ':categoria_id' => $p->categoriaId,
            ':id'           => $p->id,
        ]);
    }

    /** Update only image */
    public function updateImage(int $id, ?string $imagePathOrUrl): void
    {
        $st = $this->db->prepare(
            "UPDATE productos SET imagen = :imagen, updated_at = NOW() WHERE id = :id"
        );
        $st->execute([':imagen'=>$imagePathOrUrl, ':id'=>$id]);
    }

    /** Soft delete */
    public function softDelete(int $id): void
    {
        $st = $this->db->prepare(
            "UPDATE productos SET is_deleted = TRUE, updated_at = NOW() WHERE id = :id"
        );
        $st->execute([':id'=>$id]);
    }

    /** Backward compatible */
    public function deleteById(int $id): void
    {
        $this->softDelete($id);
    }

    private function rowToProducto(array $row): Producto
    {
        return new Producto(
            (int)($row['id'] ?? 0),
            $row['uuid'] ?? null,
            $row['marca'] ?? null,
            $row['modelo'] ?? null,
            (float)($row['precio'] ?? 0),
            (int)($row['stock'] ?? 0),
            $row['imagen'] ?? null,
            $row['categoria_id'] ?? null,
            $row['categoria_nombre'] ?? null,
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null,
            (bool)($row['is_deleted'] ?? false),
        );
    }
}
