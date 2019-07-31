<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Documentation extends CI_Controller
{
    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     *        http://example.com/index.php/welcome
     *    - or -
     *        http://example.com/index.php/welcome/index
     *    - or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */

    private $res = array();

    public function __construct()
    {
        parent::__construct();
        //$this->load->Model('Help_model');
    }

    public function index(){
        redirect('/documentation/articles');
    }

    public function articles()
    {
        // Pass the site info
        $data['site_info'] = $this->config->item('site_info');
        $data['base_url'] = $this->config->item('base_url');
        $data['site_page'] = 'documentation';

        // Title
        $data['title_addition'] = 'Articles';

        // Load stuff
        $data['stylesheet'] = 'settings';
        $data['javascript'] = 'documentation';

        // Load header library
        //$this->load->library('ForgotPasswordSystem.php');

        // load the view
        $this->load->view('templates/header.php', $data);
        $this->load->view('home/documentation/index');
        $this->load->view('templates/footer.php');
    }

    public function sentences(){
        $data['site_info'] = $this->config->item('site_info');
        $data['base_url'] = $this->config->item('base_url');
        $data['site_page'] = 'documentation';

        // Title
        $data['title_addition'] = 'Articles';

        // Load stuff
        $data['stylesheet'] = 'settings';
        $data['javascript'] = 'documentation';

        // Load header library
        //$this->load->library('ForgotPasswordSystem.php');

        // load the view
        $this->load->view('templates/header.php', $data);
        $this->load->view('home/documentation/sentence');
        $this->load->view('templates/footer.php');
    }
}