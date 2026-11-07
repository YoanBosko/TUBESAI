<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    session_destroy();
    session_start();
    $classification = "";
    $file_name = "";
    $random_image = "";
} else {
    $classification = $_SESSION['classification'] ?? "";
    $file_name = $_SESSION['file_name'] ?? "";
    $random_image = $_SESSION['random_image'] ?? "";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['audio_file'])) {
    include 'process_audio.php';

    // Simpan hasil ke sesi
    $_SESSION['classification'] = $classification;
    $_SESSION['file_name'] = $file_name;
    $_SESSION['random_image'] = $random_image;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="style.css">
    <title>AI or Real</title>
</head>

<body>
    <div class="container">
        <div class="rectangle">
            <h1>Tool to recognize the difference between<br>AI and Real Photos</h1>
            <p>Just enter the image you want, the tool will scan and tell <br> you whether the photo is AI or not.</p>
        </div>
    </div>
    <div class="forForm">
        <div class="form-container">
            <h2>Input your photo file you want</h2>
            <form action="process_image.php" method="POST" enctype="multipart/form-data">
                <div class="UploadFile">
                    <label for="gambar_file">Input Your Picture here :</label>
                    <input type="file" id="gambar_file" name="file" accept=".jpg" required>
                </div>
                <div class="options">
                    <p>Category of Photos you want to analyze</p>
                    <div class="Radioo">
                        <label>
                            <input type="radio" name="category" value="Manusia" required>
                            <img src="Foto/icon1.png" alt="Human" class="icon"> Human
                        </label>
                        <label>
                            <input type="radio" name="category" value="Pemandangan" required>
                            <img src="Foto/icon2.png" alt="Environment" class="icon"> Environment
                        </label>
                    </div>
                </div>
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>
</body>

</html>