<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('DatalatihModel');
		$this->load->model('ObjekModel');
	}
	public function index()
	{
		$data_latih = $this->DatalatihModel->semuaDatalatih();
		$data['t_datalatih'] = count($data_latih);
    $data['t_kat_positif'] = count(array_filter($data_latih, function ($var) {
      return ($var['kat_sentimen'] == 'positif');
    }));
    $data['t_kat_negatif'] = count(array_filter($data_latih, function ($var) {
      return ($var['kat_sentimen'] == 'negatif');
    }));
    $data['t_objek'] = count($this->ObjekModel->semuaObjek());
		view('home/index', $data);
	}
}
