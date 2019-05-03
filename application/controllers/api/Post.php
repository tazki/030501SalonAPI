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
class Post extends REST_Controller {

  function __construct()
  {
      // Construct the parent class
      parent::__construct();

      $this->load->model('Base_model');
  }

  public function index_get()
  {
    $user_id = $this->get('user_id');
    $user_id = (int) $user_id;

    $haku_id = $this->get('post_id');
    $haku_id = (int) $haku_id;

    $list_type = $this->get('list_type');
    switch ($list_type)
    {
      case 'trending':
        $order_by = 'ORDER BY `haku_view_count` DESC';
      break;

      case 'ranking':
        $order_by = 'ORDER BY `haku_like` DESC';
      break;
      
      default:
        $order_by = 'ORDER BY `haku_created_at` DESC';
      break;
    }

    $search_string = $this->get('q');
    $search_query = '`haku_status` = "2"';
    $search_query .= ' AND (`followed_by_user_id` = "'.$user_id.'"';
    $search_query .= ' OR `h`.`user_id` = "'.$user_id.'")';
    // if($search_string !== NULL)
    // {
        // $search_query .= ' AND (`event_name` LIKE "%'.$search_string.'%"
        //     OR `event_description` LIKE "%'.$search_string.'%"
        //     OR `event_content` LIKE "%'.$search_string.'%"
        //     OR `event_location` LIKE "%'.$search_string.'%"
        // )';
    //   $search_query .= ' AND date_format(`event_start_date`, "%M%Y") = "'.$search_string.'"';
    // }
    if(!empty($haku_id))
    {
      $search_query .= ' AND `haku_id` = "'.$haku_id.'"';
    } 

    $limit = 5;
    $next_page = $this->get('next_page');
    if(!empty($next_page) && is_numeric($next_page))
    {
      $next_page = ($next_page - 1) * $limit;
    }
    else
    {
      $next_page = 0;
    }
    /*'SELECT COUNT(*) as `total_count`
        FROM tz_user_following `uf`
        INNER JOIN tz_haku `h` ON `h`.`user_id`=`uf`.`following_user_id`
        LEFT JOIN `tz_users` `u` ON `u`.`user_id`=`h`.`user_id`
        WHERE '.$search_query*/
    $query = $this->db->query(
        'SELECT COUNT(*) as `total_count`
        FROM tz_haku `h`
        LEFT JOIN tz_user_following `uf` ON `h`.`user_id`=`uf`.`following_user_id`
        LEFT JOIN `tz_users` `u` ON `u`.`user_id`=`h`.`user_id`
        WHERE '.$search_query
    );
    $rows = $query->result_array();
    $total_count = $rows[0]['total_count'];
    $pagination = pagination('post', $total_count, $next_page, $limit);
    $query = $this->db->query(
        'SELECT `h`.*,
          `user_avatar`,
          `user_username`,
          `user_first_name`,
          `user_last_name`,
          `user_verified_badge`
        FROM tz_haku `h`
        LEFT JOIN tz_user_following `uf` ON `h`.`user_id`=`uf`.`following_user_id`
        LEFT JOIN `tz_users` `u` ON `u`.`user_id`=`h`.`user_id`
        WHERE '.$search_query.$order_by.' LIMIT '.$next_page.', '.$limit
    );
    /*
    'SELECT `h`.*,
          `user_avatar`,
          `user_username`,
          `user_first_name`,
          `user_last_name`
        FROM tz_user_following `uf`
        INNER JOIN tz_haku `h` ON `h`.`user_id`=`uf`.`following_user_id`
        LEFT JOIN `tz_users` `u` ON `u`.`user_id`=`h`.`user_id`
        WHERE '.$search_query.$order_by.' LIMIT '.$next_page.', '.$limit
    */
    $rows = $query->result_array();
    if($query->num_rows() > 0)
    {
      $user_followed_rows = $this->Base_model->list_all_by_field('tz_user_following', 'following_user_id', '', '', '', '', 'followed_by_user_id="'.$user_id.'"', 'following_user_id');
      $user_liked_rows = $this->Base_model->list_all_by_field('tz_liked', 'haku_id', '', '', '', '', 'user_id="'.$user_id.'"', 'haku_id');
      $haku_tag_users = array();
      $post_id_checker = array();
      $clean_rows = array();
      foreach($rows as $key => $val)
      {
        if(!in_array($val['haku_id'], $post_id_checker) 
          && (isset($user_followed_rows[$val['user_id']]) || $val['user_id'] == $user_id))
        {
          $post_id_checker[$val['haku_id']] = $val['haku_id'];
          $clean_rows[$key] = $val;

          #Check if created date is not today.          
          $clean_rows[$key]['readable_date'] = dateformat($val['haku_created_at'], 'F d, Y').' at '.dateformat($val['haku_created_at'], 'h:i a');
          $clean_rows[$key]['readable_short_date_no_time'] = dateformat($val['haku_created_at'], 'M. d, Y');
          $clean_rows[$key]['readable_short_date'] = dateformat($val['haku_created_at'], 'M. d').' at '.dateformat($val['haku_created_at'], 'h:i a');
          if(dateformat($val['haku_created_at'], 'Y-m-d') == datenow($format='Y-m-d'))
          {
            $clean_rows[$key]['readable_date'] = timeElapsedString($val['haku_created_at']);
          }

          if(isset($user_liked_rows[$val['haku_id']]))
          {
            $clean_rows[$key]['is_liked'] = true;
          }

          if(!empty($val['haku_tag_user']))
          {
            $haku_tag_users_tmp = explode(',', $val['haku_tag_user']);
            $clean_rows[$key]['haku_tag_user'] = $haku_tag_users_tmp;
            $haku_tag_users = array_merge($haku_tag_users, $haku_tag_users_tmp);
          }
        }
      }
      
      if(is_array($haku_tag_users) && sizeof($haku_tag_users) > 0)
      {
        $haku_tag_users = array_unique($haku_tag_users);
        $search_query = '`user_id` IN('.implode(',', $haku_tag_users).')';
        $haku_tag_users_rows = $this->Base_model->list_all_by_field('tz_users', 'user_id', '', '', '', '', $search_query, 'user_id,user_username');
        // prearr($haku_tag_users_rows);

        foreach($clean_rows as $key => $val)
        {
          if(is_array($val['haku_tag_user']))
          {
            $haku_tag_users_tmp = array();
            foreach ($val['haku_tag_user'] as $skey => $sval)
            {
              if(isset($haku_tag_users_rows[$sval]))
              {
                $haku_tag_users_tmp[$skey] = $haku_tag_users_rows[$sval];
              }
            }

            $clean_rows[$key]['haku_tag_user'] = $haku_tag_users_tmp;
          }
        }
      }

      // Set the response and exit
      $return['total_page'] = $pagination['total_page'];
      $return['rows'] = $clean_rows;
      $this->response($return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }
    else
    {
        // Set the response and exit
        $this->response([
            'status' => FALSE,
            'message' => 'No Post were found'
        ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
    }
  }

  public function detail_get()
  {
      $post_id = $this->get('post_id');
      $search_query = 'post_id="'.$post_id.'"';
      $search_query .= ' AND `trashed_at` IS NULL';
      $row = $this->Base_model->list_all('tz_haku', '', '', '', '', $search_query);
      $this->response($row, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
  }

  public function uploadimg_post()
  {
    // prearr($_FILES['haku_image_uploader']);
    $message['image_filename'] = serialize($_FILES['haku_image_uploader']);
    #remove existing image if user only click remove button
    if(empty($_FILES['haku_image_uploader']['error']))
    {
      $image_filename = imageupload('haku_image_uploader');//, $post_data['haku_image']      
      if(!empty($image_filename))
      {
        // echo $this->data['uploader_error'];
        // die;
        $message['status'] = 'success';
        $message['alert'] = '';#No Message Needed
        $message['image_filename'] = base_url('uploads').'/'.$image_filename;
        //this is what iOS/Android image upload works on.
        echo $message['image_filename'];die;
      }
      else
      {
        $message['status'] = 'danger';
        $message['alert'] = 'Image Failed to Upload this time.';        
      }
    }
    $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
  }

  public function add_post()
  {
    $user_id = $this->get('user_id');
    $message['status'] = 'danger';
    $message['alert'] = 'No Data Received';

    $this->load->library('form_validation');
    $post_data = $this->input->post(null, false);
    if(sizeof($_POST) > 0)
    {
      $count = 0;
      // foreach($post_data as $field_name => $field_val)
      // {
      //   if(!is_array($field_val))
      //   {
      //     if($field_name == 'haku_description')
      //     {
      //       $config_data[$count]['field'] = $field_name;
      //       $config_data[$count]['label'] = 'Caption';
      //       $config_data[$count]['rules'] = 'trim|required';
      //       $count++;
      //     }
      //     elseif(!in_array($field_name, array('haku_image_uploader','haku_image','haku_image_is_remove','haku_location','haku_tag_user','haku_allow_comment')))
      //     {
      //       $config_data[$count]['field'] = $field_name;
      //       $config_data[$count]['label'] = ucwords(str_replace(array('haku_', '_'), array('', ' '), $field_name));
      //       $config_data[$count]['rules'] = 'trim|required';
      //       $count++;
      //     }
      //   }
      // }

      // $this->config_data = $config_data;
      // $this->form_validation->set_rules($this->config_data);
      // if($this->form_validation->run() == true)
      // {        
        if(is_array($post_data['haku_tag_user']) && sizeof($post_data['haku_tag_user']) > 0)
        {
          $haku_tag_user = $post_data['haku_tag_user'];
          $post_data['haku_tag_user'] = implode(',', $post_data['haku_tag_user']);
        }

        $post_data['user_id'] = $user_id;
        $post_data['haku_status'] = 2;
        $post_data['haku_created_at'] = datenow();
        $post_data['haku_modified_at'] = datenow();
        $id = $this->Base_model->insert($post_data, 'tz_haku');
        if(!empty($id))
        {
          // Update User Tag Post Notification
          $this->tagpost($haku_tag_user, $id);
          $message['status'] = 'success';
          $message['alert'] = 'Post successfully share!';
        }
        else
        {
          $message['status'] = 'danger';
          $message['alert'] = 'Failed to Save';
        }
      // }
      // else
      // {
      //   #array form variables need to be declare as array
      //   $message = array();
      //   $message['status'] = 'danger';
      //   // $message['alert'] = validation_errors('<span>', '</span>');
      //   foreach($post_data as $field_name => $field_val)
      //   {
      //     $error_msg = form_error($field_name, '<span class="error">', '</span>');
      //     if(!empty($error_msg))
      //     {
      //       $message['alert'][$field_name] = $error_msg;   
      //     }
      //   }
      // }
    }

    $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code      
  }

  public function edit_post()
  {
    $user_id = $this->get('user_id');
    $post_id = $this->get('post_id');
    $message['status'] = 'danger';
    $message['alert'] = 'No Data Received';

    $this->load->library('form_validation');
    $post_data = $this->input->post(null, false);
    if(sizeof($_POST) > 0)
    {
      // this will prevent haku_image become empty if user did not upload new photo
      if(empty($post_data['haku_image']))
      {
        unset($post_data['haku_image']);
      }

      $count = 0;
      if(is_array($post_data['haku_tag_user']) && sizeof($post_data['haku_tag_user']) > 0)
      {
        $haku_tag_user = $post_data['haku_tag_user'];
        $post_data['haku_tag_user'] = implode(',', $post_data['haku_tag_user']);
      }

      $post_data['haku_modified_at'] = datenow();
      $cond['haku_id'] = $post_id;
      $this->Base_model->update($post_data, $cond, 'tz_haku');
      // Update User Tag Post Notification
      $this->tagpost($haku_tag_user, $post_id);
      $message['status'] = 'success';
      $message['alert'] = 'Post successfully share!';
    }

    $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code      
  }

  private function tagpost($haku_tag_user, $post_id)
  {
    if(is_array($haku_tag_user) && sizeof($haku_tag_user) > 0)
    {
      foreach($haku_tag_user as $key => $val)
      {
        $user_to_haku_tag_row = $this->Base_model->search_one('haku_id="'.$post_id.'" AND user_id="'.$val.'"', 'tz_user_to_haku_tag');
        if(!is_array($user_to_haku_tag_row))
        {
          $tz_user_to_haku_tag['user_id'] = $val;
          $tz_user_to_haku_tag['haku_id'] = $post_id;
          $tz_user_to_haku_tag['created_at'] = datenow();
          $this->Base_model->insert($tz_user_to_haku_tag, 'tz_user_to_haku_tag');
        }
      }
    }
  }

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

  public function deletepost_get($haku_id='')
  {
    $cond = array('haku_id' => $haku_id);
    $row = $this->Base_model->search_one($cond, 'tz_haku');
    #remove old image to save server disk space
    imageremove($row['haku_image'], 'uploads');
    #delete view/followed count entry
    $this->Base_model->delete($cond, 'tz_haku');
    $message['status'] = 'success';
    $message['alert'] = array('Post Successfully Deleted', 'success');
    $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
  }

  public function untagpost_get($haku_id='', $user_id='')
  {
    #get current followed count
    $cond = array('haku_id' => $haku_id);
    $row = $this->Base_model->search_one($cond, 'tz_haku');
    $haku_tag_user = explode(',', $row['haku_tag_user']);
    $haku_tag_user = array_diff($haku_tag_user, array($user_id));
    $haku_tag_user = implode(',', $haku_tag_user);
    
    #update remaining tag users
    $view_data = array('haku_tag_user' => $haku_tag_user);
    $this->Base_model->update($view_data, $cond, 'tz_haku');

    $message['status'] = 'success';
    $message['alert'] = array('User Successfully Untag', 'success');
    $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
  }
}




