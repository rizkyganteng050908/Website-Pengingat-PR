<?php
session_start();

// Inisialisasi data dummy awal jika session belum ada
if (!isset($_SESSION['tugas'])) {
    $_SESSION['tugas'] = [
        ['id' => 1, 'mapel' => 'Matematika', 'deskripsi' => 'PR Latihan 1 Halaman 10-20', 'deadline' => date('Y-m-d'), 'status' => 'belum'],
        ['id' => 2, 'mapel' => 'Bahasa Indonesia', 'deskripsi' => 'Merangkum Cerpen', 'deadline' => date('Y-m-d', strtotime('+1 day')), 'status' => 'belum'],
        ['id' => 3, 'mapel' => 'IPA', 'deskripsi' => 'Tugas Praktikum', 'deadline' => date('Y-m-d', strtotime('+3 day')), 'status' => 'selesai']
    ];
}

$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : 'dashboard';

// --- PROSES TAMBAH ---
if (isset($_POST['simpan'])) {
    $tugas_baru = [
        'id' => time(), // ID unik berdasarkan waktu
        'mapel' => $_POST['mapel'],
        'deskripsi' => $_POST['deskripsi'],
        'deadline' => $_POST['deadline'],
        'status' => 'belum'
    ];
    $_SESSION['tugas'][] = $tugas_baru;
    header("Location: index.php?aksi=daftar");
    exit;
}

// --- PROSES UBAH STATUS ---
if ($aksi == 'status') {
    $id = $_GET['id'];
    foreach ($_SESSION['tugas'] as $key => $val) {
        if ($val['id'] == $id) {
            $_SESSION['tugas'][$key]['status'] = ($val['status'] == 'belum') ? 'selesai' : 'belum';
        }
    }
    header("Location: index.php?aksi=daftar");
    exit;
}

