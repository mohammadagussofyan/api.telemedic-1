<?php

require APPPATH . '/libraries/REST_Controller.php';

Class Account Extends REST_Controller{
   
    
    function __construct($config = 'rest'){
        parent::__construct($config);
        $this->load->model('Mod_account');
    }

    //Prosess Signup
    public function signup_post(){
        

        $validator = array('success' => false, 'messages' => array());
		$validate_data = array(
			array(
				'field' => 'UserName',
				'label' => 'Username',
				'rules' => 'required|is_unique[SysMembership.UserName]'
			),
			array(
				'field' => 'Password',
				'label' => 'Password',
				'rules' => 'required'
            ),
            array(
				'field' => 'FirstName',
				'label' => 'FirstName',
				'rules' => 'required'
            ),
            array(
				'field' => 'LasttName',
				'label' => 'LastName',
				'rules' => 'required'
            ),
            array(
				'field' => 'GenderId',
				'label' => 'Gender',
				'rules' => 'required'
            ),
            array(
				'field' => 'DateOfBirth',
				'label' => 'DateOfBirth',
				'rules' => 'required'
            ),
            array(
				'field' => 'MobilePhone',
				'label' => 'MobilePhone',
				'rules' => 'required'
            ),
            array(
				'field' => 'Email',
				'label' => 'Email',
				'rules' => 'required'
            )
        );

        $this->form_validation->set_rules($validate_data);
		$this->form_validation->set_message('is_unique', 'The {field} already exists');
        $this->form_validation->set_message('integer', 'The {field} must be number');		
        
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
                'message' => 'Email address already registered'
            ], REST_Controller::HTTP_OK);
        }
    }
    //end proses signup
}