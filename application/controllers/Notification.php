<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Notification extends CI_Controller {

		public function __construct() {
            parent::__construct();
            if (!($this->session->userdata('user_id'))) {
                redirect('Login');
            }
            if (!($_SESSION['uid'])){
                redirect('Login');
            }
            $this->load->helper(array('form', 'url'));
            $this->load->database();
            $this->load->library('SessionTimeout');
            $sessionTimeout = new SessionTimeout();
            $sessionTimeout->checkTimeOut();
		}

		public function index()
		{
		$data['site_info'] = $this->config->item('site_info');
        $data['base_url'] = $this->config->item('base_url');
        $data['site_page'] = 'notifications';

        // Title
        $data['title_addition'] = 'Notifications';

        // Load stuff
        $data['stylesheet'] = 'notifications';
        $data['javascript'] = 'notifications';


		$this->load->view('templates/header',$data);
        //$this->load->view('top_navigation');
        $this->load->view('notification');
        $this->load->view('templates/footer');

		}

		public function reload_page(){
		    $data = array();
            $show_result = '';
            if(isset($_SESSION['uid'])) {
                $results = $this->db->query("SELECT * FROM `notification` where user_id = '" . $_SESSION['uid'] . "' ORDER BY date LIMIT 100 ")->result();
                if (count($results) > 0) {
                    foreach ($results as $query) {
                        $show_result .= '<tr role="row" class="">
                                            <td class="text-center vartical-middle">
                                                <a href="' . $query->image . '" data-fancybox="images" data-caption="' . $query->title_name . '">
                                                    <img src="' . $query->image . '" style="width:60px;">    
                                                </a>
                                            </td>
                                         
                                             <td class="text-center verticle-middle" title="' . $query->title_name . '">
                                             
                                                <a target="_blank" href="http://amazon.com/dp/' . $query->asin . '">' . $query->title_name . '</a>
                                                
                                            </td>
                                             <td class="text-center verticle-middle">
                                             
                                              <a target="_blank" href="http://amazon.com/dp/' . $query->asin . '">' . $query->asin . '</a>
                                              
                                            </td>';
                        if (($query->amznotseller == "1")) {
                            $show_result .= '<td class="text-center b red verticle-middle">
                                                    <span style="color:green; font-size:25px;margin-left: -20px;">Yes</span>
                                                </td>';
                        }
                        if (($query->amznotseller == "0")) {
                            $show_result .= ' <td class="text-center b red verticle-middle">
                                                   <span style="color:black; font-size:25px;margin-left: -20px;">No</span>
                                                  </td>';
                        }
                        if (($query->sellerstock == "1")) {
                            $show_result .= '<td class="text-center b red verticle-middle">
                                                   <span style="color:green; font-size:25px;margin-left: -20px;">Yes</span>
                                                  </td>';
                        }
                        if (($query->sellerstock == "0")) {
                            $show_result .= '<td class="text-center b red verticle-middle">
                                                    <span style="color:black; font-size:25px;margin-left: -20px;">No</span>
                                                </td>';
                        }
                        $show_result .= '</tr> 
                                            <tr>
                                                <td style="width: 100%;padding: 20px;" colspan="5">
                                                    <div>
                                                        <ul class="activityHolder">';
                        if ($query->amznotseller == "1") {
                            $show_result .= '<li class="clearfix out-of-stock"  >
                                                            <div class="col2 col-lg-1">
                                                                <div class="date" > Just now </div>
                                                            </div>
                        
                                                            <div class="col1 col-lg-11">
                                                                <div class="contm">
                                                                    <div class="cont-col1">
                                                                        <div class="label label-sm label-info" style="font-size: 1.5em;background: #d27842">
                                                                            <i class="fa fa-smile-o" aria-hidden="true"></i>
                        
                                                                        </div>
                                                                    </div>
                                                                    <div class="cont-col2">
                                                                        <div class="desc">Amazon ran out of stock on ' . date("F d, Y", strtotime($query->date)) . '</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>';
                        }
                        if ($query->amznotseller == "0") {
                            $show_result .= '
                                                            <li class="clearfix in-stock-on">
                                                                 <div class="col2 col-lg-1">
                                                                     <div class="date" style="color: white;"> Just now </div>
                                                                 </div>
                                                                <div class="col1 col-lg-11">
                                                                    <div class="contm">
                                                                        <div class="cont-col1">
                                                                            <div class="label label-sm label-info" style="font-size: 1.5em;background: #aaa">
                                                                                <i class="fa fa-frown-o" aria-hidden="true"></i>
                            
                                                                            </div>
                                                                        </div>
                                                                        <div class="cont-col2">
                                                                            <div class="desc">Amazon back in stock on ' . date("F d, Y", strtotime($query->date)) . '</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                            
                                                            </li>';
                        }
                        $show_result .= '</ul>
                                                    </div>
                                                </td>';
                        $show_result .= '<td style="display: none;"></td>
                                                        <td style="display: none;"></td>
                                                        <td style="display: none;"></td>
                                                        <td style="display: none;"></td>';
                        $show_result .= '</tr>';
                    }
                }
                $data['result'] = 'success';
                $data['show_result'] = $show_result;
                echo json_encode($data);
                exit;
            } else {
                redirect('Dashboard');
            }

        }
}