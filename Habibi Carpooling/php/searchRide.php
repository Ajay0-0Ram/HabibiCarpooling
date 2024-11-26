<?php
    session_start(); //ensure session is started to get the logged-in user

    //check if the user is logged in (based on session variable)
    if (!isset($_SESSION['username'])) {
        //if not logged in, redirect to the profile
        header("Location: ../../index.html");
        exit; 
    }

    $userName = $_SESSION['username']; //get the logged-in username

    //db connection details
    $host = 'sql207.infinityfree.com'; 
    $dbname = 'if0_37721054_profiles'; 
    $myUsername = 'if0_37721054'; 
    $myPassword = 'XBy6Pc3xIhSzC'; 

    //create a MySQLi connection
    $mysqli = new mysqli($host, $myUsername, $myPassword, $dbname);

    //check the connection
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    //check if the form is submitted to join a ride
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ride_id'])) {
        $rideID = $_POST['ride_id']; //ride ID of the ride the user wants to join

        //get the current passenger list and passengers count for this ride
        $sql = "SELECT passengersList, passengersInt FROM rides WHERE rideID = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param('i', $rideID); 
            $stmt->execute();
            $stmt->bind_result($passengersListJson, $passengersInt);
            $stmt->fetch();
            $stmt->close();

            if ($passengersListJson !== null) {
                $passengersList = json_decode($passengersListJson, true); //decode the JSON list

                //check if the user is already in the passenger list
                if (in_array($userName, $passengersList)) {
                    echo "You have already joined this ride.";
                } elseif (count($passengersList) >= $passengersInt) {
                    echo "Sorry, this ride has reached its maximum number of passengers.";
                } else {
                    //add the current user to the passenger list
                    $passengersList[] = $userName;

                    //encode the updated passenger list back to JSON
                    $updatedPassengersListJson = json_encode($passengersList);

                    //update the ride with the new passenger list
                    $sql = "UPDATE rides SET passengersList = ? WHERE rideID = ?";
                    if ($stmt = $mysqli->prepare($sql)) {
                        $stmt->bind_param('si', $updatedPassengersListJson, $rideID);
                        if ($stmt->execute()) {
                            echo "You have successfully joined the ride!";
                        } else {
                            echo "Something went wrong. Please try again.";
                        }
                        $stmt->close();
                    }
                }
            } else {
                echo "Ride not found.";
            }
        } else {
            echo "Error preparing query.";
        }
    }

    //get the search term from the form, if it exists
    $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

    //base SQL query
    $sql = "SELECT rideID, origin, destination, rideDate, passengersList, passengersInt, driver FROM rides WHERE (origin LIKE ? OR destination LIKE ?) AND (passengersList IS NULL OR JSON_LENGTH(passengersList) < passengersInt)";

    //prepare the SQL statement
    $stmt = $mysqli->prepare($sql);

    //bind params 
    $searchTermWithWildcards = '%' . $searchTerm . '%';
    $stmt->bind_param('ss', $searchTermWithWildcards, $searchTermWithWildcards);

    //execute the query
    $stmt->execute();
    $result = $stmt->get_result();

    //filter out the rides where the logged-in user is the driver
    $rides = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['driver'] !== $userName) {
                $rides[] = $row;
            }
        }
    }

    $mysqli->close(); //close the connection to the db
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search for Rides</title>
    <link rel="stylesheet" href="../css/habibiStylesV4.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Sour+Gummy:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="rides-container">
        <!-- show available ride(s) -->
        <h1>Available Rides</h1>

        <form action="searchRide.php" method="GET">
            <input type="text" id="searchText" name="search" placeholder="Search by: origin, destination, etc." value="<?php echo htmlspecialchars($searchTerm); ?>">
            <input id="submitButton" type="submit" value="Search">
        </form>

        <?php if ($rides): ?>
            <table class="rides-table">
                <tr>
                    <th>From</th>
                    <th>To</th>
                    <th>Departure Time</th>
                    <th>Action</th>
                </tr>
                <!-- show available ride(s) details -->
                <?php foreach ($rides as $availableRide): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($availableRide['origin']); ?></td>
                        <td><?php echo htmlspecialchars($availableRide['destination']); ?></td>
                        <td><?php echo htmlspecialchars($availableRide['rideDate']); ?></td>
                        <td>
                            <?php
                            $passengersList = json_decode($availableRide['passengersList'], true);
                            if (count($passengersList) < $availableRide['passengersInt']):
                            ?>
                                <form action="searchRide.php" method="POST">
                                    <input type="hidden" name="ride_id" value="<?php echo $availableRide['rideID']; ?>">
                                    <input type="submit" class="join-ride-btn" value="Join Ride">
                                </form>
                            <?php else: ?>
                                <span class="ride-full">Ride Full</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <!-- no available ride(s) -->
            <p>No rides available matching your search.</p>
        <?php endif; ?>

        <!-- button to go back to the profile -->
        <button class="back-button" onclick="window.location.href='profile.php'">Back</button>
    </div>
</body>
</html>
