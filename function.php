<?php
session_start();

// koneksi ke database (nama database harus sama dengan di phpMyAdmin)
$koneksi = mysqli_connect("localhost", "root", "", "sistem informasi perpustakaan");
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// menjalankan SELECT, hasilnya berupa array
function ambil($sql) {
    global $koneksi;
    $hasil = mysqli_query($koneksi, $sql);
    $data = [];
    while ($baris = mysqli_fetch_assoc($hasil)) {
        $data[] = $baris;
    }
    return $data;
}

// membersihkan input sebelum dimasukkan ke query
function bersih($teks) {
    global $koneksi;
    return mysqli_real_escape_string($koneksi, trim($teks));
}

// menampilkan teks di halaman
function tampil($teks) {
    return htmlspecialchars($teks);
}


/* ===== BUKU ===== */

function cari_buku($kata) {
    $kata = bersih($kata);
    return ambil("SELECT * FROM buku
                  WHERE judul LIKE '%$kata%' OR penulis LIKE '%$kata%' OR penerbit LIKE '%$kata%'
                  ORDER BY id DESC");
}

function tambah_buku($d) {
    global $koneksi;
    $judul    = bersih($d['judul']);
    $penulis  = bersih($d['penulis']);
    $penerbit = bersih($d['penerbit']);
    $tahun    = (int)$d['tahun'];
    $stok     = (int)$d['stok'];
    mysqli_query($koneksi, "INSERT INTO buku (judul, penulis, penerbit, tahun, stok)
                            VALUES ('$judul', '$penulis', '$penerbit', $tahun, $stok)");
}

function ubah_buku($id, $d) {
    global $koneksi;
    $judul    = bersih($d['judul']);
    $penulis  = bersih($d['penulis']);
    $penerbit = bersih($d['penerbit']);
    $tahun    = (int)$d['tahun'];
    $stok     = (int)$d['stok'];
    mysqli_query($koneksi, "UPDATE buku SET judul='$judul', penulis='$penulis', penerbit='$penerbit',
                            tahun=$tahun, stok=$stok WHERE id=$id");
}

function hapus_buku($id) {
    global $koneksi;
    mysqli_query($koneksi, "DELETE FROM buku WHERE id=$id");
}

// mengecek isian form buku, hasilnya daftar pesan error
function cek_buku($d) {
    $error = [];
    if (trim($d['judul']) == '')    $error[] = 'Judul harus diisi';
    if (trim($d['penulis']) == '')  $error[] = 'Penulis harus diisi';
    if (trim($d['penerbit']) == '') $error[] = 'Penerbit harus diisi';
    if (!is_numeric($d['tahun']))   $error[] = 'Tahun harus berupa angka';
    if (!is_numeric($d['stok']) || $d['stok'] < 0) $error[] = 'Stok harus berupa angka 0 atau lebih';
    return $error;
}


/* ===== ANGGOTA ===== */

function cari_anggota($kata) {
    $kata = bersih($kata);
    return ambil("SELECT * FROM anggota
                  WHERE no_anggota LIKE '%$kata%' OR nama LIKE '%$kata%' OR email LIKE '%$kata%'
                  OR telepon LIKE '%$kata%' OR alamat LIKE '%$kata%'
                  ORDER BY id DESC");
}

function tambah_anggota($d) {
    global $koneksi;
    $no      = bersih($d['no_anggota']);
    $nama    = bersih($d['nama']);
    $email   = bersih($d['email']);
    $telepon = bersih($d['telepon']);
    $alamat  = bersih($d['alamat']);
    mysqli_query($koneksi, "INSERT INTO anggota (no_anggota, nama, email, telepon, alamat)
                            VALUES ('$no', '$nama', '$email', '$telepon', '$alamat')");
}

function ubah_anggota($id, $d) {
    global $koneksi;
    $no      = bersih($d['no_anggota']);
    $nama    = bersih($d['nama']);
    $email   = bersih($d['email']);
    $telepon = bersih($d['telepon']);
    $alamat  = bersih($d['alamat']);
    mysqli_query($koneksi, "UPDATE anggota SET no_anggota='$no', nama='$nama', email='$email',
                            telepon='$telepon', alamat='$alamat' WHERE id=$id");
}

function hapus_anggota($id) {
    global $koneksi;
    mysqli_query($koneksi, "DELETE FROM anggota WHERE id=$id");
}

// $id diisi saat edit, supaya nomor anggota milik sendiri tidak dianggap kembar
function cek_anggota($d, $id = 0) {
    $error = [];
    if (trim($d['no_anggota']) == '') $error[] = 'No. anggota harus diisi';
    if (trim($d['nama']) == '')       $error[] = 'Nama harus diisi';
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $error[] = 'Format email tidak valid';
    if (trim($d['telepon']) == '')    $error[] = 'Telepon harus diisi';
    if (trim($d['alamat']) == '')     $error[] = 'Alamat harus diisi';

    $no = bersih($d['no_anggota']);
    $kembar = ambil("SELECT id FROM anggota WHERE no_anggota='$no' AND id != $id");
    if (count($kembar) > 0) $error[] = 'No. anggota sudah digunakan';
    return $error;
}


/* ===== FORM (dipakai tambah.php dan edit.php) ===== */

function tampil_error($error) {
    if (count($error) > 0) {
        echo '<div class="alert alert-danger"><ul class="mb-0">';
        foreach ($error as $pesan) {
            echo '<li>' . tampil($pesan) . '</li>';
        }
        echo '</ul></div>';
    }
}

function form_buku($d, $error, $tombol) {
    ?>
    <div class="card">
      <form method="post">
        <div class="card-body">
          <?php tampil_error($error); ?>
          <div class="form-group">
            <label>Judul</label>
            <input type="text" name="judul" class="form-control" value="<?= tampil($d['judul']) ?>">
          </div>
          <div class="form-group">
            <label>Penulis</label>
            <input type="text" name="penulis" class="form-control" value="<?= tampil($d['penulis']) ?>">
          </div>
          <div class="form-group">
            <label>Penerbit</label>
            <input type="text" name="penerbit" class="form-control" value="<?= tampil($d['penerbit']) ?>">
          </div>
          <div class="row">
            <div class="form-group col-md-6">
              <label>Tahun</label>
              <input type="number" name="tahun" class="form-control" value="<?= tampil($d['tahun']) ?>">
            </div>
            <div class="form-group col-md-6">
              <label>Stok</label>
              <input type="number" name="stok" min="0" class="form-control" value="<?= tampil($d['stok']) ?>">
            </div>
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" name="simpan" class="btn btn-primary"><?= $tombol ?></button>
          <a href="index.php?menu=buku" class="btn btn-secondary">Batal</a>
        </div>
      </form>
    </div>
    <?php
}

function form_anggota($d, $error, $tombol) {
    ?>
    <div class="card">
      <form method="post">
        <div class="card-body">
          <?php tampil_error($error); ?>
          <div class="row">
            <div class="form-group col-md-4">
              <label>No. Anggota</label>
              <input type="text" name="no_anggota" class="form-control" placeholder="AGT-004" value="<?= tampil($d['no_anggota']) ?>">
            </div>
            <div class="form-group col-md-8">
              <label>Nama</label>
              <input type="text" name="nama" class="form-control" value="<?= tampil($d['nama']) ?>">
            </div>
          </div>
          <div class="row">
            <div class="form-group col-md-6">
              <label>Email</label>
              <input type="email" name="email" class="form-control" value="<?= tampil($d['email']) ?>">
            </div>
            <div class="form-group col-md-6">
              <label>Telepon</label>
              <input type="text" name="telepon" class="form-control" value="<?= tampil($d['telepon']) ?>">
            </div>
          </div>
          <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat" rows="3" class="form-control"><?= tampil($d['alamat']) ?></textarea>
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" name="simpan" class="btn btn-primary"><?= $tombol ?></button>
          <a href="index.php?menu=anggota" class="btn btn-secondary">Batal</a>
        </div>
      </form>
    </div>
    <?php
}


/* ===== TEMPLATE AdminLTE ===== */

function header_halaman($judul, $aktif) {
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $judul ?> | SI Perpustakaan</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <style>
    h1, h2, .brand-text { font-family: Georgia, serif; }
    .main-sidebar { background: #1b2238; }
    .nav-sidebar .nav-link.active { background: rgba(255,255,255,.1) !important; box-shadow: inset 3px 0 0 #b8862b; }
    .card { box-shadow: none; border: 1px solid #dde1e8; }
    .btn-primary { background: #1b2238; border-color: #1b2238; }
    .btn-warning { background: #b8862b; border-color: #b8862b; color: #fff; }
    .rak { display: flex; align-items: flex-end; gap: 5px; overflow-x: auto; min-height: 240px; }
    .punggung { color: #fff; writing-mode: vertical-rl; padding: 12px 0; font-size: 13px; font-weight: 600;
                white-space: nowrap; overflow: hidden; text-overflow: ellipsis; border-radius: 3px 3px 0 0; }
    .punggung:hover { color: #fff; opacity: .85; }
    .punggung.habis { opacity: .5; }
    .papan { height: 14px; background: #6e5434; }
    .ringkas { display: flex; gap: 48px; margin-bottom: 20px; }
    .ringkas dd { font-family: Georgia, serif; font-size: 2rem; font-weight: 600; margin: 0; }
  </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
      </li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link">
      <i class="fas fa-book-reader ml-3 mr-2"></i>
      <span class="brand-text font-weight-light">SI Perpustakaan</span>
    </a>
    <div class="sidebar">
      <nav class="mt-3">
        <ul class="nav nav-pills nav-sidebar flex-column">
          <li class="nav-item">
            <a href="index.php" class="nav-link <?= $aktif == 'dashboard' ? 'active' : '' ?>">
              <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="index.php?menu=buku" class="nav-link <?= $aktif == 'buku' ? 'active' : '' ?>">
              <i class="nav-icon fas fa-book"></i><p>Manajemen Buku</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="index.php?menu=anggota" class="nav-link <?= $aktif == 'anggota' ? 'active' : '' ?>">
              <i class="nav-icon fas fa-users"></i><p>Manajemen Anggota</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid"><h1><?= $judul ?></h1></div>
    </section>
    <section class="content">
      <div class="container-fluid">
        <?php
        if (isset($_SESSION['pesan'])) {
            echo '<div class="alert alert-success">' . tampil($_SESSION['pesan']) . '</div>';
            unset($_SESSION['pesan']);
        }
}

function footer_halaman() {
    ?>
      </div>
    </section>
  </div>

  <footer class="main-footer"><strong>SI Perpustakaan</strong> - UTS Pemrograman Internet</footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
    <?php
}