<!doctype html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="./assets/main.css">
    <title>Gästebuch</title>

    <style type="text/css">
        
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

        
        
        $id_counter = 1;
        $answer_id_counter = 1;
        $result = [];

        

        $configDb = new stdClass();
        $configDb->server = $servername;
        $configDb->username = $username;
        $configDb->password = $password;
        $configDb->database = $dbname;

        $configFs = new stdClass();
        $configFs->file = 'entries.json';

        /**
         * @var StorageInterface $storage
         */

        $storage = new \Database();
        //  $storage = new Filesystem();

        $values = $storage->initialize($configDb);
        // $values = $storage->initialize($configFs);

        $emoji = new Emoji();
        $output = new View($values->amount);

        $currentPage = $output->getPage();

        $entry = $values->cache;
        if(!empty($entry)){
            $id_counter = $entry[count($entry) - 1]->getId() + 1;
            $answer_id_counter = $values->max_answer_id + 1;
            if(!$answer_id_counter){
                $answer_id_counter = 1;
            }
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if(!empty($_POST['firstname']) &&
            !empty($_POST['surname']) &&
            !empty($_POST['email']) &&
            !empty($_POST['text']) &&
            filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
                $entry = new Entry();
                $entry->setFirstname(trim($_POST['firstname']));
                $entry->setSurname(trim($_POST['surname']));
                $entry->setEmail(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
                $entry->setText(trim($_POST['text']));
                $entry->setText($emoji->changeEmoji($entry->getText()));
                if($_POST['parent']){
                    $entry->setParent($_POST['parent']);
                    $entry->setAnswerId($answer_id_counter);
                    $answer_id_counter++;
                    $result = $values->cache;
                    $result[$_POST['parent'] - 1]->setAnswerofanswerArray($entry);
                    $storage->saveEntry($result, true);
                } elseif($_POST['answerofanswer']){
                    $entry->setAnsweranswerId(intval($_POST['answerofanswer']));
                    $entry->setParent(0);
                    $entry->setAnswerId($answer_id_counter);
                    $answer_id_counter++;
                    $data = [];
                    $result = $values->cache;
                    $data = ['result' => $result, 'content' => $entry];
                    $storage->saveEntry($data,true);
                } else {
                    $entry->setParent(0);
                    $entry->setId($id_counter);
                    $id_counter++;
                    $result = $values->cache;
                    $result[] = $entry;
                    $storage->saveEntry($result, false);
                }
            }  
        }
    ?>

    <h1>Formular</h1>

    <form id="form" method="post" action="">
        <label for="firstname">Vorname</label><input id="firstname" class="input" type="text" name="firstname" value="">
        <label for="surname">Nachname</label><input id="surname" class="input" type="text" name="surname" value="">
        <label for="email">E-Mail</label><input id="email" class="input" type="text" name="email" value="">
        <label for="text">Text</label><textarea id="text" class="input_textarea" type="text" name="text"></textarea>
        <input id="input_button" type="submit" value="Senden">
    </form>

    <!-- <table style="width:100%; margin-top: 10px;">
        <tr>
            <th>ID</th>
            <th>Text</th>
            <th>E-Mail</th>
            <th>Nachname</th>
            <th>Vorname</th>
            <th></th>
        </tr>
        <?php

            // if($_POST['page']){
            //     $output->getPage();
            // }

            // $result = $storage->getEntries();


            // $output->printData($result, true);

        ?>
    </table> -->

    <div class="grid">
        <span></span>
        <span>ID</span>
        <span>Text</span>
        <span>E-Mail</span>
        <span>Nachname</span>
        <span>Vorname</span>
        <span></span>
    </div>

        <?php
            if ($_POST['page']) {
                $output->getPage();
            }
            $result = $storage->getEntries();
            $output->printData($result, true)
        ?>
    
        <form method="post">
            <?php
                $currentPage = $output->getPage();
                $output = new View($storage->amount);
                $output->displayPages($currentPage);
            ?>
        </form>
  </body>
</html>