<?php
header('Content-Type: application/json');

// Get the raw POST data
$inputData = file_get_contents('php://input');
$data = json_decode($inputData, true);

if (isset($data['postIndex']) && isset($data['author']) && isset($data['text'])) {
    $postIndex = $data['postIndex'];
    $author = htmlspecialchars(trim($data['author']));
    $text = htmlspecialchars(trim($data['text']));
    
    if (empty($text)) {
        echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
        exit;
    }

    $commentsFile = 'comments.json';
    $allComments = [];

    // Load existing comments if file exists
    if (file_exists($commentsFile)) {
        $jsonContent = file_get_contents($commentsFile);
        $allComments = json_decode($jsonContent, true) ?? [];
    }

    // Initialize array for this post if it doesn't exist
    if (!isset($allComments[$postIndex])) {
        $allComments[$postIndex] = [];
    }

    // Add new comment
    $allComments[$postIndex][] = [
        'author' => $author,
        'text' => $text,
        'date' => date('Y-m-d H:i:s')
    ];

    // Save back to file
    if (file_put_contents($commentsFile, json_encode($allComments, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true, 'comments' => $allComments[$postIndex]]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save comment to server']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}
?>
