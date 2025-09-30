<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Báo cáo công việc</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="icon" href="{{ asset('favicon_io/favicon-32x32.png') }}" type="image/png">
  <style>
    body {
      background-color: #f6f8fb;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    a { text-decoration: none; }

    /* Sidebar */
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

    /* Profile */
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

    /* Content */
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

    /* Cards */
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

    /* Filter */
    .filter {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
    }
    .filter select, .filter button {
      border-radius: 8px;
      width: 200px;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div>
      <h2><i class="bi bi-kanban"></i> Quản lý</h2>
      <a href="{{ route('home') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <a href="{{ route('reports.index') }}" class="active"><i class="bi bi-clipboard-data"></i> Báo cáo</a>
      <a href="{{ route('tasks.calendar') }}"><i class="bi bi-list-task"></i> Công việc</a>
      <a href="#"><i class="bi bi-people"></i> Nhân sự</a>
      <a href="#"><i class="bi bi-gear"></i> Cài đặt</a>
    </div>

    <!-- Profile -->
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
    <!-- Hiển thị thông báo -->
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

    <h1>Báo cáo công việc</h1>

    <!-- Bộ lọc thời gian và xuất PDF -->
    <div class="filter">
      <select id="timeFilter" class="form-control">
        <option value="today">Hôm nay</option>
        <option value="week">Tuần này</option>
        <option value="month">Tháng này</option>
      </select>
      <a href="{{ route('reports.export') }}?period=today" id="exportPDF" class="btn btn-primary">Xuất PDF</a>
    </div>

    <!-- Thống kê -->
    <div class="row g-3">
      <div class="col-md-3">
        <div class="stat-card">
          <h5>Tổng công việc</h5>
          <h2 id="totalTasks">{{ $totalTasks ?? 0 }}</h2>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <h5>Hoàn thành đúng hạn</h5>
          <h2 id="completedOnTime">{{ $completedOnTime ?? 0 }}</h2>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <h5>Tiến độ trung bình</h5>
          <h2 id="avgProgress">{{ $avgProgress ?? 0 }}%</h2>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <h5>Tỷ lệ quá hạn</h5>
          <h2 id="overdueRate">{{ $overdueRate ?? 0 }}%</h2>
        </div>
      </div>
    </div>

    <!-- Biểu đồ tiến độ trung bình -->
    <div class="card mt-4 p-4">
      <h5>Tiến độ trung bình theo thời gian</h5>
      @if(empty($progressData))
        <p class="text-muted">Chưa có dữ liệu để hiển thị biểu đồ.</p>
      @else
        <canvas id="progressChart" height="100"></canvas>
      @endif
    </div>

    <!-- Biểu đồ so sánh trạng thái -->
    <div class="card mt-4 p-4">
      <h5>So sánh trạng thái công việc</h5>
      @if(empty($comparisonData))
        <p class="text-muted">Chưa có dữ liệu để hiển thị biểu đồ.</p>
      @else
        <canvas id="comparisonChart" height="100"></canvas>
      @endif
    </div>

    <!-- Thống kê theo người phụ trách -->
    <div class="card mt-4 p-4">
      <h5>Thống kê theo người phụ trách</h5>
      <table class="table table-bordered table-hover" id="userStatsTable">
        <thead class="table-dark">
          <tr>
            <th>Người phụ trách</th>
            <th>Số công việc</th>
            <th>Tiến độ trung bình</th>
          </tr>
        </thead>
        <tbody id="userStatsBody">
          @forelse($userStats as $stat)
            <tr>
              <td>{{ $stat->user ? $stat->user->name : 'Không xác định' }}</td>
              <td>{{ $stat->total }}</td>
              <td>{{ round($stat->avg_progress, 2) }}%</td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center">Chưa có dữ liệu.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Danh sách công việc -->
    <div class="card mt-4 p-4">
      <h5>Danh sách công việc trong khoảng thời gian</h5>
      <table class="table table-bordered table-hover" id="reportTable">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Tên công việc</th>
            <th>Trạng thái</th>
            <th>Hạn chót</th>
            <th>Tiến độ</th>
            <th>Người phụ trách</th>
          </tr>
        </thead>
        <tbody id="reportTableBody">
          @forelse($tasks as $task)
            <tr>
              <td>{{ $task->id }}</td>
              <td>{{ $task->title }}</td>
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
              <td style="width:180px;">
                <div class="progress">
                  <div class="progress-bar {{ $task->progress == 100 ? 'bg-success' : 'bg-info' }}" 
                       role="progressbar" 
                       style="width: {{ $task->progress ?? 0 }}%">
                    {{ $task->progress ?? 0 }}%
                  </div>
                </div>
              </td>
              <td>{{ $task->user ? $task->user->name : 'Không xác định' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center">Chưa có công việc nào.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- JavaScript -->
  <script>
    // Dữ liệu ban đầu từ server
    const initialData = {
      today: {
        total: {{ $totalTasks ?? 0 }},
        completedOnTime: {{ $completedOnTime ?? 0 }},
        avgProgress: {{ $avgProgress ?? 0 }},
        overdueRate: {{ $overdueRate ?? 0 }},
        progressData: {!! json_encode($progressData ?? []) !!},
        comparisonData: {!! json_encode($comparisonData ?? []) !!},
        tasks: {!! json_encode($tasks ?? []) !!},
        userStats: {!! json_encode($userStats->map(function($stat) {
            return [
                'user_id' => $stat->user_id,
                'user_name' => $stat->user ? $stat->user->name : 'Không xác định',
                'total' => $stat->total,
                'avg_progress' => round($stat->avg_progress, 2)
            ];
        })->toArray()) !!}
      },
      week: { total: 0, completedOnTime: 0, avgProgress: 0, overdueRate: 0, progressData: [], comparisonData: [], tasks: [], userStats: [] },
      month: { total: 0, completedOnTime: 0, avgProgress: 0, overdueRate: 0, progressData: [], comparisonData: [], tasks: [], userStats: [] }
    };

    // Khởi tạo biểu đồ tiến độ trung bình (đường)
    let progressChart;
    function initProgressChart(data) {
      const ctx = document.getElementById('progressChart');
      if (ctx) {
        if (progressChart) progressChart.destroy();
        progressChart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: data.progressData.map(item => item.date),
            datasets: [{
              label: 'Tiến độ trung bình (%)',
              data: data.progressData.map(item => item.progress),
              borderColor: '#2ecc71',
              backgroundColor: 'rgba(46, 204, 113, 0.2)',
              fill: true,
              tension: 0.4
            }]
          },
          options: {
            scales: {
              y: { beginAtZero: true, max: 100, title: { display: true, text: 'Tiến độ trung bình (%)' } },
              x: { title: { display: true, text: 'Ngày' } }
            },
            plugins: {
              legend: { display: true, position: 'top' },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    return `${context.dataset.label}: ${context.parsed.y}%`;
                  }
                }
              }
            }
          }
        });
      }
    }

    // Khởi tạo biểu đồ so sánh trạng thái (cột)
    let comparisonChart;
    function initComparisonChart(data) {
      const ctx = document.getElementById('comparisonChart');
      if (ctx) {
        if (comparisonChart) comparisonChart.destroy();
        comparisonChart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: ['Hoàn thành', 'Đang làm', 'Quá hạn'],
            datasets: [{
              label: 'Số lượng công việc',
              data: [data.comparisonData.completed, data.comparisonData.in_progress, data.comparisonData.overdue],
              backgroundColor: ['#2ecc71', '#f1c40f', '#e74c3c'],
              borderColor: ['#27ae60', '#e67e22', '#c0392b'],
              borderWidth: 1
            }]
          },
          options: {
            scales: {
              y: { beginAtZero: true, title: { display: true, text: 'Số lượng công việc' } },
              x: { title: { display: true, text: 'Trạng thái' } }
            },
            plugins: {
              legend: { display: false },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    return `${context.dataset.label}: ${context.parsed.y} công việc`;
                  }
                }
              }
            }
          }
        });
      }
    }

    // Cập nhật bảng công việc
    function updateTable(tasks) {
      const tbody = document.getElementById('reportTableBody');
      tbody.innerHTML = '';
      if (tasks.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Chưa có công việc nào.</td></tr>';
        return;
      }
      tasks.forEach(task => {
        const statusBadge = task.status === 'completed' ? '<span class="badge bg-success">Hoàn thành</span>' :
                           task.status === 'in_progress' ? '<span class="badge bg-warning text-dark">Đang làm</span>' :
                           task.status === 'overdue' ? '<span class="badge bg-danger">Quá hạn</span>' :
                           '<span class="badge bg-secondary">Chờ xử lý</span>';
        const row = `
          <tr>
            <td>${task.id}</td>
            <td>${task.title}</td>
            <td>${statusBadge}</td>
            <td>${task.deadline || ''}</td>
            <td style="width:180px;">
              <div class="progress">
                <div class="progress-bar ${task.progress == 100 ? 'bg-success' : 'bg-info'}" 
                     role="progressbar" 
                     style="width: ${task.progress || 0}%">
                  ${task.progress || 0}%
                </div>
              </div>
            </td>
            <td>${task.user ? task.user.name : 'Không xác định'}</td>
          </tr>`;
        tbody.innerHTML += row;
      });
    }

    // Cập nhật bảng thống kê người phụ trách
    function updateUserStatsTable(userStats) {
      const tbody = document.getElementById('userStatsBody');
      tbody.innerHTML = '';
      if (userStats.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center">Chưa có dữ liệu.</td></tr>';
        return;
      }
      userStats.forEach(stat => {
        const row = `
          <tr>
            <td>${stat.user_name}</td>
            <td>${stat.total}</td>
            <td>${stat.avg_progress}%</td>
          </tr>`;
        tbody.innerHTML += row;
      });
    }

    // Cập nhật dashboard
    function updateDashboard(data) {
      document.getElementById('totalTasks').textContent = data.total;
      document.getElementById('completedOnTime').textContent = data.completedOnTime;
      document.getElementById('avgProgress').textContent = `${data.avgProgress}%`;
      document.getElementById('overdueRate').textContent = `${data.overdueRate}%`;
      document.getElementById('exportPDF').href = `{{ route('reports.export') }}?period=${document.getElementById('timeFilter').value}`;
      initProgressChart(data);
      initComparisonChart(data);
      updateTable(data.tasks);
      updateUserStatsTable(data.userStats);
    }

    // Xử lý bộ lọc thời gian
    document.getElementById('timeFilter').addEventListener('change', function() {
      const period = this.value;
      if (initialData[period].total !== 0) {
        updateDashboard(initialData[period]);
      } else {
        fetch(`/reports/data?period=${period}`)
          .then(response => response.json())
          .then(data => {
            initialData[period] = data;
            updateDashboard(data);
          })
          .catch(error => console.error('Lỗi khi tải dữ liệu:', error));
      }
    });

    // Khởi tạo ban đầu
    document.addEventListener('DOMContentLoaded', function() {
      updateDashboard(initialData.today);
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>