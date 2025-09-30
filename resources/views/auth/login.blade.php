<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng nhập - MTAC+</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f4f6f9;
    }
    .login-container {
      display: flex;
      min-height: 100vh;
    }
    .login-left {
      flex: 1;
      background: url('https://khachhang.moitruongachau.com/storage/login-pages/RD9FVpa8NBdO2GYCmpurtHtlHk5CMFEoDi98hasJ.png') no-repeat center center;
      background-size: cover;
    }
    .login-right {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .card {
      width: 400px;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-left"></div>
    <div class="login-right">
      <div class="card">
        <div class="text-center mb-3">
          <h3><span class="text-primary">MTAC</span><span class="text-danger">+</span></h3>
          <p>Phần Mềm Báo Cáo Công Việc</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
          @csrf
          <div class="mb-3">
            <label>Tài khoản</label>
            <input type="text" name="login" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <div class="d-flex justify-content-between mb-3">
            <div>
              <input type="checkbox" name="remember"> Ghi nhớ
            </div>
            <a href="#">Quên mật khẩu?</a>
          </div>
          <button class="btn btn-primary w-100">Đăng nhập</button>
        </form>

        <div class="mt-3 text-center">
          <p>Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký</a></p>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
