<?php
// Start session if not already started
function session_start_safe() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Function to sanitize user input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user is logged in
function is_logged_in() {
    session_start_safe();
    return isset($_SESSION['user_id']);
}

// Function to redirect user
function redirect($location) {
    header("Location: $location");
    exit;
}

// Function to display error message
function display_error($message) {
    return "<div class='error-message'>$message</div>";
}

// Function to display success message
function display_success($message) {
    return "<div class='success-message'>$message</div>";
}

// Function to get user data
function get_user_data($user_id, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, avatar, bio, date_joined FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return false;
    }
}

// Function to get topics with pagination
function get_topics($pdo, $page = 1, $limit = 10, $search = '') {
    $offset = ($page - 1) * $limit;
    
    try {
        $query = "SELECT t.id, t.title, t.content, t.created_at, t.user_id, u.username, 
                 (SELECT COUNT(*) FROM comments WHERE topic_id = t.id) as comment_count 
                 FROM topics t
                 JOIN users u ON t.user_id = u.id";
        
        $params = [];
        
        if (!empty($search)) {
            $query .= " WHERE t.title LIKE ? OR t.content LIKE ?";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $query .= " ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return false;
    }
}

// Function to count total topics (for pagination)
function count_topics($pdo, $search = '') {
    try {
        $query = "SELECT COUNT(*) as total FROM topics";
        $params = [];
        
        if (!empty($search)) {
            $query .= " WHERE title LIKE ? OR content LIKE ?";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'];
    } catch(PDOException $e) {
        return 0;
    }
}

// Function to get a single topic and its details
function get_topic($pdo, $topic_id) {
    try {
        $stmt = $pdo->prepare("SELECT t.*, u.username, u.avatar 
                              FROM topics t 
                              JOIN users u ON t.user_id = u.id 
                              WHERE t.id = ?");
        $stmt->execute([$topic_id]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return false;
    }
}

// Function to get comments for a topic
function get_comments($pdo, $topic_id, $parent_id = 0) {
    try {
        $stmt = $pdo->prepare("SELECT c.*, u.username, u.avatar 
                              FROM comments c 
                              JOIN users u ON c.user_id = u.id 
                              WHERE c.topic_id = ? AND c.parent_id = ? 
                              ORDER BY c.created_at ASC");
        $stmt->execute([$topic_id, $parent_id]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return false;
    }
}

// Function to format date
function format_date($date) {
    $timestamp = strtotime($date);
    return date('F j, Y \a\t g:i a', $timestamp);
}

// Function to get excerpt from content
function get_excerpt($content, $length = 150) {
    if (strlen($content) <= $length) {
        return $content;
    }
    
    return substr($content, 0, $length) . '...';
}

// Function to generate pagination links
function pagination($total_pages, $current_page, $url = '?page=') {
    $links = '';
    
    if ($total_pages > 1) {
        $links .= '<div class="pagination">';
        
        if ($current_page > 1) {
            $links .= '<a href="' . $url . ($current_page - 1) . '" class="pagination-prev">&laquo; Previous</a>';
        }
        
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $current_page) {
                $links .= '<span class="pagination-current">' . $i . '</span>';
            } else {
                $links .= '<a href="' . $url . $i . '">' . $i . '</a>';
            }
        }
        
        if ($current_page < $total_pages) {
            $links .= '<a href="' . $url . ($current_page + 1) . '" class="pagination-next">Next &raquo;</a>';
        }
        
        $links .= '</div>';
    }
    
    return $links;
}

// Function to get avatar URL based on avatar value (or default to placeholder)
function get_avatar_url($avatar) {
    $placeholder_urls = [
        "https://pixabay.com/get/g72625ae205a8c231fe8f03053d4a18cd2a3b1c1eeeb16871039124e17770ed97db69f5b03f6d30f672b89be6d9e54dbd5631ea9f314c23df50527330e60fd73f_1280.jpg",
        "https://pixabay.com/get/gd26d9bc36bcb2b13c2dce79d687a97e97e3094eddcf166ea918271034c572a600fb757a14b86c2a2deb50915cbc209f8cdfaaa2b07f274b302d09d20c6b9c309_1280.jpg",
        "https://pixabay.com/get/g61ddd1d5adecff3fc0d6b697c3ad005005d0cd397d95c46d9d4f9f019b88dde1aead1cf4be96bb3a869e43ee9b0ef56bb9f11bd8229d72f5d4d05044f4c3e451_1280.jpg",
        "https://pixabay.com/get/g3d40ed6de0c8da96299d089bffdf0cebf834da3dd33bb9993ee7a70655cd880d82c673c523a5ff831c572d0247243a7317ca6e1b32b5d4ac0e97f5d9d670d23f_1280.jpg"
    ];
    
    if (empty($avatar)) {
        // Return a random placeholder from the array
        $random_index = array_rand($placeholder_urls);
        return $placeholder_urls[$random_index];
    }
    
    return $avatar;
}
?>
