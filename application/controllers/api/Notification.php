<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . 'libraries/REST_Controller.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Notification extends REST_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        $this->load->model('Base_model');
    }

    public function index_get()
    {
        $user_id = $this->get('user_id');
        $search_string = $this->get('q');
        $user_notice_rows = array();

        // List Haku Tag to User Notification
        $order_by = 'ORDER BY `utht`.`created_at` DESC';
        $search_query = '`utht`.`user_id`="'.$user_id.'"';
        $query = $this->db->query(
		    'SELECT `utht`.*,
		    	`u`.`user_avatar`,
                `u`.`user_first_name`,
		    	`u`.`user_last_name`
		    FROM `tz_user_to_haku_tag` `utht`
		    LEFT JOIN `tz_users` `u` ON `u`.`user_id`=`utht`.`user_id`
		    -- LEFT JOIN `tz_haku` `h` ON `h`.`haku_id`=`utht`.`haku_id`
		    WHERE '.$search_query.$order_by.' LIMIT 0, 50'
		);
		$rows = $query->result_array();
		if($query->num_rows() > 0)
		{
            foreach($rows as $key => $val)
            {
                $user_notice_rows['tag_'.$key]['haku_id'] = $val['haku_id'];
                $user_notice_rows['tag_'.$key]['avatar'] = $val['user_avatar'];
                $user_notice_rows['tag_'.$key]['name'] = $val['user_first_name'].' '.$val['user_last_name'];
                $user_notice_rows['tag_'.$key]['message'] = 'tagged you in a post.';

                #Check if created date is not today.          
                $user_notice_rows['tag_'.$key]['readable_date'] = dateformat($val['created_at'], 'F d, Y').' at '.dateformat($val['created_at'], 'h:i a');
                $user_notice_rows['tag_'.$key]['readable_short_date_no_time'] = dateformat($val['created_at'], 'M. d, Y');
                $user_notice_rows['tag_'.$key]['readable_short_date'] = dateformat($val['created_at'], 'M. d').' at '.dateformat($val['created_at'], 'h:i a');
                if(dateformat($val['created_at'], 'Y-m-d') == datenow($format='Y-m-d'))
                {
                    $user_notice_rows['tag_'.$key]['readable_date'] = timeElapsedString($val['created_at']);
                }

                if(!empty($val['read_at']))
                {
                    $user_notice_rows['tag_'.$key]['read'] = 1;
                }
            }
        }           

        $search_query = '`notification_status` = "2"';
        // if($search_string !== NULL)
        // {
        //     $search_query .= ' AND (`company_name` LIKE "%'.$search_string.'%"
        //         OR `semi_membership_id` LIKE "%'.$search_string.'%"
        //     )';
        // }
        $notification_status = array();
        $user_to_notification = $this->Base_model->list_all('tz_user_to_notification', '', '', '', '', 'user_id="'.$user_id.'"');
        if(is_array($user_to_notification))
        {
            foreach($user_to_notification as $key => $val)
            {
                if(!empty($val['read_at']))
                {
                    $notification_status[$val['notification_id']]['read'] = 1;
                }

                if(!empty($val['delete_at']))
                {
                    $notification_status[$val['notification_id']]['delete'] = 1;
                }
            }            
        }
        $rows = $this->Base_model->list_all('tz_notification', '', '', 'created_at', 'desc', $search_query);
        // Check if the users data store contains users (in case the database result returns NULL)
        if(is_array($rows) && sizeof($rows) > 0)
        {
            $tz_notification_rows = array();
            foreach($rows as $key => $val)
            {
                $tz_notification_rows['notice_'.$key]['notification_id'] = $val['notification_id'];
                $tz_notification_rows['notice_'.$key]['name'] = 'Nail Artists Admin';
                $tz_notification_rows['notice_'.$key]['message'] = $val['notification_title'];
                #Check if created date is not today.          
                $tz_notification_rows['notice_'.$key]['readable_date'] = dateformat($val['created_at'], 'F d, Y').' at '.dateformat($val['created_at'], 'h:i a');
                $tz_notification_rows['notice_'.$key]['readable_short_date_no_time'] = dateformat($val['created_at'], 'M. d, Y');
                $tz_notification_rows['notice_'.$key]['readable_short_date'] = dateformat($val['created_at'], 'M. d').' at '.dateformat($val['created_at'], 'h:i a');
                if(dateformat($val['created_at'], 'Y-m-d') == datenow($format='Y-m-d'))
                {
                    $tz_notification_rows['notice_'.$key]['readable_date'] = timeElapsedString($val['created_at']);
                }

                if(isset($notification_status[$val['notification_id']]['read'])
                    && !isset($notification_status[$val['notification_id']]['delete_at']))
                {
                    $tz_notification_rows['notice_'.$key]['read'] = 1;    
                }
            }

            // This will enable all Post of Admin will appear on top
            $user_notice_rows = array_merge($tz_notification_rows, $user_notice_rows);
        }

        if(sizeof($user_notice_rows) > 0)
        {
            // Count all Unread Notice
            $unread = 0;
            foreach($user_notice_rows as $key => $val)
            {
                if(!isset($val['read']))
                {
                    $unread++; 
                }
            }
            
            $return_data['unread_count'] = $unread;
            $return_data['rows'] = $user_notice_rows;
            // prearr($return_data);

            // Set the response and exit
            $this->response($return_data, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No notification were found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function detail_get()
    {
        $notification_id = $this->get('n_id');
        if(!empty($notification_id))
        {
            $row = $this->Base_model->search_one('notification_id="'.$notification_id.'"', 'tz_notification');
            #Check if created date is not today.          
            $row['readable_date'] = dateformat($row['created_at'], 'F d, Y').' at '.dateformat($row['created_at'], 'h:i a');
            $row['readable_short_date_no_time'] = dateformat($row['created_at'], 'M. d, Y');
            $row['readable_short_date'] = dateformat($row['created_at'], 'M. d').' at '.dateformat($row['created_at'], 'h:i a');
            if(dateformat($row['created_at'], 'Y-m-d') == datenow($format='Y-m-d'))
            {
                $row['readable_date'] = timeElapsedString($row['created_at']);
            }
            $this->response($row, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No notification were found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function read_get()
    {
        $user_id = $this->get('user_id');
        $notification_id = $this->get('n_id');
        if(!empty($user_id) && !empty($notification_id))
        {
            $user_to_notification_row = $this->Base_model->search_one('notification_id="'.$notification_id.'" AND user_id="'.$user_id.'"', 'tz_user_to_notification');
            if(!is_array($user_to_notification_row))
            {
                $tz_user_to_notification['user_id'] = $user_id;
                $tz_user_to_notification['notification_id'] = $notification_id;
                $tz_user_to_notification['read_at'] = datenow();
                $this->Base_model->insert($tz_user_to_notification, 'tz_user_to_notification');
            }

            $row = $this->Base_model->search_one('notification_id="'.$notification_id.'"', 'tz_notification');
            $this->response($row, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

    public function readtagpost_get()
    {
        $user_id = $this->get('user_id');
        $haku_id = $this->get('p');
        if(!empty($user_id) && !empty($haku_id))
        {
            $cond['user_id'] = $user_id;
            $cond['haku_id'] = $haku_id;
            $tz_user_to_haku_tag['read_at'] = datenow();
            $this->Base_model->update($tz_user_to_haku_tag, $cond, 'tz_user_to_haku_tag');

            $this->response([
                'status' => TRUE,
                'message' => 'Tag Post Read'
            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }
}