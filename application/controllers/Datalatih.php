<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Datalatih extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('DatalatihModel');
		$this->load->model('ObjekModel');
	}
	public function index()
	{
		$data['data_latih'] = $this->DatalatihModel->semuaDatalatih();
		$data['objek'] = $this->ObjekModel->semuaObjek();
		view('data_latih/index', $data);
	}
	public function form()
	{
		$data['objek'] = $this->ObjekModel->semuaObjek();
		view('data_latih/form', $data);
	}
	public function edit_objek($id_objek)
	{
		$data['detail'] = $this->ObjekModel->objekById($id_objek);
		$data['objek'] = $this->ObjekModel->semuaObjek();
		view('data_latih/form', $data);
	}

	public function input_datalatih()
	{
		if ($this->DatalatihModel->inputDatalatih()) {
			$this->session->set_flashdata('success', 'Data Latih Berhasil Ditambah');
		} else {
			$this->session->set_flashdata('danger', 'Data Latih Gagal Ditambah');
		}
		redirect('datalatih/form');
	}

	public function upload_datalatih()
	{
		$filename = $_FILES['file']['name'];
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if ($ext != 'json') {
			$this->session->set_flashdata('danger', 'Gagal Upload. Ekstensi yang diizinkan .json');
			// echo "gagal upload";
		}else{
			
			$file = $_FILES['file']['tmp_name'];
			$json = file_get_contents($file);
			$data_doc = json_decode($json, TRUE);
			// var_dump($data_doc);
			// die;
			$data_latih = uji_tweet($data_doc,'3','upload');
			if($this->DatalatihModel->uploadDatalatih($data_latih)){
				// $this->session->set_flashdata('success', count($data_latih).' Data Latih Berhasil Diupload');
				// echo "sukses upload";
			}else{
				$this->session->set_flashdata('danger', 'Gagal Upload. Kesalahan pada query');
				// echo "gagal upload";
			}
		}
		// redirect('datalatih/form');

	}
	public function simpan_objek()
	{
		$cekObjek = $this->ObjekModel->objekByIdTwitter($this->input->post('id_twitter'));

		if ($this->input->post('submit') == "Tambah") {
			if ($cekObjek) {
				$this->session->set_flashdata('danger', 'Gagal tambah. Objek Dengan Twitter ' . $cekObjek['id_twitter'] . ' sudah terdaftar');
				redirect('datalatih/form');
			}
			if ($this->ObjekModel->tambahObjek()) {
				$this->session->set_flashdata('success', 'Data Objek Berhasil Ditambah');
			} else {
				$this->session->set_flashdata('danger', 'Data Objek Gagal Ditambah');
			}
		} else {
			if ($this->input->post('id_twitter') != $this->input->post('id_twitter_lama')) {
				if ($cekObjek) {
					$this->session->set_flashdata('danger', 'Gagal edit. Objek Dengan Twitter ' . $cekObjek['id_twitter'] . ' sudah terdaftar');
					redirect('datalatih/form');
				}
			}
			if ($this->ObjekModel->editObjek()) {
				$this->session->set_flashdata('success', 'Data Objek Berhasil Diedit');
			} else {
				$this->session->set_flashdata('danger', 'Data Objek Gagal Diedit');
			}
		}
		redirect('datalatih/form');
	}

	public function hapus_objek($id_objek)
	{
		$objek = $this->ObjekModel->objekById($id_objek);

		if ($objek) {
			if ($this->ObjekModel->hapusObjek($id_objek)) {
				$this->session->set_flashdata('success', 'Data Objek Berhasil Dihapus');
			} else {
				$this->session->set_flashdata('success', 'Data Objek Gagal Dihapus');
			}
		}
		redirect('datalatih/form');;
	}
}
