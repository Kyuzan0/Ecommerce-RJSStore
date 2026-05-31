<?php

class AkunPreset extends BaseModel
{
    protected string $table = 'akun_preset';

    /**
     * Get all presets, ordered by name.
     */
    public function getAll(): array
    {
        $rows = $this->db->fetchAll("SELECT * FROM akun_preset ORDER BY nama_layanan ASC");
        foreach ($rows as &$row) {
            $row['durasi_arr'] = json_decode($row['durasi'], true) ?: [];
            $row['paket_arr'] = json_decode($row['paket'], true) ?: [];
        }
        return $rows;
    }

    /**
     * Get presets formatted for the product form JS (keyed by name).
     */
    public function getForForm(): array
    {
        $rows = $this->getAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['nama_layanan']] = [
                'label'  => $row['nama_layanan'],
                'durasi' => $row['durasi_arr'],
                'paket'  => $row['paket_arr'],
            ];
        }
        // Always include a "Lainnya" fallback
        $result['Lainnya'] = [
            'label'  => 'Lainnya (custom)',
            'durasi' => ['1 Bulan', '3 Bulan', '6 Bulan', '12 Bulan', 'Lifetime'],
            'paket'  => [],
        ];
        return $result;
    }

    /**
     * Create a new preset.
     */
    public function createPreset(string $nama, array $durasi, array $paket): int
    {
        return $this->create([
            'nama_layanan' => $nama,
            'durasi'       => json_encode($durasi),
            'paket'        => json_encode($paket),
        ]);
    }

    /**
     * Update an existing preset.
     */
    public function updatePreset(int $id, string $nama, array $durasi, array $paket): bool
    {
        return $this->update($id, [
            'nama_layanan' => $nama,
            'durasi'       => json_encode($durasi),
            'paket'        => json_encode($paket),
        ]);
    }
}
