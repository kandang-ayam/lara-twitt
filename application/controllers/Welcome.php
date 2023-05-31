<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('DatalatihModel');
	}
	public function index()
	{
		// $data['data_latih'] = $this->DatalatihModel->semuaDatalatih();
		$this->load->view('welcome_message');
	}
	public function tes_hitung()
	{
		$data['data_doc'] = $this->DatalatihModel->semuaDatalatih();
		$this->load->view('home/tes_hitung', $data);
	}
	public function add_datalatih()
	{
		$data['data_doc'] = $this->DatalatihModel->semuaDatalatih();
		$this->load->view('home/add_datalatih', $data);
	}
}
