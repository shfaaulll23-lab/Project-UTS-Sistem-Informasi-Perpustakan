<?php
require 'function.php';

// menu yang dibuka: dashboard (default), buku, atau anggota
$menu = 'dashboard';
if (isset($_GET['menu'])) {
    $menu = $_GET['menu'];
}
if ($menu != 'dashboard' && $menu != 'buku' && $menu != 'anggota') {
    header('Location: index.php');
    exit;
}

$cari = '';
if (isset($_GET['cari'])) {
    $cari = trim($_GET['cari']);
}

if ($menu == 'dashboard') {
    $judul = 'Dashboard';
} elseif ($menu == 'buku') {
    $judul = 'Manajemen Buku';
} else {
    $judul = 'Manajemen Anggota';
}

header_halaman($judul, $menu);


/* ================= DASHBOARD ================= */
if ($menu == 'dashboard') {

    $total_buku    = ambil("SELECT COUNT(*) AS total FROM buku")[0]['total'];
    $total_stok    = ambil("SELECT SUM(stok) AS total FROM buku")[0]['total'];
    $total_anggota = ambil("SELECT COUNT(*) AS total FROM anggota")[0]['total'];
    if ($total_stok == '') {
        $total_stok = 0;
    }

    $buku_baru    = ambil("SELECT * FROM buku ORDER BY id DESC LIMIT 5");
    $anggota_baru = ambil("SELECT * FROM anggota ORDER BY id DESC LIMIT 5");

    // 14 buku terbaru untuk rak, yang paling lama di kiri
    $rak = array_reverse(ambil("SELECT * FROM buku ORDER BY id DESC LIMIT 14"));
    $warna = ['#2F4B7C', '#7A3B4E', '#3E6B5A', '#8A6420', '#5C4B8A', '#4F5D75'];
    ?>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Rak buku</h3>
        <small class="text-muted ml-2">Makin tinggi punggung buku, makin banyak stoknya.</small>
      </div>
      <div class="card-body pb-0">
        <div class="rak">
          <?php foreach ($rak as $b) {
              $tinggi = 96 + $b['stok'] * 16;
              if ($tinggi > 230) {
                  $tinggi = 230;
              }
              $lebar = 34 + (strlen($b['judul']) % 4) * 6;
              $kelas = 'punggung';
              if ($b['stok'] == 0) {
                  $kelas = 'punggung habis';
              }
          ?>
            <a class="<?= $kelas ?>" href="index.php?menu=buku&cari=<?= urlencode($b['judul']) ?>"
               title="<?= tampil($b['judul']) ?> (stok <?= $b['stok'] ?>)"
               style="height: <?= $tinggi ?>px; width: <?= $lebar ?>px; background: <?= $warna[$b['id'] % 6] ?>;">
              <?= tampil($b['judul']) ?>
            </a>
          <?php } ?>
          <?php if (count($rak) == 0) { ?>
            <p class="m-auto text-muted">Rak masih kosong. <a href="tambah.php?menu=buku">Tambah buku pertama</a>.</p>
          <?php } ?>
        </div>
      </div>
      <div class="papan"></div>
    </div>

    <dl class="ringkas">
      <div><dt class="text-muted font-weight-normal">Judul buku</dt><dd><?= $total_buku ?></dd></div>
      <div><dt class="text-muted font-weight-normal">Total stok</dt><dd><?= $total_stok ?></dd></div>
      <div><dt class="text-muted font-weight-normal">Anggota terdaftar</dt><dd><?= $total_anggota ?></dd></div>
    </dl>

    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header"><h3 class="card-title">Buku terbaru</h3></div>
          <div class="card-body p-0">
            <table class="table table-sm mb-0">
              <thead><tr><th>Judul</th><th>Penulis</th><th>Stok</th></tr></thead>
              <tbody>
                <?php foreach ($buku_baru as $b) { ?>
                  <tr>
                    <td><?= tampil($b['judul']) ?></td>
                    <td><?= tampil($b['penulis']) ?></td>
                    <td><?= $b['stok'] ?></td>
                  </tr>
                <?php } ?>
                <?php if (count($buku_baru) == 0) { ?>
                  <tr><td colspan="3" class="text-center text-muted">Belum ada buku</td></tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header"><h3 class="card-title">Anggota terbaru</h3></div>
          <div class="card-body p-0">
            <table class="table table-sm mb-0">
              <thead><tr><th>No. Anggota</th><th>Nama</th><th>Email</th></tr></thead>
              <tbody>
                <?php foreach ($anggota_baru as $a) { ?>
                  <tr>
                    <td><?= tampil($a['no_anggota']) ?></td>
                    <td><?= tampil($a['nama']) ?></td>
                    <td><?= tampil($a['email']) ?></td>
                  </tr>
                <?php } ?>
                <?php if (count($anggota_baru) == 0) { ?>
                  <tr><td colspan="3" class="text-center text-muted">Belum ada anggota</td></tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

<?php
}


/* ================= DATA BUKU ================= */
if ($menu == 'buku') {
    $data_buku = cari_buku($cari);
    ?>
    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-md-6 mb-2">
            <a href="tambah.php?menu=buku" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Buku</a>
          </div>
          <div class="col-md-6">
            <form method="get" class="form-inline justify-content-md-end">
              <input type="hidden" name="menu" value="buku">
              <input type="text" name="cari" class="form-control mr-2" placeholder="Cari judul / penulis / penerbit" value="<?= tampil($cari) ?>">
              <button type="submit" class="btn btn-info mr-1"><i class="fas fa-search"></i> Cari</button>
              <a href="index.php?menu=buku" class="btn btn-secondary">Reset</a>
            </form>
          </div>
        </div>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover">
          <thead>
            <tr>
              <th style="width: 50px">No</th>
              <th>Judul</th>
              <th>Penulis</th>
              <th>Penerbit</th>
              <th>Tahun</th>
              <th>Stok</th>
              <th style="width: 170px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; foreach ($data_buku as $b) { ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= tampil($b['judul']) ?></td>
                <td><?= tampil($b['penulis']) ?></td>
                <td><?= tampil($b['penerbit']) ?></td>
                <td><?= $b['tahun'] ?></td>
                <td><?= $b['stok'] ?></td>
                <td>
                  <a href="edit.php?menu=buku&id=<?= $b['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                  <form action="hapus.php" method="post" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
                    <input type="hidden" name="menu" value="buku">
                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                  </form>
                </td>
              </tr>
            <?php } ?>
            <?php if (count($data_buku) == 0) { ?>
              <tr><td colspan="7" class="text-center text-muted py-4">Data tidak ditemukan</td></tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
      <div class="card-footer">Total: <?= count($data_buku) ?> data</div>
    </div>
<?php
}


/* ================= DATA ANGGOTA ================= */
if ($menu == 'anggota') {
    $data_anggota = cari_anggota($cari);
    ?>
    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-md-6 mb-2">
            <a href="tambah.php?menu=anggota" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Anggota</a>
          </div>
          <div class="col-md-6">
            <form method="get" class="form-inline justify-content-md-end">
              <input type="hidden" name="menu" value="anggota">
              <input type="text" name="cari" class="form-control mr-2" placeholder="Cari no. anggota / nama / email" value="<?= tampil($cari) ?>">
              <button type="submit" class="btn btn-info mr-1"><i class="fas fa-search"></i> Cari</button>
              <a href="index.php?menu=anggota" class="btn btn-secondary">Reset</a>
            </form>
          </div>
        </div>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover">
          <thead>
            <tr>
              <th style="width: 50px">No</th>
              <th>No. Anggota</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Telepon</th>
              <th>Alamat</th>
              <th style="width: 170px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; foreach ($data_anggota as $a) { ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= tampil($a['no_anggota']) ?></td>
                <td><?= tampil($a['nama']) ?></td>
                <td><?= tampil($a['email']) ?></td>
                <td><?= tampil($a['telepon']) ?></td>
                <td><?= tampil($a['alamat']) ?></td>
                <td>
                  <a href="edit.php?menu=anggota&id=<?= $a['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                  <form action="hapus.php" method="post" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
                    <input type="hidden" name="menu" value="anggota">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                  </form>
                </td>
              </tr>
            <?php } ?>
            <?php if (count($data_anggota) == 0) { ?>
              <tr><td colspan="7" class="text-center text-muted py-4">Data tidak ditemukan</td></tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
      <div class="card-footer">Total: <?= count($data_anggota) ?> data</div>
    </div>
<?php
}

footer_halaman();