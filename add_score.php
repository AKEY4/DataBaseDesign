<?php
include 'settings.php';
$conn = mysqli_connect($host, $user, $pwd, $dbnm);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shooter = trim($_POST['shooter']);
    $distance = trim($_POST['distance']);
    $score = trim($_POST['score']);
    $location = trim($_POST['location']);
    $time = trim($_POST['time']); // This will now be a valid datetime value
    $bow_id = trim($_POST['bow']);
    $arrow_id = trim($_POST['arrow']);

    if ($shooter && $distance && $score && $location && $time && $bow_id && $arrow_id) {
        // Insert into shot table
        $sql = "INSERT INTO shot (distance, score, location, time, type_bow, type_arrow) 
                VALUES ('$distance', '$score', '$location', '$time', '$bow_id', '$arrow_id')";

        if ($conn->query($sql) === TRUE) {
            $shot_id = $conn->insert_id; // Get the last inserted shot_id

            // Insert into main table to link the shot with the customer (shooter)
            $link_sql = "INSERT INTO main (shot_id, cust_id) VALUES ('$shot_id', '$shooter')";
            if ($conn->query($link_sql) === TRUE) {
                echo "<p>Score added and linked successfully!</p>";
            } else {
                echo "<p>Error linking shot to shooter: " . $conn->error . "</p>";
            }
        } else {
            echo "<p>Error inserting shot: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Please fill in all fields.</p>";
    }
}

$shooters = $conn->query("SELECT cust_id, firstname, lastname FROM customers");

// Updated to reference the correct table and column names
$bows = $conn->query("SELECT type_bow, bow_name FROM bows"); // Corrected table and column names
$arrows = $conn->query("SELECT type_arrow, arrow_name FROM arrows"); // Corrected table and column names

// Debugging: Check if the bow query returns any results
if (!$bows) {
    echo "<p>Error fetching bows: " . $conn->error . "</p>";
} else {
    echo "<p>Bows fetched successfully</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Score</title>
    <link rel="stylesheet" href="styles/style.css">
    <script src="scripts.js" defer></script>
</head>
<body>
    <h2>Add Shot Score</h2>
    <form method="POST">
        <label>Shooter:</label>
        <select name="shooter" required>
            <option value="">-- Select Shooter --</option>
            <?php while($row = $shooters->fetch_assoc()) {
                echo "<option value='" . $row['cust_id'] . "'>" . $row['firstname'] . " " . $row['lastname'] . "</option>";
            } ?>
        </select>
        
        <label>Distance:</label>
        <input type="number" name="distance" required>
        
        <label>Score:</label>
        <input type="number" name="score" required>
        
        <label>Location:</label>
        <input type="text" name="location" required>
        
        <label>Time:</label>
        <input type="datetime-local" name="time" required>
        
        <label>Bow:</label>
        <select name="bow" required>
            <option value="">-- Select Bow --</option>
            <?php
            if ($bows->num_rows > 0) {
                while($row = $bows->fetch_assoc()) {
                    echo "<option value='" . $row['type_bow'] . "'>" . $row['bow_name'] . "</option>";
                }
            } else {
                echo "<option value=''>No bows available</option>";
            }
            ?>
        </select>
        
        <label>Arrow:</label>
        <select name="arrow" required>
            <option value="">-- Select Arrow --</option>
            <?php while($row = $arrows->fetch_assoc()) {
                echo "<option value='" . $row['type_arrow'] . "'>" . $row['arrow_name'] . "</option>";
            } ?>
        </select>
        
        <input type="submit" value="Add Score">
    </form>
</body>
</html>

<?php $conn->close(); ?>
