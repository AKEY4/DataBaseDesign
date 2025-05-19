<?php
include 'settings.php';
$conn = mysqli_connect($host, $user, $pwd, $dbnm);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shooter = trim($_POST['shooter']);
    $distance = trim($_POST['distance']);
    $score = trim($_POST['score']);
    $location = trim($_POST['location']);
    $time = trim($_POST['time']);
    $bow_id = trim($_POST['bow']);
    $arrow_id = trim($_POST['arrow']);

    if ($shooter && $distance && $score && $location && $time && $bow_id && $arrow_id) {
        // Insert into shot table without specifying shot_id (it will auto-increment)
        $sql = "INSERT INTO shot (distance, score, location, time, type_bow, type_arrow) 
                VALUES ('$distance', '$score', '$location', '$time', '$bow_id', '$arrow_id')";

        // Check if the insertion was successful
        if ($conn->query($sql) === TRUE) {
            // Get the auto-generated shot_id
            $shot_id = $conn->insert_id;  // Get the last inserted ID
            // Insert into main table linking shot_id with the shooter
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

// Fetch options for shooter, bow, arrow, and location
$shooters = $conn->query("SELECT cust_id, firstname, lastname FROM customers");
$bows = $conn->query("SELECT type_bow, bow_name FROM bows");
$arrows = $conn->query("SELECT type_arrow, arrow_name FROM arrows");
$locations = $conn->query("SELECT DISTINCT location FROM shot");  // Get unique locations
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Score</title>
    <link rel="stylesheet" href="styles/style.css">
    <script src="scripts/script.js" defer></script>
</head>
<body>
    <h2>Add Shot Score</h2>
    
    <!-- Link back to home.php -->
    <p><a href="home.php">Back to Home</a></p>

    <form method="POST">
        <!-- Shooter Dropdown -->
        <label>Shooter:</label>
        <select name="shooter" required>
            <option value="">-- Select Shooter --</option>
            <?php while($row = $shooters->fetch_assoc()) {
                echo "<option value='" . $row['cust_id'] . "'>" . $row['firstname'] . " " . $row['lastname'] . "</option>";
            } ?>
        </select>

        <!-- Distance Input -->
        <label>Distance:</label>
        <input type="number" name="distance" required>

        <!-- Score Input -->
        <label>Score:</label>
        <input type="number" name="score" required>

        <!-- Location Dropdown -->
        <label>Location:</label>
        <select name="location" required>
            <option value="">-- Select Location --</option>
            <?php while($row = $locations->fetch_assoc()) {
                echo "<option value='" . $row['location'] . "'>" . $row['location'] . "</option>";
            } ?>
        </select>

        <!-- Time Picker -->
        <label>Time:</label>
        <input type="datetime-local" name="time" required>

        <!-- Bow Dropdown -->
        <label>Bow:</label>
        <select name="bow" required>
            <option value="">-- Select Bow --</option>
            <?php while($row = $bows->fetch_assoc()) {
                echo "<option value='" . $row['type_bow'] . "'>" . $row['bow_name'] . "</option>";
            } ?>
        </select>

        <!-- Arrow Dropdown -->
        <label>Arrow:</label>
        <select name="arrow" required>
            <option value="">-- Select Arrow --</option>
            <?php while($row = $arrows->fetch_assoc()) {
                echo "<option value='" . $row['type_arrow'] . "'>" . $row['arrow_name'] . "</option>";
            } ?>
        </select>

        <!-- Submit Button -->
        <input type="submit" value="Add Score">
    </form>
</body>
</html>

<?php $conn->close(); ?>
