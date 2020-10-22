<?php
    require_once('classes.php');
    class Emoji {

        public $output;
        public $folder;

        function __construct(){
            $this->folder = scandir('C:\xampp\htdocs\emoji\twemoji\twemoji-master\assets\72x72', SCANDIR_SORT_DESCENDING);
            $this->output = [];
            usort($this->folder, array($this, 'arraysort'));

        }

        function changeEmoji($text){
              
            for($i = 0; $i < count($this->folder); $i++){
                $file = $this->folder[$i];
                $code = strval(substr($file, 0, strlen($this->folder[$i]) - 4));
                $teile = [];
                $teile = explode('-', $code);
                $emoji = '';
                for($j = 0; $j < count($teile); $j++){
                    $teile[$j] = '&#x' . $teile[$j] . ';';
                    $emoji .= $teile[$j];
                }
                $results[$i] = $this->modify('&#xFE0F;', $teile);
                for($j = 0; $j < count($results[$i]); $j++){
                    $text = str_replace(html_entity_decode(implode('', $results[$i][$j])), '<img src="..\emoji\twemoji\twemoji-master\assets\72x72\\'.$file.'" alt="'.$code.'" width="20" height="20">', $text);
                }
                
            }
        
            $this->output = $text;
            
            return $this->output;
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
    }