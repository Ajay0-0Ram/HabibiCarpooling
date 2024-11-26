<?php
    session_start();
    //check if the user is logged in
    if (!isset($_SESSION['username'])) {

        //if not loged in, redirect to the homepage
        header("Location: ../../index.html"); 
        exit; 
    }

    //get the logged-in username
    $username = $_SESSION['username'];

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

    //initialize $ridesTaken and $ridesPosted as empty arrays
    $ridesTaken = [];
    $ridesPosted = [];

    //get rides posted by the user
    $sqlPosted = "SELECT * FROM rides WHERE driver = ?";
    $stmtPosted = $conn->prepare($sqlPosted);
    $stmtPosted->bind_param("s", $username);
    $stmtPosted->execute();
    $resultPosted = $stmtPosted->get_result();
    $ridesPosted = $resultPosted->fetch_all(MYSQLI_ASSOC);

    //get rides taken by the user using JSON_CONTAINS
    $sqlTaken = "SELECT * FROM rides WHERE JSON_CONTAINS(passengersList, ?)";
    $stmtTaken = $conn->prepare($sqlTaken);
    $usernameJson = json_encode([$username]); //ensure the username is JSON-encoded
    $stmtTaken->bind_param("s", $usernameJson);
    $stmtTaken->execute();
    $resultTaken = $stmtTaken->get_result();
    $ridesTaken = $resultTaken->fetch_all(MYSQLI_ASSOC);

    //close statements
    $stmtPosted->close();
    $stmtTaken->close();

    //close the db connection
    $conn->close();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Profile</title>
        <link rel="stylesheet" href="../css/habibiStylesV4.css">
        <link href="https://fonts.googleapis.com/css2?family=Sour+Gummy:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
        
    </head>
    <body>

        <div id="profile">
            <!-- welcome message -->
            <h1>Welcome, <span style="color:#1fb4c1"> <?php echo htmlspecialchars($username); ?> ! </span></h1>
        
            <!-- container for the Divs -->
            <div class="profile-children-container">
                <!-- search bar -->
                <div class="profilechild" id="search">
                    <h2>Search for a Ride</h2>
                    <form action="searchRide.php" method="GET">
                        <input type="text" id="searchText" name="search" placeholder="Search by: origin, destination, etc." required>
                        <input id="submitButton" type="submit" value="Search">
                    </form>
                </div>
        
                <!-- post a new ride -->
                <div class="profilechild" id="post">
                    <h2>Post a Ride</h2>
                    <form action="postRide.php" method="GET">
                        <input id="submitButton" type="submit" value="Post a New Ride">
                    </form>
                </div>
        
                <!-- display rides posted by the user -->
                <div class="profilechild" id="display">
                    <h2>Rides Posted</h2>
                    <?php if (count($ridesPosted) > 0): ?>
                        <ul>
                            <?php foreach ($ridesPosted as $ride): ?>
                                <li>
                                    <strong><a href="rideInfo.php?rideID=<?php echo $ride['rideID']; ?>">Ride ID: <?php echo $ride['rideID']; ?></a></strong><br>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>You have not posted any rides yet.</p>
                    <?php endif; ?>
                </div>
        
                <!-- display rides taken by the user -->
                <div class="profilechild" id="taken">
                    <h2>Rides Taken</h2>
                    <?php if (count($ridesTaken) > 0): ?>
                        <ul>
                            <?php foreach ($ridesTaken as $ride): ?>
                                <li>
                                    <strong><a href="rideInfo.php?rideID=<?php echo $ride['rideID']; ?>">Ride ID: <?php echo $ride['rideID']; ?></a></strong><br>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>You have not taken any rides yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- button to redirect to homepage -->
        <button id="logoutButton" onclick="window.location.href='../../index.html'">Logout</button>
            
        </div>
        


    </body>
</html>