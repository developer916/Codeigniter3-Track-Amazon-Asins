<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

class Report extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!($this->session->userdata('user_id'))) {
            redirect('Login');
        }
        if (!($_SESSION['uid'])){
            redirect('Login');
        }

        $this->load->library('SessionTimeout');
        $sessionTimeout = new SessionTimeout();
        $sessionTimeout->checkTimeOut();
    }

    public function index()
    {

        $user_id = ($this->session->userdata('user_id'));

        $data['site_info'] = $this->config->item('site_info');
        $data['base_url'] = $this->config->item('base_url');
        $data['site_page'] = 'reports';

        // Title
        $data['title_addition'] = 'Reports';

        //Get product
        $data['products'] = $this->db
            ->from('amaz_aug')
            ->where('user_id', $user_id)
            ->get()
            ->result();

        // Load stuff
        $data['stylesheet'] = 'reports';
        $data['javascript'] = 'reports';

        // Load header library
        //$this->load->library('ForgotPasswordSystem.php');

        // load the view
        $this->load->view('templates/header.php', $data);
        $this->load->view('report', $data);
        $this->load->view('templates/footer.php');
    }

    public function export($type = 1)
    {
        $user_id = ($this->session->userdata('user_id'));
        $startDate = $this->input->get('startDate', TRUE);
        $endDate = $this->input->get('endDate', TRUE);
        $asins = $this->input->get('asin', TRUE);

        $asins = implode("','", explode(',', $asins));
        if($startDate) $startDate = date('Y-m-d', strtotime($startDate));
        else $startDate = false;
        if(!$endDate) $endDate = date('Y-m-d');

        $query = "SELECT A.image, A.title_name, A.asin, A.sellerstock, A.amznotseller, A.date, 
                    IFNULL(DATEDIFF(A.date, (SELECT MAX(B.date) FROM notification B WHERE B.date < A.date AND A.user_id = B.user_id AND A.asin = B.asin)), 0) `days` 
                    FROM notification A 
                  WHERE A.user_id = $user_id AND A.asin IN ('$asins') ";
        if($endDate) $query .= " AND A.date BETWEEN '$startDate' AND '$endDate' ";
        $query .= " ORDER BY A.date DESC";

        $reports = $this->db->query($query)->result();

        if($type == 2) {

            $dompdf = new Dompdf();
            $pdfContents = $this->load->view('report_pdf', array('reports' => $reports), true);
            $dompdf->loadHtml($pdfContents);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream();
        } else if($type == 3) {

            error_reporting(E_ALL);
            ini_set('display_errors', TRUE);
            ini_set('display_startup_errors', TRUE);
            date_default_timezone_set('Europe/London');

            if (PHP_SAPI == 'cli')
                die('This example should only be run from a Web Browser');

            /** Include PHPExcel */
            require_once APPPATH . 'libraries/PHPExcel/Classes/PHPExcel.php';


            // Create new PHPExcel object
            $objPHPExcel = new PHPExcel();

            // Set document properties
            $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
                ->setLastModifiedBy("Maarten Balliauw")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");


            // Add some data
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Title')
                ->setCellValue('B1', 'ASIN')
                ->setCellValue('C1', 'Out of Stock Date')
                ->setCellValue('D1', 'Back in Stock Date')
                ->setCellValue('E1', 'Number days Amazon Out of Stock');

            $index = 3;
            foreach ($reports as $report) {

                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue("A$index", $report->title_name)
                    ->setCellValue("B$index", $report->asin)
                    ->setCellValue("C$index", ($report->amznotseller == '1' ? date('Y-m-d', strtotime($report->date)) : ''))
                    ->setCellValue("D$index", ($report->amznotseller == '0' ? date('Y-m-d', strtotime($report->date)) : ''))
                    ->setCellValue("E$index", $report->days);

                $index++;
            }

            // Rename worksheet
            $objPHPExcel->getActiveSheet()->setTitle('Reports');


            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);


            // Redirect output to a client’s web browser (Excel5)
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="01simple.xls"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');

            // If you're serving to IE over SSL, then the following may be needed
            header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header ('Pragma: public'); // HTTP/1.0

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
        } else {

            //Render views
            $data = array();
            $data['site_info'] = $this->config->item('site_info');
            $data['base_url'] = $this->config->item('base_url');
            $data['site_page'] = 'reports';

            // Title
            $data['title_addition'] = 'Report results';
            // Load stuff
            $data['stylesheet'] = 'reports';
            $data['javascript'] = 'reports';
            $data['reports'] = $reports;

            // load the view
            $this->load->view('templates/header.php', $data);
            $this->load->view('report_result', $data);
            $this->load->view('templates/footer.php');
        }
    }
}