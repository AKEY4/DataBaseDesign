<?php
include 'settings.php';
$conn = mysqli_connect($host, $user, $pwd, $dbnm);

// Fetch shooters
$shooters = $conn->query("SELECT cust_id, CONCAT(firstname, ' ', lastname) AS shooter_name FROM customers");

if (isset($_GET['shooter_id']) && isset($_GET['round_id'])) {
    $shooter_id = $_GET['shooter_id'];
    $round_id = $_GET['round_id'];

    // Fetch shots for the selected round
    $shots_query = $conn->prepare("SELECT s.shot_id, s.distance, s.score, s.location, s.time 
                                   FROM shot s 
                                   WHERE s.round_id = ? AND EXISTS (
                                       SELECT 1 FROM main m WHERE m.cust_id = ? AND m.shot_id = s.shot_id
                                   )");
    $shots_query->bind_param("ii", $round_id, $shooter_id);
    $shots_query->execute();
    $shots_result = $shots_query->get_result();

    // Fetch average score for the selected round
    $avg_query = $conn->prepare("SELECT AVG(s.score) AS average_score
                                 FROM shot s
                                 WHERE s.round_id = ? AND EXISTS (
                                     SELECT 1 FROM main m WHERE m.cust_id = ? AND m.shot_id = s.shot_id
                                 )");
    $avg_query->bind_param("ii", $round_id, $shooter_id);
    $avg_query->execute();
    $avg_result = $avg_query->get_result();
    $avg_row = $avg_result->fetch_assoc();
    $average_score = $avg_row['average_score'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rounds and Shots</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <h2>View Shots from a Round</h2>

    <!-- Shooter Selection -->
    <form method="GET">
        <label for="shooter_id">Choose a Shooter:</label>
        <select name="shooter_id" id="shooter_id" onchange="this.form.submit()" required>
            <option value="">-- Select Shooter --</option>
            <?php while ($row = $shooters->fetch_assoc()) { ?>
                <option value="<?= $row['cust_id'] ?>" <?= (isset($_GET['shooter_id']) && $_GET['shooter_id'] == $row['cust_id']) ? 'selected' : '' ?>>
                    <?= $row['shooter_name'] ?>
                </option>
            <?php } ?>
        </select>
    </form>

    <?php if (isset($_GET['shooter_id'])): ?>
        <!-- Round Selection -->
        <?php
            $shooter_id = $_GET['shooter_id'];
            $rounds = $conn->prepare("SELECT round_id, location FROM round WHERE cust_id = ?");
            $rounds->bind_param("i", $shooter_id);
            $rounds->execute();
            $rounds_result = $rounds->get_result();
        ?>
        <form method="GET">
            <input type="hidden" name="shooter_id" value="<?= $shooter_id ?>">
            <label for="round_id">Choose a Round:</label>
            <select name="round_id" id="round_id" onchange="this.form.submit()" required>
                <option value="">-- Select Round --</option>
                <?php while ($round = $rounds_result->fetch_assoc()) { ?>
                    <option value="<?= $round['round_id'] ?>" <?= (isset($_GET['round_id']) && $_GET['round_id'] == $round['round_id']) ? 'selected' : '' ?>>
                        <?= $round['location'] ?> (Round <?= $round['round_id'] ?>)
                    </option>
                <?php } ?>
            </select>
        </form>
    <?php endif; ?>

    <?php if (isset($shots_result) && $shots_result->num_rows > 0): ?>
        <h3>Shots in Round <?= $_GET['round_id'] ?>:</h3>
        <p><strong>Average Score: </strong><?= number_format($average_score, 2) ?></p> <!-- Display average score -->

        <table border="1">
            <tr>
                <th>Shot ID</th>
                <th>Distance</th>
                <th>Score</th>
                <th>Location</th>
                <th>Time</th>
            </tr>
            <?php while ($shot = $shots_result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $shot['shot_id'] ?></td>
                    <td><?= $shot['distance'] ?></td>
                    <td><?= $shot['score'] ?></td>
                    <td><?= $shot['location'] ?></td>
                    <td><?= $shot['time'] ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php elseif (isset($shots_result)): ?>
        <p>No shots found for this round.</p>
    <?php endif; ?>

</body>
</html>

<?php $conn->close(); ?>
