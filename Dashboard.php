<?php
include('ConnDatabase.php');


// Si elle n'existe pas, l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    // Redirige immédiatement vers la page de login
    header("Location: INSPECTOR LOGIN.php");
    exit(); // Très important : arrête l'exécution du script
}




// Select the database
$conn->select_db($dbname);

// Create table if it doesn't exist
$IPPM_table = "CREATE TABLE IF NOT EXISTS ippm (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Creation_Date DATE DEFAULT (CURRENT_DATE),
    Creation_Time TIME DEFAULT (CURRENT_TIME),
    Shift VARCHAR(20) NOT NULL,
    Inspector_Id VARCHAR(50) NOT NULL,
    PO VARCHAR(50) NOT NULL,
    PN VARCHAR(50) NOT NULL,
    Issue VARCHAR(50) NOT NULL,
    Quantity INT NOT NULL,
    SortedQuantity INT NOT NULL,
    NokQuantity INT NOT NULL,
    Process VARCHAR (50) NOT NULL,
    TeamLeader VARCHAR(50) NOT NULL
)";

if ($conn->query($IPPM_table) === TRUE) {
    echo "✅ Table 'IPPM' created successfully.<br>";
} else {
    echo "❌ Error creating table: " . $conn->error . "<br>";
}
// 1. Définir le fuseau horaire
date_default_timezone_set('Africa/Casablanca'); 
$heure_actuelle = (int)date("H"); // Récupère l'heure (ex: 14)
$Time = date("H:m:s");
// 2. Déterminer le Shift
if ($heure_actuelle >= 6 && $heure_actuelle < 14) {
    $shift = "Morning";
} elseif ($heure_actuelle >= 14 && $heure_actuelle < 22) {
    $shift = "Evening";
} else {
    // De 22:00 à 05:59
    $shift = "Night";
}

