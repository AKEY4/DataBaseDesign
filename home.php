<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shot Data Display</title>
    <link rel="stylesheet" href="styles/style.css">
    <script>
        function fetchShots() {
            const shooter = document.getElementById("shooter").value;
            window.location.href = `?shooter=${shooter}`;
        }

        function sortTable(column) {
            const urlParams = new URLSearchParams(window.location.search);
            let order = urlParams.get('order') === 'ASC' ? 'DESC' : 'ASC';
            urlParams.set('sort', column);
            urlParams.set('order', order);
            window.location.search = urlParams.toString();
        }
    </script>
</head>
<body>
    <h2 style="text-align: center;">Shot Data by Shooter</h2>
    <select id="shooter" onchange="fetchShots()">
        <option value="">Select Shooter (All Shots)</option>
        <?php
        include 'settings.php';
        $conn = mysqli_connect($host, $user, $pwd, $dbnm);
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
            <?php
            $columns = ['shot_id', 'distance', 'score', 'location', 'time', 'type_bow', 'type_arrow'];
            foreach ($columns as $column) {
                echo "<th onclick=\"sortTable('$column')\">" . ucfirst(str_replace('_', ' ', $column)) . "</th>";
            }
            ?>
        </tr>
        <?php
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'shot_id';
        $order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
        $sql = "SELECT s.shot_id, s.distance, s.score, s.location, s.time, s.type_bow, s.type_arrow FROM shot s ";
        if (isset($_GET['shooter']) && $_GET['shooter'] != '') {
            $shooter = $_GET['shooter'];
            $sql .= "JOIN main m ON s.shot_id = m.shot_id WHERE m.cust_id='$shooter' ";
        }
        $sql .= "ORDER BY $sort $order";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                foreach ($columns as $column) {
                    echo "<td>" . $row[$column] . "</td>";
                }
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No shots found</td></tr>";
        }
        $conn->close();
        ?>
    </table>
</body>
</html>