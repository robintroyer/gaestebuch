<?php 
    require_once('classes.php');
    class Entry implements JsonSerializable {
        protected $id;
        protected $firstname;
        protected $surname;
        protected $email;
        protected $text;
        protected $parent;
        protected $has_answer;
        protected $answer_id;
        protected $answer_answer_id;
        protected $answerofanswer = [];

        public function __construct(){
            
        }

        public function setId($id){
            $this->id = $id;
        }
        public function setFirstname($firstname){
            $this->firstname = $firstname;
        }
        public function setSurname($surname){
            $this->surname = $surname;
        }
        public function setEmail($email){
            $this->email = $email;
        }
        public function setText($text){
            $this->text = $text;
        }
        public function setParent($parent){
            $this->parent = $parent;
        }
        public function setHasAnswer($has_answer){
            $this->has_answer = $has_answer;
        }
        public function setAnswerId($answer_id){
            $this->answer_id = $answer_id;
        }
        public function setAnsweranswerId($answer_answer_id){
            $this->answer_answer_id = $answer_answer_id;
        }
        public function setAnswerofanswer($answerofanswer){
            $this->answerofanswer = $answerofanswer;
        }
        public function setAnswerofanswerArray($answerofanswer){
            $this->answerofanswer[] = $answerofanswer;
        }
        

        public function getId(){
            return $this->id;
        }
        public function getFirstname(){
            return $this->firstname;
        }
        public function getSurname(){
            return $this->surname;
        }
        public function getEmail(){
            return $this->email;
        }
        public function getText(){
            return $this->text;
        }
        public function getParent(){
            return $this->parent;
        }
        public function getHasanswer(){
            return $this->has_answer;
        }
        public function getAnswerId(){
            return $this->answer_id;
        }
        public function getAnsweranswerId(){
            return $this->answer_answer_id;
        }
        public function getAnswerofanswer(){
            return $this->answerofanswer;
        }
        public function getAnswerofanswerIndex($index){
            return $this->answerofanswer[$index];
        }

        public function jsonSerialize(){
            return (object)get_object_vars($this);
        }

        public function set($data){
            foreach ($data as $key => $value){
                $this->{$key} = $value;
                if (
                    is_array($value)
                    && !empty($value)
                ) {
                    foreach ($value as $val){
                        $answer = new Entry();
                        $answer->set($val);
                        $index = array_search($answer->answer_id, array_column($value, 'answer_id'));
                        $this->{$key}[$index] = $answer;
                    }
                }     
            }
        }
    }