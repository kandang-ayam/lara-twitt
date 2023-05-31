<?php

class UsersModel extends CI_Model
{

    public function usersByUsername($username)
    {
        return $this->db->get_where('admin', ['username' => $username])->row_array();
    }
    public function usersById($id_admin)
    {
        return $this->db->get_where('admin', ['id_admin' => $id_admin])->row_array();
    }

    public function semuaUsers()
    {
        return $this->db->get('admin')->result_array();
    }

    public function tambahAdmin()
    {
        try {
            $data = [
                "username" => $this->input->post('username', true),
                "password" => password_hash($this->input->post('password', true), PASSWORD_DEFAULT),
            ];

            $this->db->insert('admin', $data);
            return true;
        } catch (\SQLException $e) {
            return false;
        }
    }

    public function editAdmin()
    {
        try {
            $id_admin = $this->input->post('id_admin', true);
            $username = $this->input->post('username');
            $password = $this->input->post('password');

            if (empty($password)) {
                $data = [
                    "username" => $username,
                ];
            } else {
                $data = [
                    "username" => $username,
                    "password" => password_hash($password, PASSWORD_DEFAULT),
                ];
            }

            $this->db->where('id_admin', $id_admin);
            $this->db->update('admin', $data);
            return true;
        } catch (\SQLException $e) {
            return false;
        }
    }

    public function hapusUser($id_admin)
    {
        try {
            $this->db->delete('admin', ['id_admin' => $id_admin]);
            return true;
        } catch (\SQLException $e) {
            return false;
        }
    }
}
