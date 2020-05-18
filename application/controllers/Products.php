<?php

require APPPATH . '/libraries/REST_Controller.php';
Class Products Extends REST_Controller{

    function __construct($config = 'rest'){
        parent::__construct($config);
    }

    public function index_get(){
        
                $this->db->select('*');
                $this->db->from('RefGender');
                $product = $this->db->get()->result();
                $data = array();
                foreach($product as $p){
                    $row_array['Value']      = $p->NameValue;
                    $row_array['Desc']       = $p->DescValue;
                    array_push($data,$row_array);
                }
            $this->response($data, 200);
        }
}