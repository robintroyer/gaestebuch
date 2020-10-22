<?php

    require_once('classes.php');
    
    class View {

        public $servername;
        public $username;
        public $password;
        public $dbname;

        /**
         * @var \Database $conn
         */
        public $conn;
        public $sql;
        public $currentPage;
        


        /**
         * @param \Database $database
         */
        public function __construct($database, $servername, $username, $password, $dbname){
            $this->conn = $database;
            $this->currentPage = 1;
            $this->servername = $servername;
            $this->username = $username;
            $this->password = $password;
            $this->dbname = $dbname;
        }     
        
        public function printData($result, $first){

            if($first){
                if($result){
                    foreach($result as $res){
                        $data = "<tr><td>".$res[0]."</td><td>".$res[4]."</td>
                        <td>".$res[3]."</td><td>".$res[2]."</td>
                        <td>".$res[1]."</td><td><button class='answerbutton' onclick='addID(".$res[0].")'>antworten</button></td></tr>";
                        echo $data;
                        $this->printData($res[6], false);
                    }
                }
            } else {
                if($result){
                    foreach((array)$result as $res){
                        $data = "<tr><td>".$res[0]."</td><td>".$res[4]."</td>
                        <td>".$res[3]."</td><td>".$res[2]."</td>
                        <td>".$res[1]."</td><td><button class='answerbutton' onclick='answerOfAnswer(".$res[0].")'>antworten</button></td></tr>";
                        echo $data;
                        $this->printData($res[6], false);
                    }
                }
            }
        }

        function arraysort($a, $b){
            return strlen($b) - strlen($a);
        }

        function modify($char, $input){
            $results = [];
            $basecount = count($input);
            $combinations = $this->getCombinations($basecount);
            foreach($combinations as $combination){
                $item = $input;
                foreach(array_reverse(array_keys($combination)) as $index){
                    $replace = (bool)$combination[$index];
                    if($replace){
                        array_splice($item, $index + 1, 0, $char);
                    }
                }
                $results[] = $item;
            }
            return $results;
        }

        function getCombinations($count){
            $totalCombinations = pow(2, $count);
            $ret = [];
            for($x = 0; $x < $totalCombinations; $x++){
                $bin = decbin($x);
                $padded = str_pad($bin, $count, 0, STR_PAD_LEFT);
                $ret[] = str_split($padded);
            }
            return $ret;
        }

        
        public function displayPages(){

            $result = $this->conn->showPages();

            echo 'Seite ';
            if($this->currentPage > 1){
                echo '<input type="submit" name="page" value="<-">';
                echo '<input type="hidden" name="prevPage" value="'.$this->currentPage.'"></input>';
            }
            for($i = 1; $i <= ceil($result->num_rows / 3); $i++){
                if($i == $this->currentPage){
                    echo '<strong> '.$i.' </strong>';
                } else {
                    echo '<input type="submit" name="page" value="'.$i.'">';
                }
            }
            if($result->num_rows > 3){
                if($this->currentPage < $result->num_rows / 3){
                    echo '<input type="submit" name="page" value="->">';
                    echo '<input type="hidden" name="prevPage" value="'.$this->currentPage.'">';
                }
            }
        }

        public function getPage(){
            if($_POST['prevPage']){
                $prevPage = $_POST['prevPage'];
            }
            if(!empty($_POST['page']) && $_POST['page'] != '->' && $_POST['page'] != '<-'){
                $this->currentPage = $_POST['page'];
            }
            if(empty($_POST['page'])){
                $this->currentPage = 1;
            } else if($_POST['page'] === '->'){
                $this->currentPage = $prevPage + 1;
            } else if($_POST['page'] === '<-'){
                $this->currentPage = $prevPage - 1;
            }
            echo $this->currentPage;
            return $this->currentPage;
        }
    }