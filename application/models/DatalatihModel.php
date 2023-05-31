<?php

class DatalatihModel extends CI_Model
{
  public function semuaDatalatih()
  {
    return $this->db->get('data_latih')->result_array();
  }

  public function acakDataLatih()
  {
    // $this->db->order_by('id_objek', 'RANDOM');
    $this->db->limit(9);
    $query = $this->db->get('data_latih');
    return $query->result_array();
  }
  public function datalatihByObjek()
  {
    $jml_objek = $this->input->post('filter')['objek'];
    $id_objek = $this->semuaObjek();

    $i=0;
    $j=0;
    foreach ($id_objek as $obj) {
      $this->db->where('id_objek', $obj['id_objek']);
      $this->db->limit($jml_objek[$i]);
      // $this->db->order_by('id_data','random');
      $query = $this->db->get('data_latih');
      $data = $query->result_array();
      foreach($data as $d){
        $data_latih[$j]['id_data'] = $d['id_data'];
        $data_latih[$j]['id_objek'] = $d['id_objek'];
        $data_latih[$j]['tgl_tweet'] = $d['tgl_tweet'];
        $data_latih[$j]['tweet'] = $d['tweet'];
        $data_latih[$j]['prepos_text'] = $d['prepos_text'];
        $data_latih[$j]['kat_sentimen'] = $d['kat_sentimen'];
        $j++;
      }
      $i++;
    }
    return $data_latih;
  }
  public function semuaObjek()
  {
    return $this->db->get('objek')->result_array();
  }
  public function updatePrePosText($id_data, $prepos)
  {
    try {
      $this->db->where('id_data', $id_data);
      $this->db->update('data_latih', ["prepos_text" => $prepos]);
      return true;
    } catch (\SQLException $e) {
      return false;
    }
  }

  public function simpanDatalatih($data)
  {
    try {
      $this->db->insert('data_latih', $data);
      return true;
    } catch (\SQLException $e) {
      return false;
    }
  }
  public function inputDatalatih()
  {
    try {
      $data_uji = [
        'tweet' => $this->input->post('tweet')
      ];
      $uji = uji_tweet($data_uji, 3, 'uji_sentimen');
      $data = [
        'id_objek' => $this->input->post('id_objek'),
        'tgl_tweet' => $this->input->post('tgl_tweet'),
        'tweet' => $data_uji['tweet'],
        'prepos_text' => $uji['prepos_text'],
        'kat_sentimen' => $uji['kategori'],
      ];
      $this->db->insert('data_latih', $data);
      return true;
    } catch (\SQLException $e) {
      return false;
    }
  }
  public function uploadDatalatih($data_latih)
  {
    try {
      $this->db->insert_batch('data_latih', $data_latih);
      return true;
    } catch (\SQLException $e) {
      return false;
    }
  }
}
