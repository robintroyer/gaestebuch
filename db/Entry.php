<?php 
    require_once('classes.php');
    class Entry {
        public $firstname;
        public $surname;
        public $email;
        public $text;
        public $parent;
        public $answerofanswer = [];

        public function set_firstname($firstname){
            $this->firstname = $firstname;
        }
        public function set_surname($surname){
            $this->surname = $surname;
        }
        public function set_email($email){
            $this->email = $email;
        }
        public function set_text($text){
            $this->text = $text;
        }
        public function set_parent($parent){
            $this->parent = $parent;
        }
        public function set_answerofanswer($answerofanswer){
            $this->answerofanswer = $answerofanswer;
        }

        public function get_firstname(){
            return $this->firstname;
        }
        public function get_surname(){
            return $this->surname;
        }
        public function get_email(){
            return $this->email;
        }
        public function get_text(){
            return $this->text;
        }
        public function get_parent(){
            return $this->parent;
        }
        public function get_answerofanswer(){
            return $this->answerofanswer;
        }

        public function setEntries($id, $firstname, $surname, $email, $text, $parent, $answer){
            $entry = [$id, $firstname, $surname, $email, $text, $parent, $answer];
            return $entry;
        }

        public function getEntries(){
            
        }

        public function addAnswer($answer){

        }
    }