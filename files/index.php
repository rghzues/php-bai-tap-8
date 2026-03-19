<?php
require_once '../config/db.php';

// Xử lý xoá lớp học
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        // Kiểm tra xem lớp có sinh viên không
        $check = $pdo->prepare("SELECT COUNT(*) FROM sinh_vien WHERE lop_hoc_id = ?");
        $check->execute([$id]);
        $count = $check->fetchColumn();

        if ($count > 0) {
            $error = "Không thể xoá! Lớp này đang có <strong>$count sinh viên</strong>.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM lop_hoc WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Xoá lớp học thành công!";
        }
    } catch (PDOException $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Lấy danh sách lớp học kèm số sinh viên
$stmt = $pdo->query("
    SELECT lh.*, COUNT(sv.id) as so_sinh_vien
    FROM lop_hoc lh
    LEFT JOIN sinh_vien sv ON sv.lop_hoc_id = lh.id
    GROUP BY lh.id
    ORDER BY lh.id DESC
");
$danhSachLop = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Lớp Học</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #eff6ff;
            --danger: #dc2626;
            --success: #16a34a;
            --sidebar-bg: #1e293b;
        }
        body { background: #f1f5f9; font-family: 'Segoe UI', sans-serif; }

        .sidebar {
            width: 240px; min-height: 100vh; background: var(--sidebar-bg);
            position: fixed; top: 0; left: 0; z-index: 100;
            padding-top: 1rem;
        }
        .sidebar-brand {
            color: #fff; font-weight: 700; font-size: 1.1rem;
            padding: 1rem 1.25rem 1.5rem; border-bottom: 1px solid #334155;
            display: flex; align-items: center; gap: .5rem;
        }
        .sidebar-brand i { color: #60a5fa; font-size: 1.3rem; }
        .nav-link {
            color: #94a3b8; padding: .65rem 1.25rem;
            display: flex; align-items: center; gap: .6rem;
            border-radius: 0; transition: all .2s;
        }
        .nav-link:hover, .nav-link.active {
            color: #fff; background: rgba(255,255,255,.07);
            border-left: 3px solid #60a5fa;
        }
        .nav-section {
            font-size: .7rem; font-weight: 600; letter-spacing: .08em;
            color: #475569; padding: 1rem 1.25rem .3rem; text-transform: uppercase;
        }

        .main-content { margin-left: 240px; padding: 2rem; }

        .page-header {
            background: #fff; border-radius: 12px; padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem; display: flex; align-items: center;
            justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,.06);
        }
        .page-title { font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0; }
        .page-sub { color: #64748b; font-size: .85rem; margin: 0; }

        .card-table {
            background: #fff; border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden;
        }
        .table { margin: 0; }
        .table thead th {
            background: #f8fafc; border-bottom: 2px solid #e2e8f0;
            font-size: .8rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: .05em; color: #475569; padding: .85rem 1rem;
        }
        .table tbody td { padding: .85rem 1rem; vertical-align: middle; color: #334155; }
        .table tbody tr:hover { background: #f8fafc; }

        .badge-status {
            display: inline-flex; align-items: center; gap: .3rem;
            padding: .3rem .7rem; border-radius: 999px; font-size: .75rem; font-weight: 600;
        }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }

        .btn-icon {
            width: 32px; height: 32px; border-radius: 8px; border: 0;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: .85rem; transition: all .15s; text-decoration: none;
        }
        .btn-edit { background: #eff6ff; color: #2563eb; }
        .btn-edit:hover { background: #2563eb; color: #fff; }
        .btn-del { background: #fff1f2; color: #dc2626; }
        .btn-del:hover { background: #dc2626; color: #fff; }

        .sv-count {
            background: #eff6ff; color: #2563eb;
            border-radius: 999px; padding: .15rem .6rem;
            font-size: .8rem; font-weight: 600;
        }
        .empty-state { text-align: center; padding: 3rem; color: #94a3b8; }
        .empty-state i { font-size: 2.5rem; display: block; margin-bottom: .75rem; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-brand"><i class="bi bi-mortarboard-fill"></i> Quản Lý SV</div>
    <div class="nav-section">Tổng quan</div>
    <a href="../index.php" class="nav-link"><i class="bi bi-grid-1x2"></i> Dashboard</a>
    <div class="nav-section">Quản lý</div>
    <a href="index.php" class="nav-link active"><i class="bi bi-building"></i> Lớp Học</a>
    <a href="../sinh_vien/index.php" class="nav-link"><i class="bi bi-people"></i> Sinh Viên</a>
</div>

<!-- Main -->
<div class="main-content">

    <?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-check-circle-fill"></i> <?= $success ?>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-exclamation-circle-fill"></i> <?= $error ?>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-building me-2 text-primary"></i>Danh Sách Lớp Học</h1>
            <p class="page-sub">Tổng cộng: <?= count($danhSachLop) ?> lớp học</p>
        </div>
        <a href="add.php" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i> Thêm Lớp Mới
        </a>
    </div>

    <div class="card-table">
        <?php if (empty($danhSachLop)): ?>
        <div class="empty-state">
            <i class="bi bi-building-x"></i>
            Chưa có lớp học nào. <a href="add.php">Thêm lớp đầu tiên</a>
        </div>
        <?php else: ?>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th style="width:60px">#</th>
                    <th>Tên Lớp</th>
                    <th>Số Sinh Viên</th>
                    <th>Trạng Thái</th>
                    <th style="width:120px">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($danhSachLop as $i => $lop): ?>
                <tr>
                    <td class="text-muted"><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($lop['name']) ?></strong></td>
                    <td>
                        <span class="sv-count"><i class="bi bi-people-fill"></i> <?= $lop['so_sinh_vien'] ?></span>
                    </td>
                    <td>
                        <?php if ($lop['status'] == 1): ?>
                            <span class="badge-status badge-active"><i class="bi bi-circle-fill" style="font-size:.5rem"></i> Hoạt động</span>
                        <?php else: ?>
                            <span class="badge-status badge-inactive"><i class="bi bi-circle-fill" style="font-size:.5rem"></i> Ngừng</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit.php?id=<?= $lop['id'] ?>" class="btn-icon btn-edit" title="Sửa">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="?delete=<?= $lop['id'] ?>"
                           class="btn-icon btn-del"
                           title="Xoá"
                           onclick="return confirm('Xoá lớp \'<?= htmlspecialchars(addslashes($lop['name'])) ?>\'?\n(Lớp phải không có sinh viên)')">
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
