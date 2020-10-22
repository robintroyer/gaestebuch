<?php

interface StorageInterface {
    /**
     * @return array
     */
    public function getEntries();

    public function saveEntry($entry, $is_answer);

    public function initialize($config);
}