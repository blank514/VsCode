<?php

include('ConnDatabase.php');

// Select the database
$conn->select_db($dbname);


// Requête pour récupérer toutes les inspections
$query = "SELECT * FROM ippm ORDER BY Creation_Date ASC";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<h2> IPPM DATA </h2>";
    echo "<table border='0.5' style='width:100%; border-collapse: colaps; text-align: left;'>
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

   // Requête pour récupérer toutes les inspections
   $result = mysqli_query($conn, $query);
$months = [];
$sales = [];

while($row = mysqli_fetch_assoc($result)) {
    $months[] = $row['NokQuantity'];
    $sales[] = $row['Creation_Date'];
}
//$Creation_Date[];
//$Creation_Time[];
//$Shift[]=$row['Shift'];
//$Inspector_Id[]=$row['Inspector_Id'];
//$PO[];
//$PN[];
//$Issue[];
//$Quantity[];
//$SortedQuantity[];
//$NokQuantity[];
//$Process[];
//$TeamLeader[];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Chart.js with PHP</title>
    <!-- Include Chart.js from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div style="width: 80%;">
        <canvas id="myChart"></canvas>
    </div>

    <script>
    const ctx = document.getElementById('myChart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'line', // You can change this to 'line', 'pie', etc.
        data: {
            // Pass PHP arrays into JavaScript using json_encode
            labels: <?php echo json_encode($sales); ?>, 
            datasets: [{
                label: 'NOK QUANTITY By Date',
                data: <?php echo json_encode($months); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: { y: { beginAtZero: true } }
        }
    });
</script>
</body>
</html>