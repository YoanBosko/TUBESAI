
<?php
// Konfigurasi untuk mendukung file besar
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');
ini_set('max_execution_time', 300);
ini_set('max_input_time', 300);

// Folder sementara untuk menyimpan file gambar
$temp_folder = "temp_images";

// Buat folder sementara jika belum ada
if (!is_dir($temp_folder)) {
    mkdir($temp_folder, 0777, true);
}

// Proses file jika formulir dikirim
$result = ""; // Variabel untuk menyimpan hasil prediksi
$file_name = ""; // Variabel untuk menyimpan nama file
$classification = ""; // Variabel untuk menyimpan hasil klasifikasi
$random_image = ""; // Variabel untuk menyimpan nama gambar acak
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image_file'])) {
    $file_error = $_FILES['image_file']['error'];
    $file_size = $_FILES['image_file']['size'];
    $file_tmp = $_FILES['image_file']['tmp_name'];
    $file_name = basename($_FILES['image_file']['name']);
    $temp_file_path = $temp_folder . DIRECTORY_SEPARATOR . $file_name;

    // Validasi file upload
    if ($file_error === UPLOAD_ERR_OK) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);

        // Daftar MIME type yang diterima
        $allowed_mime_types = ['image/jpeg', 'image/jpg'];

        if (!in_array($mime_type, $allowed_mime_types)) {
            die("Error: File harus berupa format JPG. Tipe MIME yang diizinkan: " . implode(', ', $allowed_mime_types) . ". Tipe MIME file: $mime_type");
        }

        // Pindahkan file ke folder sementara
        if (move_uploaded_file($file_tmp, $temp_file_path)) {
            // Gunakan interpreter Python dari virtual environment
            $python = escapeshellcmd(realpath("./myenv/Scripts/python.exe")); // Sesuaikan path ke Python di myenv
            $script = escapeshellcmd(realpath("predict_image.py")); // Path absolut ke skrip Python

            $escaped_file_path = escapeshellarg($temp_file_path);
            $command = "$python $script $escaped_file_path";

            // Eksekusi perintah dan tangkap output
            $output = shell_exec($command . " 2>&1");
            if ($output === null) {
                $classification = "Error: Tidak dapat menjalankan skrip Python.";
            } else {
                // Proses output Python berdasarkan format
                if (strpos($output, "RESULT_START") !== false && strpos($output, "RESULT_END") !== false) {
                    $start = strpos($output, "RESULT_START") + strlen("RESULT_START");
                    $end = strpos($output, "RESULT_END");
                    $content = substr($output, $start, $end - $start);

                    // Ambil nama file dan klasifikasi
                    $lines = explode("\n", $content);
                    foreach ($lines as $line) {
                        if (strpos($line, "FILE_NAME=") === 0) {
                            $file_name = trim(str_replace("FILE_NAME=", "", $line));
                        } elseif (strpos($line, "CLASSIFICATION=") === 0) {
                            $classification = trim(str_replace("CLASSIFICATION=", "", $line));
                        }
                    }
                } else {
                    $classification = "Error: Tidak ada hasil klasifikasi yang valid dari Python.";
                }
            }

            // Pilih gambar acak berdasarkan klasifikasi
            $base_path = __DIR__ . '/assets/';
            $folders = ['ManusiaAI', 'ManusiaReal', 'PemandanganAI', 'PemandanganReal'];
            if (in_array($classification, $folders)) {
                $folder_path = $base_path . $classification;
                $images = array_diff(scandir($folder_path), ['.', '..']);
                $random_image = $images ? $images[array_rand($images)] : null;
            }

            // Hapus file sementara setelah proses selesai
            if (file_exists($temp_file_path)) {
                unlink($temp_file_path);
            }
        } else {
            $classification = "Gagal mengunggah file.";
        }
    } else {
        $classification = "Error saat mengunggah file. Kode error: $file_error";
    }

    // Hapus folder sementara jika kosong
    if (is_dir($temp_folder) && count(scandir($temp_folder)) === 2) {
        rmdir($temp_folder);
    }
}
?>
