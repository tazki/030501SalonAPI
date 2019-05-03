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
class Member extends REST_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        $this->load->model('Base_model');

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        // $this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
        // $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
        // $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
        // $this->methods['event_post']['limit'] = 50; // 50 requests per hour per user/key
    }
    
    public function index_get()
    {        
        $id = $this->security->xss_clean($this->get('user_id'));        
        // Find and return a single record for a particular user.
        $id = (int) $id;
        if($id <= 0)
        {
            // Invalid id, set the response and exit.
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }
        else
        {
            $row = $this->Base_model->search_one('user_id="'.$id.'"', 'tz_users');
            if(is_array($row))
            {
                #for Member Since
                $row['member_since'] = dateformat($row['user_created_at'], 'Y');

                $query = $this->db->query(
                    'SELECT COUNT(*) as count
                    FROM `tz_user_following`
                    WHERE `followed_by_user_id` ="'.$id.'"'
                );
                $user_following_count = $query->result_array(); 
                $row['user_following_count'] = 0;
                if(isset($user_following_count[0]))
                {
                    $row['user_following_count'] = $user_following_count[0]['count'];
                }

                $query = $this->db->query(
                    'SELECT `haku_id`,
                        `haku_image`
                    FROM `tz_haku`
                    WHERE `user_id` ="'.$id.'"
                    ORDER BY `haku_created_at` DESC'
                );
                $haku_rows = $query->result_array(); 
                $row['post_count'] = sizeof($haku_rows);
                $row['post_rows'] = $haku_rows;

                $row['user_json'] = json_decode($row['user_json'], true);
                $this->set_response($row, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
            else
            {
                $this->set_response([
                    'status' => FALSE,
                    'message' => 'User could not be found'
                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
        }
    }

    public function othermember_get()
    {        
        $user_id = $this->security->xss_clean($this->get('user_id'));
        $other_user_id = $this->security->xss_clean($this->get('other_user_id'));
        // Find and return a single record for a particular user.
        $other_user_id = (int) $other_user_id;
        if($other_user_id <= 0)
        {
            // Invalid id, set the response and exit.
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }
        else
        {
            $row = $this->Base_model->search_one('user_id="'.$other_user_id.'"', 'tz_users');
            if(is_array($row))
            {
                #for Member Since
                $row['member_since'] = dateformat($row['user_created_at'], 'Y');

                $query = $this->db->query(
                    'SELECT COUNT(*) as count
                    FROM `tz_user_following`
                    WHERE `followed_by_user_id` ="'.$other_user_id.'"'
                );
                $user_following_count = $query->result_array(); 
                $row['user_following_count'] = 0;
                if(isset($user_following_count[0]))
                {
                    $row['user_following_count'] = $user_following_count[0]['count'];
                }

                $query = $this->db->query(
                    'SELECT `following_user_id`
                    FROM `tz_user_following`
                    WHERE `followed_by_user_id` ="'.$user_id.'"
                    AND `following_user_id` ="'.$other_user_id.'"'
                );
                $user_following_user = $query->result_array(); 
                $row['is_followed'] = 0;
                if(isset($user_following_user[0]))
                {
                    $row['is_followed'] = 1;
                }

                $query = $this->db->query(
                    'SELECT `haku_id`,
                        `haku_image`
                    FROM `tz_haku`
                    WHERE `user_id` ="'.$other_user_id.'"'
                );
                $haku_rows = $query->result_array(); 
                $row['post_count'] = sizeof($haku_rows);
                $row['post_rows'] = $haku_rows;

                $row['user_json'] = json_decode($row['user_json'], true);
                $this->set_response($row, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
            else
            {
                $this->set_response([
                    'status' => FALSE,
                    'message' => 'User could not be found'
                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
        }
    }

    public function country_get()
    {
        $rows = $this->Base_model->list_all('tz_country', '', '', 'country_name', 'asc', 'country_status="active"', 'country_name');
        if(is_array($rows))
        {
            $this->set_response($rows, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            $this->set_response([
                'status' => FALSE,
                'message' => 'Event could not be found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function register_post()
    {
        $message['status'] = 'danger';
        $message['alert'] = 'No Data Received';

        $this->load->library('form_validation');
        $post_data = $this->input->post(null, false);
        if(sizeof($_POST) > 0)
        {
            $count = 0;
            foreach($post_data as $field_name => $field_val)
            {
                if(!is_array($field_val) && !in_array($field_val, array('tazki04@gmail.com')))
                {
                    if($field_name == 'user_email_address')
                    {
                        $config_data[$count]['field'] = $field_name;
                        $config_data[$count]['label'] = ucwords(str_replace(array('user_', '_'), array('', ' '), $field_name));
                        $config_data[$count]['rules'] = 'trim|required|valid_email';
                        $count++;
                    }
                    elseif($field_name == 'user_username')
                    {
                        $config_data[$count]['field'] = $field_name;
                        $config_data[$count]['label'] = ucwords(str_replace(array('user_', '_'), array('', ' '), $field_name));
                        $config_data[$count]['rules'] = 'trim|required|is_unique[tz_users.user_username]';
                        $count++;
                    }
                    elseif($field_name == 'user_confirm_password')
                    {
                        $config_data[$count]['field'] = $field_name;
                        $config_data[$count]['label'] = ucwords(str_replace(array('user_', '_'), array('', ' '), $field_name));
                        $config_data[$count]['rules'] = 'trim|required|matches[user_password]';
                        $count++;
                    }
                    elseif(!in_array($field_name, array('user_fax_number')))
                    {
                        $config_data[$count]['field'] = $field_name;
                        $config_data[$count]['label'] = ucwords(str_replace(array('user_', '_'), array('', ' '), $field_name));
                        $config_data[$count]['rules'] = 'trim|required';
                        $count++;
                    }
                }
            }        

            $this->config_data = $config_data;
            $this->form_validation->set_rules($this->config_data);
            if($this->form_validation->run() == true)
            {
                $cond['user_trashed_by'] = 0;
                $cond['user_email_address'] = $post_data['user_email_address'];
                $row = $this->Base_model->search_one($cond, 'tz_users');
                if(is_array($row))
                {
                    $message = array();
                    $message['status'] = 'danger';
                    $message['alert']['user_email_address'] = '<span class="error">The Email Address field must contain unique value.</span>';
                }
                else
                {
                    if(!isset($post_data['terms_and_condition']))
                    {
                        $message = array();
                        $message['status'] = 'danger';
                        $message['alert'] = 'You must agree to our terms and conditions and that you have read our privacy policy including our cookie use.';
                    }
                    else
                    {
                        $post_data['user_group_id'] = 5;
                        $post_data['user_current_status_id'] = 2;
                        $post_data['user_created_at'] = datenow();
                        $post_data['user_modified_at'] = datenow();
                        $post_data['user_password'] = do_hash($post_data['user_password'], 'md5');
                        $id = $this->Base_model->insert($post_data, 'tz_users');
                        if(!empty($id))
                        {                        
                            $message['user_id'] = $id;
                            $message['user_group_id'] = 5;
                            $message['status'] = 'success';
                            $message['alert'] = 'You have successfully registered!';

                            $this->load->library('email');
                            $config['useragent'] = 'Nail Artists Admin';
                            $config['mailtype'] = 'html';
                            $config['charset'] = 'utf-8';
                            $config['wordwrap'] = TRUE;
                            $this->email->initialize($config);
                            $this->email->from('no-reply@nailartists.app', '');
                            $this->email->to($post_data['user_email_address']);
                            $this->email->bcc('mark@nailartists.app');
                            
                            $this->email->subject('Successful Nail Artists App Registration');
                            $post_data['user_id'] = $id;
                            $post_data['encoded_email'] = urlencode(base64_encode($post_data['user_email_address']));
                            $this->email->message($this->load->view('api/mail_register_success', $post_data, true));
                            $this->email->send();
                        }
                        else
                        {
                            $message['status'] = 'danger';
                            $message['alert'] = 'Failed to Register';
                        }
                    }
                }
            }
            else
            {
                #array form variables need to be declare as array
                $message = array();
                $message['status'] = 'danger';
                // $message['alert'] = validation_errors('<span>', '</span>');
                foreach($post_data as $field_name => $field_val)
                {
                    $error_msg = form_error($field_name, '<span class="error">', '</span>');
                    if(!empty($error_msg))
                    {
                        $message['alert'][$field_name] = $error_msg;   
                    }
                }
            }
        }

        $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }

    public function registersocial_post()
    {
        $message['status'] = 'danger';
        $message['alert'] = 'No Data Received';

        $post_data = $this->input->post(null, false);
        if(sizeof($_POST) > 0)
        {
            $cond['user_trashed_by'] = 0;
            $cond['user_username'] = $post_data['user_username'];
            $row = $this->Base_model->search_one($cond, 'tz_users');
            if(is_array($row))
            {
                $message['user_id'] = $row['user_id'];
                $message['user_group_id'] = $row['user_group_id'];
                $message['status'] = 'success';
                $message['alert'] = '';
            }
            else
            {
                $post_data['user_group_id'] = 5;
                $post_data['user_current_status_id'] = 2;
                $post_data['user_created_at'] = datenow();
                $post_data['user_modified_at'] = datenow();
                $message['post_data'] = $post_data;
                $id = $this->Base_model->insert($post_data, 'tz_users');
                if(!empty($id))
                {    
                    $message['user_id'] = $id;
                    $message['user_group_id'] = 5;
                    $message['status'] = 'success';
                    $message['alert'] = 'You have successfully registered!';                
                }
                else
                {
                    $message['status'] = 'danger';
                    $message['alert'] = 'Failed to Register';
                }
            }
        }

        $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }

    public function updateprofile_post($id)
    {
        $id = $this->security->xss_clean($id);

        // Find and return a single record for a particular user.
        $id = (int) $id;
        $message['status'] = 'danger';
        $message['alert'] = 'No Data Received';

        $this->load->library('form_validation');
        $post_data = $this->input->post(null, false);
        if(sizeof($_POST) > 0)
        {
            $count = 0;
            foreach($post_data as $field_name => $field_val)
            {
                if(!is_array($field_val)
                    && !in_array($field_val, array('tazki04@gmail.com')))
                {
                    if($field_name == 'user_email_address')
                    {
                        $config_data[$count]['field'] = $field_name;
                        $config_data[$count]['label'] = ucwords(str_replace(array('user_', '_'), array('', ' '), $field_name));
                        $config_data[$count]['rules'] = 'trim|required|valid_email';
                        $count++;
                    }
                    elseif($field_name == 'user_password' && !empty($field_val))
                    {
                        $config_data[$count]['field'] = $field_name;
                        $config_data[$count]['label'] = ucwords(str_replace(array('user_', '_'), array('', ' '), $field_name));
                        $config_data[$count]['rules'] = 'trim|required';
                        $count++;
                    }
                    // elseif($field_name == 'user_confirm_password' && !empty($field_val))
                    // {
                    //     $config_data[$count]['field'] = $field_name;
                    //     $config_data[$count]['label'] = ucwords(str_replace(array('user_', '_'), array('', ' '), $field_name));
                    //     $config_data[$count]['rules'] = 'trim|required|matches[user_password]';
                    //     $count++;
                    // }
                    elseif(!in_array($field_name, array('user_password', 'user_mobile_number', 'haku_dataimage','user_avatar', 'haku_json')))
                    {
                        $config_data[$count]['field'] = $field_name;
                        $config_data[$count]['label'] = ucwords(str_replace(array('user_', '_'), array('', ' '), $field_name));
                        $config_data[$count]['rules'] = 'trim|required';
                        $count++;
                    }
                }
            }

            $this->config_data = $config_data;
            $this->form_validation->set_rules($this->config_data);
            if($this->form_validation->run() == true)
            {
                $cond['user_trashed_by'] = 0;
                $cond['user_email_address'] = $post_data['user_email_address'];
                $row = $this->Base_model->search_one($cond, 'tz_users');
                if(is_array($row) && $row['user_email_address'] != $post_data['user_email_address'])
                {
                    $message = array();
                    $message['status'] = 'danger';
                    $message['alert']['user_email_address'] = 'The Email Address field must contain unique value.';
                }
                else
                {
                    if(!empty($post_data['user_password']))
                    {
                        $post_data['user_password'] = do_hash($post_data['user_password'], 'md5');
                    }
                    else
                    {
                        unset($post_data['user_password']);
                    }
                    
                    // unset user_avatar to avoid image getting remove.
                    if(empty($post_data['user_avatar']))
                    {
                        unset($post_data['user_avatar']);
                    }

                    $post_data['user_json'] = json_encode($post_data['user_json']);
                    $post_data['user_modified_at'] = datenow();
                    $cond = '`user_id` = "'.$id.'"';
                    if($this->Base_model->update($post_data, $cond, 'tz_users'))
                    {
                        $message['status'] = 'success';
                        $message['alert'] = 'Data Successfully Saved';
                    }
                    else
                    {
                        $message['status'] = 'danger';
                        $message['alert'] = 'Data Failed to Save';
                    }
                }                
            }
            else
            {
                #array form variables need to be declare as array
                $message = array();
                $message['config_data'] = $config_data;
                $message['status'] = 'danger';
                // $message['alert'] = validation_errors('<span>', '</span>');
                foreach($post_data as $field_name => $field_val)
                {
                    $error_msg = form_error($field_name, '<span class="error">', '</span>');
                    if(!empty($error_msg))
                    {
                        $message['alert'][$field_name] = $error_msg;   
                    }
                }
            }
        }

        $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }
    
    public function login_post()
    {
        $this->load->library('form_validation');
        $post_data = $this->input->post(null, false);
        if(sizeof($_POST) > 0)
        {
            $message['status'] = 'danger';
            $message['alert'] = 'Fill all the fields.';

            $this->config_login = array(
                array(
                    'field'   => 'user_email_address',
                    'label'   => 'Email Address',
                    'rules'   => 'trim|required|valid_email'
                ),
                array(
                    'field'   => 'user_password',
                    'label'   => 'Password',
                    'rules'   => 'trim|required'
                )
            );
            $this->form_validation->set_rules($this->config_login);
            if($this->form_validation->run() == true)
            {
                $cond['user_trashed_by'] = 0;
                $cond['user_group_id'] = 5;
                $cond['user_current_status_id'] = 2;
                $cond['user_email_address'] = $post_data['user_email_address'];
                $cond['user_password'] = do_hash($post_data['user_password'], 'md5');
                $row = $this->Base_model->search_one($cond, 'tz_users');
                if(isset($row) && is_array($row))
                {
                    $cond = array('user_id' => $row['user_id']);
                    $user_logged_in = array('user_is_login' => 1);
                    $this->Base_model->update($user_logged_in, $cond, 'tz_users');
                    $row['user_language_id'] = 1;
                    $message['row'] = $row;
                    $message['status'] = 'success';
                    $message['alert'] = 'Welcome back!';
                }
                else
                {
                    $message['status'] = 'danger';
                    $message['alert'] = 'Email Address or Password Incorrect!';
                }
            }
            else
            {
                #array form variables need to be declare as array
                $message = array();
                $message['status'] = 'danger';
                foreach($post_data as $field_name => $field_val)
                {
                    $error_msg = form_error($field_name, '<span class="error">', '</span>');
                    if(!empty($error_msg))
                    {
                        $message['alert'][$field_name] = $error_msg;   
                    }
                }
            }
        }

        $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }

    public function activate_get($email)
    {
        // echo urlencode(base64_encode('tazki04@gmail.com')).'taz';
        $user_email_verified['user_email_verified'] = 1;
        $user_email_verified['user_current_status_id'] = 2;
        $cond = '`user_email_address` = "'.base64_decode(urldecode($email)).'"';
        if($this->Base_model->update($user_email_verified, $cond, 'tz_users'))
        {
            $message['status'] = 'success';
            $message['alert'] = 'Your account in Semicon Event has been activated Successfully!';
        }
        else
        {
            $message['status'] = 'danger';
            $message['alert'] = 'Failed to activate your account, <br / >Please contact Semicon Event Admin';
        }

        $this->load->view('api/mail_register_activate_success', $message);
    }

    public function forgotpassword_post()
    {
        $this->load->library('form_validation');
        $post_data = $this->input->post(null, false);
        if(sizeof($_POST) > 0)
        {
            $message['status'] = 'danger';
            $message['alert'] = 'Fill all the fields.';

            $count = 0;
            foreach($post_data as $field_name => $field_val)
            {
                $config_data[$count]['field'] = $field_name;
                $config_data[$count]['label'] = ucwords(str_replace(array('user_', '_'), array('', ' '), $field_name));
                $config_data[$count]['rules'] = 'trim|required|valid_email';
                $count++;
            }
            $this->config_data = $config_data;
            $this->form_validation->set_rules($this->config_data);
            if($this->form_validation->run() == true)
            {
                $cond['user_email_address'] = $post_data['user_email_address'];
                $row = $this->Base_model->search_one($cond, 'tz_users');
                if(isset($row) && is_array($row))
                {
                    $message['row'] = $row;
                    $message['status'] = 'success';
                    $message['alert'] = 'Password Reset already sent on your email';

                    $this->load->library('email');
                    $config['useragent'] = 'Event Admin';
                    $config['mailtype'] = 'html';
                    $config['charset'] = 'utf-8';
                    $config['wordwrap'] = TRUE;
                    $this->email->initialize($config);
                    $this->email->from('no-reply@nailartists.app', '');
                    $this->email->to($post_data['user_email_address']);
                    // $this->email->cc('another@another-example.com');
                    $this->email->bcc('mark@nailartists.app');

                    $this->email->subject('Password Reset');
                    $post_data['encoded_email'] = urlencode(base64_encode($row['user_email_address'].'|'.$row['user_id']));
                    $this->email->message($this->load->view('api/mail_forgot_password', $post_data, true));
                    $this->email->send();
                }
                else
                {
                    $message['status'] = 'danger';
                    $message['alert'] = 'Email Address is Incorrect!';
                }
            }
            else
            {
                #array form variables need to be declare as array
                $message = array();
                $message['status'] = 'danger';
                foreach($post_data as $field_name => $field_val)
                {
                    $error_msg = form_error($field_name, '<span class="error">', '</span>');
                    if(!empty($error_msg))
                    {
                        $message['alert'][$field_name] = $error_msg;
                    }
                }
            }
        }

        $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }

    public function resetpassword_get($email)
    {
        $message['form_url'] = site_url('api/member/resetpassword/'.$email);
        $this->load->view('api/mail_reset_password', $message);
    }

    public function resetpassword_post($email)
    {        
        $this->load->library('form_validation');
        $post_data = $this->input->post(null, false);
        if(sizeof($_POST) > 0)
        {
            $message['status'] = 'danger';
            $message['alert'] = 'Fill all the fields.';

            $count = 0;
            foreach($post_data as $field_name => $field_val)
            {
                if($field_name == 'user_confirm_password')
                {
                    $config_data[$count]['field'] = $field_name;
                    $config_data[$count]['label'] = ucwords(str_replace(array('user_', '_'), array('', ' '), $field_name));
                    $config_data[$count]['rules'] = 'trim|required|matches[user_password]';
                    $count++;
                }
                else
                {
                    $config_data[$count]['field'] = $field_name;
                    $config_data[$count]['label'] = ucwords(str_replace(array('user_', '_'), array('', ' '), $field_name));
                    $config_data[$count]['rules'] = 'trim|required';
                    $count++;
                }
            }
            $this->config_data = $config_data;
            $this->form_validation->set_rules($this->config_data);
            if($this->form_validation->run() == true)
            {                
                $user_password['user_password'] = do_hash($post_data['user_password'], 'md5');
                $arr_tmp = explode('|', base64_decode(urldecode($email)));
                $cond = '`user_email_address` = "'.$arr_tmp[0].'" AND `user_id` = "'.$arr_tmp[1].'"';
                if($this->Base_model->update($user_password, $cond, 'tz_users'))
                {
                    $message['status'] = 'success';
                    $message['alert'] = 'Password Successfully Modified!';
                }
                else
                {
                    $message['status'] = 'danger';
                    $message['alert'] = 'Failed to reset your password, <br / >Please contact Semicon Event Admin';
                }
            }
            else
            {
                #array form variables need to be declare as array
                $message = array();
                $message['status'] = 'danger';
                $message['alert'] = validation_errors('<span>', '</span>');
            }
        }

        $message['form_url'] = site_url('api/member/resetpassword/'.$email);
        $this->load->view('api/mail_reset_password', $message);
    }

    public function email_get()
    {
        $post_data['user_email_address'] = $this->get('email');

        $this->load->library('email');
        #email reset sending will be place here
        $config['useragent'] = 'Event Admin';
        $config['mailtype'] = 'html';
        $config['charset'] = 'utf-8';
        $config['wordwrap'] = TRUE;
        // $config['send_multipart'] = FALSE;
        $this->email->initialize($config);
        // $this->data['status_message'] = array('Unable to send email', 'warning');
        $this->email->from('no-reply@nailartists.app', '');
        $this->email->to($post_data['user_email_address']);
        // $this->email->cc('another@another-example.com');
        $this->email->bcc('mark@nailartists.app');

        // $this->email->subject('Successful Event App Registration');
        // $post_data['encoded_email'] = urlencode(base64_encode($post_data['user_email_address']));
        // $this->email->message($this->load->view('api/mail_register_success', $post_data, true));

        $this->email->subject('Password Reset');
        $post_data['encoded_email'] = urlencode(base64_encode($this->get('email').'|'.$this->get('id')));
        $this->email->message($this->load->view('api/mail_forgot_password', $post_data, true));
        if($this->email->send())
        {
            echo 'mail sent '.$post_data['user_email_address'];
            // setcookie('user_email_address',$post_data['user_email_address'],time()+86400);
            // $this->data['status_message'] = array('Password Reset already sent on your email', 'success');
        }
        else
        {
            echo 'mail failed to send<br>';
            echo $this->email->print_debugger();
        }
    }

    public function memberfollowers_get()
    {
        $user_id = $this->get('user_id');
        $user_id = (int) $user_id;

        $search_query = '`following_user_id` = "'.$user_id.'"';
        $search_query .= ' AND `user_current_status_id` = "2"';
        $search_string = $this->get('q');
        $search_string = $this->security->xss_clean($search_string);        
        if(!empty($search_string) && $search_string !== NULL)
        {
            $search_query .= ' AND (`user_first_name` LIKE "%'.$search_string.'%"
                OR `user_last_name` LIKE "%'.$search_string.'%"
                OR `user_username` LIKE "%'.$search_string.'%"
            )';
        }

        $query = $this->db->query(
            'SELECT `user_id`,
              `user_avatar`,
              `user_username`,
              `user_first_name`,
              `user_last_name`,
              `user_follower_count`
            FROM `tz_user_following` as `uf`
            LEFT JOIN `tz_users` as `u` ON `u`.`user_id`=`uf`.`followed_by_user_id`
            WHERE '.$search_query.'
            ORDER BY `user_follower_count` DESC;'
        );
        $rows = $query->result_array();   
        if($query->num_rows() > 0)
        {
            $user_following_rows = $this->Base_model->list_all_by_field('tz_user_following', 'following_user_id', '', '', '', '', 'followed_by_user_id="'.$user_id.'"', 'following_user_id');
            foreach($rows as $key => $val)
            {
                if(isset($user_following_rows[$val['user_id']]))
                {
                    $rows[$key]['is_followed'] = true;
                }
            }
            
            // Set the response and exit
            $this->response($rows, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No User were found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function memberlist_get()
    {
        $is_followed_user = $this->get('is_followed_user');        

        $user_id = $this->get('user_id');
        $user_id = (int) $user_id;

        $search_query = '`user_id` != "'.$user_id.'"';
        $search_query .= ' AND `user_current_status_id` = "2"';
        $search_string = $this->get('q');
        $search_string = $this->security->xss_clean($search_string);        
        if(!empty($search_string) && $search_string !== NULL)
        {
            $search_query .= ' AND (`user_first_name` LIKE "%'.$search_string.'%"
                OR `user_last_name` LIKE "%'.$search_string.'%"
                OR `user_username` LIKE "%'.$search_string.'%"
            )';
        } 

        $query = $this->db->query(
            'SELECT `user_id`,
              `user_avatar`,
              `user_username`,
              `user_first_name`,
              `user_last_name`,
              `user_follower_count`
            FROM `tz_users`
            WHERE '.$search_query.'
            ORDER BY `user_follower_count` DESC;'
        );
        $rows = $query->result_array();   
        if($query->num_rows() > 0)
        {
            // prearr($rows);die;
            $user_following_rows = $this->Base_model->list_all_by_field('tz_user_following', 'following_user_id', '', '', '', '', 'followed_by_user_id="'.$user_id.'"', 'following_user_id');
            
            $notFollowedUsers = array();
            foreach($rows as $key => $val)
            {
                if($is_followed_user == 'true')
                {
                    if(isset($user_following_rows[$val['user_id']]))
                    {
                        $rows[$key]['is_followed'] = true;
                    }
                    else
                    {
                        //this will remove all Unfollowed Users
                        unset($rows[$key]);
                    }
                }
                elseif($is_followed_user == 'false')
                {
                    if(isset($user_following_rows[$val['user_id']]))
                    {
                        //this will remove all Followed Users
                        unset($rows[$key]);
                    }
                    else
                    {
                        $notFollowedUsers[$val['user_id']] = $val['user_id'];
                    }
                }
            }

            // prearr($notFollowedUsers);
            // if($is_followed_user == 'false' && sizeof($notFollowedUsers) > 0)
            // {
            //     $notFollowedUsers = implode(',', $notFollowedUsers);
            //     $user_not_followed_post = $this->Base_model->list_all('tz_haku', '', '', '', '', 'user_id IN('.$notFollowedUsers.')', 'haku_id');
            //     prearr($user_not_followed_post);
            // }

            //SOS this is to keep array key to start with zero.
            $count = 0;
            $clean_rows = array();
            foreach($rows as $key => $val)
            {
                $clean_rows[$count] = $val;
                $count++;
            }
            //EOS this is to keep array key to start with zero.

          // Set the response and exit
          $this->response($rows, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No User were found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function followmember_get($following_id='', $followed_by_user_id='')
    {
        #get current followed count
        $cond = array('user_id' => $following_id);
        $row = $this->Base_model->search_one($cond, 'tz_users');
        $row['user_follower_count'] = ($row['user_follower_count'] > 0) ? $row['user_follower_count'] : 0;
        #check if user/article are being followed
        $cond = array(
            'following_user_id' => $following_id,
            'followed_by_user_id' => $followed_by_user_id
        );
        $row_following = $this->Base_model->search_one($cond, 'tz_user_following');
        if(is_array($row_following))
        {
            #delete view/followed count entry
            $this->Base_model->delete($cond, 'tz_user_following');
            #deduct on total view/followed count
            $view_count = $row['user_follower_count'] - 1;
            $message['status'] = 'success';
            $message['alert'] = array('User Successfully Unfollow', 'success');
        }
        else
        {
            #add view/followed count
            $post_data['followed_at'] = dateNow();
            $post_data['following_user_id'] = $following_id;
            $post_data['followed_by_user_id'] = $followed_by_user_id;
            $this->Base_model->insert($post_data, 'tz_user_following');
            #add on total view/followed count
            $view_count = $row['user_follower_count'] + 1;
            $message['status'] = 'success';
            $message['alert'] = array('User Successfully Follow', 'success');
        }

        #update followed count
        $view_data = array('user_follower_count' => $view_count);
        $cond = array('user_id' => $following_id);
        $this->Base_model->update($view_data, $cond, 'tz_users');
        $message['view_count'] = $view_count;
        $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code        
    }

    public function postlistlike_get()
    {
        $user_id = $this->get('user_id');
        $user_id = (int) $user_id;
        $post_id = $this->get('post_id');
        $post_id = (int) $post_id;

        $search_query = '`haku_id` = "'.$post_id.'"';
        $search_query .= ' AND `unliked_at` IS NULL';
        $search_string = $this->get('q');
        $search_string = $this->security->xss_clean($search_string);        
        if(!empty($search_string) && $search_string !== NULL)
        {
            $search_query .= ' AND (`user_first_name` LIKE "%'.$search_string.'%"
                OR `user_last_name` LIKE "%'.$search_string.'%"
                OR `user_username` LIKE "%'.$search_string.'%"
            )';
        } 

        $query = $this->db->query(
            'SELECT `l`.`user_id`,
              `user_avatar`,
              `user_username`,
              `user_first_name`,
              `user_last_name`,
              `user_follower_count`
            FROM `tz_liked` `l`
            LEFT JOIN `tz_users` `u` ON `u`.`user_id`=`l`.`user_id`
            WHERE '.$search_query.'
            ORDER BY `liked_at` DESC;'
        );
        $rows = $query->result_array();   
        if($query->num_rows() > 0)
        {
            $user_following_rows = $this->Base_model->list_all_by_field('tz_user_following', 'following_user_id', '', '', '', '', 'followed_by_user_id="'.$user_id.'"', 'following_user_id');

            $notFollowedUsers = array();
            foreach($rows as $key => $val)
            {
                if(isset($user_following_rows[$val['user_id']]))
                {
                    $rows[$key]['is_followed'] = true;
                }
            }

            //SOS this is to keep array key to start with zero.
            $count = 0;
            $clean_rows = array();
            foreach($rows as $key => $val)
            {
                $clean_rows[$count] = $val;
                $count++;
            }
            //EOS this is to keep array key to start with zero.

          // Set the response and exit
          $this->response($rows, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No User were found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function uploadimg_post()
    {
        // prearr($_FILES['user_avatar_uploader']);
        $message['image_filename'] = serialize($_FILES['user_avatar_uploader']);
        #remove existing image if user only click remove button
        if(empty($_FILES['user_avatar_uploader']['error']))
        {
        $image_filename = imageupload('user_avatar_uploader');//, $post_data['user_avatar']      
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

    public function deactivateaccount_get()
    {
        $user_id = $this->get('user_id');
        $user_id = (int) $user_id;
        $user_data['user_trashed_by'] = $user_id;
        $user_data['user_trashed_at'] = datenow();
        $cond = '`user_id` = "'.$user_id.'"';
        $this->Base_model->update($user_data, $cond, 'tz_users');
        $message['status'] = 'success';
        $message['alert'] = array('Account Successfully Deactivated', 'success');
        $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }

    public function deactivateaccountconfirm_post()
    {
        $this->load->library('form_validation');
        $post_data = $this->input->post(null, false);
        if(sizeof($_POST) > 0)
        {
            $message['status'] = 'danger';
            $message['alert'] = 'Fill all the fields.';

            $this->config_login = array(
                array(
                    'field'   => 'user_password',
                    'label'   => 'Password',
                    'rules'   => 'trim|required'
                )
            );
            $this->form_validation->set_rules($this->config_login);
            if($this->form_validation->run() == true)
            {
                $cond['user_trashed_by'] = 0;
                $cond['user_group_id'] = 5;
                $cond['user_current_status_id'] = 2;
                $cond['user_id'] = $post_data['user_id'];
                $cond['user_password'] = do_hash($post_data['user_password'], 'md5');
                $row = $this->Base_model->search_one($cond, 'tz_users');
                if(isset($row) && is_array($row))
                {
                    $message['status'] = 'success';
                    $message['alert'] = 'Password Confirm';
                }
                else
                {
                    $message['status'] = 'danger';
                    $message['alert'] = 'Password Incorrect!';
                }
            }
            else
            {
                #array form variables need to be declare as array
                $message = array();
                $message['status'] = 'danger';
                foreach($post_data as $field_name => $field_val)
                {
                    $error_msg = form_error($field_name, '<span class="error">', '</span>');
                    if(!empty($error_msg))
                    {
                        $message['alert'][$field_name] = $error_msg;   
                    }
                }
            }
        }

        $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }
 
    public function reporterror_post()
    {
        $message['status'] = 'danger';
        $message['alert'] = 'Fill all the fields.';
        
        $this->load->library('form_validation');
        $post_data = $this->input->post(null, false);
        if(sizeof($_POST) > 0)
        {
            $this->config_data = array(
                array(
                    'field'   => 'subject',
                    'label'   => 'Subject',
                    'rules'   => 'trim|required'
                ),
                array(
                    'field'   => 'description',
                    'label'   => 'Description',
                    'rules'   => 'trim|required'
                )
            );
            $this->form_validation->set_rules($this->config_data);
            if($this->form_validation->run() == true)
            {
                $post_data['created_at'] = datenow();
                $post_data['created_by'] = $post_data['user_id'];
                $id = $this->Base_model->insert($post_data, 'tz_bug_reports');
                $message['status'] = 'success';
                $message['alert'] = 'Problem Submitted';
            }
            else
            {
                #array form variables need to be declare as array
                $message = array();
                $message['status'] = 'danger';
                foreach($post_data as $field_name => $field_val)
                {
                    $error_msg = form_error($field_name, '<span class="error">', '</span>');
                    if(!empty($error_msg))
                    {
                        $message['alert'][$field_name] = $error_msg;   
                    }
                }
            }
        }

        $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }

    public function translation_get()
    {
        $translate['version'] = '1.0.0';
        $translate['lang']['en']['nailArtists'] = 'Nail Artists';
        $translate['lang']['en']['latest'] = 'Latest';
        $translate['lang']['en']['trending'] = 'Trending';
        $translate['lang']['en']['ranking'] = 'Nail Artists';
        $translate['lang']['en']['like'] = 'like';
        $translate['lang']['en']['likes'] = 'likes';

        $translate['lang']['ja']['nailArtists'] = 'ネイルアーティスト';
        $translate['lang']['ja']['latest'] = '最新';
        $translate['lang']['ja']['trending'] = 'トレンド';
        $translate['lang']['ja']['ranking'] = 'ランキング';
        $translate['lang']['ja']['like'] = 'ライク';
        $translate['lang']['ja']['likes'] = 'ライク';

        $translate['lang']['sp']['nailArtists'] = 'Artistas de uñas';
        $translate['lang']['sp']['latest'] = 'Más reciente';
        $translate['lang']['sp']['rrending'] = 'Tendencias';
        $translate['lang']['sp']['ranking'] = 'Clasificación';
        $translate['lang']['sp']['like'] = 'Me gusta';
        $translate['lang']['sp']['likes'] = 'Me gusta';
        $this->set_response($translate, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }
}
