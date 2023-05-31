<?php
ini_set('memory_limit', '-1');
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('tanggal_indo')) {
  function tanggal_indo($date)
  {
    date_default_timezone_set('Asia/Jakarta');
    // array hari dan bulan
    // $Hari = array("Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu");
    $Bulan = array("Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des");

    // pemisahan tahun, bulan, hari, dan waktu
    $tahun = substr($date, 0, 4);
    $bulan = substr($date, 5, 2);
    $tgl = substr($date, 8, 2);
    $waktu = substr($date, 11, 5);
    $hari = date("w", strtotime($date));
    // $result = $Hari[$hari].", ".$tgl." ".$Bulan[(int)$bulan-1]." ".$tahun." ".$waktu;
    $result = $tgl . " " . $Bulan[(int)$bulan - 1] . " " . $tahun . " " . $waktu;

    return $result;
  }
}

function view($page = null, $data = null)
{
  $ci = get_instance();

  $ci->load->view('templates/header', $data);
  $ci->load->view('templates/topbar', $data);
  // $ci->load->view('templates/navbar', $data);
  $ci->load->view($page, $data);
  $ci->load->view('templates/footer', $data);
}
function format_rupiah($number)
{

  if ($number == '') $number = 0;
  return number_format($number, 0, '.', ',');
}

function is_logged_in_admin()
{
  $ci = get_instance();

  if (!$ci->session->userdata('admin-iduser')) {
    $ci->session->set_flashdata('danger', 'Silahkan login terlebih dahulu');
    redirect('/');
  }
}

function flash()
{
  $ci = get_instance();
  if ($ci->session->flashdata('danger')) {
    echo '<div class="alert alert-danger alert-styled-left alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    ' . $ci->session->flashdata('danger') . '.
                </div>';
  }
  if ($ci->session->flashdata('success')) {
    echo '<div class="alert alert-success alert-styled-left alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    ' . $ci->session->flashdata('success') . '.
                </div>';
  }
}

