<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mod_account extends CI_Model {

	public function register(){
		$otpkey = mt_rand(1000,9999);
		$password = password_hash($this->input->post('Password'), PASSWORD_DEFAULT);
		$email = $this->input->post('Email');
		$mobile = NULL;
		$mail = NULL;

		if (filter_var($email, FILTER_SANITIZE_NUMBER_INT)) {
			$mobile = $this->input->post('Email');
		} elseif(filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$mail = $this->input->post('Email');
			$this->mail_otp($otpkey,$email);
		}
		$this->db->trans_start();
		
		$patient = array(
			'FirstName'     => $this->input->post('FirstName'),
			'LastName'		=> $this->input->post('LastName'),
			'Password'     	=> $password,
			'GenderId'		=> $this->input->post('GenderId'),
			'DateOfBirth'   => $this->input->post('DateOfBirth'),
			'MobilePhone'	=> $mobile,
			'Email'			=> $mail,
			'RecordStatus'  => 1,
			'CreatedDate'	=> date('Y-m-d H:i:s',now()),
			'CreatedBy'		=> $this->input->post('FirstName').' '.$this->input->post('LastName')
		);
		$this->db->insert('Patient', $patient);
		$PatientId = $this->db->insert_id();

		$otp = array(
			'PatientId'		=> $PatientId,
			'OTP'			=> $otpkey,
			'ExpiredTime'	=> date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +3 minutes")),
			'RecordStatus'  => 1,
			'CreatedDate'	=> date('Y-m-d H:i:s',now()),
			'CreatedBy'		=> $this->input->post('FirstName').' '.$this->input->post('LastName')
		);
		$this->db->insert('OTP', $otp);

		$afftectedRows=$this->db->affected_rows();
		$this->db->trans_complete();
		$this->db->select('Id, FirstName, LastName, Password, Email');
		$this->db->from('patient'); 
        $this->db->where('Id', $PatientId);
		$user = $this->db->get()->row_array();
        if($afftectedRows>0){
            return $user;
        }
        else{
            return FALSE;
        }
	}

	public function login(){
        $Username = $this->input->post('Username');
		$Password = $this->input->post('Password');
		$otpkey = mt_rand(1000,9999);
		$mobile = NULL;
		$mail = NULL;

		$this->db->select('Id, FirstName, LastName, Password, Email');
		$this->db->from('patient'); 
        $this->db->where('Email', $Username)
                ->or_where('MobilePhone', $Username);
		$user = $this->db->get()->row_array();
		
		if($user){
			$isPasswordTrue = password_verify($Password, $user['Password']);

			if($isPasswordTrue){ 
				if (filter_var($Username, FILTER_SANITIZE_NUMBER_INT)) {
					$mobile = $this->input->post('Email');
				} elseif(filter_var($Username, FILTER_VALIDATE_EMAIL)) {
					$this->mail_otp($otpkey,$user['Email']);
				}
				$this->db->trans_start();
                $otp = array(
					'PatientId'		=> $user['Id'],
					'OTP'			=> $otpkey,
					'ExpiredTime'	=> date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +3 minutes")),
					'RecordStatus'  => 1,
					'CreatedDate'	=> date('Y-m-d H:i:s',now()),
					'CreatedBy'		=> 1
				);
				$this->db->insert('OTP', $otp);
		
				$afftectedRows=$this->db->affected_rows();
				$this->db->trans_complete();
				if($afftectedRows>0){
					return $user;
				}
				else{
					return FALSE;
				}
            }
		}
		return false;  
	}
	
	function mail_otp($otp,$email){
        $from_email = "no-reply@masdelima.net"; 
        $to_email = $email; 

        $config = Array(
            'protocol'  => 'smtp',
            'smtp_host' => 'ssl://mail.masdelima.net',
            'smtp_port' => 465,
            'smtp_user' => 'no-reply@masdelima.net',
            'smtp_pass' => 'Bogor@16',
            'mailtype'  => 'html',
            'charset'  	=> 'iso-8859-1',
            'wordwrap'  => TRUE
        );

		$this->load->library('email', $config);
		$this->email->set_newline("\r\n");   

        $this->email->from($from_email, 'Info@mayapadahospital.com'); 
        $this->email->to($to_email);
        $this->email->subject('Verify Your Register'); 
        $this->email->message('Code your OTP is '.$otp);
		$this->email->send();
	}
	
	function checkUname(){
		$Username = $this->input->post('Username');
		$otpkey = mt_rand(1000,9999);
		$this->db->select('Id, FirstName, Lastname, Email');
		$this->db->from('patient'); 
		$this->db->where('Email', $Username);
		$this->db->or_where('MobilePhone', $Username);
		$result = $this->db->get();
		$id = $result->row_array();
		if($result->num_rows()){
			$this->db->select('Id, FirstName, LastName, Password, Email');
			$this->db->from('patient'); 
			$this->db->where('Id', $id['Id']);
			$a = $this->db->get();
			$user = $a->row_array();
			$this->mail_otp($otpkey,$id['Email']);

			$otp = array(
				'PatientId'		=> $id['Id'],
				'OTP'			=> $otpkey,
				'ExpiredTime'	=> date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +3 minutes")),
				'RecordStatus'  => 1,
				'CreatedDate'	=> date('Y-m-d H:i:s',now()),
				'CreatedBy'		=> 1
			);
			$this->db->insert('OTP', $otp);

			return $user;
		}
		else{
			return FALSE;
		}
	}

	function resetPassword(){
		$Password = password_hash($this->input->post('Password'), PASSWORD_DEFAULT);
		$id = $this->session->userdata('Id');
		
		$this->db->trans_start();
		$data = array(
			'Password' => $Password
		);
		$this->db->where('id', $id);
		$this->db->update('patient', $data);
		$afftectedRows=$this->db->affected_rows();
		$this->db->trans_complete();
		if($afftectedRows>0){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}


	// public function signup(){
	// 	$salt = $this->salt();
	// 	$otpkey = mt_rand(1000,9999); 
	// 	$password = $this->makePassword($this->input->post('Password'), $salt);
	// 	$email = $this->input->post('Email');
		
	// 	$this->db->trans_start();
		
	// 	$member   = array(
    //         'UserName'    	 => $this->input->post('UserName'),
	// 		'Password'     	 => $password,
	// 		'HospitalUnitId' => 1,
	// 		'RecordStatus'   => 1,
	// 		'CreatedDate'	 => date('Y-m-d H:i:s',now()),
	// 		'CreatedBy'		 => 1
	// 	);
	// 	$this->db->insert('SysMembership', $member);
	// 	$MembershipId = $this->db->insert_id();
		
	// 	$patient = array(
	// 		'FirstName'     => $this->input->post('FirstName'),
	// 		'LastName'		=> $this->input->post('LastName'),
	// 		'Password'     	=> $password,
	// 		'GenderId'		=> $this->input->post('GenderId'),
	// 		'DateOfBirth'   => $this->input->post('DateOfBirth'),
	// 		'MobilePhone'	=> $this->input->post('MobilePhone'),
	// 		'Email'			=> $this->input->post('Email'),
	// 		'RecordStatus'  => 1,
	// 		'CreatedDate'	=> date('Y-m-d H:i:s',now()),
	// 		'CreatedBy'		=> 1
	// 	);
	// 	$this->db->insert('Patient', $patient);
	// 	$PatientId = $this->db->insert_id();

	// 	$otp = array(
	// 		'PatientId'		=> $PatientId,
	// 		'MembershipId'	=> $MembershipId,
	// 		'OTP'			=> $otpkey,
	// 		'ExpiredTime'	=> date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +2 minutes")),
	// 		'RecordStatus'  => 1,
	// 		'CreatedDate'	=> date('Y-m-d H:i:s',now()),
	// 		'CreatedBy'		=> 1
	// 	);
	// 	$this->db->insert('OTP', $otp);

	// 	$this->mail_otp($otpkey,$email);

	// 	$afftectedRows=$this->db->affected_rows();
    //     $this->db->trans_complete();
    //     if($afftectedRows>0){
    //         return TRUE;
    //     }
    //     else{
    //         return FALSE;
    //     }
	// }
}