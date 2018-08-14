<?php
$servername = "localhost";
$username = "emperiu1_tb";
$password = "tb2016";

// Create connection
$conn = mysqli_connect($servername, $username, $password);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully";

function insertDB($conn)
{
  $sql = "INSERT INTO user (id_user, username, first_name, last_name)
  VALUES ('John', 'Doe', 'john@example.com')";

  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
}

function selectDB($conn, $chatid)
{
    $sql = "SELECT id_user, FROM user";
    $result = $conn->query($sql);

    return $result;
}
?>
