<?php
require_once '../config/db.php';

$errors = [];
$name   = '';
$email  = '';
$phone  = '';
$lop_hoc_id = '';
$status = 0;

// Lấy danh sách lớp cho dropdown
$dsLop = $pdo->query("SELECT id, name FROM lop_hoc WHERE status = 1 ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name']       ?? '');
    $email      = trim($_POST['email']      ?? '');
    $phone      = trim($_POST['phone']      ?? '');
    $lop_hoc_id = trim($_POST['lop_hoc_id'] ?? '');
    $status     = isset($_POST['status']) ? 1 : 0;

    // Validation
    if ($name === '') {
        $errors['name'] = 'Vui lòng nhập họ tên sinh viên.';
    } elseif (strlen($name) > 100) {
        $errors['name'] = 'Họ tên không được quá 100 ký tự.';
    }

    if ($email === '') {
        $errors['email'] = 'Vui lòng nhập email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email không đúng định dạng.';
    } else {
        $chk = $pdo->prepare("SELECT id FROM sinh_vien WHERE email = ?");
        $chk->execute([$email]);
        if ($chk->fetch()) $errors['email'] = 'Email này đã được sử dụng.';
    }

    if ($phone === '') {
        $errors['phone'] = 'Vui lòng nhập số điện thoại.';
    } elseif (!preg_match('/^[0-9]{9,15}$/', $phone)) {
        $errors['phone'] = 'Số điện thoại không hợp lệ (9–15 chữ số).';
    } else {
        $chk = $pdo->prepare("SELECT id FROM sinh_vien WHERE phone = ?");
        $chk->execute([$phone]);
        if ($chk->fetch()) $errors['phone'] = 'Số điện thoại này đã được sử dụng.';
    }

    if ($lop_hoc_id === '' || !is_numeric($lop_hoc_id)) {
        $errors['lop_hoc_id'] = 'Vui lòng chọn lớp học.';
    } else {
        $chk = $pdo->prepare("SELECT id FROM lop_hoc WHERE id = ?");
        $chk->execute([$lop_hoc_id]);
        if (!$chk->fetch()) $errors['lop_hoc_id'] = 'Lớp học không tồn tại.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO sinh_vien (name, email, phone, lop_hoc_id, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $lop_hoc_id, $status]);
        header('Location: index.php?added=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Sinh Viên Mới</title>
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

        .form-card { background: #fff; border-radius: 12px; max-width: 620px; box-shadow: 0 1px 3px rgba(0,0,0,.08); overflow: hidden; }
        .form-card-header { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 1.5rem; color: #fff; }
        .form-card-header h5 { margin: 0; font-weight: 700; font-size: 1.1rem; }
        .form-card-header p { margin: .25rem 0 0; opacity: .8; font-size: .85rem; }
        .form-card-body { padding: 2rem 1.75rem; }
        .form-label { font-weight: 600; color: #374151; font-size: .875rem; margin-bottom: .4rem; }
        .form-label .req { color: #dc2626; }
        .input-group-text { background: #f8fafc; border-color: #e2e8f0; color: #64748b; }
        .form-control, .form-select {
            border-color: #e2e8f0;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.12);
        }
        .form-control.is-invalid, .form-select.is-invalid { border-color: #dc2626; }
        .form-control.is-invalid:focus, .form-select.is-invalid:focus { box-shadow: 0 0 0 3px rgba(220,38,38,.12); }
        .breadcrumb { background: none; padding: 0; margin-bottom: 1.5rem; }
        .breadcrumb-item a { color: #2563eb; text-decoration: none; }
        .section-divider { border: 0; border-top: 1px solid #f1f5f9; margin: 1.5rem 0; }
        .form-check-input:checked { background-color: #2563eb; border-color: #2563eb; }
        .no-class-warn { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: .75rem 1rem; font-size: .85rem; color: #92400e; }
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

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-people"></i> Sinh Viên</a></li>
            <li class="breadcrumb-item active">Thêm Mới</li>
        </ol>
    </nav>

    <div class="form-card">
        <div class="form-card-header">
            <h5><i class="bi bi-person-plus-fill me-2"></i>Thêm Sinh Viên Mới</h5>
            <p>Điền đầy đủ thông tin để tạo hồ sơ sinh viên</p>
        </div>

        <div class="form-card-body">
            <?php if (empty($dsLop)): ?>
            <div class="no-class-warn mb-4">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                Chưa có lớp học nào đang hoạt động.
                <a href="../lop_hoc/add.php">Tạo lớp học trước</a> rồi thêm sinh viên.
            </div>
            <?php endif; ?>

            <form method="POST" novalidate>

                <!-- Họ tên -->
                <div class="mb-3">
                    <label class="form-label" for="name">Họ Và Tên <span class="req">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($name) ?>"
                            placeholder="Nguyễn Văn A"
                            maxlength="100"
                            autofocus
                        >
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?= $errors['name'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label class="form-label" for="email">Email <span class="req">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($email) ?>"
                            placeholder="sinhvien@email.com"
                            maxlength="100"
                        >
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= $errors['email'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Số điện thoại -->
                <div class="mb-3">
                    <label class="form-label" for="phone">Số Điện Thoại <span class="req">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-phone"></i></span>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($phone) ?>"
                            placeholder="0912345678"
                            maxlength="15"
                        >
                        <?php if (isset($errors['phone'])): ?>
                            <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <hr class="section-divider">

                <!-- Lớp học -->
                <div class="mb-3">
                    <label class="form-label" for="lop_hoc_id">Lớp Học <span class="req">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                        <select
                            id="lop_hoc_id"
                            name="lop_hoc_id"
                            class="form-select <?= isset($errors['lop_hoc_id']) ? 'is-invalid' : '' ?>"
                        >
                            <option value="">-- Chọn lớp học --</option>
                            <?php foreach ($dsLop as $lop): ?>
                                <option value="<?= $lop['id'] ?>" <?= $lop_hoc_id == $lop['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($lop['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['lop_hoc_id'])): ?>
                            <div class="invalid-feedback"><?= $errors['lop_hoc_id'] ?></div>
                        <?php endif; ?>
                    </div>
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
                        <label class="form-check-label" for="status">Sinh viên đang học</label>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4" <?= empty($dsLop) ? 'disabled' : '' ?>>
                        <i class="bi bi-person-plus me-1"></i> Thêm Sinh Viên
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
