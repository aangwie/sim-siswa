<div class="modal fade" id="modalImport" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Import Data Siswa</h5>
        <button type="button" class="btn-close" id="btnCloseModal" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        <div id="formSection">
            <div class="d-flex justify-content-between align-items-center mb-3 p-2 border rounded bg-light">
                <span>Belum punya format?</span>
                <a href="download_template.php" class="btn btn-sm btn-outline-primary">Download Template</a>
            </div>
            <div class="mb-3">
                <label class="form-label">Pilih File Excel (.xlsx)</label>
                <input class="form-control" type="file" id="file_excel" accept=".xlsx, .xls">
            </div>
            <div class="alert alert-warning"><small>Maksimal 500 baris per file disarankan.</small></div>
        </div>

        <div id="progressSection" style="display: none;">
            <p id="progressText" class="text-center fw-bold mb-2">Mengupload File...</p>
            <div class="progress" style="height: 25px;">
                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%">0%</div>
            </div>
            <p class="text-center small text-muted mt-2">Jangan tutup jendela ini hingga selesai.</p>
        </div>

        <div id="resultMessage" class="mt-3"></div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="btnBatal" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btnStartImport" onclick="startImport()">Mulai Import</button>
        <button type="button" class="btn btn-success" id="btnSelesai" style="display:none;" onclick="location.reload()">Selesai & Refresh</button>
      </div>
    </div>
  </div>
</div>

<script>
async function startImport() {
    const fileInput = document.getElementById('file_excel');
    if (fileInput.files.length === 0) {
        alert("Pilih file terlebih dahulu!");
        return;
    }

    // 1. Siapkan UI
    document.getElementById('formSection').style.display = 'none';
    document.getElementById('btnStartImport').style.display = 'none';
    document.getElementById('btnBatal').style.display = 'none';
    document.getElementById('btnCloseModal').style.display = 'none'; // Cegah tutup
    document.getElementById('progressSection').style.display = 'block';
    
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const resultMsg = document.getElementById('resultMessage');
    const file = fileInput.files[0];

    // 2. Langkah Pertama: Upload File
    const formData = new FormData();
    formData.append('step', 'upload');
    formData.append('file', file);

    try {
        let response = await fetch('import_handler.php', { method: 'POST', body: formData });
        let data = await response.json();

        if (data.status !== 'success') throw new Error(data.message);

        // Jika upload sukses, kita dapat nama file sementara dan total baris
        const tempFileName = data.filename;
        const totalRows = data.total_rows;
        const batchSize = 20; // Proses 20 baris per request agar tidak berat
        let processed = 0;
        let successCount = 0;
        let failCount = 0;

        progressText.innerText = "Memproses Data ke Database...";

        // 3. Langkah Kedua: Looping Batch Import
        while (processed < totalRows) {
            const formDataBatch = new FormData();
            formDataBatch.append('step', 'process_batch');
            formDataBatch.append('filename', tempFileName);
            formDataBatch.append('start', processed); // Mulai dari baris ke-n
            formDataBatch.append('limit', batchSize); // Ambil 20 baris

            let resBatch = await fetch('import_handler.php', { method: 'POST', body: formDataBatch });
            let dataBatch = await resBatch.json();

            if (dataBatch.status === 'error') {
                console.error(dataBatch.message);
            }

            successCount += dataBatch.success;
            failCount += dataBatch.failed;
            processed += batchSize;

            // Update Progress Bar
            let percent = Math.min(100, Math.round((processed / totalRows) * 100));
            progressBar.style.width = percent + "%";
            progressBar.innerText = percent + "%";
        }

        // 4. Selesai
        progressBar.classList.remove('progress-bar-animated');
        progressText.innerText = "Proses Selesai!";
        resultMsg.innerHTML = `<div class="alert alert-info">
                                Sukses: <b>${successCount}</b><br>
                                Gagal/Duplikat: <b>${failCount}</b>
                               </div>`;
        document.getElementById('btnSelesai').style.display = 'block';
        document.getElementById('btnCloseModal').style.display = 'block';

    } catch (error) {
        alert("Terjadi Kesalahan: " + error.message);
        location.reload();
    }
}
</script>