<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo công việc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" href="{{ asset('favicon_io/favicon-32x32.png') }}" type="image/png">
    <style>
        /* Giữ nguyên các style hiện tại */
        .content {
            margin-left: 240px;
            padding: 25px;
            min-height: 100vh;
            background: #f8f9fa;
        }
        h1 {
            font-size: 26px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 25px;
        }
        .card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: transform 0.2s;
            background: #ffffff;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background: linear-gradient(90deg, #3498db, #1abc9c);
            color: white;
            font-weight: 600;
            padding: 15px;
            border-radius: 12px 12px 0 0;
        }
        .card-body {
            padding: 20px;
        }
        .stat-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .stat-card:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
            transform: translateY(-4px);
        }
        .stat-card h5 {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        .stat-card h2 {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
        }
        .filter {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }
        .filter select, .filter button {
            border-radius: 8px;
            width: 200px;
        }
        .filter .btn-primary {
            background: linear-gradient(90deg, #3498db, #1abc9c);
            border: none;
        }
        .filter .btn-primary:hover {
            background: linear-gradient(90deg, #2980b9, #16a085);
        }
        .loading {
            display: none;
            color: #3498db;
            font-size: 14px;
            margin-left: 10px;
        }
        .loading i {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .table {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .table thead {
            background: #2c3e50;
            color: white;
        }
        .table tbody tr {
            transition: background-color 0.2s;
        }
        .table tbody tr:hover {
            background-color: #f1f3f5;
        }
        .badge {
            font-size: 0.9rem;
            padding: 6px 10px;
        }
        .progress {
            border-radius: 8px;
            height: 20px;
        }
        .chart-container {
            position: relative;
            width: 100%;
            padding: 20px;
        }
        .chart-container canvas {
            width: 100% !important;
            height: auto !important;
            max-height: 300px;
        }
        @media (max-width: 576px) {
            .content {
                margin-left: 0;
                padding: 15px;
            }
            .filter {
                flex-direction: column;
                align-items: stretch;
            }
            .filter select, .filter button {
                width: 100%;
            }
            .stat-card h2 {
                font-size: 24px;
            }
            .table {
                font-size: 0.85rem;
            }
            .chart-container canvas {
                max-height: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    @include('layout.sidebar')

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

        <!-- Bộ lọc thời gian và xuất PDF -->
        <div class="card mb-4">
            <div class="card-header">Bộ lọc báo cáo</div>
            <div class="card-body">
                <div class="filter">
                    <select id="timeFilter" class="form-control">
                        <option value="today">Hôm nay</option>
                        <option value="week">Tuần này</option>
                        <option value="month">Tháng này</option>
                    </select>
                    <a href="{{ route('reports.export') }}?period=today" id="exportPDF" class="btn btn-primary">
                        <span class="text">Xuất PDF</span>
                        <span class="loading"><i class="bi bi-arrow-clockwise"></i> Đang xử lý...</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Thống kê -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <h5>Tổng công việc</h5>
                    <h2 id="totalTasks">{{ $tasks->count() }}</h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h5>Hoàn thành</h5>
                    <h2 id="completed">{{ $comparisonData['completed'] }}</h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h5>Đang thực hiện</h5>
                    <h2 id="inProgress">{{ $comparisonData['in_progress'] }}</h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h5>Quá hạn</h5>
                    <h2 id="overdue">{{ $comparisonData['overdue'] }}</h2>
                </div>
            </div>
        </div>

        <!-- Biểu đồ tiến độ trung bình -->
        <div class="card mt-4">
            <div class="card-header">Tiến độ trung bình theo thời gian</div>
            <div class="card-body">
                <div class="chart-container">
                    @if(empty($progressData))
                        <p class="text-muted">Chưa có dữ liệu để hiển thị biểu đồ.</p>
                    @else
                        <canvas id="progressChart" height="100"></canvas>
                    @endif
                </div>
            </div>
        </div>

        <!-- Biểu đồ so sánh trạng thái -->
        <div class="card mt-4">
            <div class="card-header">So sánh trạng thái công việc</div>
            <div class="card-body">
                <div class="chart-container">
                    @if(empty($comparisonData))
                        <p class="text-muted">Chưa có dữ liệu để hiển thị biểu đồ.</p>
                    @else
                        <canvas id="comparisonChart" height="100"></canvas>
                    @endif
                </div>
            </div>
        </div>

        <!-- Thống kê theo người phụ trách -->
        <div class="card mt-4">
            <div class="card-header">Thống kê theo người phụ trách</div>
            <div class="card-body">
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
                                <td>{{ $stat->user ? ($stat->user->manager ? $stat->user->manager->name : 'Không có') : 'Không xác định' }}</td>
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
        </div>

        <!-- Danh sách công việc -->
        <div class="card mt-4">
            <div class="card-header">Danh sách công việc trong khoảng thời gian</div>
            <div class="card-body">
                <table class="table table-bordered table-hover" id="reportTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Tên công việc</th>
                            <th>Trạng thái</th>
                            <th>Hạn chót</th>
                            <th>Tiến độ</th>
                            <th>Nhân viên</th>
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
                                <td>{{ $task->deadline ? date('d/m/Y', strtotime($task->deadline)) : 'Chưa đặt' }}</td>
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
                                <td>{{ $task->user && $task->user->manager ? $task->user->manager->name : 'Không có' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Chưa có công việc nào của bạn.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Dữ liệu ban đầu từ server
        const initialData = {
            today: {
                total: {{ $tasks->count() }},
                completed: {{ $comparisonData['completed'] }},
                inProgress: {{ $comparisonData['in_progress'] }},
                overdue: {{ $comparisonData['overdue'] }},
                progressData: {!! json_encode($progressData ?? []) !!},
                comparisonData: {!! json_encode($comparisonData ?? []) !!},
                tasks: {!! json_encode($tasks->map(function($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'status' => $task->status,
                        'deadline' => $task->deadline ? date('d/m/Y', strtotime($task->deadline)) : 'Chưa đặt',
                        'progress' => $task->progress ?? 0,
                        'user_id' => $task->user_id,
                        'user_name' => $task->user ? $task->user->name : 'Không xác định',
                        'manager_name' => $task->user && $task->user->manager ? $task->user->manager->name : 'Không có'
                    ];
                })->toArray()) !!},
                userStats: {!! json_encode($userStats->map(function($stat) {
                    return [
                        'user_id' => $stat->user_id,
                        'user_name' => $stat->user ? $stat->user->name : 'Không xác định',
                        'manager_name' => $stat->user && $stat->user->manager ? $stat->user->manager->name : 'Không có',
                        'total' => $stat->total,
                        'avg_progress' => round($stat->avg_progress, 2)
                    ];
                })->toArray()) !!}
            },
            week: { total: 0, completed: 0, inProgress: 0, overdue: 0, progressData: [], comparisonData: [], tasks: [], userStats: [] },
            month: { total: 0, completed: 0, inProgress: 0, overdue: 0, progressData: [], comparisonData: [], tasks: [], userStats: [] }
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
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true, max: 100, title: { display: true, text: 'Tiến độ trung bình (%)', font: { size: 14 } } },
                            x: { title: { display: true, text: 'Ngày', font: { size: 14 } } }
                        },
                        plugins: {
                            legend: { position: 'top', labels: { font: { size: 12 } } },
                            tooltip: {
                                backgroundColor: '#2c3e50',
                                titleFont: { size: 14 },
                                bodyFont: { size: 12 },
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
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true, title: { display: true, text: 'Số lượng công việc', font: { size: 14 } } },
                            x: { title: { display: true, text: 'Trạng thái', font: { size: 14 } } }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#2c3e50',
                                titleFont: { size: 14 },
                                bodyFont: { size: 12 },
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
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">Chưa có công việc nào của bạn.</td></tr>';
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
                        <td>${task.deadline}</td>
                        <td style="width:180px;">
                            <div class="progress">
                                <div class="progress-bar ${task.progress == 100 ? 'bg-success' : 'bg-info'}"
                                     role="progressbar"
                                     style="width: ${task.progress || 0}%">
                                    ${task.progress || 0}%
                                </div>
                            </div>
                        </td>
                        <td>${task.user_name}</td>
                        <td>${task.manager_name}</td>
                    </tr>`;
                tbody.innerHTML += row;
            });
        }

        // Cập nhật bảng thống kê người phụ trách
        function updateUserStatsTable(userStats) {
            const tbody = document.getElementById('userStatsBody');
            tbody.innerHTML = '';
            if (userStats.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center">Chưa có dữ liệu của bạn.</td></tr>';
                return;
            }
            userStats.forEach(stat => {
                const row = `
                    <tr>
                        <td>${stat.manager_name}</td>
                        <td>${stat.total}</td>
                        <td>${stat.avg_progress}%</td>
                    </tr>`;
                tbody.innerHTML += row;
            });
        }

        // Cập nhật dashboard
        function updateDashboard(data) {
            document.getElementById('totalTasks').textContent = data.total;
            document.getElementById('completed').textContent = data.completed;
            document.getElementById('inProgress').textContent = data.inProgress;
            document.getElementById('overdue').textContent = data.overdue;
            document.getElementById('exportPDF').href = `{{ route('reports.export') }}?period=${document.getElementById('timeFilter').value}`;
            initProgressChart(data);
            initComparisonChart(data);
            updateTable(data.tasks);
            updateUserStatsTable(data.userStats);
        }

        // Xử lý bộ lọc thời gian với debounce
        let timeout;
        document.getElementById('timeFilter').addEventListener('change', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                const period = this.value;
                const loading = document.querySelector('#exportPDF .loading');
                loading.style.display = 'inline';
                if (initialData[period].total !== 0) {
                    updateDashboard(initialData[period]);
                    loading.style.display = 'none';
                } else {
                    fetch(`/reports/data?period=${period}`)
                        .then(response => response.json())
                        .then(data => {
                            initialData[period] = data;
                            updateDashboard(data);
                            loading.style.display = 'none';
                        })
                        .catch(error => {
                            console.error('Lỗi khi tải dữ liệu:', error);
                            loading.style.display = 'none';
                        });
                }
            }, 300);
        });

        // Khởi tạo ban đầu
        document.addEventListener('DOMContentLoaded', function() {
            updateDashboard(initialData.today);
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>