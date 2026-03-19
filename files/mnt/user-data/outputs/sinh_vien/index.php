<?php
require_once '../config/db.php';

$success = '';
if (isset($_GET['added'])) $success = 'Thêm sinh viên thành công!';

// Xử lý xoá
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM sinh_vien WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    $success = 'Xoá sinh viên thành công!';
}

$stmt = $pdo->query("
    SELECT sv.*, lh.name AS ten_lop
    FROM sinh_vien sv
    LEFT JOIN lop_hoc lh ON lh.id = sv.lop_hoc_id
    ORDER BY sv.id DESC
");
$danhSach = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sinh Viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; font-family: 'Segoe UI', sans-serif; }
        .sidebar { width: 240px; min-height: 100vh; background: #1e293b; position: fixed; top: 0; left: 0; z-index: 100; padding-top: 1rem; }
        .sidebar-brand { color: #fff; font-weight: 700; font-size: 1.1rem; padding: 1rem 1.25rem 1.5rem; border-bottom: 1px solid #334155; display: flex; align-items: center; gap: .5rem; }
        .sidebar-brand i { color: #60a5fa; }
        .nav-section { font-size: .7rem; font-weight: 600; letter-spacing: .08em; color: #475569; padding: 1rem 1.25rem .3rem; text-transform: uppercase; }
        .nav-link { color: #94a3b8; padding: .65rem 1.25rem; display: flex; align-items: center; gap: .6rem; transition: all .2s; }
        .nav-link:hover, .nav-link.active { color: #fff; background: rgba(255,255,255,.07); border-left: 3px solid #60a5fa; }
        .main-content { margin-left: 240px; padding: 2rem; }
        .page-header { background: #fff; border-radius: 12px; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .page-title { font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0; }
        .page-sub { color: #64748b; font-size: .85rem; margin: 0; }
        .card-table { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden; }
        .table { margin: 0; }
        .table thead th { background: #f8fafc; border-bottom: 2px solid #e2e8f0; font-size: .8rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #475569; padding: .85rem 1rem; }
        .table tbody td { padding: .85rem 1rem; vertical-align: middle; color: #334155; }
        .table tbody tr:hover { background: #f8fafc; }
        .badge-status { display: inline-flex; align-items: center; gap: .3rem; padding: .3rem .7rem; border-radius: 999px; font-size: .75rem; font-weight: 600; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }
        .btn-icon { width: 32px; height: 32px; border-radius: 8px; border: 0; display: inline-flex; align-items: center; justify-content: center; font-size: .85rem; transition: all .15s; text-decoration: none; }
        .btn-del { background: #fff1f2; color: #dc2626; }
        .btn-del:hover { background: #dc2626; color: #fff; }
        .empty-state { text-align: center; padding: 3rem; color: #94a3b8; }
        .empty-state i { font-size: 2.5rem; display: block; margin-bottom: .75rem; }
        .avatar { width: 34px; height: 34px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand"><i class="bi bi-mortarboard-fill"></i> Quản Lý SV</div>
    <div class="nav-section">Tổng quan</div>
    <a href="../index.php" class="nav-link"><i class="bi bi-grid-1x2"></i> Dashboard</a>
    <div class="nav-section">Quản lý</div>
    <a href="../lop_hoc/index.php" class="nav-link"><i class="bi bi-building"></i> Lớp Học</a>
    <a href="index.php" class="nav-link active"><i class="bi bi-people"></i> Sinh Viên</a>
</div>

<div class="main-content">

    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i> <?= $success ?>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-people me-2 text-primary"></i>Danh Sách Sinh Viên</h1>
            <p class="page-sub">Tổng cộng: <?= count($danhSach) ?> sinh viên</p>
        </div>
        <a href="Add_sv.php" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-person-plus"></i> Thêm Sinh Viên
        </a>
    </div>

    <div class="card-table">
        <?php if (empty($danhSach)): ?>
        <div class="empty-state">
            <i class="bi bi-people"></i>
            Chưa có sinh viên nào. <a href="Add_sv.php">Thêm sinh viên đầu tiên</a>
        </div>
        <?php else: ?>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Sinh Viên</th>
                    <th>Email</th>
                    <th>Điện Thoại</th>
                    <th>Lớp</th>
                    <th>Trạng Thái</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($danhSach as $i => $sv): ?>
                <tr>
                    <td class="text-muted"><?= $i + 1 ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar"><?= mb_strtoupper(mb_substr($sv['name'], 0, 1)) ?></div>
                            <strong><?= htmlspecialchars($sv['name']) ?></strong>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($sv['email']) ?></td>
                    <td><?= htmlspecialchars($sv['phone']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($sv['ten_lop'] ?? '—') ?></span></td>
                    <td>
                        <?php if ($sv['status'] == 1): ?>
                            <span class="badge-status badge-active"><i class="bi bi-circle-fill" style="font-size:.5rem"></i> Đang học</span>
                        <?php else: ?>
                            <span class="badge-status badge-inactive"><i class="bi bi-circle-fill" style="font-size:.5rem"></i> Nghỉ học</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?delete=<?= $sv['id'] ?>"
                           class="btn-icon btn-del"
                           title="Xoá"
                           onclick="return confirm('Xoá sinh viên \'<?= htmlspecialchars(addslashes($sv['name'])) ?>\'?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
