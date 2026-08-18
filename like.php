<?php
if(isset($_POST['id'])) {
    $id = $_POST['id'];
    $file = 'comments.txt';
    $content = file_get_contents($file);
    
    // RegEx se count update karna
    $pattern = '/<span data-like-id="'.$id.'" class="like-count">(\d+)<\/span>/';
    
    $new_content = preg_replace_callback($pattern, function($matches) {
        $new_val = $matches[1] + 1;
        return '<span data-like-id="'.$matches[0].'" class="like-count">'.$new_val.'</span>';
    }, $content);
    
    // Yahan ek simple trick: agar regex kaam na kare toh file read kar ke update karna
    // Lekin ye simple logic aapke file structure ke liye behtar hai
    $content = preg_replace('/<span data-like-id="'.$id.'" class="like-count">(\d+)<\/span>/', '<span data-like-id="'.$id.'" class="like-count">'.($matches[1] + 1).'</span>', $content);

    // Isay asan banate hain:
    preg_match('/<span data-like-id="'.$id.'" class="like-count">(\d+)<\/span>/', $content, $matches);
    $current = (int)$matches[1];
    $next = $current + 1;
    $content = str_replace($matches[0], '<span data-like-id="'.$id.'" class="like-count">'.$next.'</span>', $content);
    
    file_put_contents($file, $content);
    echo $next; // Naya count wapas bhejna
}
?>
