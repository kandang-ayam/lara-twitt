<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('UsersModel');
    }
    public function index($id_user = null)
    {
        $data['users'] = $this->UsersModel->semuaUsers();

        if ($id_user != null) {
            $data['detail'] = $this->UsersModel->usersById($id_user);
        }
        view('users/index', $data);
    }

    public function simpan_user()
    {
        $cekUsername = $this->UsersModel->usersByUsername($this->input->post('username'));

        if ($this->input->post('submit') == "Tambah") {
            if ($cekUsername) {
                $this->session->set_flashdata('danger', 'Gagal tambah. Username ' . $cekUsername['username'] . ' sudah terdaftar');
                redirect('users');
            }
            if ($this->UsersModel->tambahAdmin()) {
                $this->session->set_flashdata('success', 'Data Admin Berhasil Ditambah');
            } else {
                $this->session->set_flashdata('danger', 'Data Admin Gagal Ditambah');
            }
        } else {
            if ($this->input->post('username') != $this->input->post('username_lama')) {
                if ($cekUsername) {
                    $this->session->set_flashdata('danger', 'Gagal edit. Username ' . $cekUsername['username'] . ' sudah terdaftar');
                    redirect('users');
                }
            }
            if ($this->UsersModel->editAdmin()) {
                $this->session->set_flashdata('success', 'Data Admin Berhasil Diedit');
            } else {
                $this->session->set_flashdata('danger', 'Data Admin Gagal Diedit');
            }
        }
        redirect('users');
    }

    public function hapus_user($id_user)
    {
        $user = $this->UsersModel->usersById($id_user);

        if ($user) {
            if ($this->UsersModel->hapusUser($id_user)) {

                $this->session->set_flashdata('success', 'Data Admin Berhasil Dihapus');
            } else {
                $this->session->set_flashdata('success', 'Data Admin Gagal Dihapus');
            }
        }
        redirect('users');;
    }
}
