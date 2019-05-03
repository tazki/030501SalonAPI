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
class Comment extends REST_Controller {

  function __construct()
  {
      // Construct the parent class
      parent::__construct();

      $this->load->model('Base_model');
  }

  public function index_get()
  {
    $haku_id = $this->get('haku_id');
    $haku_id = (int) $haku_id;

    $order_by = 'ORDER BY `created_at` ASC;';
    $search_query = ' `haku_id` = "'.$haku_id.'"';
    $search_query .= ' AND `status` = "1"';
    $query = $this->db->query(
        'SELECT `hc`.*,
          `user_avatar`,
          `user_username`,
          `user_first_name`,
          `user_last_name`
        FROM `tz_haku_comment` `hc`
        LEFT JOIN `tz_users` `u` ON `u`.`user_id`=`hc`.`user_id`
        WHERE '.$search_query.$order_by//.' LIMIT '.$current_page.', '.$limit
    );
    $rows = $query->result_array();   
    if($query->num_rows() > 0)
    {
      $arr_tmp = array();
      foreach($rows as $key => $val)
      {
        $readable_date = dateformat($val['created_at'], 'M d').' at '.dateformat($val['created_at'], 'h:i a');
        if(empty($val['reply_comment_id']))
        {
          $arr_tmp[$val['comment_id']] = $val;
          $arr_tmp[$val['comment_id']]['readable_date'] = $readable_date;
        } 
      }

      #need to run after creating array for initial value.
      foreach($rows as $key => $val)
      {
        $readable_date = dateformat($val['created_at'], 'M d').' at '.dateformat($val['created_at'], 'h:i a');
        if(!empty($val['reply_comment_id']))
        {
          $arr_tmp[$val['reply_comment_id']]['comment_reply'][$val['comment_id']] = $val;
          $arr_tmp[$val['reply_comment_id']]['comment_reply'][$val['comment_id']]['readable_date'] = $readable_date;
        }
      }

      // Set the response and exit
      $this->response($arr_tmp, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }
    else
    {
        // Set the response and exit
        $this->response([
            'status' => FALSE,
            'message' => 'No Post were found'
        ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
    }
  }

  public function add_get()
  {
    $user_id = $this->get('user_id');
    $haku_id = $this->get('haku_id');
    $comment = $this->get('comment');
    $reply_comment_id = $this->get('reply_id');

    $post_data['user_id'] = $this->security->xss_clean($user_id);
    $post_data['haku_id'] = $this->security->xss_clean($haku_id);
    $post_data['comment'] = $this->security->xss_clean($comment);
    $post_data['created_at'] = datenow();
    $post_data['status'] = 1;

    if(!empty($reply_comment_id) && is_numeric($reply_comment_id))
    {
      $post_data['reply_comment_id'] = $reply_comment_id;
    }

    // prearr($post_data);die;
    $id = $this->Base_model->insert($post_data, 'tz_haku_comment');
    if(!empty($id))
    {
      #get current followed count
      $cond = array('haku_id' => $haku_id);
      $row = $this->Base_model->search_one($cond, 'tz_haku');
      $comment_count = ($row['haku_comment'] > 0) ? $row['haku_comment'] + 1 : 1;
      $comment_data = array('haku_comment' => $comment_count);
      $cond = array('haku_id' => $haku_id);
      $this->Base_model->update($comment_data, $cond, 'tz_haku');

      $message['status'] = 'success';
      $message['alert'] = 'Comment successfully save!';
    }
    else
    {
      $message['status'] = 'danger';
      $message['alert'] = 'Failed to Save';
    }

    $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code      
  }
/*
  public function likepost_get($haku_id='', $followed_by_user_id='')
  {
    #get current followed count
    $cond = array('haku_id' => $haku_id);
    $row = $this->Base_model->search_one($cond, 'tz_haku');
    $row['haku_like'] = ($row['haku_like'] > 0) ? $row['haku_like'] : 0;
    #check if user/article are being followed
    $cond = array(
        'haku_id' => $haku_id,
        'user_id' => $followed_by_user_id
    );
    $row_following = $this->Base_model->search_one($cond, 'tz_liked');
    if(is_array($row_following))
    {
        #delete view/followed count entry
        $this->Base_model->delete($cond, 'tz_liked');
        #deduct on total view/followed count
        $view_count = $row['haku_like'] - 1;
        $message['status'] = 'success';
        $message['alert'] = array('Post Successfully Unlike', 'success');
    }
    else
    {
        #add view/followed count
        $post_data['liked_at'] = dateNow();
        $post_data['haku_id'] = $haku_id;
        $post_data['user_id'] = $followed_by_user_id;
        $this->Base_model->insert($post_data, 'tz_liked');
        #add on total view/followed count
        $view_count = $row['haku_like'] + 1;
        $message['status'] = 'success';
        $message['alert'] = array('Post Successfully Like', 'success');
    }

    #update followed count
    $view_data = array('haku_like' => $view_count);
    $cond = array('haku_id' => $haku_id);
    $this->Base_model->update($view_data, $cond, 'tz_haku');
    $message['view_count'] = $view_count;
    $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
  }

  public function viewpost_get($haku_id='')
  {
    #get current followed count
    $cond = array('haku_id' => $haku_id);
    $row = $this->Base_model->search_one($cond, 'tz_haku');
    $row['haku_view_count'] = ($row['haku_view_count'] > 0) ? $row['haku_view_count'] : 0;    
    #update view count
    $view_count = $row['haku_view_count'] + 1;
    $view_data = array('haku_view_count' => $view_count);
    $cond = array('haku_id' => $haku_id);
    $this->Base_model->update($view_data, $cond, 'tz_haku');
    $message['view_count'] = $view_count;
    $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
  }
*/
}




