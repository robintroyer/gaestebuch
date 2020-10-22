<?php
    require_once('classes.php');
    class Database {

        public $conn;
        public $replacement;

        public function __construct($servername, $username, $password, $dbname){
            $this->conn = new mysqli($servername, $username, $password, $dbname);
            if($this->conn->connect_error){
                die("Connection failed: " . $this->conn->connect_error);
            }
            return $this->conn;
        }

        public function linkDB(){
            $link = mysqli_connect('localhost','root','','test');
            if($link === false){
                die('ERROR: Could not connect. ' . mysqli_connect_error());
            }
        }

        public function createTable(){
            $sql = "CREATE TABLE Gaeste(
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            firstname VARCHAR(30) NOT NULL,
            surname VARCHAR(30) NOT NULL,
            email VARCHAR(50) NOT NULL,
            `text` VARCHAR(50) NOT NULL
            )";

            if($this->conn->query($sql) === true){
                echo 'Table created successfully';
            } else {
                'Error creating table: ' . $this->conn->error;
            }
        }

        public function insertData($firstname, $surname, $email, $text, $parent, $answerOf){
            echo 'a';
            $firstname = $firstname;
            $surname = $surname;
            $email = $email;
            $text = $text;
            $answerOf = $answerOf;
            $parent = $parent;

            if($answerOf > 0 || $parent > 0){
                $sql = "INSERT INTO antworten(firstname, surname, email, answer_text, entry_id, answer_answer_id)
                VALUES('$firstname','$surname','$email','$text', '$parent','$answerOf')";
            } else {
                $sql = "INSERT INTO Gaeste(firstname, surname, email, `text`, parent)
                VALUES('$firstname','$surname','$email','$text','$parent')";
            }

            if($sql){
                if($this->conn->query($sql) === TRUE){
                    echo 'New record added successfully';
                } else {
                    echo 'Error: ' . $sql . '<br />' . $this->conn->error;
                }
            }
        }

        public function getData($currentPage){

            $sql = "SELECT id, firstname, surname, email, `text`, parent FROM Gaeste WHERE id > ($currentPage - 1) * 3 AND id <= $currentPage * 3";
            $result = $this->conn->query($sql);
            $outputs = [];
            $entries = [];
            $answers = [];

            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){  
                    $id = $row['id'];
                    $firstname = $row['firstname'];
                    $surname = $row['surname'];
                    $email = $row['email'];
                    $text = $row['text'];
                    $parent = $row['parent'];
                    $this->replacement = new Emoji();

                    $replacedtext = $this->replacement->changeEmoji($text);

                    $entry = new Entry();
                    $entries[] = $entry->setEntries($id, $firstname, $surname ,$email, $replacedtext, $parent, $answers);
                   
                    $outputs = $entries;
                }
            }
            
            $outputs = $this->getAnswer($entries);
            return $outputs;
        }

        public function getAnswer($entries){
            if($entries){
                foreach($entries as &$entry){
                    $entry_id = $entry[0];
                    $sql = "SELECT answer_id, firstname, surname, email, answer_text, entry_id, answer_answer_id, has_answer
                    FROM antworten
                    WHERE entry_id = $entry_id";
                    $answerresult = $this->conn->query($sql);

                    if($answerresult){
                        while($answerrow = $answerresult->fetch_assoc()){
                            $a_id = $answerrow['answer_id'];
                            $a_firstname = $answerrow['firstname'];
                            $a_surname = $answerrow['surname'];
                            $a_email = $answerrow['email'];
                            $a_text = $answerrow['answer_text'];
                            $a_parent = $answerrow['entry_id'];
                            $answer_answer_id = $answerrow['answer_answer_id'];
                            $has_answer = $answerrow['has_answer'];
                            $replacedanswertext = $this->replacement->changeEmoji($a_text);

                            if($has_answer == 1){
                                $answer = $this->getAnswersOfAnswer($entry, $a_id, $a_firstname, $a_surname, $a_email, $replacedanswertext, $answer_answer_id);
                            } else {
                                $answer = 0;
                            }

                            if($answer == 0){
                                $answers = [$a_id, $a_firstname, $a_surname, $a_email, $replacedanswertext, $answer_answer_id, $answer];
                            } else {
                                $answers = [$a_id, $a_firstname, $a_surname, $a_email, $replacedanswertext, $answer_answer_id, $answer];
                            }

                            $entry[6][] = $answers;

                            echo '<br />';
                        }
                    }
                }
            }
            return $entries;
        }

        public function getAnswersOfAnswer($entry, $a_id, $a_firstname, $a_surname, $a_email, $replacedanswertext, $answer_answer_id){
            
            $entry[6][] = [$a_id, $a_firstname, $a_surname, $a_email, $replacedanswertext, $answer_answer_id];

            highlight_string("<?php\n\$data =\n" . var_export($entry, true) . ";\n?>");

            $answer_id = $entry[6][count($entry[6]) - 1][0];
            echo $answer_id;
            
            $sql = "SELECT answer_id, firstname, surname, email, answer_text, entry_id, answer_answer_id, has_answer
                    FROM antworten
                    WHERE answer_answer_id = $answer_id";
            $answerresult = $this->conn->query($sql);
            print_r($answerresult);
            while($answerrow = $answerresult->fetch_assoc()){
                $a_id = $answerrow['answer_id'];
                $a_firstname = $answerrow['firstname'];
                $a_surname = $answerrow['surname'];
                $a_email = $answerrow['email'];
                $a_text = $answerrow['answer_text'];
                $answer_answer_id = $answerrow['answer_answer_id'];
                $has_answer = $answerrow['has_answer'];
                $replacedanswertext = $this->replacement->changeEmoji($a_text);

                if($has_answer){
                    $answer = $this->getAnswersOfAnswer($entry, $a_id, $a_firstname, $a_surname, $a_email, $replacedanswertext, $answer_answer_id);
                } else {
                    $answer = 0;
                }
                $answers[] = [$a_id, $a_firstname, $a_surname, $a_email, $replacedanswertext, $answer_answer_id, $answer];
            }
            
            return $answers;
        }

        public function showPages(){
            $sql = "SELECT firstname, surname, email, `text` FROM Gaeste";
            $result = $this->conn->query($sql);
            return $result;
        }

        public function hasAnswer($id){
            $sql = "UPDATE antworten
            SET has_answer = 1
            WHERE answer_id = $id";

            if($this->conn->query($sql)){
                echo 'Record updated successfully';
            } else {
                echo 'Error updating record: ' . $this->conn->error;
            }
        }

        public function closeConnection(){
            $this->conn->close();
        }
    }