// --- PROSES HAPUS ---
if ($aksi == 'hapus') {
    $id = $_GET['id'];
    foreach ($_SESSION['tugas'] as $key => $val) {
        if ($val['id'] == $id) {
            unset($_SESSION['tugas'][$key]);
        }
    }
    $_SESSION['tugas'] = array_values($_SESSION['tugas']); // Re-index array
    header("Location: index.php?aksi=daftar");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Aplikasi Pengingat PR (Tanpa Database)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0f172a; color: #fff; }
        .sidebar { background-color: #1e293b; min-height: 100vh; }
        .card-custom { background-color: #1e293b; border: 1px solid #334155; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar p-3">
            <h4 class="text-primary fw-bold mb-4">📅 PR Reminder</h4>
            <ul class="nav nav-pills flex-column gap-2">
                <li class="nav-item"><a href="index.php?aksi=dashboard" class="nav-link <?= $aksi=='dashboard'?'active':'text-white'; ?>">Dashboard</a></li>
                <li class="nav-item"><a href="index.php?aksi=daftar" class="nav-link <?= $aksi=='daftar'?'active':'text-white'; ?>">Daftar PR</a></li>
                <li class="nav-item"><a href="index.php?aksi=tambah" class="nav-link <?= $aksi=='tambah'?'active':'text-white'; ?>">+ Tambah PR</a></li>
            </ul>
        </div>

        <!-- Content Area -->
        <div class="col-md-10 p-4">
            
            <?php if ($aksi == 'dashboard'): 
                $total = count($_SESSION['tugas']);
                $belum = count(array_filter($_SESSION['tugas'], fn($t) => $t['status'] == 'belum'));
                $selesai = count(array_filter($_SESSION['tugas'], fn($t) => $t['status'] == 'selesai'));
            ?>
                <h2>Halo, Hafid! 👋</h2>
                <p class="text-muted">Selamat belajar, jangan lupa kerjakan tugasmu!</p>

                <h5 class="mt-4">Ringkasan PR</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card card-custom p-3 text-center">
                            <span class="text-muted">Total Tugas</span>
                            <h2 class="fw-bold text-info"><?= $total; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-custom p-3 text-center">
                            <span class="text-muted">Belum Selesai</span>
                            <h2 class="fw-bold text-warning"><?= $belum; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-custom p-3 text-center">
                            <span class="text-muted">Selesai</span>
                            <h2 class="fw-bold text-success"><?= $selesai; ?></h2>
                        </div>
                    </div>
                </div>

                <h5>Deadline Terdekat</h5>
                <div class="list-group">
                    <?php 
                    $tugas_list = $_SESSION['tugas'];
                    usort($tugas_list, fn($a, $b) => strcmp($a['deadline'], $b['deadline']));
                    $today = date('Y-m-d');
                    $count = 0;
                    foreach($tugas_list as $d):
                        if ($d['status'] == 'selesai') continue;
                        if ($count++ >= 3) break;
                        $is_today = ($d['deadline'] == $today);
                    ?>
                        <div class="list-group-item card-custom text-white mb-2 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($d['mapel']); ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($d['deskripsi']); ?></small>
                            </div>
                            <div>
                                <span class="me-3"><?= date('d M Y', strtotime($d['deadline'])); ?></span>
                                <?php if($is_today): ?>
                                    <span class="badge bg-danger">Hari Ini</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php elseif ($aksi == 'daftar'): 
                $filter = isset($_GET['mapel']) ? $_GET['mapel'] : '';
            ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>Daftar PR / Tugas</h2>
                    <a href="index.php?aksi=tambah" class="btn btn-primary">+ Tambah PR</a>
                </div>

                <!-- Filter -->
                <form method="GET" action="index.php" class="row mb-3">
                    <input type="hidden" name="aksi" value="daftar">
                    <div class="col-md-3">
                        <select name="mapel" class="form-select bg-dark text-white border-secondary" onchange="this.form.submit()">
                            <option value="">Semua Mapel</option>
                            <option value="Matematika" <?= $filter=='Matematika'?'selected':''; ?>>Matematika</option>
                            <option value="Bahasa Indonesia" <?= $filter=='Bahasa Indonesia'?'selected':''; ?>>Bahasa Indonesia</option>
                            <option value="IPA" <?= $filter=='IPA'?'selected':''; ?>>IPA</option>
                            <option value="PPKn" <?= $filter=='PPKn'?'selected':''; ?>>PPKn</option>
                        </select>
                    </div>
                </form>

                <div class="card card-custom p-3">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Mapel</th>
                                <th>Deskripsi</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach($_SESSION['tugas'] as $row): 
                                if($filter != '' && $row['mapel'] != $filter) continue;
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['mapel']); ?></td>
                                <td><?= htmlspecialchars($row['deskripsi']); ?></td>
                                <td><?= date('d M Y', strtotime($row['deadline'])); ?></td>
                                <td>
                                    <?php if($row['status'] == 'belum'): ?>
                                        <span class="badge bg-danger">Belum</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Selesai</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="index.php?aksi=status&id=<?= $row['id']; ?>" class="btn btn-sm btn-info">Ubah Status</a>
                                    <a href="index.php?aksi=hapus&id=<?= $row['id']; ?>" onclick="return confirm('Hapus tugas ini?')" class="btn btn-sm btn-danger">Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($aksi == 'tambah'): ?>
                <div class="col-md-6">
                    <div class="card card-custom p-4">
                        <h3 class="mb-3 text-primary">Tambah PR Baru</h3>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Mata Pelajaran</label>
                                <select name="mapel" class="form-select bg-dark text-white border-secondary" required>
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                    <option value="Matematika">Matematika</option>
                                    <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                                    <option value="IPA">IPA</option>
                                    <option value="PPKn">PPKn</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi Tugas</label>
                                <textarea name="deskripsi" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="Tulis deskripsi tugas..." required></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Deadline</label>
                                <input type="date" name="deadline" class="form-control bg-dark text-white border-secondary" required>
                            </div>
                            <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                            <a href="index.php?aksi=daftar" class="btn btn-secondary">Batal</a>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>
