<?php
session_start();
include 'db_config.php'; // Remplacez par votre fichier de connexion
if (ob_get_length()) ob_clean();
// 1. Nom du fichier
$filename = "Rapport_IPPM_" . date('d-m-y_h-m-s') . ".xls";

// 2. Headers pour forcer l'ouverture dans Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// 3. Entêtes des colonnes (Titres)
// Note: \t crée une nouvelle colonne, \n crée une nouvelle ligne
echo "ID\tDate\tHeure\tShift\tInspecteur\tPO\tPN\tQuantité\tNOK\tProcess\tTeam Leader\n";

// 4. Exécution de votre requête
$query = "SELECT * FROM ippm ORDER BY Creation_Date DESC";
$result = $conn->query($query) or die($conn->error);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Séparation de la date et de l'heure pour Excel
        $timestamp = strtotime($row['Creation_Date']);
        $date = date('d/m/Y', $timestamp);
        $time = date('H:i:s', $timestamp);

        // Nettoyage des données pour éviter de casser les colonnes Excel
        echo $row['Id'] . "\t";
        echo $date . "\t";
        echo $time . "\t";
        echo ($row['Shift'] ?? 'N/A') . "\t";
        echo $row['Inspector_Id'] . "\t";
        echo $row['PO'] . "\t";
        echo $row['PN'] . "\t";
        echo $row['Quantity'] . "\t";
        echo $row['NokQuantity'] . "\t";
        echo $row['Process'] . "\t";
        echo str_replace(["\n", "\r", "\t"], ' ', $row['TeamLeader']) . "\n";
    }
} else {
    echo "Aucune donnée trouvée";
}

exit();

