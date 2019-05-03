<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends Base_Model {

	public function __construct()
    {
    	$this->table = 'tz_'.preg_replace("/_model$/", 's', strtolower(get_Class($this)));    	
    }   

    public function is_user_login()
    {
    	$row = $this->session->user_data;
    	if(!is_array($row))
    	{
    		$uri_redirect = site_url('login');
			redirect($uri_redirect);
    	}

        return $row;
    }

    public function user_timein()
    {
        $this->db->select('*');
        $this->db->from('tz_user_log');
        $this->db->join('tz_users', 'tz_users.user_id = tz_user_log.user_id');
        $this->db->order_by('user_time_in', 'DESC');
        $query = $this->db->get();
        if($query->num_rows() > 0)
        {
            return $query->result_array();
        }
    }
}