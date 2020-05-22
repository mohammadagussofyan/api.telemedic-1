<?php

require APPPATH . '/libraries/REST_Controller.php';

Class Account Extends REST_Controller{
    
    function __construct($config = 'rest'){
        parent::__construct($config);
        $this->load->model('Mod_account');
    }

    public function register_post(){
        $validator = array('success' => false, 'messages' => array());
        $this->form_validation->set_rules('FirstName', 'FirstName', 'required');
        $this->form_validation->set_rules('LastName', 'LastName', 'required');
        $this->form_validation->set_rules('GenderId', 'Gender', 'required');
        $this->form_validation->set_rules('DateOfBirth', 'DOB', 'required');
        $this->form_validation->set_rules('Email', 'Email Or Phone', 'trim|is_unique[Patient.Email],trim|is_unique[Patient.MobilePhone]');
        $this->form_validation->set_rules('Password', 'Password', 'required');

        /* Custom Messages */
        $this->form_validation->set_message('required', 'You must enter a %s.');
        $this->form_validation->set_message('is_unique', 'That %s is already in use, please try again.');
        
        if($this->form_validation->run() === true) {
            $res = $this->Mod_account->register();
            if($res == TRUE){
                $this->response([
                    'result'  => TRUE,
                    'message' => 'A OTP has been sent to your email address'
                ], REST_Controller::HTTP_OK);
            }
            else{
                $this->response([
                    'result'  => FALSE,
                    'message' => 'Cannot Registered your Account'
                ], REST_Controller::HTTP_OK);
            }
        }
        else {
            $this->response([
                'result'  => FALSE,
                'message' => $this->form_validation->error_array()
            ], REST_Controller::HTTP_OK);
        }
    }

    public function checkUname_post(){
        $Username = $this->input->post('Username');
        $validator = array('success' => false, 'messages' => array());
        $this->db->select('Id, FirstName, Lastname');
        $this->db->from('patient'); 
        $this->db->where('Email', $Username);
        $this->db->or_where('MobilePhone', $Username);
        $result = $this->db->get();

        if($result->num_rows()) {
            $this->response([
                'result'  => TRUE,
                'message' => 'User Found'
            ], REST_Controller::HTTP_OK);
        }
        else {
            $this->response([
                'result'  => FALSE,
                'message' => 'User Not Found'
            ], REST_Controller::HTTP_OK);
        }
    }

    public function login_post(){
        $login = $this->Mod_account->login();
        if($login) {
            $this->load->library('session');
            $newdata = array(
                'id'  	        => $login['Id'],	      
                'sessionid'  	=> $this->session->userdata('session_id'),	      
                'firstname'     => $login['FirstName'],
                'lastname'      => $login['LastName']
            );

            $user = $this->session->set_userdata($newdata);
            $this->response([
                'result'  => TRUE,
                'message' => 'User login successfull.',
            ], REST_Controller::HTTP_OK);				
        }
        else {
            $this->response([
                'result'  => FALSE,
                'message' => 'Wrong Address or Password'
            ], REST_Controller::HTTP_OK);
        } 
    }
    
    public function signup_post(){
        $validator = array('success' => false, 'messages' => array());
        $this->form_validation->set_rules('UserName', 'Username', 'required|is_unique[SysMembership.UserName]');
        $this->form_validation->set_rules('Password', 'Password', 'required');
        $this->form_validation->set_rules('FirstName', 'FirstName', 'required');
        $this->form_validation->set_rules('LastName', 'LastName', 'required');
        $this->form_validation->set_rules('GenderId', 'Gender', 'required');
        $this->form_validation->set_rules('DateOfBirth', 'DOB', 'required');
        $this->form_validation->set_rules('MobilePhone', 'Phone', 'required');
        $this->form_validation->set_rules('Email', 'Email', 'required');

        /* Custom Messages */
        $this->form_validation->set_message('required', 'You must enter a %s.');
		$this->form_validation->set_message('is_unique', 'That %s is already in use, please try again.');	
        
        if($this->form_validation->run() === true) {
            $res = $this->Mod_account->signup();
            if($res == TRUE){
                $this->response([
                    'result'  => TRUE,
                    'message' => 'A OTP has been sent to your email address'
                ], REST_Controller::HTTP_OK);
            }
            else{
                $this->response([
                    'result'  => FALSE,
                    'message' => 'Cannot Registered your Account'
                ], REST_Controller::HTTP_OK);
            }
        }
        else {
            $this->response([
                'result'  => FALSE,
                'message' => $this->form_validation->error_array()
            ], REST_Controller::HTTP_OK);
        }
    }
    //end proses signup

}