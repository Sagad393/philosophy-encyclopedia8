<?php
// إعداد الاتصال بقاعدة البيانات
$host = 'localhost';
$dbname = 'philosophy_db'; // اسم قاعدة البيانات
$username = 'root'; // اسم المستخدم الافتراضي في XAMPP
$password = ''; // الباسورد الافتراضي فارغ

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['error' => 'فشل الاتصال بقاعدة البيانات']));
}

// استقبال نوع الطلب (جلب تعليقات أم إضافة تعليق)
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'get_comments') {
    $story_id = isset($_GET['story_id']) ? intval($_GET['story_id']) : 0;
    
    // جلب التعليقات مع ترتيبها زمنياً
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE story_id = ? ORDER BY created_at ASC");
    $stmt->execute([$story_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($comments);
    exit;
} 
elseif ($action == 'add_comment') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $story_id = intval($data['story_id']);
    $parent_id = !empty($data['parent_id']) ? intval($data['parent_id']) : null;
    $user_name = htmlspecialchars(strip_tags($data['user_name']));
    $comment_text = htmlspecialchars(strip_tags($data['comment_text']));
    
    if(!empty($user_name) && !empty($comment_text)) {
        $stmt = $pdo->prepare("INSERT INTO comments (story_id, parent_id, user_name, comment_text) VALUES (?, ?, ?, ?)");
        $stmt->execute([$story_id, $parent_id, $user_name, $comment_text]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'البيانات غير مكتملة']);
    }
    exit;
}
?>