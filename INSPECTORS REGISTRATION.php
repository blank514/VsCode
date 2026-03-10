<?php
include('ConnDatabase.php');

// Select the database
$conn->select_db($dbname);

// Create inspectors table if it doesn't exist
$inspectors_table = "CREATE TABLE IF NOT EXISTS inspectors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    inspector_id VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    password1 VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($inspectors_table) === TRUE) {
    echo "✅ Table 'inspectors' created successfully.<br>";
} else {
    echo "❌ Error creating table: " . $conn->error . "<br>";
}

// Handle form submission
$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_inspector"])) 
{
    // 1. Collect and clean data
    $full_name = strtolower(trim( filter_input(INPUT_POST,"full_name",FILTER_SANITIZE_SPECIAL_CHARS) ?? ""));
    $inspector_id = strtolower(trim( filter_input(INPUT_POST,"inspector_id",FILTER_SANITIZE_SPECIAL_CHARS) ?? ""));
    $email = trim( filter_input(INPUT_POST,"email",FILTER_SANITIZE_EMAIL) ?? "");
    $phone_number = strtolower(trim( filter_input(INPUT_POST,"phone_number",FILTER_SANITIZE_SPECIAL_CHARS) ?? ""));
    $password = $_POST["password1"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
   
    $is_valid = true; // Flag to track errors

    // 2. Comprehensive Validation
    if (empty($full_name) || empty($inspector_id) || empty($email) || empty($password) || empty($phone_number)) {
        $message = "❌ Please fill in all fields";
        $message_type = "error";
        $is_valid = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "❌ Invalid email format";
        $message_type = "error";
        $is_valid = false;
    } elseif ($password !== $confirm_password) {
        $message = "❌ Passwords do not match!";
        $message_type = "error";
        $is_valid = false;
    } elseif (strlen($password) < 6) {
        $message = "❌ Password must be at least 6 characters long";
        $message_type = "error";
        $is_valid = false;
    }

    // 3. Check if id already exists (if still valid)
    if ($is_valid) {
        $check_stmt = $conn->prepare("SELECT id FROM inspectors WHERE inspector_id = ?");
        $check_stmt->bind_param("s", $inspector_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $message = "❌ Error: Id '$inspector_id' already exists.";
            $message_type = "error";
            $is_valid = false;
        }
        $check_stmt->close();
    }

    // 4. Final Insertion
    if ($is_valid) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Make sure column names match: full_name, inspector_id, email, phone_number, password1
        $stmt = $conn->prepare("INSERT INTO inspectors (full_name, inspector_id, email, phone_number, password1) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssss", $full_name, $inspector_id, $email, $phone_number, $hashed_password);
            
            if ($stmt->execute()) {
                $message = "✅ Inspector '$full_name' added successfully!";
                $message_type = "success";
               
            } else {
                if (strpos($stmt->error, "Duplicate entry") !== false) {
                    $message = "❌ Inspector ID '$inspector_id' already exists";
                } else {
                    $message = "❌ Database Error: " . $stmt->error;
                }
                $message_type = "error";
            }
            $stmt->close();
        } else {
            $message = "❌ SQL Prepare Error: " . $conn->error;
            $message_type = "error";
        }
    }
}

// Fetch all inspectors
$result = $conn->query("SELECT * FROM inspectors ORDER BY id DESC");
$inspectors = [];
if ($result) {
    $inspectors = $result->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quality Process - Add Inspector</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 2rem;
        }

        .header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 2rem;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 2.5rem;
            letter-spacing: 2px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .form-section h2 {
            color: #333;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 0.9rem;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
        }

        .message {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 6px;
            text-align: center;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #721c24;
        }

        .inspectors-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .inspectors-section h2 {
            color: #333;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }

        .inspectors-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .inspectors-table th,
        .inspectors-table td {
            padding: 1rem;
            text-align: left;
            border: 1px solid #ddd;
        }

        .inspectors-table th {
            background: #f2f2f2;
            font-weight: 600;
        }

        .inspectors-table tr:nth-child(even) {
            background: #fafafa;
        }

        .inspectors-table tr:hover {
            background: #e3f2fd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Quality Process</h1>
    </div>

    <div class="container">
        <div class="form-section">
            <h2>Add New Inspector</h2>
            
            <?php if ($message): ?>
                <div class="message <?= $message_type ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name:</label>
                        <input type="text" id="full_name" name="full_name" placeholder="Enter full name" required>
                    </div>
                    <div class="form-group">
                        <label for="inspector_id">Inspector ID:</label>
                        <input type="text" id="inspector_id" name="inspector_id" placeholder="Enter inspector ID" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address:</label>
                        <input type="email" id="email" name="email" placeholder="email@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Phone Number:</label>
                        <input type="tel" id="phone_number" name="phone_number" placeholder="+123456789" required>
                    </div>
                    <div class="form-group">
                        <label for="password1">Password:</label>
                        <input type="password" id="password1" name="password1" required>
                    </div>
                      <div class="form-group">
                      <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                </div>
                <button type="submit" name="add_inspector" class="submit-btn">Register Inspector</button>
            </form>
            <p style="margin-top: 1rem; text-align: center; font-size: 0.9rem;">
            Already registered? <a href="INSPECTORS LOGIN.php"> LOGIN </a>
          </p>
        </div>
        
        <div class="inspectors-section">
            <h2>Registered Inspectors</h2>
            <?php if (empty($inspectors)): ?>
                <p style="text-align: center; color: #777; padding: 1rem;">No inspectors registered yet.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="inspectors-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Inspector ID</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Joined Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inspectors as $inspector): ?>
                                <tr>
                                    <td><?= htmlspecialchars($inspector['id']) ?></td>
                                    <td><?= htmlspecialchars($inspector['full_name']) ?></td>
                                    <td><span style="font-weight: bold; color: #764ba2;"><?= htmlspecialchars($inspector['inspector_id']) ?></span></td>
                                    <td><?= htmlspecialchars($inspector['email']) ?></td>
                                    <td><?= htmlspecialchars($inspector['phone_number']) ?></td>
                                    <td><?= date('Y-m-d', strtotime($inspector['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
 $_SESSION['email'] = $inspector['email'] ;
 $_SESSION['phone_number'] = $inspector['phone_number'];
?>