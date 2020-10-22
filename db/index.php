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

  <script type="text/javascript">
        function addID(id){
            console.log(id);
            
            var input = document.createElement('input');
            input.type = 'number';
            input.id = 'hiddeninput';
            input.name = 'parent';
            input.value = id;
            var container = document.getElementById('form');
            container.appendChild(input);
        }
        function answerOfAnswer(id){
            var input = document.createElement('input');
            input.type = 'number';
            input.id = 'hiddeninput';
            input.name = 'answerofanswer';
            input.value = id;
            var container = document.getElementById('form');
            container.appendChild(input);
        }
  </script>
    <?php
        error_reporting(E_ALL & ~E_NOTICE);
        require_once('classes.php');
        $firstname = '';
        $surname = '';
        $email = '';
        $text = '';
        $servername = 'localhost';
        $username = 'root';
        $password = '';
        $dbname = 'myDB';        

        $database = new \Database($servername, $username, $password, $dbname);

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if(!empty($_POST['firstname']) &&
            !empty($_POST['surname']) &&
            !empty($_POST['email']) &&
            !empty($_POST['text']) &&
            filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
                $entries = new Entry();
                $entries->set_firstname(trim($_POST['firstname']));
                $entries->set_surname(trim($_POST['surname']));
                $entries->set_email(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
                $entries->set_text(trim($_POST['text']));
                if($_POST['parent']){
                    $entries->set_parent($_POST['parent']);
                    $entries->set_answerofanswer(0);
                    $database->insertData($entries->get_firstname(), $entries->get_surname(),
                    $entries->get_email(), $entries->get_text(), $entries->get_parent(), $entries->get_answerofanswer());  
                } else if($_POST['answerofanswer']){
                    $database->hasAnswer($_POST['answerofanswer']);
                    echo $_POST['answerofanswer'];
                    $entries->set_answerofanswer($_POST['answerofanswer']);
                    $entries->set_parent(0);
                    $database->insertData($entries->get_firstname(), $entries->get_surname(),
                    $entries->get_email(), $entries->get_text(), $entries->get_parent(), $entries->get_answerofanswer()); 
                } else {
                    $entries->set_parent(0);
                    $entries->set_answerofanswer(0);
                    $database->insertData($entries->get_firstname(), $entries->get_surname(),
                    $entries->get_email(), $entries->get_text(), $entries->get_parent(), $entries->get_answerofanswer());  
                }
            }  
        }
    ?>

    <h1>Formular</h1>

    <form id="form" method="post" action="">
        <p>Vorname: </p><input type="text" name="firstname" value="">
        <p>Nachname: </p><input type="text" name="surname" value="">
        <p>E-Mail: </p><input type="text" name="email" value="">
        <p>Text: </p><textarea type="text" name="text"></textarea>
        <input type="submit" value="Senden">
    </form>

    <table style="width:100%; margin-top: 10px;">
        <tr>
            <th>ID</th>
            <th>Text</th>
            <th>E-Mail</th>
            <th>Nachname</th>
            <th>Vorname</th>
            <th></th>
        </tr>
        <?php
            $output = new View($database, $servername, $username, $password, $dbname);
            if($_POST['page']){
                $output->getPage();
            }
            $currentPage = $output->getPage();
            $result = $database->getData($currentPage);
            $output->printData($result, true);
        ?>
    </table>
        <form method="post">
            <?php
                $output->displayPages();
                $database->closeConnection();
            ?>
        </form>
  </body>
</html>