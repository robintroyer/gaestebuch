<?php
    require_once('classes.php');

    class Filesystem implements StorageInterface {
        private $content;
        private $answer_ids = [];
        private $answer_id;
        private $filename;
        public $output;
        public $amount;

        public function initialize($config){

            $this->filename = $config->file;
            $entries = $this->getEntries();
            $this->answer_id = $this->getAnswerID($entries);

            $data = new stdClass();
            $data->max_answer_id = $this->answer_id;

            if($entries){
                $data->amount = $entries[count($entries) - 1]->getId();
            }

            $data->cache = $entries;

            // $this->output = new View($data->amount);

            return $data;
        }

        public function getEntries(){
            $this->content = file_get_contents($this->filename);
            $this->content = json_decode($this->content, false);

            $contents = [];
            if ($this->content){
                foreach ($this->content as $content){
                    $entry = new Entry();
                    $entry->set($content);
                    $contents[] = $entry;
                }
            }

            $this->content = $contents;
            if($contents){
                $this->amount = $contents[count($contents) - 1]->getId();
            } else {
                $this->amount = 0;
            }
            // echo $this->amount;

            return $contents;
        }

        public function saveEntry($entry, $is_answer){
            
            $data = $this->getEntries();
            if(is_array($entry)){

                if(array_key_exists('content', $entry)){
                    $data = $entry['result'];
                    $answer = $entry['content'];
                    $id = $answer->getAnsweranswerId();
                }

                
                if(isset($id)){
                    $new_result = [];
                    foreach($data as $res){
                        $new_result[] = $this->removeDuplicates($res);
                    }

                    for($i = 0; $i < count($new_result); $i++){
                        $res = &$new_result[$i];
                        $this->loopArray($res, $answer);
                    }

                    $this->new_result = $new_result;

                    $this->content = $this->getEntries();
                    $file = fopen($this->filename, 'w');

                    $encoded_input = json_encode($new_result);
                    fwrite($file, $encoded_input);
                    fclose($file);
                } else {
                    $this->content = $this->getEntries();
                    $file = fopen($this->filename, 'w');


                    $encoded_input = json_encode($entry);

                    fwrite($file, $encoded_input);
                    fclose($file);
                }
            }

            if($this->amount){
                $this->amount = $this->content[count($this->content) - 1]->getId();
            } else {
                $this->amount = 0;
            }
            



            
        }

        private function removeDuplicates($array){
            if(!empty($array->getAnswerofanswer())){
                foreach($array->getAnswerofanswer() as $k => $v){
                    foreach($array->getAnswerofanswer() as $key => $value){
                        if($k != $key && $v->getAnswerId() == $value->getAnswerId()){
                            $duplicate = $array->getAnswerofanswerIndex($k);
                            unset($duplicate);
                        }
                    }
                }
                $array->setAnswerofanswer(array_values($array->getAnswerofanswer()));
                foreach($array->getAnswerofanswer() as &$arr){
                    $this->removeDuplicates($arr);
                }
            }
            return $array;
        }

        private function loopArray(&$res, $answer){
            if(empty($res->getAnswerofanswer()) && $res->getParent() == 0 && !property_exists((object)$res, 'answer_answer_id')){
                return $res;
            }
            for($i = 0; $i < count($res->getAnswerofanswer()); $i++){
                $r = &$res->getAnswerofanswerIndex($i);
                if($r->getAnswerId() == $answer->getAnsweranswerId()){
                    $r->setAnswerofanswerArray($answer);
                } else {
                    $this->loopArray($r, $answer);
                }
            }
        }

        private function getAnswerID($results){
            if($results){
                foreach($results as $result){
                    foreach($result->getAnswerofanswer() as $res){
                        $this->answer_id = $this->compareID($res, $this->answer_ids);
                        $this->answer_ids[] = $res->getAnswerId();
                    }
                }
            }
            if(count($this->answer_ids) > 0){
                return max($this->answer_ids);
            }
        }       
        
        private function compareID($res, $answer_ids){
            foreach((array)$res->getAnswerofanswer() as $r){
                $this->compareID($r, $answer_ids);
                $this->answer_ids[] = $r->getAnswerId();
            }
            return $this->answer_ids;
        }
    }