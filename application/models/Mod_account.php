<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mod_account extends CI_Model {

    public function salt(){
		return password_hash("rasmuslerdorf", PASSWORD_DEFAULT);
	}

    public function makePassword($password = null, $salt = null){
		if($password && $salt) {
			return hash('sha256', $password.$salt);
		}
    }

    public function getUserDataById($userId) {
		$sql = "SELECT * FROM user WHERE user_id = ?";
		$query = $this->db->query($sql, array($userId));
		return $query->row_array();
	}

    public function signup(){
		$set = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$code = substr(str_shuffle($set), 0, 32);
		$salt = $this->salt();
		$otp = mt_rand(1,100000); 
        $password = $this->makePassword($this->input->post('Password'), $salt);
        $member   = array(
            'UserName'    	 => $this->input->post('UserName'),
			'Password'     	 => $password,
			'HospitalUnitId' => 1,
			'RecordStatus'   => 1,
			'CreatedDate'	 => date('Y-m-d H:i:s',now()),
			'CreatedBy'		 => 1
		);
		$this->db->insert('SysMembership', $member);
		$MembershipId = $this->db->insert_id();
		
		$patient = array(
			'FirstName'     => $this->input->post('FirstName'),
			'LastName'		=> $this->input->post('LastName'),
			'Password'     	=> $password,
			'GenderId'		=> $this->input->post('GenderId'),
			'DateOfBirth'   => $this->input->post('DateOfBirth'),
			'MobilePhone'	=> $this->input->post('MobilePhone'),
			'Email'			=> $this->input->post('Email'),
			'RecordStatus'  => 1,
			'CreatedDate'	=> date('Y-m-d H:i:s',now()),
			'CreatedBy'		=> 1
		);
		$this->db->insert('Patient', $patient);
		$PatientId = $this->db->insert_id();

		$otp = array(
			'PatientId'		=> $PatientId,
			'MembershipId'	=> $MembershipId,
			'OTP'			=> $otp,
			'ExpiredTime'	=> date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +2 minutes")),
			'RecordStatus'  => 1,
			'CreatedDate'	=> date('Y-m-d H:i:s',now()),
			'CreatedBy'		=> 1
		);

        $this->db->trans_start();
		$this->db->insert('OTP', $otp);
        $afftectedRows=$this->db->affected_rows();
        $this->db->trans_complete();
        if($afftectedRows>0){
            return TRUE;
        }
        else{
            return FALSE;
        }
    }

}