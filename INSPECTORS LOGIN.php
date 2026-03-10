<?php
include('ConnDatabase.php');
// Select the database
$conn->select_db($dbname);

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["login"])) {
    $inspector_id =strtolower(trim( filter_input(INPUT_POST,"inspector_id",FILTER_SANITIZE_SPECIAL_CHARS) ?? ""));
    $password_input = $_POST["password"] ?? "";

    if (empty($inspector_id) || empty($password_input)) {
        $message = "❌ Please fill in all fields.";
        $message_type = "error";
    } else {
        // 1. Rechercher l'inspecteur par son ID unique
        $stmt = $conn->prepare("SELECT id, full_name, password1 FROM inspectors WHERE inspector_id = ?");
        $stmt->bind_param("s", $inspector_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // Vérifier le mot de passe
            if (password_verify($password_input, $user['password1'])) {
            
                $_SESSION['inspector_id'] = $inspector_id; // Stocke l'ID texte (ex: ins-001)
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                
                header("Location: dashboard.php");
                exit();
            } else {
        
                $message = "❌ Invalid Password.";
                $message_type = "error";
            }
        } else {
            $message = "❌ Inspector ID not found.";
            $message_type = "error";
        }
        
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quality Process - Login</title>
    <!-- Réutilisez vos styles CSS ici -->
    <style>
        /* Insérez le CSS de votre page d'inscription ici pour garder le même design */
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; padding: 2rem; display: flex; justify-content: center; }
        .login-card { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .form-group { margin-bottom: 1rem; }
        input { width: 100%; padding: 0.8rem; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px; }
        .btn { width: 100%; padding: 1rem; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 1rem; text-align: center; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2 style="text-align: center; margin-bottom: 1.5rem;">Inspectors Login</h2>
        
        <?php if ($message): ?>
            <div class="<?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Inspector ID</label>
                <input type="text" name="inspector_id" placeholder="Enter your ID" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" name="login" class="btn">Login</button>
        </form>
        <p style="margin-top: 1rem; text-align: center; font-size: 0.9rem;">
            Not registered? <a href="INSPECTORS REGISTRATION.php">Create an account</a>
        </p>
    </div>
</body>
</html>
