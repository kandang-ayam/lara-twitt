<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Datauji extends CI_Controller
{

  public function __construct()
  {
    parent::__construct();
    $this->load->model('DatalatihModel');
    $this->load->model('ObjekModel');
  }
  public function index()
  {
    $this->form_validation->set_rules('nilai_k', 'nilai k', 'trim|required');
    $this->form_validation->set_rules('tweet', 'text tweet', 'required');
    $data = [
      'nilai_k' => '',
      'tweet' => '',
      'kategori' => '',
      'prepos_text' => ''
    ];
    if ($this->form_validation->run() == FALSE) {
      view('data_uji/index', $data);
    } else {

      $input_tweet = $this->input->post('tweet');
      $date_tweet = date('Y-m-d H:i:s');
      $data_uji = [
        'tweet' => $input_tweet
      ];
      $uji = uji_tweet($data_uji, $this->input->post('nilai_k'), 'uji_sentimen');
      $data = [
        'nilai_k' => $this->input->post('nilai_k'),
        'tweet' => $this->input->post('tweet'),
        'kategori' => $uji['kategori'],
        'prepos_text' => str_replace(',', ' ', $uji['prepos_text'])
      ];
      echo json_encode($data, true);
      // view('data_uji/index', $data);
    }
  }
  public function akurasi_form()
  {
    $data['nilai_k'] = '';
    $data['dt_akurasi'] = [];
    $data['objek'] = $this->ObjekModel->semuaObjek();
    $data['data_latih'] = $this->DatalatihModel->semuaDatalatih();

    $this->form_validation->set_rules('nilai_k', 'nilai k', 'trim|required');

    if ($this->form_validation->run() == FALSE) {
      view('data_uji/akurasi_form', $data);
    } else {

      $data['nilai_k'] = $this->input->post('nilai_k');
      $data['dt_akurasi'] = uji_tweet(NULL, $data['nilai_k'], 'akurasi');
      $data['tp'] = 0;
      $data['fn'] = 0;
      $data['fp'] = 0;
      $data['tn'] = 0;

      foreach ($data['dt_akurasi'] as $dt) {
        if ($dt['kat_prediksi'] == 'positif' && $dt['kat_aktual'] == 'positif') {
          $data['tp']++;
        }
        if ($dt['kat_prediksi'] == 'negatif' && $dt['kat_aktual'] == 'negatif') {
          $data['tn']++;
        }
        if ($dt['kat_prediksi'] == 'positif' && $dt['kat_aktual'] == 'negatif') {
          $data['fp']++;
        }
        if ($dt['kat_prediksi'] == 'negatif' && $dt['kat_aktual'] == 'positif') {
          $data['fn']++;
        }
      }
      echo json_encode($data);
    }
  }
  public function dt_akurasi()
  {
    $data['akurasi']['tp'] = 0;
    $data['akurasi']['fp'] = 0;
    $data['akurasi']['pnetral'] = 0;
    
    $data['akurasi']['tn'] = 0;
    $data['akurasi']['fn'] = 0;
    $data['akurasi']['nnetral'] = 0;

    $data['akurasi']['tnetral'] = 0;
    $data['akurasi']['netralp'] = 0;
    $data['akurasi']['netraln'] = 0;

    $data['akurasi']['total'] = 0;
    

    if ($this->input->post('filter')['objek'][0] != "") {
      $data_akurasi = [];
      $nilai_k = $this->input->post('filter')['nilai_k'];
      $data_akurasi = uji_tweet(NULL, $nilai_k, 'akurasi');

      $data['draw'] = 1;
      $data['recordsTotal'] = count($data_akurasi);
      $data['akurasi']['total'] = count($data_akurasi);
      $data['recordsFiltered'] = count($data_akurasi);
      $index = 0;
      foreach ($data_akurasi as $dt) {
        $d[$index]['tgl_tweet'] = $dt['tgl_tweet'];
        $d[$index]['tweet'] = $dt['tweet'];
        $d[$index]['prepos_text'] = $dt['prepos_text'];
        $d[$index]['kat_prediksi'] = $dt['kat_prediksi'];
        $d[$index]['kat_aktual'] = $dt['kat_aktual'];

        if ($dt['kat_prediksi'] == 'positif' && $dt['kat_aktual'] == 'positif') {
          $data['akurasi']['tp']++;
        }
        if ($dt['kat_prediksi'] == 'positif' && $dt['kat_aktual'] == 'negatif') {
          $data['akurasi']['fp']++;
        }
        if ($dt['kat_prediksi'] == 'positif' && $dt['kat_aktual'] == 'netral') {
          $data['akurasi']['pnetral']++;
        }

        if ($dt['kat_prediksi'] == 'negatif' && $dt['kat_aktual'] == 'negatif') {
          $data['akurasi']['tn']++;
        }
        if ($dt['kat_prediksi'] == 'negatif' && $dt['kat_aktual'] == 'positif') {
          $data['akurasi']['fn']++;
        }
        if ($dt['kat_prediksi'] == 'negatif' && $dt['kat_aktual'] == 'netral') {
          $data['akurasi']['nnetral']++;
        }

        if ($dt['kat_prediksi'] == 'netral' && $dt['kat_aktual'] == 'netral') {
          $data['akurasi']['tnetral']++;
        }
        if ($dt['kat_prediksi'] == 'netral' && $dt['kat_aktual'] == 'positif') {
          $data['akurasi']['netralp']++;
        }
        if ($dt['kat_prediksi'] == 'netral' && $dt['kat_aktual'] == 'negatif') {
          $data['akurasi']['netraln']++;
        }
        
        $index++;
      }
    } else {
      $data['draw'] = 0;
      $data['recordsTotal'] = 0;
      $data['recordsFiltered'] = 0;
      $d[] = "";
    }
    $data['data'] = $d;
    echo json_encode($data);
  }
}
