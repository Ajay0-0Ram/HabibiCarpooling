
<?php
session_start(); 

//check if the user is logged in (based on session variable)
if (!isset($_SESSION['username'])) {
    //if not loged in, redirect to the homepage
    header("Location: ../../index.html");
    exit; //stop further code execution to ensure the redirect works
}

//check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //collect the data from the form
    $departure = htmlspecialchars($_POST['departure']);
    $destination = htmlspecialchars($_POST['destination']);
    $date = htmlspecialchars($_POST['date']);
    $passengersInt = htmlspecialchars($_POST['seats']);
    $passengersListJson = json_encode([]); //initialize an empty list for passengers

    //get the username from the session (assuming the user is logged in)
    $username = $_SESSION['username']; //this holds the logged-in user's username

    //db connection details
    $host = 'sql207.infinityfree.com';
    $dbname = 'if0_37721054_profiles'; 
    $myUsername = 'if0_37721054'; 
    $myPassword = 'XBy6Pc3xIhSzC'; 

    //create a MySQLi connection
    $conn = new mysqli($host, $myUsername, $myPassword, $dbname);

    //check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    //prepare the SQL query to insert the ride details into the database
    $sql = "INSERT INTO rides (driver, origin, destination, rideDate, passengersInt, passengersList) 
            VALUES (?, ?, ?, ?, ?, ?)";

    //prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        //bind the params to the query
        $stmt->bind_param("ssssis", $username, $departure, $destination, $date, $passengersInt, $passengersListJson);

        //execute the query
        if ($stmt->execute()) {
            echo "Ride posted successfully!"; // Provide feedback
            //redirect to the profile page
            header('Location: profile.php');
            exit;
        } else {
            echo "Something went wrong. Please try again."; //handle failure :(
        }

        //close the prepared statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error; //handle SQL errors
    }

    //close the db connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
    <title>Post a ride</title>

    <head>
    <link rel="stylesheet" href="../css/habibiStylesV4.css"> 
        <link href="https://fonts.googleapis.com/css2?family=Sour+Gummy:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    </head>

    <body>
        <!-- form to post a ride -->
        <div id="postRideForm">
            <form action="postRide.php" method="POST">
                <fieldset>
                    <legend>Enter Ride Details</legend>

                    Origin<br>
                    <input type="text" name="departure" required>
                    </br>

                    Destination<br>
                    <input type="text" name="destination" required>
                    </br>

                    Departure Time<br>
                    <input type="datetime-local" name="date" required>
                    </br>

                    Seats Available<br>
                    <input type="number" name="seats" required>
                    </br>

                    <input type="submit">

                </fieldset>
            </form>
        </div>
        <!-- button to go back to profile -->
        <button class="back-button" onclick="window.location.href='profile.php'">Back</button>

    </body>
</html>
