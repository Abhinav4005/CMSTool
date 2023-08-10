<?php
$dataFilePath = __DIR__ . '/content.txt';

function readDataFromFile($file)
{
    $data = [];
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $data = json_decode($content, true);
    }
    return $data;
}

function writeDataToFile($file, $data)
{
    $content = json_encode($data);
    file_put_contents($file, $content);
}

function handleFileUpload($fieldName)
{
    $targetDir = 'uploads/';
    $targetFile = $targetDir . basename($_FILES[$fieldName]['name']);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if file is a valid image or video
    $imageTypes = ['jpg', 'jpeg', 'png', 'gif'];
    $videoTypes = ['mp4', 'avi', 'mov'];

    if ($_FILES[$fieldName]['tmp_name'] !== '') {
        if (in_array($fileType, $imageTypes)) {
            $fileType = 'image';
        } elseif (in_array($fileType, $videoTypes)) {
            $fileType = 'video';
        } else {
            $uploadOk = 0;
        }

        // Move the uploaded file to the target directory
        if ($uploadOk) {
            move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetFile);
            return $targetFile;
        }
    }

    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $contentItems = readDataFromFile($dataFilePath);
    header('Content-Type: application/json');
    echo json_encode($contentItems);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newContent = $_POST;
    $newContent['image'] = handleFileUpload('image');
    $newContent['video'] = handleFileUpload('video');

    if ($newContent && isset($newContent['title']) && ($newContent['image'] || $newContent['video']) && isset($newContent['content'])) {
        $contentItems = readDataFromFile($dataFilePath);
        $contentItems[] = $newContent;
        writeDataToFile($dataFilePath, $contentItems);

        header('Content-Type: application/json');
        echo json_encode($newContent);
        exit;
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request. Missing data.']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['index'])) {
    $index = intval($_GET['index']);
    $contentItems = readDataFromFile($dataFilePath);

    if ($index >= 0 && $index < count($contentItems)) {
        // Remove image and video files if they exist
        if (isset($contentItems[$index]['image']) && file_exists($contentItems[$index]['image'])) {
            unlink($contentItems[$index]['image']);
        }
        if (isset($contentItems[$index]['video']) && file_exists($contentItems[$index]['video'])) {
            unlink($contentItems[$index]['video']);
        }

        array_splice($contentItems, $index, 1);
        writeDataToFile($dataFilePath, $contentItems);
        http_response_code(204);
        exit;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Content not found.']);
        exit;
    }
}

http_response_code(404);
echo json_encode(['error' => 'Not found.']);
