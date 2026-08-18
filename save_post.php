<?php
header('Content-Type: application/json');

$file = 'posts.json';

// File exist na ho toh empty array bana dein
if (!file_exists($file)) {
    file_put_contents($file, json_encode([]));
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$posts = json_decode(file_get_contents($file), true);
if (!is_array($posts)) {
    $posts = [];
}

$action = isset($input['action']) ? $input['action'] : 'add';

if ($action === 'add') {
    $newPost = [
        'title' => $input['title'],
        'date' => $input['date'],
        'tag' => $input['tag'],
        'content' => $input['content']
    ];
    
    // Naya post array me append karein
    $posts[] = $newPost;
    
    if (file_put_contents($file, json_encode($posts, JSON_PRETTY_PRINT))) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to write file']);
    }
} 
elseif ($action === 'delete') {
    $index = isset($input['index']) ? intval($input['index']) : -1;
    
    if (isset($posts[$index])) {
        array_splice($posts, $index, 1); // Post remove kar dein
        if (file_put_contents($file, json_encode($posts, JSON_PRETTY_PRINT))) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update file']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Post not found']);
    }
}
?>
