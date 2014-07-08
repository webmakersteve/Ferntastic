<?php

namespace Ferntastic\fQuery;

class fQueryData {

    private $data = array();

    function __construct( $data ) {

        $this->data = $data;

    }

    function row( $id ) {

        if ( isset( $this->data[$id] ) ) return stripslashes($this->data[$id]);
        else return false;

    }

    public function to_array() {

        return $this->data;

    }

    function __get( $key ) {
        return $this->row( $key );
    }


}