function uji_tweet($data_uji = null, $p_nilai_k, $type)
{
  // error_reporting(1);
  $ci = get_instance();
  $ci->load->model('DatalatihModel');
  $stopword = base_url() . "assets/stopword.txt";
  $sw = file_get_contents($stopword);

  if ($type == "uji_sentimen") {
    $data_doc = $ci->DatalatihModel->semuaDatalatih();
  } else if ($type == "akurasi") {
    $data_doc = $ci->DatalatihModel->datalatihByObjek();
  } else {
    $data_doc = $data_uji;
    // var_dump($data_doc);
    // die;
  }

  if ($sw) {
    $arrStopword = explode("\n", $sw);
  }
  $merge_tweet = [];
  $doc = $data_doc;
  if ($type == 'akurasi' || $type == 'upload') {
    if ($type == "upload") {
      $f_positif = base_url() . "assets/kata_positif.txt";
      $f_negatif = base_url() . "assets/kata_negatif.txt";
      $kata_positif = file_get_contents($f_positif);
      $kata_negatif = file_get_contents($f_negatif);
      if ($kata_positif) {
        $arrKataPositif = explode("\n", $kata_positif);
        $arrKataPositif = array_map('ltrim', $arrKataPositif);
        $arrKataPositif = array_map('rtrim', $arrKataPositif);
      }
      if ($kata_negatif) {
        $arrKataNegatif = explode("\n", $kata_negatif);
        $arrKataNegatif = array_map('ltrim', $arrKataNegatif);
        $arrKataNegatif = array_map('rtrim', $arrKataNegatif);
      }
    }

    $data_uji = $doc;
  }
  // echo "TOTAL DATA = " . count($data_uji) . '<br/>';
  $n = 1;
  foreach ($data_uji as $dt) {
    if ($type == 'uji_sentimen' || $type == 'upload') {
      if ($type == 'upload') {
        $data = [
          'created_at' => $dt['created_at'],
          'text' => $dt['text'],
        ];
      } else {
        $data = [
          'created_at' => '',
          'text' => $data_uji['tweet'],
        ];
      }

      // PRE PROCESSING
      $str = Cleansing($data['text']);
      // case folding
      $strCf = Case_Folding($str);
      // tokenizing
      $strTkn = Tokenizing($strCf);
      $expStrTkn = explode(" ", $strTkn);
      $prepos_text = "";
      // REMOVE STOPWORD DAN STEMMING
      $jmlKataPositif = 0;
      $jmlKataNegatif = 0;
      $jmlKataNetral = 0;
      foreach ($expStrTkn as $tkn) {
        if (!in_array($tkn, preg_replace("/[^a-z-?! ]/i", "", $arrStopword))) {
          $kata = trim($tkn);
          $stemming = Nazief_Stemming($kata);
          if ($stemming != '') {
            $prepos_text .= $stemming . ',';
            if ($type == "upload") {
              if (in_array($stemming, $arrKataPositif)) {
                $jmlKataPositif++;
              } else if (in_array($stemming, $arrKataNegatif)) {
                $jmlKataNegatif++;
              } else {
                $jmlKataNetral++;
              }
            }
          }
        }
      }
      $prepos_text = substr($prepos_text, 0, -1);
      if (strlen($prepos_text) < 3) {
        continue;
      }
    } else {
      $data = [
        'created_at' => '',
        'text' => $dt['tweet'],
      ];
      $prepos_text = $dt['prepos_text'];
    }
    // END OF PRE PROCESSING
    if ($type != 'upload') {
      $data_uji = [
        'id_data' => 0,
        'id_objek' => 1,
        'tgl_tweet' => '',
        'tweet' => $data['text'],
        'prepos_text' => $prepos_text,
        'kat_sentimen' => NULL
      ];
      // PERSIAPAN PEMBOBOTAN TF-IDF
      // TAMBAHKAN KALIMAT DATA UJI KE ARRAY DATA LATIH
      // if ($type == 'uji_sentimen') {
      array_push($doc, $data_uji);
      // }
      $jml_doc = count($doc);
      // echo "JML DOC = " . $jml_doc . '<br/>';
      foreach ($doc as $d) {
        $tweet = explode(',', $d['prepos_text']);
        foreach ($tweet as $perkata) {
          array_push($merge_tweet, $perkata);
        }
      }
      $perkata = array_count_values($merge_tweet);
      // PENGHITUNGAN BOBOT TF-IDF
      $indexSkalar = 0;
      foreach ($perkata as $key => $value) {
        $arrSkalar[$indexSkalar][0] = $key;
        // Nilai TF-IDF
        for ($i = 0; $i < $jml_doc; $i++) {
          $nilai_tf_idf = 0;
          $kata_sama = array_filter(explode(',', $doc[$i]['prepos_text']), function ($v, $k) use ($key) {
            return $v == $key;
          }, ARRAY_FILTER_USE_BOTH);
          $jml_kata_sama = count($kata_sama);

          if ($jml_kata_sama > 0) {
            $nilai_tf_idf = (round(log10($jml_doc / $jml_kata_sama), 5) + 1);
          }
          $arrSkalar[$indexSkalar][$i + 1] = $nilai_tf_idf;
        }
        $indexSkalar++;
      }

      // JUMLAHKAN NILAI SKALAR
      $t_skalar = [];
      for ($i = 1; $i < $jml_doc; $i++) {
        $t_skalar[$i]['nilai'] = 0;
      }
      foreach ($arrSkalar as $skalar) {
        for ($i = 1; $i < $jml_doc; $i++) {
          $n_skalar = round($skalar[$jml_doc] * $skalar[$i], 5);
          // jumlahkan nilai skalar setiap kolom
          $t_skalar[$i]['nilai'] += $n_skalar;
          $t_skalar[$i]['kat'] = $doc[$i - 1]['kat_sentimen'];
        }
      }
      // HITUNG PANJANG VEKTOR
      $t_vektor = [];
      for ($i = 0; $i < $jml_doc; $i++) {
        $t_vektor[$i] = 0;
      }
      foreach ($perkata as $key => $value) {
        for ($i = 0; $i < $jml_doc; $i++) {

          $n_vektor = 0;
          $kata_sama = array_filter(explode(',', $doc[$i]['prepos_text']), function ($v, $k) use ($key) {
            return $v == $key;
          }, ARRAY_FILTER_USE_BOTH);
          $jml_kata_sama = count($kata_sama);

          if ($jml_kata_sama > 0) {
            $n_vektor = round(pow((round(log10($jml_doc / $jml_kata_sama), 5) + 1), 2), 5);
          }
          // JUMLAHKAH NILAI VEKTOR SETIAP KOLOM
          $t_vektor[$i] += $n_vektor;
        }
      }
      // HITUNG AKAN VEKTOR
      $akarVektor = [];
      $i = 0;
      foreach ($t_vektor as $total) {
        $akarVektor[$i] = round(sqrt($total), 3);
        $i++;
      }
      // HITUNG NILAI COSINE SIMILARITY
      $i = 0;
      $arrCosim = [];
      foreach ($t_skalar as $skalar) {
        $n_cosim = round($skalar['nilai'] / ($akarVektor[$jml_doc - 1] * $akarVektor[$i]), 3);
        $arrCosim[$i]['nilai'] = $n_cosim;
        $arrCosim[$i]['kat'] = $skalar['kat'];
        $i++;
      }

      // HITUNG NILAI K
      $nilai_k = $p_nilai_k;
      rsort($arrCosim);

      $arrSejumlahK = [];
      for ($i = 0; $i < $nilai_k; $i++) {
        array_push($arrSejumlahK, $arrCosim[$i]);
      }

      $cosim_positif = array_filter($arrSejumlahK, function ($var) {
        return ($var['kat'] == 'positif');
      });
      $cosim_negatif = array_filter($arrSejumlahK, function ($var) {
        return ($var['kat'] == 'negatif');
      });

      if (count($cosim_positif) > count($cosim_negatif)) {
        $kat_sentimen = "positif";
      } else if (count($cosim_positif) < count($cosim_negatif)) {
        $kat_sentimen = "negatif";
      } else {
        $kat_sentimen = "netral";
      }
      //   die;
      if ($type == 'uji_sentimen') {
        $data_return = [
          'prepos_text' => $prepos_text,
          'kategori' => $kat_sentimen
        ];
      } else {
        $data_return[$n] = [
          'id_data' => $dt['id_data'],
          'id_objek' => $dt['id_objek'],
          'tgl_tweet' => $dt['tgl_tweet'],
          'tweet' => $dt['tweet'],
          'prepos_text' => $dt['prepos_text'],
          'kat_prediksi' => $dt['kat_sentimen'],
          'kat_aktual' => $kat_sentimen,
        ];
      }
    } else {

      // $kategori = ['positif', 'negatif'];
      // $rand_kat = array_rand($kategori);
      if (($jmlKataPositif - $jmlKataNegatif) == 0) {
        $kategori = 'netral';
      }
      if ($jmlKataPositif > $jmlKataNegatif) {
        $kategori = 'positif';
      }
      if ($jmlKataNegatif > $jmlKataPositif) {
        $kategori = 'negatif';
      }
      // echo $n." negatif = ".$jmlKataNegatif.'<br/>';
      // echo "positif = ".$jmlKataPositif.'<br/>';
      // echo "netral = ".$jmlKataNetral.'<br/>';

      $id_objek = $ci->input->post('id_objek');
      $data_return[$n] = [
        'id_objek' => $id_objek,
        'tgl_tweet' => $dt['created_at'],
        'tweet' => $dt['text'],
        'prepos_text' => $prepos_text,
        'kat_sentimen' => $kategori,
      ];
    }
    $n++;
  }
  // var_dump($data_return);
  return $data_return;
}