echo "Shift: " . $shift. "<br>";
echo "Time: " . $Time. "<br>";
// 2. Traitement du formulaire d'ajout d'inspection
$msg = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["SubmitIppm"])) {
    $ins_id = $_SESSION['inspector_id']; // Récupéré lors du login
    $PO=trim($_POST['PO'] ?? "");
    $PN=trim($_POST['PN'] ?? "");
    $Issue=trim($_POST['Issue'] ?? "");
    $Quantity=trim($_POST['Quantity'] ?? "");
    $SortedQuantity=trim($_POST['SortedQuantity'] ?? "");
    $NokQuantity=trim($_POST['NokQuantity'] ?? "");
    $Process=trim($_POST['Process'] ?? "");
    $TeamLeader=trim($_POST['TeamLeader'] ?? "");

        // On vérifie si les chaînes ne sont pas vides (autorise le "0")
        if ( $PO !== "" && $PN !== "" && $Issue !== "" && $Quantity !== ""&& $SortedQuantity !== "" && $NokQuantity !== ""&& $Process !== ""&& $TeamLeader !== "") {
 
            // Correction : On ajoute Inspector_Id dans les colonnes (Total 9 colonnes)
            $stmt = $conn->prepare("INSERT INTO ippm (Shift, Inspector_Id, PO, PN, Issue, Quantity, SortedQuantity, NokQuantity, Process, TeamLeader) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            // Vérifiez bien si PO est un nombre (i) ou du texte (s)
            $stmt->bind_param("sssssiiiss",$shift,  $ins_id, $PO, $PN, $Issue, $Quantity, $SortedQuantity, $NokQuantity, $Process, $TeamLeader);
    
            if ($stmt->execute()) {
                $msg = "<div class='success'>✅ Inspection report saved!</div>";

                // REDIRECTION vers la même page (ex: dashboard.php)
                 header("Location: dashboard.php");
                 exit(); // Très important pour arrêter le script
                 
            } else {
                $msg = "<div class='error'>❌ Error: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            $msg = "<div class='error'>❌ Please fill in all required fields.</div>";
        }
    }
    $current_ins_id = $_SESSION['inspector_id'] ?? '';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Quality Process</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; }
        .header { background: #764ba2; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        
        /* Form Styles */
        .form-grid { display: grid; grid-template-columns: 2fr 1fr auto; gap: 10px; align-items: end; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .btn-save { background: #27ae60; color: white; border: none; padding: 11px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        
        /* Table Styles */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f8f9fa; text-align: left; padding: 12px; border-bottom: 2px solid #dee2e6; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .status-passed { color: #27ae60; font-weight: bold; }
        .status-failed { color: #e74c3c; font-weight: bold; }
        
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 10px; }
        .logout { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></h2>
        <a href="INSPECTORS LOGIN.php" class="logout">Logout</a>
    </div>

    <?= $msg ?>
    <!-- Section Saisie -->
<div class="card">
    <h2>IPPM Data</h2>
    <form method="POST" action="">
    <div class="form-group">
            <label for="PO">PO :</label>
            <!-- Note: Votre SQL a une contrainte UNIQUE, donc cet ID ne peut être utilisé qu'une fois -->
            <input type="text" id="PO" name="PO">
        </div>
    <div class="form-group">
            <label for="PN">PN :</label>
            <!-- Note: Votre SQL a une contrainte UNIQUE, donc cet ID ne peut être utilisé qu'une fois -->
            <input type="text" id="PN" name="PN" >
        </div>
        <div class="form-group">
            <label for="Issue">Issue :</label>
            <!-- Note: Votre SQL a une contrainte UNIQUE, donc cet ID ne peut être utilisé qu'une fois -->
            <input type="text" id="Issue" name="Issue" >
        </div>
        <div class="form-group">
            <label for="Quantity">Quantity :</label>
            <!-- Note: Votre SQL a une contrainte UNIQUE, donc cet ID ne peut être utilisé qu'une fois -->
            <input type="text" id="Quantity" name="Quantity" >
        </div>
        <div class="form-group">
            <label for="SortedQuantity">SortedQuantity:</label>
            <!-- Note: Votre SQL a une contrainte UNIQUE, donc cet ID ne peut être utilisé qu'une fois -->
            <input type="text" id="SortedQuantity" name="SortedQuantity" >
        </div>
        <div class="form-group">
            <label for="NokQuantity">NokQuantity :</label>
            <!-- Note: Votre SQL a une contrainte UNIQUE, donc cet ID ne peut être utilisé qu'une fois -->
            <input type="text" id="NokQuantity" name="NokQuantity" >
        </div>
        <div class="form-group">
            <label for="Process">Process :</label>
            <!-- Note: Votre SQL a une contrainte UNIQUE, donc cet ID ne peut être utilisé qu'une fois -->
            <input type="text" id="Process" name="Process" >
        </div>
        <div class="form-group">
            <label for="TeamLeader">TeamLeader :</label>
            <!-- Note: Votre SQL a une contrainte UNIQUE, donc cet ID ne peut être utilisé qu'une fois -->
            <input type="text" id="TeamLeader" name="TeamLeader" >
        </div>
        <button type="submit" name="SubmitIppm" class="submit-btn">Enregistrer</button>
        <a href="export.php" class="btn-export">
    📊 Exporter vers Excel
</a>

    </form>
</div>

</body>
</html>
<?php
// 3. Récupération des données pour le tableau
 // Assurez-vous d'utiliser inspector_id

if (!empty($current_ins_id)) {
    $query = "SELECT * FROM ippm WHERE inspector_id = ? ORDER BY id DESC";
    $stmt_list = $conn->prepare($query);

    if ($stmt_list) {
        $stmt_list->bind_param("s", $current_ins_id);
        $stmt_list->execute();
        $result = $stmt_list->get_result();
        $IPPM_DATA = $result->fetch_all(MYSQLI_ASSOC);
        $stmt_list->close();
    } else {
        echo "❌ Erreur de préparation : " . $conn->error;
    }
} else {
    $IPPM_DATA = []; // Liste vide si pas d'ID
}
// Requête pour récupérer toutes les inspections
$sql = "SELECT * FROM ippm ORDER BY Creation_Date ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2> IPPM DATA </h2>";
    echo "<table border='1' style='width:100%; border-collapse: collapse; text-align: left;'>
            <tr style='background-color: #f2f2f2;'>
                <th>Date</th>
                <th>Time</th>
                <th>Shift</th>
                <th>Inspecteur</th>
                <th>PO</th>
                <th>PN</th>
                <th>Issue</th>
                <th>Quantité</th>
                <th>Sorted</th>
                <th>Nok</th>
                <th>Process</th>
                <th>Team Leader</th>
            </tr>";

    // Boucle sur chaque ligne de la base de données
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row["Creation_Date"]) . "</td>
                <td>" . htmlspecialchars($row["Creation_Time"]) . "</td>
                 <td>" . htmlspecialchars($row["Shift"]) . "</td>
                <td>" . htmlspecialchars($row["Inspector_Id"]) . "</td>
                <td>" . htmlspecialchars($row["PO"]) . "</td>
                <td>" . htmlspecialchars($row["PN"]) . "</td>
                <td>" . htmlspecialchars($row["Issue"]) . "</td>
                <td>" . htmlspecialchars($row["Quantity"]) . "</td>
                <td>" . htmlspecialchars($row["SortedQuantity"]) . "</td>
                <td style='color: red; font-weight: bold;'>" . htmlspecialchars($row["NokQuantity"]) . "</td>
                <td>" . htmlspecialchars($row["Process"]) . "</td>
                <td>" . htmlspecialchars($row["TeamLeader"]) . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>Aucune donnée enregistrée pour le moment.</p>";
}

?>
