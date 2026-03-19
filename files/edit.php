<?php
require_once '../config/db.php';

// Lấy ID từ URL
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: index.php'); exit; }

// Lấy dữ liệu lớp hiện tại
$stmt = $pdo->prepare("SELECT * FROM lop_hoc WHERE id = ?");
$stmt->execute([$id]);
$lop = $stmt->fetch();
if (!$lop) { header('Location: index.php'); exit; }

$errors = [];
$name   = $lop['name'];
$status = $lop['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;

    if ($name === '') {
        $errors['name'] = 'Vui lòng nhập tên lớp học.';
    } elseif (strlen($name) > 100) {
        $errors['name'] = 'Tên lớp không được quá 100 ký tự.';
    } else {
        // Kiểm tra trùng tên (bỏ qua chính nó)
        $check = $pdo->prepare("SELECT id FROM lop_hoc WHERE name = ? AND id != ?");
        $check->execute([$name, $id]);
        if ($check->fetch()) {
            $errors['name'] = 'Tên lớp này đã tồn tại.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE lop_hoc SET name = ?, status = ? WHERE id = ?");
        $stmt->execute([$name, $status, $id]);
        header('Location: index.php?updated=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Lớp Học</title>
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
        .form-card { background: #fff; border-radius: 12px; max-width: 560px; box-shadow: 0 1px 3px rgba(0,0,0,.08); overflow: hidden; }
        .form-card-header { background: #fffbeb; border-bottom: 1px solid #fde68a; padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: .75rem; }
        .form-card-header i { font-size: 1.2rem; color: #d97706; }
        .form-card-header h5 { margin: 0; font-weight: 700; color: #0f172a; }
        .form-card-body { padding: 1.75rem 1.5rem; }
        .form-label { font-weight: 600; color: #374151; font-size: .9rem; }
        .form-control:focus { border-color: #d97706; box-shadow: 0 0 0 3px rgba(217,119,6,.1); }
        .form-check-input:checked { background-color: #d97706; border-color: #d97706; }
        .breadcrumb { background: none; padding: 0; margin-bottom: 1.5rem; }
        .breadcrumb-item a { color: #2563eb; text-decoration: none; }
        .id-badge { display: inline-flex; align-items: center; gap: .3rem; background: #f1f5f9; border-radius: 6px; padding: .15rem .6rem; font-size: .8rem; color: #64748b; font-family: monospace; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand"><i class="bi bi-mortarboard-fill"></i> Quản Lý SV</div>
    <div class="nav-section">Tổng quan</div>
    <a href="../index.php" class="nav-link"><i class="bi bi-grid-1x2"></i> Dashboard</a>
    <div class="nav-section">Quản lý</div>
    <a href="index.php" class="nav-link active"><i class="bi bi-building"></i> Lớp Học</a>
    <a href="../sinh_vien/index.php" class="nav-link"><i class="bi bi-people"></i> Sinh Viên</a>
</div>

<div class="main-content">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-building"></i> Lớp Học</a></li>
            <li class="breadcrumb-item active">Sửa Lớp</li>
        </ol>
    </nav>

    <div class="form-card">
        <div class="form-card-header">
            <i class="bi bi-pencil-fill"></i>
            <h5>Sửa Thông Tin Lớp Học</h5>
            <span class="id-badge ms-auto">ID #<?= $id ?></span>
        </div>
        <div class="form-card-body">
            <form method="POST" novalidate>
                <!-- Tên lớp -->
                <div class="mb-4">
                    <label class="form-label" for="name">
                        Tên Lớp Học <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                        value="<?= htmlspecialchars($name) ?>"
                        placeholder="VD: CNTT01, KTPM02..."
                        maxlength="100"
                        autofocus
                    >
                    <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback"><?= $errors['name'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Trạng thái -->
                <div class="mb-4">
                    <label class="form-label d-block">Trạng Thái</label>
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="status"
                            name="status"
                            <?= $status ? 'checked' : '' ?>
                        >
                        <label class="form-check-label" for="status">Đang hoạt động</label>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-2 pt-2">
                    <button type="submit" class="btn btn-warning px-4 text-white">
                        <i class="bi bi-save me-1"></i> Cập Nhật
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-arrow-left me-1"></i> Quay Lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
