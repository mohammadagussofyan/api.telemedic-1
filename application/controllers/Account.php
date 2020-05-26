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
            $newdata = array(
                        'Id'  	        => $res['Id'],	            
                        'Email'         => $res['Email'],
                        'LastName'      => $res['LastName'],
                        'FirstName'     => $res['FirstName']
                    );

            $user = $this->session->set_userdata($newdata);
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

    Public function verify_otp_post(){
        $Id  = $this->session->userdata('Id');
        $Otp = $this->input->post('otp');
        $validator = array('success' => false, 'messages' => array());
        $this->form_validation->set_rules('otp', 'OTP', 'required');

        $this->form_validation->set_message('required', 'You must enter a %s.');
        $this->form_validation->set_message('is_unique', 'That %s is already in use, please try again.');
        
        if($this->form_validation->run() === true) {
            $validator = array('success' => false, 'messages' => array());
            $this->db->select('Id, otp');
            $this->db->from('otp'); 
            $this->db->where('PatientId', $Id);
            $this->db->where('OTP', $Otp);
            $this->db->where('RecordStatus', 1);
            $this->db->where('ExpiredTime >', date('Y-m-d H:i:s',now()));
            $result = $this->db->get();
            
            if($result->num_rows()) {
                $this->db->query("UPDATE OTP SET RecordStatus = 0 WHERE PatientId=$Id AND OTP=$Otp");
                $this->response([
                    'result'  => TRUE,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
            }
            else {
                $this->response([
                    'result'  => FALSE,
                    'message' => 'Otp not Match'
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
        $validator = array('success' => false, 'messages' => array());
        $this->form_validation->set_rules('Username', 'Username', 'required');

        $this->form_validation->set_message('required', 'You must enter a %s.');
        $this->form_validation->set_message('is_unique', 'That %s is already in use, please try again.');

        if($this->form_validation->run() === true) {
            $res = $this->Mod_account->checkUname();

            $newdata = array(
                'Id'  	        => $res['Id']
            );

            $user = $this->session->set_userdata($newdata);

            if($res == TRUE) {
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
        else {
            $this->response([
                'result'  => FALSE,
                'message' => $this->form_validation->error_array()
            ], REST_Controller::HTTP_OK);
        }
    }

    public function login_post(){
        $login = $this->Mod_account->login();
        if($login) {
            $this->load->library('session');
            $newdata = array(
                        'Id'  	        => $login['Id'],	            
                        'Email'         => $login['Email'],
                        'LastName'      => $login['LastName'],
                        'FirstName'     => $login['FirstName']
                    );

            $this->session->set_userdata($newdata);
            $this->response([
                'result'  => True,
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

    public function resetPassword_post(){
        $validator = array('success' => false, 'messages' => array());
        $this->form_validation->set_rules('Password', 'Password', 'required');
        $this->form_validation->set_rules('Repassword', 'Confirm Password', 'required|matches[Password]');

        $this->form_validation->set_message('required', 'You must enter a %s.');
        $this->form_validation->set_message('is_unique', 'That %s is already in use, please try again.');

        if($this->form_validation->run() === true) {
            $res = $this->Mod_account->resetPassword();

            if($res == TRUE) {
                $this->response([
                    'result'  => TRUE,
                    'message' => 'Success'
                ], REST_Controller::HTTP_OK);
            }
            else {
                $this->response([
                    'result'  => FALSE,
                    'message' => 'Failed!!'
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

    public function logout_get(){
        foreach ($_SESSION as $key => $value) {
          unset($_SESSION[$key]);
        }
        $this->response([
            'result'  => True,
            'message' => 'User logout successfull.',
        ], REST_Controller::HTTP_OK);
      }
    
    // public function signup_post(){
    //     $validator = array('success' => false, 'messages' => array());
    //     $this->form_validation->set_rules('UserName', 'Username', 'required|is_unique[SysMembership.UserName]');
    //     $this->form_validation->set_rules('Password', 'Password', 'required');
    //     $this->form_validation->set_rules('FirstName', 'FirstName', 'required');
    //     $this->form_validation->set_rules('LastName', 'LastName', 'required');
    //     $this->form_validation->set_rules('GenderId', 'Gender', 'required');
    //     $this->form_validation->set_rules('DateOfBirth', 'DOB', 'required');
    //     $this->form_validation->set_rules('MobilePhone', 'Phone', 'required');
    //     $this->form_validation->set_rules('Email', 'Email', 'required');

    //     /* Custom Messages */
    //     $this->form_validation->set_message('required', 'You must enter a %s.');
	// 	$this->form_validation->set_message('is_unique', 'That %s is already in use, please try again.');	
        
    //     if($this->form_validation->run() === true) {
    //         $res = $this->Mod_account->signup();
    //         if($res == TRUE){
    //             $this->response([
    //                 'result'  => TRUE,
    //                 'message' => 'A OTP has been sent to your email address'
    //             ], REST_Controller::HTTP_OK);
    //         }
    //         else{
    //             $this->response([
    //                 'result'  => FALSE,
    //                 'message' => 'Cannot Registered your Account'
    //             ], REST_Controller::HTTP_OK);
    //         }
    //     }
    //     else {
    //         $this->response([
    //             'result'  => FALSE,
    //             'message' => $this->form_validation->error_array()
    //         ], REST_Controller::HTTP_OK);
    //     }
    // }
    //end proses signup

}