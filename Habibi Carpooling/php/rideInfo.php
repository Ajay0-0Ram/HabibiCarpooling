<?php
    session_start();

    //check if the user is logged in
    if (!isset($_SESSION['username'])) {
        //if not loged in, redirect to the homepage
        header("Location: ../../index.html");
        exit;
    }

    //get the username
    $username = $_SESSION['username'];

    //get the ride ID from the URL query string
    if (!isset($_GET['rideID'])) {
        echo "Ride ID is missing.";
        exit;
    }
    $rideID = $_GET['rideID'];

    //db connection details
    $host = 'sql207.infinityfree.com';
    $dbname = 'if0_37721054_profiles'; 
    $myUsername = 'if0_37721054'; 
    $myPassword = 'XBy6Pc3xIhSzC'; 

    //create a MySQLi connection
    $conn = new mysqli($host, $myUsername, $myPassword, $dbname);

    //check for connection errors
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    //get the ride details using the rideID
    $sql = "SELECT * FROM rides WHERE rideID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $rideID); //bind the rideID as an int
    $stmt->execute();
    $result = $stmt->get_result();

    //if no ride is found, show an error
    if ($result->num_rows == 0) {
        echo "Ride not found.";
        exit;
    }

    //fetch the ride details
    $ride = $result->fetch_assoc();

    //check if the logged-in user is the driver or a passenger
    $isDriver = ($ride['driver'] == $username);
    $passengers = json_decode($ride['passengersList'], true);
    $isPassenger = ($ride['driver'] != $username);

    //fetch contact info for the driver and passengers
    $driverContact = null;
    $passengerContacts = [];

    if ($isDriver) {
        //if driver: show the passengers' contact info
        foreach ($passengers as $passenger) {
            $sqlContact = "SELECT email, telephone FROM users WHERE username = ?";
            $stmtContact = $conn->prepare($sqlContact);
            $stmtContact->bind_param("s", $passenger);
            $stmtContact->execute();
            $resultContact = $stmtContact->get_result();
            if ($resultContact->num_rows > 0) {
                $passengerInfo = $resultContact->fetch_assoc();
                $passengerContacts[] = [
                    'email' => $passengerInfo['email'],
                    'telephone' => $passengerInfo['telephone']
                ];
            }
            $stmtContact->close();
        }
    } else {
        //if passenger: show the driver's contact info
        $sqlContact = "SELECT email, telephone FROM users WHERE username = ?";
        $stmtContact = $conn->prepare($sqlContact);
        $stmtContact->bind_param("s", $ride['driver']);
        $stmtContact->execute();
        $resultContact = $stmtContact->get_result();
        if ($resultContact->num_rows > 0) {
            $driverContact = $resultContact->fetch_assoc();
        }
    }

    $stmt->close();

    //handle ride deletion or passenger removal if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['deleteRide']) && $isDriver) {
            //delete the ride from the db
            $sqlDelete = "DELETE FROM rides WHERE rideID = ?";
            $stmtDelete = $conn->prepare($sqlDelete);
            $stmtDelete->bind_param("i", $rideID);
            $stmtDelete->execute();
            $stmtDelete->close();
            $conn->close();

            header("Location: profile.php"); //redirect to profile
            exit;
        }

        if (isset($_POST['removePassenger']) && $isPassenger) {
            //remove the user from the passengers list
            $passengers = array_diff($passengers, [$username]);  //remove the user from the list
            $newPassengersList = json_encode(array_values($passengers));  //update the indexes in the array

            //calculate the new number of passengers
            $newPassengersCount = count($passengers);

            //update the passengers list and number of passengers in the database
            $sqlUpdate = "UPDATE rides SET passengersList = ?, passengersInt = ? WHERE rideID = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("sii", $newPassengersList, $newPassengersCount, $rideID); // Bind 3 parameters
            $stmtUpdate->execute();
            $stmtUpdate->close();

            //redirect back to their profile
            $conn->close();
            header("Location: profile.php");
            exit;
            }
        }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ride Info</title>
    <link rel="stylesheet" href="../css/rideInfoStylesV4.css">
    <link href="https://fonts.googleapis.com/css2?family=Sour+Gummy:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body class="blurredBackground">

<div id="rideInfo">
    <h1>Ride Information</h1>

    <div class="ride-info-container">

        <div class="ride-info1">
        <!-- ride info -->
            
            <h3>Ride ID: <?php echo htmlspecialchars($ride['rideID']); ?></h3>
            
            <p><strong>Driver:</strong> <?php echo htmlspecialchars($ride['driver']); ?></p>
        
            <p><strong>Origin:</strong> <?php echo htmlspecialchars($ride['origin']); ?></p>
        
            <p><strong>Destination:</strong> <?php echo htmlspecialchars($ride['destination']); ?></p>
        
            <p><strong>Date:</strong> <?php 
                $rideDate = new DateTime($ride['rideDate']);
                echo $rideDate->format('l, F j, Y g:i A');
            ?></p>
        
            <p><strong>Seats:</strong> <?php echo htmlspecialchars($ride['passengersInt'] + 1); ?></p>

        

        
            <p><strong>Passengers:</strong> <?php 
                echo count($passengers) > 0 ? implode(', ', $passengers) : "No passengers yet.";
            ?></p>
        </div>
        
        <div class="ride-info2">
            <!-- contact info -->
            <?php if ($isDriver): ?>
                <h3>Passenger Contact Info:</h3>
                <ul>
                    <?php foreach ($passengerContacts as $contact): ?>
                        <li>
                            <strong>Email:</strong> <?php echo htmlspecialchars($contact['email']); ?><br>
                            <strong>Telephone:</strong> <?php echo htmlspecialchars($contact['telephone']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php elseif ($isPassenger): ?>
                <h3>Driver's Contact Info:</h3>
                <p>
                    <strong>Email:</strong> <?php echo htmlspecialchars($driverContact['email']); ?><br>
                    <strong>Telephone:</strong> <?php echo htmlspecialchars($driverContact['telephone']); ?>
                </p>
            <?php endif; ?>
        </div>
    </div> 
    <br>
    <br>
        <!-- button for the driver to delete the ride -->
        <?php if ($isDriver): ?>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this ride?');">
                <button class="delete-button" type="submit" name="deleteRide">Delete Ride</button>
            </form>
        <?php endif; ?>

        <!-- button for the passengers to remove themselves -->
        <?php if ($isPassenger): ?>
            <form method="POST" onsubmit="return confirm('Are you sure you want to remove yourself from this ride?');">
                <button class="delete-button" type="submit" name="removePassenger">Remove Me</button>
            </form>
        <?php endif; ?>
    
</div>
<br>
<br>



<button class="back-button" onclick="window.location.href='profile.php'">Back to Profile</button>
 

</body>
</html>