<?php
    require_once('classes.php');
     class Database implements StorageInterface {
        protected $cache = null;
        protected $conn;
        public $currentPage;
        public $amount;

        public function __construct(){
            $this->amount = 0;
            // echo $this->amount;
        }

        public function initialize($config){
            $this->conn = new mysqli($config->server, $config->username, $config->password, $config->database);
            if($this->conn->connect_error){
                die("Connection failed: " . $this->conn->connect_error);
            }

            if($this->cache === null){
                $this->cache = $this->getEntries();
            }
            $values = new stdClass();
            $values->cache = $this->cache;

            $sql = "SELECT MAX(answer_id)
                    FROM antworten";
            $result = $this->conn->query($sql);
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    $max_id = $row['MAX(answer_id)'];
                }
            }
            $result->free();
            $values->max_answer_id = $max_id;
            if($this->cache){
                $values->amount = $this->cache[count($this->cache) - 1]->getId();
                $this->amount = $this->cache[count($this->cache) - 1]->getId();
            } else {
                $values->amount = 0;
            }
            return $values;
        }

        public function getEntries(){
            $sql = "SELECT id, firstname, surname, email, `text`, parent
                    FROM Gaeste";
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

                    $entry = new Entry();
                    $entry->setId($id);
                    $entry->setFirstname($firstname);
                    $entry->setSurname($surname);
                    $entry->setEmail($email);
                    $entry->setText($text);
                    $entry->setParent($parent);
                    $entry->setAnswerofanswer($answers);

                    $entries[] = $entry;
                    
                    $outputs = $entries;
                }
            }

            $result->free();

            $outputs = $this->getAnswer($entries);


            return $outputs;
        }

        public function saveEntry($entry, $is_answer){
            $ids = [];
            if(array_key_exists('result', $entry)){
                if($is_answer){
                    $last_entry = $entry['content'];
                    highlight_string("<?php\n\$data =\n" . var_export($last_entry, true) . ";\n?>");
                }
            } else {
                if($is_answer){
                    foreach($entry as $ent){
                        $this->maxID($ent->getAnswerofanswer(), $ids);
                    }
                    $max_id = max($ids);
                    unset($last_entry);
                    foreach((array)$entry as $ent){
                        $this->lastEntry($ent->getAnswerofanswer(), $max_id, $last_entry);
                    }
                } else {
                    foreach($entry as $ent){
                        $ids[] = $ent->getId();
                    }
                    $max_id = max($ids);
                    unset($last_entry);
                    foreach((array)$entry as $ent){
                        if($max_id == $ent->getId()){
                            $last_entry = $ent;
                        }
                    }
                }
            }

            $firstname = $last_entry->getFirstname();
            $surname = $last_entry->getSurname();
            $email = $last_entry->getEmail();
            $text = $last_entry->getText();
            if(property_exists($last_entry, 'answer_answer_id')){
                $answer_answer_id = $last_entry->getAnsweranswerId();
            }
            if(property_exists($last_entry, 'parent')){
                $parent = $last_entry->getParent();
            } else {
                $parent = 0;
            }
            if($answer_answer_id > 0 || $parent > 0){
                $sql = "INSERT INTO antworten(firstname, surname, email, answer_text, entry_id, answer_answer_id)
                        VALUES('$firstname', '$surname', '$email', '$text', '$parent', '$answer_answer_id')";
            } else {
                $sql = "INSERT INTO Gaeste(firstname, surname, email, `text`, parent)
                        VALUES('$firstname','$surname','$email','$text','$parent')";
            }
            if($sql){
                if($this->conn->query($sql) === true){
                    // echo 'New record added successfully';
                    echo '<div id="toast" class="show">Eintrag erfolgreich erstellt!</div>';
                    echo '<script type="text/javascript">hideToast();</script>';
                } else {
                    echo 'Error: ' . $sql . '<br />' . $this->conn->error;
                }
            }
            if(!empty($answer_answer_id)){
                $sql = "UPDATE antworten
                        SET has_answer = 1
                        WHERE answer_id = $answer_answer_id";
    
                if($this->conn->query($sql)){
                    echo 'Record updated successfully';
                } else {
                    echo 'Error updating record: ' . $this->conn->error;
                }
            }


            // if($this->cache){
            //     $this->amount = $this->cache[count($this->cache) - 1]->getId();
            // } else {
            //     $this->amount = 0;
            // }

            $sql = "SELECT MAX(id)
                    FROM Gaeste";
            $result = $this->conn->query($sql);
            if($result){
                while($row = $result->fetch_assoc()){
                    $id = $row['MAX(id)'];
                }
            }

            $this->amount = $id;

            // echo $id;

        }

        private function lastEntry($ent, $id, &$last_entry){
            foreach($ent as $e){
                if($id == $e->getAnswerId()){
                    $last_entry = $e;
                }
                if(!empty($e->getAnswerofanswer())){
                    $this->lastEntry($e->getAnswerofanswer(), $id, $last_entry);
                }
            }
        }

        private function maxID($ent, &$ids){
            foreach($ent as $e){
                $ids[] = $e->getAnswerId();
                if(!empty($e->getAnswerofanswer())){
                    $this->maxID($e->getAnswerofanswer(), $ids);
                }
            }
            return $ids;
        }

        private function getAnswer($entries){
            if($entries){
                foreach($entries as &$entry){
                    $entry_id = $entry->getId();
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
                            $answer_answer_id = $answerrow['answer_answer_id'];
                            $has_answer = $answerrow['has_answer'];
                            if($has_answer == 1){
                                $entry_clone = clone($entry);
                                $answer = $this->getAnswersOfAnswer($entry_clone, $a_id, $a_firstname, $a_surname, $a_email, $a_text, $answer_answer_id);
                            } else {
                                $answer = 0;
                            }

                            $new_entry = new Entry();
                            $new_entry->setAnswerId($a_id);
                            $new_entry->setFirstname($a_firstname);
                            $new_entry->setSurname($a_surname);
                            $new_entry->setEmail($a_email);
                            $new_entry->setText($a_text);
                            $new_entry->setAnsweranswerId($answer_answer_id);
                            $new_entry->setAnswerofanswer($answer);

                            $entry->setAnswerofanswerArray($new_entry);

                        }
                    }
                }
            }
            return $entries;
        }

        private function getAnswersOfAnswer($entry_clone, $a_id, $a_firstname, $a_surname, $a_email, $a_text, $answer_answer_id){
            
            $new_entry = new Entry();
            $new_entry->setAnswerId($a_id);
            $new_entry->setFirstname($a_firstname);
            $new_entry->setSurname($a_surname);
            $new_entry->setEmail($a_email);
            $new_entry->setText($a_text);
            $new_entry->setAnsweranswerId($answer_answer_id);

            $entry_clone->setAnswerofanswerArray($new_entry);

            $answer_id = $entry_clone->getAnswerofanswerIndex(count($entry_clone->getAnswerofanswer()) - 1)->getAnswerId();
            $sql = "SELECT answer_id, firstname, surname, email, answer_text, entry_id, answer_answer_id, has_answer
                    FROM antworten
                    WHERE answer_answer_id = $answer_id";
            $answerresult = $this->conn->query($sql);
            while($answerrow = $answerresult->fetch_assoc()){
                $a_id = $answerrow['answer_id'];
                $a_firstname = $answerrow['firstname'];
                $a_surname = $answerrow['surname'];
                $a_email = $answerrow['email'];
                $a_text = $answerrow['answer_text'];
                $answer_answer_id = $answerrow['answer_answer_id'];
                $has_answer = $answerrow['has_answer'];
                if($has_answer){
                    $answer = $this->getAnswersOfAnswer($entry_clone, $a_id, $a_firstname, $a_surname, $a_email, $a_text, $answer_answer_id);
                } else {
                    $answer = 0;
                }

                $new_entry = new Entry();
                $new_entry->setAnswerId($a_id);
                $new_entry->setFirstname($a_firstname);
                $new_entry->setSurname($a_surname);
                $new_entry->setEmail($a_email);
                $new_entry->setText($a_text);
                $new_entry->setAnsweranswerId($answer_answer_id);
                $new_entry->setAnswerofanswer($answer);

                $answers[] = $new_entry;
            }
            $answerresult->free();

            return $answers;
        }

        public function closeConnection(){
            $this->conn->close();
        }
    }