<form method="POST">

    <div class="form-group">

        <label for="solusi">
            Solusi / Catatan Guru BK
        </label>

        <textarea name="solusi" id="solusi" rows="8" required><?= htmlspecialchars($data['solusi'] ?? ''); ?></textarea>

    </div>


    <div class="form-group">

        <label for="status">
            Status Konseling
        </label>

        <select name="status" id="status" required>

            <option value="terjadwal" <?= ($data['status'] === 'terjadwal') ? 'selected' : ''; ?>>
                Terjadwal
            </option>

            <option value="selesai" <?= ($data['status'] === 'selesai') ? 'selected' : ''; ?>>
                Selesai
            </option>

            <option value="batal" <?= ($data['status'] === 'batal') ? 'selected' : ''; ?>>
                Batal
            </option>

        </select>

    </div>


    <button type="submit" name="simpan">
        <i class="bi bi-save"></i>
        Simpan Perubahan
    </button>

</form>