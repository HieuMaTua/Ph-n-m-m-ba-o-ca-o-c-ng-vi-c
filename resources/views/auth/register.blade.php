<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng ký - MTAC+</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
  <div class="card p-4 shadow" style="width:400px;">
    <h3 class="text-center mb-3">Đăng ký</h3>

    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Tên</label>
            <input id="name" type="text" name="name" class="form-control" required autofocus>
        </div>
    
        <div class="mb-3">
            <label for="phone" class="form-label">Số điện thoại</label>
            <input id="phone" type="text" name="phone" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">email</label>
          <input id="email" type="email" name="email" class="form-control" required>
      </div>
    
        <div class="mb-3">
            <label for="password" class="form-label">Mật khẩu</label>
            <input id="password" type="password" name="password" class="form-control" required>
        </div>
    
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
        </div>
    
        <button type="submit" class="btn btn-primary w-100">Đăng ký</button>
    </form>    

    <div class="mt-3 text-center">
      <p>Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></p>
    </div>
  </div>
</body>
</html>
