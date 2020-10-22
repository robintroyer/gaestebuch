<!doctype html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Gästebuch</title>

    <style type="text/css">
        table, th, td {
            border: 1px solid black;
            text-align: center;
        }

        td {
            white-space: pre;
        }
    </style>
  </head>
  <body>

    <?php
error_reporting(E_ALL & ~E_NOTICE);
        $firstname = '';
        $surname = '';
        $email = '';
        $text = '';
        $gaeste = array();
        $servername = 'localhost';
        $username = 'root';
        $password = '';
        $dbname = 'myDB';
        $emailpattern = '';
        $bad_email = '';
        // $currentPage = 1;

        if($_POST['prevPage']){
            $prevPage = $_POST['prevPage'];
        }

        if(!empty($_POST['page']) && $_POST['page'] != '->' && $_POST['page'] != '<-'){
            $currentPage = $_POST['page'];
        }
        
        if(empty($_POST['page'])){
            $currentPage = 1;
        } else if($_POST['page'] == '->'){
            echo 'a';
            $currentPage = $prevPage + 1;
        } else if($_POST['page'] == '<-'){
            echo 'b';
            $currentPage = $prevPage - 1;
        }

       
        /* $link = mysqli_connect('localhost', 'root', '', 'test');

        if($link === FALSE){
            die('ERROR: Could not connect. ' . mysqli_connect_error());
        } */
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){

            if(!empty($_POST['firstname']) &&
            !empty($_POST['surname']) &&
            !empty($_POST['email']) &&
            !empty($_POST['text']) &&
            filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
                $firstname = trim($_POST['firstname']);
                $surname = trim($_POST['surname']);
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $text = trim($_POST['text']);

                if(strlen($text) != strlen(utf8_decode($text))){
                    echo 'unicode';
                    echo strlen(utf8_decode($text));
                }

                $sql = "INSERT INTO Gaeste(firstname, surname, email, `text`)
                VALUES ('$firstname', '$surname', '$email', '$text')";
            }  
        }

        if($sql){
            if($conn->query($sql) === TRUE){
                echo 'New record added successfully';
            } else {
                echo 'Error: ' . $sql . "<br>" . $conn->error;
            }
        }

        $conn->close();

        /*$sql = 'CREATE TABLE Gaeste(
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            firstname VARCHAR(30) NOT NULL,
            surname VARCHAR(30) NOT NULL,
            email VARCHAR(50) NOT NULL,
            `text` VARCHAR(50) NOT NULL
            )';

        if($conn->query($sql) === TRUE){
            echo 'Table Gaeste created successfully';
        } else {
            echo 'Error creating table: ' . $conn->error;
        } */

        /* if($conn->query($sql) === TRUE){
            echo 'Database created successfully';
        } else {
            echo 'Error creating Database: ' . $conn->error;
        }

        $conn->close(); */

    ?>

    <h1>Formular</h1>


    <?php
        echo "\u{1F30F}";
    ?>


    <form method="post" action="">
        <p>Vorname: </p><input type="text" name="firstname" value="">
        <p>Nachname: </p><input type="text" name="surname" value="">
        <p>E-Mail: </p><input type="text" name="email" value="">
        <p>Text: </p><textarea type="text" name="text"></textarea>
        <input type="submit" value="Senden">
    </form>

    <table style="width:100%; margin-top: 10px;">
        <tr>
            <th>Vorname</th>
            <th>Nachname</th>
            <th>E-Mail</th>
            <th>Text</th>
        </tr>
        <?php

            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "SELECT firstname, surname, email, `text` FROM Gaeste WHERE id > ($currentPage - 1) * 3 AND id <= $currentPage * 3";
            $result = $conn->query($sql);

            // print_r ($result->fetch_assoc());

            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    
                    echo "<tr><td>".$row['firstname']."</td><td>".$row['surname']."</td><td>".$row['email']."</td><td>".$row['text']."\n
                    <form method='post'><input type='text' name='answer'><input type='submit' value='Antworten'></form></td></tr>";
                }
                    
            } else {
                echo '0 results';
            }

        ?>
    </table>

        <form method="post">
            <?php
                $sql = "SELECT firstname, surname, email, `text` FROM Gaeste";
                $result = $conn->query($sql);

                

                echo 'Seite: ';
                if($currentPage > 1){
                    echo '<input type="submit" name="page" value="<-">';
                    echo '<input type="hidden" name="prevPage" value="'.$currentPage.'"></input>';
                }
                
                for($i = 1; $i <= ceil($result->num_rows / 3); $i++){
                    if($i == $currentPage){
                        echo '<strong> '.$i.' </strong>';
                    } else {
                        echo '<input type="submit" name="page" value="'.$i.'">';
                    }
                    
                }
                if($result->num_rows > 3 && $currentPage <= floor($result->num_rows / 3)){
                    echo '<input type="submit" name="page" value="->">';
                    echo '<input type="hidden" name="prevPage" value="'.$currentPage.'">';
                   // echo '<input type="submit" value="->" name="next">';
                }
                
                $conn->close();
            ?>
        </form>
  </body>
</html>