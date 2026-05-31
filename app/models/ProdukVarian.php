<?php

class ProdukVarian extends BaseModel
{
    protected string $table = 'produk_varian';

    /**
     * Get all variants for a product, cheapest first.
     */
    public function getByProduk(int $produkId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM produk_varian WHERE produk_id = ? ORDER BY harga ASC, id ASC",
            [$produkId]
        );
    }

    /**
     * Get a single variant scoped to its product (safety check).
     */
    public function findForProduk(int $varianId, int $produkId): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM produk_varian WHERE id = ? AND produk_id = ?",
            [$varianId, $produkId]
        );
    }

    /**
     * Replace all variants for a product (used on product update).
     * $variants: array of ['durasi','paket','harga','account_info'].
     */
    public function replaceForProduk(int $produkId, array $variants): void
    {
        $this->db->execute("DELETE FROM produk_varian WHERE produk_id = ?", [$produkId]);
        foreach ($variants as $v) {
            $this->db->execute(
                "INSERT INTO produk_varian (produk_id, durasi, paket, harga, account_info) VALUES (?, ?, ?, ?, ?)",
                [$produkId, $v['durasi'], $v['paket'], $v['harga'], $v['account_info']]
            );
        }
    }

    /**
     * Get the lowest price among a product's variants (for catalog display).
     */
    public function getMinPrice(int $produkId): ?int
    {
        $row = $this->db->fetchOne(
            "SELECT MIN(harga) AS min_harga FROM produk_varian WHERE produk_id = ?",
            [$produkId]
        );
        return $row && $row['min_harga'] !== null ? (int) $row['min_harga'] : null;
    }

    /**
     * Build a human-readable label from durasi + paket.
     */
    public static function label(array $variant): string
    {
        $label = $variant['durasi'] ?? '';
        if (!empty($variant['paket'])) {
            $label .= ' - ' . $variant['paket'];
        }
        return trim($label);
    }
}
