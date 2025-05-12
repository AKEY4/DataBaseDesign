        <!-- $conn = new mysqli("feenix-mariadb.swin.edu.au", "s105411788", "Group3", "s105411788_db"); -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shot Data Display</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        select, table {
            margin: 20px auto;
            display: block;
            width: 80%;
        }
        table {
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
    <script>
        function fetchShots() {
            const shooter = document.getElementById("shooter").value;
            window.location.href = `?shooter=${shooter}`;
        }
    </script>
</head>
<body>
    <h2 style="text-align: center;">Shot Data by Shooter</h2>
    <select id="shooter" onchange="fetchShots()">
        <option value="">Select Shooter (All Shots)</option>
        <?php
        $conn = new mysqli("feenix-mariadb.swin.edu.au", "s105411788", "Group3", "s105411788_db");
        $shooters = $conn->query("SELECT DISTINCT c.cust_id, c.firstname, c.lastname FROM customers c JOIN main m ON c.cust_id = m.cust_id");
        while($shooter = $shooters->fetch_assoc()) {
            $shooter_name = $shooter['firstname'] . ' ' . $shooter['lastname'];
            $selected = isset($_GET['shooter']) && $_GET['shooter'] == $shooter['cust_id'] ? 'selected' : '';
            echo "<option value='" . $shooter['cust_id'] . "' $selected>" . $shooter_name . "</option>";
        }
        ?>
    </select>
    <table>
        <tr>
            <th>Shot ID</th>
            <th>Distance</th>
            <th>Score</th>
            <th>Location</th>
            <th>Time</th>
            <th>Type Bow</th>
            <th>Type Arrow</th>
        </tr>
        <?php
        $sql = "SELECT s.shot_id, s.distance, s.score, s.location, s.time, s.type_bow, s.type_arrow FROM shot s ";
        if (isset($_GET['shooter']) && $_GET['shooter'] != '') {
            $shooter = $_GET['shooter'];
            $sql .= "JOIN main m ON s.shot_id = m.shot_id WHERE m.cust_id='$shooter'";
        }
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["shot_id"] . "</td><td>" . $row["distance"] . "</td><td>" . $row["score"] . "</td><td>" . $row["location"] . "</td><td>" . $row["time"] . "</td><td>" . $row["type_bow"] . "</td><td>" . $row["type_arrow"] . "</td></tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No shots found</td></tr>";
        }
        $conn->close();
        ?>
    </table>
</body>
</html>
