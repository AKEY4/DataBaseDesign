<?php
include 'settings.php';
$conn = mysqli_connect($host, $user, $pwd, $dbnm);

// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shooter = trim($_POST['shooter']);
    $distance = trim($_POST['distance']);
    $score = trim($_POST['score']);
    $location = trim($_POST['location']);
    $time = trim($_POST['time']);
    $type_bow = trim($_POST['bow']);
    $type_arrow = trim($_POST['arrow']);
    $round_id = trim($_POST['round']);

    // Validate and process the score
    if (strtolower($score) == 'x') {
        $score = 10; // If the user enters 'x', set score to 10
    } elseif (strtolower($score) == 'm') {
        $score = 0; // If the user enters 'm', set score to 0
    } elseif (!is_numeric($score) || $score < 0 || $score > 10 || floor($score) != $score) {
        echo "<p>Invalid score! Please enter a whole number between 0 and 10, or 'x' or 'm'.</p>";
        return; // Stop further execution if the score is invalid
    }

    if ($shooter && $distance && $score && $location && $time && $type_bow && $type_arrow && $round_id) {
        // Check if the round is already full (20 shots per round)
        $check_round = $conn->prepare("SELECT COUNT(*) AS shot_count FROM shot WHERE round_id = ?");
        $check_round->bind_param("i", $round_id);
        $check_round->execute();
        $check_result = $check_round->get_result();
        $check_data = $check_result->fetch_assoc();

        if ($check_data['shot_count'] >= 20) {
            echo "<p>Error: The selected round is already full.</p>";
            return;
        }

        // Insert the new shot into the shot table
        $sql = "INSERT INTO shot (distance, score, location, time, type_bow, type_arrow, round_id) 
                VALUES ('$distance', '$score', '$location', '$time', '$type_bow', '$type_arrow', $round_id)";

        if ($conn->query($sql) === TRUE) {
            // Get the auto-generated shot_id
            $shot_id = $conn->insert_id;

            // Insert into main table linking shot_id with the shooter
            $link_sql = "INSERT INTO main (shot_id, cust_id) VALUES ('$shot_id', '$shooter')";
            if ($conn->query($link_sql) === TRUE) {
                echo "<p>Score added and linked successfully!</p>";
            } else {
                echo "<p>Error linking shot to shooter in main table: " . $conn->error . "</p>";
            }
        } else {
            echo "<p>Error inserting shot into shot table: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Please fill in all fields.</p>";
    }
}

// Fetch shooters
$shooters = $conn->query("SELECT cust_id, CONCAT(firstname, ' ', lastname) AS shooter_name FROM customers");
$bows = $conn->query("SELECT type_bow, bow_name FROM bows");
$arrows = $conn->query("SELECT type_arrow, arrow_name FROM arrows");

// Fetch rounds for the selected shooter
$rounds_result = [];
if (isset($_GET['shooter_id'])) {
    $shooter_id = $_GET['shooter_id'];
    $rounds_query = $conn->prepare("SELECT round_id, location FROM round WHERE cust_id = ?");
    $rounds_query->bind_param("i", $shooter_id);
    $rounds_query->execute();
    $rounds_result = $rounds_query->get_result();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Score</title>
    <link rel="stylesheet" href="styles/style.css">
    <script src="scripts/script.js" defer></script>
</head>
<body>
    <nav>
        <a href="home.php">Home</a>
        <a href="add_score.php">Add Score</a>
        <a href="rounds.php">Rounds</a>
    </nav>
    <h2>Add Shot Score</h2>

    <!-- Shooter Dropdown (updated) -->
    <form method="GET" action="">
        <label for="shooter_id">Choose a Shooter:</label>
        <select name="shooter_id" id="shooter_id" required onchange="this.form.submit()">
            <option value="">-- Select Shooter --</option>
            <?php while ($row = $shooters->fetch_assoc()) { ?>
                <option value="<?= $row['cust_id'] ?>" <?= (isset($_GET['shooter_id']) && $_GET['shooter_id'] == $row['cust_id']) ? 'selected' : '' ?>>
                    <?= $row['shooter_name'] ?>
                </option>
            <?php } ?>
        </select>
    </form>

    <?php if (isset($_GET['shooter_id'])): ?>
        <!-- Round Dropdown for the selected shooter -->
        <form method="POST">
            <input type="hidden" name="shooter" value="<?= $_GET['shooter_id'] ?>">
            
            <label for="round_id">Choose a Round:</label>
            <select name="round" id="round_id" required>
                <option value="no_round">-- No Round --</option>
                <?php while ($row = $rounds_result->fetch_assoc()) { ?>
                    <option value="<?= $row['round_id'] ?>"><?= $row['location'] ?> (Round <?= $row['round_id'] ?>)</option>
                <?php } ?>
            </select>

            <!-- Distance Input -->
            <label>Distance:</label>
            <input type="number" name="distance" required>

            <!-- Score Input -->
            <label>Score:</label>
            <input type="text" name="score" required placeholder="Enter a score (0-10), 'x' or 'm'">

            <!-- Location Input -->
            <label>Location:</label>
            <input type="text" name="location" required>

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

            <input type="submit" value="Add Score">
        </form>
    <?php endif; ?>

</body>
</html>

<?php $conn->close(); ?>
