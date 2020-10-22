<?php

    require_once('classes.php');
    
    class View {
        /**
         * @var \Database $conn
         */
        // public $conn;
        private $currentPage;
        private $amount;
        
        /**
         * @param \Database $database
         * 
         * 
         */

        public function __construct($amount){
            // $this->currentPage = 1;
            $this->amount = $amount;
        }     

        private function adaptToPage($data, $page){
            $new_data = [];
            if($data){
                foreach($data as $dat){
                    if($dat->getId() > ($page - 1) * 3 && $dat->getId() <= $page * 3){
                        $new_data[] = $dat;
                    }
                }
            }
            return $new_data;
        }
        
        public function printData($result, $first){
            // if($first){
            //     $result = $this->adaptToPage($result, $this->currentPage);
            //     if($result){
            //         foreach($result as $res){
            //             $data = "<tr><td>".$res->getId()."</td><td>".$res->getText()."</td>
            //             <td>".$res->getEmail()."</td><td>".$res->getSurname()."</td>
            //             <td>".$res->getFirstname()."</td><td><button class='answerbutton' onclick='addID(".$res->getId().")'>antworten</button>
            //             </td></tr>";
            //             echo $data;
            //             if(!empty($res->getAnswerofanswer())){
            //                 $this->printData($res->getAnswerofanswer(), false);
            //             }
            //         }
            //     }
            // } else {
            //     if($result){
            //         foreach((array)$result as $res){
            //             $data = "<tr><td>".$res->getAnswerId()."</td><td>".$res->getText()."</td>
            //             <td>".$res->getEmail()."</td><td>".$res->getSurname()."</td>
            //             <td>".$res->getFirstname()."</td><td><button class='answerbutton' onclick='answerOfAnswer(".$res->getAnswerId().")'>antworten</button></td></tr>";
            //             echo $data;
            //             if($res->getAnswerofanswer()){
            //                 $this->printData($res->getAnswerofanswer(), false);
            //             }
            //         }
            //     }
            // }
            if ($first) {
                $result = $this->adaptToPage($result, $this->currentPage);
                if ($result) {
                    foreach ($result as $res) {
                        echo '<details><summary class="row"><span>' . $res->getId(). '</span>
                        <span>' . $res->getText() . '</span><span>' . $res->getEmail() . '</span>
                        <span>' . $res->getSurname() . '</span><span>' . $res->getFirstname() . '</span>
                        <span><button class="answerbutton" onclick="answerOfAnswer(' . $res->getId() . ')">Antworten</button></span>
                        </summary>';
                        if (!empty($res->getAnswerofanswer())) {
                            $this->printData($res->getAnswerofanswer(), false);
                        }
                        echo '</details>';
                    }
                }
            } else {
                if ($result) {
                    foreach ((array)$result as $res) {
                        echo '<details><summary class="row"><span>' . $res->getAnswerId() . '</span>
                        <span>' . $res->getText() . '</span><span>' . $res->getEmail() . '</span>
                        <span>' . $res->getSurname() . '</span><span>' . $res->getFirstname() . '</span>
                        <span><button class="answerbutton" onclick="answerOfAnswer(' . $res->getAnswerId() . ')">Antworten</button></span>
                        </summary>';
                        if ($res->getAnswerofanswer()) {
                            $this->printData($res->getAnswerofanswer(), false);
                        }
                        echo '</details>';
                    }
                }
            }

            // mobile

            if ($first) {
                $result = $this->adaptToPage($result, $this->currentPage);
                if ($result) {
                    foreach ($result as $res) {
                        echo '<details><summary class="mobile"></summary><span>' . $res->getAnswerId() . '</span>
                        <span>' . $res->getText() . '</span><span>' . $res->getEmail() . '</span>
                        <span>' . $res->getSurname() . '</span><span>' . $res->getFirstname() . '</span>
                        <span><button class="answerbutton" onclick="answerOfAnswer(' . $res->getId() . ')">Antworten</button></span>';
                        if (!empty($res->getAnswerofanswer())) {
                            $this->printData($res->getAnswerofanswer(), false);
                        }
                        echo '</details>';
                    }
                }
            } else {
                
            }
        }

        public function displayPages($page){
            // echo $this->amount;
            $this->currentPage = $page;
            // echo $this->currentPage;
            echo 'Seite ';
            echo '<div class="pagination">';
            if($this->currentPage > 1){
                echo '<input type="submit" name="page" value="<-">';
                echo '<input type="hidden" name="prevPage" value="'.$this->currentPage.'"></input>';
            }
            for($i = 1; $i <= ceil($this->amount / 3); $i++){
                if($i == $this->currentPage){
                    // echo '<strong> '.$i.' </strong>';
                    echo '<input class="current" type="submit" name="page" value="' . $i . '">';
                } else {
                    echo '<input type="submit" name="page" value="'.$i.'">';
                }
            }
            if($this->amount > 3){
                if($this->currentPage < $this->amount / 3){
                    echo '<input type="submit" name="page" value="->">';
                    echo '<input type="hidden" name="prevPage" value="'.$this->currentPage.'">';
                }
            }
            echo '</div>';
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
            echo $prevPage;
            // echo $this->currentPage;
            return $this->currentPage;
        }
    }