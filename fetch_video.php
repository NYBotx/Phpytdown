<?php
require 'vendor/autoload.php';

use YoutubeDl\YoutubeDl;
use YoutubeDl\Exception\NotFoundException;

// Sanitize input
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['video_url'])) {
    $videoUrl = filter_var(trim($_POST['video_url']), FILTER_SANITIZE_URL);

    if (!filter_var($videoUrl, FILTER_VALIDATE_URL)) {
        header("Location: index.php?error=Invalid URL format.");
        exit;
    }

    $yt = new YoutubeDl([
        'extract-audio' => false,
        'format' => 'bestvideo+bestaudio/best',
        'dump-json' => true,
    ]);

    try {
        $video = $yt->download($videoUrl);

        $title = htmlspecialchars($video->getTitle());
        $thumbnail = htmlspecialchars($video->getThumbnails()[0]['url'] ?? '');
        $formats = $video->getFormats();

        echo "<h1>Video: $title</h1>";
        echo "<img src='$thumbnail' alt='Thumbnail' style='max-width:200px;'><br>";
        echo "<h3>Select Format:</h3>";

        foreach ($formats as $format) {
            $quality = htmlspecialchars($format->getResolution() ?: 'Audio Only');
            $ext = htmlspecialchars($format->getExt());
            $url = urlencode($format->getUrl());

            echo "<form method='GET' action='download.php'>";
            echo "<input type='hidden' name='download_url' value='$url'>";
            echo "<input type='hidden' name='title' value='$title'>";
            echo "<button type='submit'>$quality - $ext</button>";
            echo "</form><br>";
        }
    } catch (NotFoundException $e) {
        header("Location: index.php?error=Video not found.");
        exit;
    } catch (Exception $e) {
        header("Location: index.php?error=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: index.php?error=Invalid request.");
    exit;
}
?>
