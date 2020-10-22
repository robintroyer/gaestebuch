<?php

    class Filesystem {

        public function createJSON($array){
            $file = fopen('entries.json', 'w');
            fwrite($file, json_encode($array));
            fclose($file);
        }

        public function readJSON(){
            $file = fopen('entries.json', 'r');
            $content = fread($file, filesize('entries.json'));
            fclose($file);
            return $content;
        }

        


    }