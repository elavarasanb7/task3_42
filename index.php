<?php
$page_title = 'Home';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Get current page for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 10;

// Get total topics count for pagination
$total_topics = count_topics($pdo);
$total_pages = ceil($total_topics / $limit);

// Get topics for current page
$topics = get_topics($pdo, $page, $limit);

// Get user count and recent users
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $user_count = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM topics");
    $topic_count = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM comments");
    $comment_count = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT username, avatar, date_joined FROM users ORDER BY date_joined DESC LIMIT 5");
    $recent_users = $stmt->fetchAll();
} catch(PDOException $e) {
    $user_count = 0;
    $topic_count = 0;
    $comment_count = 0;
    $recent_users = [];
}

include 'includes/header.php';
?>

<div class="hero">
    <div class="hero-content">
        <h1>Welcome to Our Community Forum</h1>
        <p>Join discussions, share your thoughts, and connect with a community of like-minded individuals.</p>
        <?php if (!is_logged_in()): ?>
            <a href="register.php" class="btn">Join Now</a>
        <?php else: ?>
            <a href="create_topic.php" class="btn">Start a Discussion</a>
        <?php endif; ?>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo $user_count; ?></div>
        <div class="stat-label">Members</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $topic_count; ?></div>
        <div class="stat-label">Topics</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $comment_count; ?></div>
        <div class="stat-label">Comments</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Latest Discussions</h2>
        <?php if (is_logged_in()): ?>
            <a href="create_topic.php" class="btn">Create New Topic</a>
        <?php endif; ?>
    </div>
    
    <?php if ($topics && count($topics) > 0): ?>
        <div class="topic-list">
            <?php foreach ($topics as $topic): ?>
                <div class="topic-item">
                    <div class="topic-avatar">
                        <img src="<?php echo get_avatar_url($topic['avatar'] ?? ''); ?>" alt="User Avatar">
                    </div>
                    <div class="topic-content">
                        <div class="topic-header">
                            <h3 class="topic-title"><a href="topic.php?id=<?php echo $topic['id']; ?>"><?php echo htmlspecialchars($topic['title']); ?></a></h3>
                        </div>
                        <div class="topic-meta">
                            <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($topic['username']); ?></span>
                            <span><i class="fas fa-clock"></i> <?php echo format_date($topic['created_at']); ?></span>
                            <span><i class="fas fa-comments"></i> <?php echo $topic['comment_count']; ?> comments</span>
                        </div>
                        <div class="topic-excerpt">
                            <?php echo get_excerpt($topic['content']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php echo pagination($total_pages, $page); ?>
        
    <?php else: ?>
        <div class="empty-state">
            <p>No discussions found. Be the first to start a discussion!</p>
            <?php if (is_logged_in()): ?>
                <a href="create_topic.php" class="btn">Create New Topic</a>
            <?php else: ?>
                <a href="login.php" class="btn">Login to Start a Discussion</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <h2>Newest Members</h2>
    </div>
    
    <?php if ($recent_users && count($recent_users) > 0): ?>
        <div class="user-list">
            <?php foreach ($recent_users as $user): ?>
                <div class="user-item">
                    <div class="user-avatar">
                        <img src="<?php echo get_avatar_url($user['avatar'] ?? ''); ?>" alt="User Avatar">
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                        <div class="user-joined">Joined: <?php echo format_date($user['date_joined']); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>No users found.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
