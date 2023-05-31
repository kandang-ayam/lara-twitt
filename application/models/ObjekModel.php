<?php

class ObjekModel extends CI_Model
{
  public function semuaObjek()
  {
    return $this->db->get('objek')->result_array();
  }

  public function objekById($id_objek)
  {
    return $this->db->get_where('objek', ['id_objek' => $id_objek])->row_array();
  }
  public function objekByIdTwitter($id_twitter)
  {
    return $this->db->get_where('objek', ['id_twitter' => $id_twitter])->row_array();
  }
  public function tambahObjek()
  {
    try {
      $data = [
        "nama_objek" => $this->input->post('nama_objek', true),
        "id_twitter" => $this->input->post('id_twitter', true),
      ];

      $this->db->insert('objek', $data);
      return true;
    } catch (\SQLException $e) {
      return false;
    }
  }
  public function editObjek()
  {
    try {
      $id_objek = $this->input->post('id_objek', true);
      $nama_objek = $this->input->post('nama_objek');
      $id_twitter = $this->input->post('id_twitter');

      $data = [
        "nama_objek" => $nama_objek,
        "id_twitter" => $id_twitter,
      ];

      $this->db->where('id_objek', $id_objek);
      $this->db->update('objek', $data);
      return true;
    } catch (\SQLException $e) {
      return false;
    }
  }

  public function hapusObjek($id_objek)
    {
        try {
            $this->db->delete('objek', ['id_objek' => $id_objek]);
            return true;
        } catch (\SQLException $e) {
            return false;
        }
    }
}
