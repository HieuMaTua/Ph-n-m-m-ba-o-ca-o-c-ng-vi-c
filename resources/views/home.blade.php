<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Báo cáo công việc - Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="icon" href="{{ asset('favicon_io/favicon-32x32.png') }}" type="image/png">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      background-color: #f6f8fb;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    a { text-decoration: none; }
    .sidebar {
      height: 100vh;
      background: linear-gradient(180deg, #1976f3, #0d47a1);
      color: white;
      padding: 20px 15px;
      position: fixed;
      width: 240px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      border-radius: 0 20px 20px 0;
    }
    .sidebar h2 {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 20px;
    }
    .sidebar a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 14px;
      color: #ecf0f1;
      border-radius: 12px;
      margin-bottom: 8px;
      transition: all 0.3s;
      font-weight: 500;
    }
    .sidebar a:hover, .sidebar a.active {
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
    }
    .profile {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      padding: 8px;
      display: flex;
      align-items: center;
      transition: background 0.3s;
    }
    .profile img {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #fff;
    }
    .profile h6 {
      font-size: 14px;
      font-weight: 600;
      margin: 0;
    }
    .btn-logout {
      background: transparent;
      border: none;
      color: #fff;
      font-size: 18px;
      margin-left: 8px;
      transition: color 0.3s;
    }
    .btn-logout:hover { color: #e74c3c; }
    .content {
      margin-left: 240px;
      padding: 25px;
      min-height: 100vh;
    }
    h1 {
      font-size: 26px;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 25px;
    }
    .stat-card {
      background: #fff;
      border: none;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      padding: 20px;
      transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-4px); }
    .stat-card h5 { font-size: 16px; color: #7f8c8d; }
    .stat-card h2 { font-size: 28px; font-weight: bold; margin-top: 10px; }
    .error-message {
      display: none;
      color: #e74c3c;
      font-size: 14px;
      margin-top: 5px;
    }
    .search-filter {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
    }
    .search-filter input, .search-filter select {
      border-radius: 8px;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div>
      <h2><i class="bi bi-kanban"></i> Quản lý</h2>
      <a href="{{ route('home') }}" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <a href="{{ route('reports.index') }}"><i class="bi bi-clipboard-data"></i> Báo cáo</a>
      <a href="{{ route('tasks.calendar') }}"><i class="bi bi-list-task"></i> Công việc</a>
      <a href="{{ route('nhansu.index') }}"><i class="bi bi-people"></i> Nhân sự</a>
      <a href="#"><i class="bi bi-gear"></i> Cài đặt</a>
    </div>
    <div class="profile">
      <img src="https://i.pravatar.cc/100" alt="Avatar" class="me-2">
      <div class="flex-grow-1">
        <h6>{{ Auth::user()->name ?? 'Người dùng' }}</h6>
        <small>
          @if(Auth::user() && Auth::user()->role)
            @if(Auth::user()->role == 'director')
              Giám đốc
            @elseif(Auth::user()->role == 'manager')
              Quản lý
            @else
              Nhân viên
            @endif
          @else
            Không xác định
          @endif
        </small>
      </div>
      <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn-logout"><i class="bi bi-box-arrow-right"></i></button>
      </form>
    </div>
  </div>

  <!-- Content -->
  <div class="content">
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <h1>Dashboard báo cáo công việc</h1>

    <div class="card mb-3" style="border-radius: 12px; background: linear-gradient(90deg, #007bff, #00c6ff); color: white;">
      <div class="card-body d-flex justify-content-between align-items-center">
          <div>
              <h4>Xin chào, {{ Auth::user()->name }}</h4>
              <p id="currentDate"></p>
              <p id="currentTime" style="font-weight: bold;"></p>
          </div>
          <div class="text-end">
              <i class="bi bi-list-task" style="font-size: 2rem;"></i>
              <p class="mb-0">Hôm nay có <strong>{{ $tasksToday }}</strong> công việc</p>
              <small>Tiến độ trung bình: {{ $avgProgress ?? 0 }}%</small>
          </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-md-3">
        <div class="stat-card">
          <h5>Công việc hôm nay</h5>
          <h2>{{ $tasksToday ?? 0 }}</h2>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <h5>Đã hoàn thành</h5>
          <h2>{{ $completed ?? 0 }}</h2>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <h5>Đang thực hiện</h5>
          <h2>{{ $inProgress ?? 0 }}</h2>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <h5>Quá hạn</h5>
          <h2>{{ $overdue ?? 0 }}</h2>
        </div>
      </div>
    </div>

    <div class="card mt-4 p-4">
      <h5>Thống kê tiến độ</h5>
      @if(($completed ?? 0) + ($inProgress ?? 0) + ($overdue ?? 0) == 0)
        <p class="text-muted">Chưa có dữ liệu để hiển thị biểu đồ.</p>
      @else
        <canvas id="taskChart" height="100"></canvas>
      @endif
    </div>

    <div class="card mt-4 p-4">
      <h5>Danh sách công việc</h5>
      <div class="search-filter">
        <input type="text" id="searchInput" class="form-control" placeholder="Tìm kiếm công việc...">
        <select id="statusFilter" class="form-control" style="width: 200px;">
          <option value="">Tất cả trạng thái</option>
          <option value="pending">Chờ xử lý</option>
          <option value="in_progress">Đang làm</option>
          <option value="completed">Hoàn thành</option>
          <option value="overdue">Quá hạn</option>
        </select>
        <select id="roleFilter" class="form-control" style="width: 200px;">
          <option value="">Tất cả chức vụ</option>
          <option value="director">Giám đốc</option>
          <option value="manager">Quản lý</option>
          <option value="staff">Nhân viên</option>
        </select>
      </div>

      <form id="taskForm" action="{{ route('tasks.store') }}" method="POST" class="row g-3 mb-4" enctype="multipart/form-data">
        @csrf
        <div class="col-md-3">
          <input type="text" name="title" class="form-control" placeholder="Tên công việc" required>
          <div class="error-message" id="title-error">Vui lòng nhập tên công việc.</div>
        </div>
        <div class="col-md-2">
          <select name="status" class="form-control">
            <option value="pending">Chờ xử lý</option>
            <option value="in_progress">Đang làm</option>
            <option value="completed">Hoàn thành</option>
            <option value="overdue">Quá hạn</option>
          </select>
        </div>
        <div class="col-md-2">
          <input type="date" name="deadline" class="form-control">
        </div>
        <div class="col-md-2">
          <input type="number" name="progress" min="0" max="100" class="form-control" placeholder="% tiến độ"
                 @if(Auth::user()->role == 'staff') disabled @endif>
          <div class="error-message" id="progress-error">Tiến độ phải từ 0 đến 100.</div>
        </div>
        <div class="col-md-2">
          <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png">
        </div>
        <div class="col-md-1">
          <button type="submit" class="btn btn-primary w-100">Thêm</button>
        </div>
      </form>

      <table class="table table-bordered table-hover" id="taskTable">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Tên công việc</th>
            <th>Chức vụ</th>
            <th>Thuộc sự quản lý</th>
            <th>Trạng thái</th>
            <th>Hạn chót</th>
            <th>Ngày tạo</th>
            <th>File</th>
            <th>Hành động</th>
            <th>Tiến độ</th>
          </tr>
        </thead>
        <tbody>
          @forelse($tasks as $task)
            <tr data-status="{{ $task->status }}" data-role="{{ $task->user->role ?? '' }}">
              <td>{{ $task->id }}</td>
              <td>{{ $task->title }}</td>
              <td>
                @if($task->user && $task->user->role == 'director')
                  <span class="badge bg-primary">Giám đốc</span>
                @elseif($task->user && $task->user->role == 'manager')
                  <span class="badge bg-info">Quản lý</span>
                @else
                  <span class="badge bg-secondary">Nhân viên</span>
                @endif
              </td>
              <td>{{ $task->user && $task->user->manager ? $task->user->manager->name : 'Không có' }}</td>
              <td>
                @if($task->status == 'completed')
                  <span class="badge bg-success">Hoàn thành</span>
                @elseif($task->status == 'in_progress')
                  <span class="badge bg-warning text-dark">Đang làm</span>
                @elseif($task->status == 'overdue')
                  <span class="badge bg-danger">Quá hạn</span>
                @else
                  <span class="badge bg-secondary">Chờ xử lý</span>
                @endif
              </td>
              <td>{{ $task->deadline }}</td>
              <td>{{ $task->created_at->format('d/m/Y') }}</td>
              <td>
                @if($task->file_path)
                  @if(Auth::user()->role == 'director' || ($task->user && $task->user->manager_id == Auth::id()) || $task->user_id == Auth::id())
                    <a href="{{ asset('storage/' . $task->file_path) }}" target="_blank" class="btn btn-sm btn-info">Xem file</a>
                  @else
                    <span class="text-muted">Chỉ giám đốc, quản lý phụ trách, hoặc chủ file xem được</span>
                  @endif
                @else
                  Không có
                @endif
              </td>
              <td>
                @if(Auth::user()->role == 'director' || ($task->user && $task->user->manager_id == Auth::id()) || $task->user_id == Auth::id())
                  <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $task->id }}">Sửa</button>
                  <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa công việc này?')">Xóa</button>
                  </form>
                @else
                  <span class="text-muted">Không có quyền</span>
                @endif
              </td>
              <td style="width:180px;">
                <div class="progress">
                  <div class="progress-bar {{ $task->progress == 100 ? 'bg-success' : 'bg-info' }}" 
                       role="progressbar" 
                       style="width: {{ $task->progress ?? 0 }}%">
                    {{ $task->progress ?? 0 }}%
                  </div>
                </div>
              </td>
            </tr>

            <!-- Modal sửa -->
            <div class="modal fade" id="editModal{{ $task->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form action="{{ route('tasks.update', $task) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                      <h5 class="modal-title">Chỉnh sửa công việc</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Tên công việc</label>
                        <input type="text" name="title" class="form-control" value="{{ $task->title }}" required>
                        <div class="error-message" id="edit-title-error{{ $task->id }}">Vui lòng nhập tên công việc.</div>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-control">
                          <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                          <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>Đang làm</option>
                          <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                          <option value="overdue" {{ $task->status == 'overdue' ? 'selected' : '' }}>Quá hạn</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Hạn chót</label>
                        <input type="date" name="deadline" class="form-control" value="{{ $task->deadline }}">
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Tiến độ (%)</label>
                        <input type="number" name="progress" min="0" max="100" class="form-control" value="{{ $task->progress ?? 0 }}"
                               @if(Auth::user()->role == 'staff') disabled @endif>
                        <div class="error-message" id="edit-progress-error{{ $task->id }}">Tiến độ phải từ 0 đến 100.</div>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Upload file (nếu thay đổi)</label>
                        <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png">
                        @if($task->file_path)
                          <small>File hiện tại: <a href="{{ asset('storage/' . $task->file_path) }}" target="_blank">Xem file</a></small>
                        @endif
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                      <button type="submit" class="btn btn-primary">Lưu</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          @empty
            <tr>
              <td colspan="10" class="text-center">Chưa có công việc nào.</td>
            </tr>
          @endforelse
        </tbody>
      </table>

      @if($tasks instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-3">
          {{ $tasks->links() }}
        </div>
      @endif
    </div>
  </div>

  <!-- JavaScript -->
  <script>
    // Kiểm tra form thêm công việc
    document.getElementById('taskForm').addEventListener('submit', function (e) {
      const title = document.querySelector('input[name="title"]').value;
      const progress = document.querySelector('input[name="progress"]').value;
      const file = document.querySelector('input[name="file"]').files[0];
      const titleError = document.getElementById('title-error');
      const progressError = document.getElementById('progress-error');
      let hasError = false;

      titleError.style.display = 'none';
      progressError.style.display = 'none';

      if (!title.trim()) {
        titleError.style.display = 'block';
        hasError = true;
      }

      if (progress && (progress < 0 || progress > 100)) {
        progressError.style.display = 'block';
        hasError = true;
      }

      if (file) {
        const validExtensions = ['pdf', 'doc', 'docx', 'jpg', 'png'];
        const extension = file.name.split('.').pop().toLowerCase();
        if (!validExtensions.includes(extension)) {
          alert('File phải có định dạng: pdf, doc, docx, jpg, png');
          hasError = true;
        }
      }

      if (hasError) {
        e.preventDefault();
      }
    });

    // Kiểm tra form chỉnh sửa công việc
    document.querySelectorAll('form[action*="/tasks/"]').forEach(form => {
      form.addEventListener('submit', function (e) {
        const title = form.querySelector('input[name="title"]').value;
        const progress = form.querySelector('input[name="progress"]').value;
        const file = form.querySelector('input[name="file"]').files[0];
        const titleError = form.querySelector('[id*="edit-title-error"]');
        const progressError = form.querySelector('[id*="edit-progress-error"]');
        let hasError = false;

        titleError.style.display = 'none';
        progressError.style.display = 'none';

        if (!title.trim()) {
          titleError.style.display = 'block';
          hasError = true;
        }

        if (progress && (progress < 0 || progress > 100)) {
          progressError.style.display = 'block';
          hasError = true;
        }

        if (file) {
          const validExtensions = ['pdf', 'doc', 'docx', 'jpg', 'png'];
          const extension = file.name.split('.').pop().toLowerCase();
          if (!validExtensions.includes(extension)) {
            alert('File phải có định dạng: pdf, doc, docx, jpg, png');
            hasError = true;
          }
        }

        if (hasError) {
          e.preventDefault();
        }
      });
    });

    // Tìm kiếm và lọc bảng
    function filterTable() {
      const searchInput = document.getElementById('searchInput').value.toLowerCase();
      const statusFilter = document.getElementById('statusFilter').value;
      const roleFilter = document.getElementById('roleFilter').value;
      const rows = document.querySelectorAll('#taskTable tbody tr');

      rows.forEach(row => {
        const title = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const status = row.getAttribute('data-status');
        const role = row.getAttribute('data-role');
        const matchesSearch = title.includes(searchInput);
        const matchesStatus = !statusFilter || status === statusFilter;
        const matchesRole = !roleFilter || role === roleFilter;

        row.style.display = matchesSearch && matchesStatus && matchesRole ? '' : 'none';
      });
    }

    document.getElementById('searchInput').addEventListener('input', filterTable);
    document.getElementById('statusFilter').addEventListener('change', filterTable);
    document.getElementById('roleFilter').addEventListener('change', filterTable);

    // Khởi tạo biểu đồ cột
    document.addEventListener('DOMContentLoaded', function () {
      const ctx = document.getElementById('taskChart');
      if (ctx) {
        new Chart(ctx, {
          type: 'bar',
          data: {
            labels: ['Hoàn thành', 'Đang làm', 'Quá hạn'],
            datasets: [{
              label: 'Số lượng công việc',
              data: [{{ $completed ?? 0 }}, {{ $inProgress ?? 0 }}, {{ $overdue ?? 0 }}],
              backgroundColor: ['#2ecc71', '#f1c40f', '#e74c3c'],
              borderColor: ['#27ae60', '#e67e22', '#c0392b'],
              borderWidth: 1
            }]
          },
          options: {
            scales: {
              y: {
                beginAtZero: true,
                title: {
                  display: true,
                  text: 'Số lượng công việc'
                }
              },
              x: {
                title: {
                  display: true,
                  text: 'Trạng thái'
                }
              }
            },
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    let label = context.dataset.label || '';
                    let value = context.parsed.y || 0;
                    return `${label}: ${value} công việc`;
                  }
                }
              }
            }
          }
        });
      }
    });

    function updateDateTime() {
      const now = new Date();
      const days = ['Chủ Nhật','Thứ Hai','Thứ Ba','Thứ Tư','Thứ Năm','Thứ Sáu','Thứ Bảy'];
      const dayName = days[now.getDay()];
      const date = now.getDate();
      const month = now.getMonth() + 1;
      const year = now.getFullYear();
      const formattedDate = `${dayName}, ${date} tháng ${month}, ${year}`;
      const time = now.toLocaleTimeString('vi-VN');
      document.querySelectorAll("#currentDate").forEach(el => el.innerText = formattedDate);
      document.querySelectorAll("#currentTime").forEach(el => el.innerText = time);
    }

    setInterval(updateDateTime, 1000);
    updateDateTime();
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>