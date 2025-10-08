<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo công việc - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('favicon_io/favicon-32x32.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging.js"></script>
    <style>
        .content { padding: 20px; }
        .wallboard-card {
            background: linear-gradient(135deg, #ffffff, #f1f3f5);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: transform 0.2s;
        }
        .wallboard-card:hover {
            transform: translateY(-5px);
        }
        .wallboard-card h6 {
            margin-bottom: 10px;
            color: #2c3e50;
            font-weight: 600;
        }
        .wallboard-card p {
            margin: 5px 0;
            font-size: 0.95rem;
            color: #34495e;
        }
        .status-pending { color: #6c757d; font-weight: bold; }
        .status-in_progress { color: #f39c12; font-weight: bold; }
        .status-completed { color: #27ae60; font-weight: bold; }
        .status-overdue { color: #c0392b; font-weight: bold; }
        .stat-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: box-shadow 0.2s;
        }
        .stat-card:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
        }
        .stat-card h5 {
            font-size: 1rem;
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        .stat-card h2 {
            font-size: 2.2rem;
            color: #2c3e50;
            margin: 0;
        }
        .card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(90deg, #3498db, #1abc9c);
            color: white;
            font-weight: 600;
            padding: 15px;
            border-radius: 12px 12px 0 0;
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
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .loading-spinner i {
            font-size: 1.5rem;
            color: #3498db;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }
        .toast {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            max-width: 350px;
        }
        @media (max-width: 576px) {
            .stat-card h2 {
                font-size: 1.8rem;
            }
            .wallboard-card {
                padding: 12px;
            }
            .chart-container {
                padding: 10px;
            }
            .chart-container canvas {
                max-height: 250px;
            }
            .toast-container {
                top: 10px;
                right: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    @include('layout.sidebar')

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

        <!-- Toast container cho thông báo nổi -->
        @php
            $nearDueTasks = $allRecentTasks->filter(function($task) {
                $deadline = \Carbon\Carbon::parse($task->deadline);
                return $task->deadline && 
                       $deadline->isToday() || ($deadline->isFuture() && now()->diffInDays($deadline) <= 1) && 
                       $task->status != 'completed';
            });
        @endphp
        <div class="toast-container">
            @foreach($nearDueTasks as $task)
                <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
                    <div class="toast-header">
                        <strong class="me-auto">Thông báo gần hết hạn</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        Công việc <strong>"{{ $task->title }}"</strong> sắp hết hạn vào {{ date('d/m/Y', strtotime($task->deadline)) }}.
                        <div class="mt-2">
                            <button class="btn btn-sm btn-primary remind-again" data-task-id="{{ $task->id }}">Nhắc lại</button>
                            <button class="btn btn-sm btn-secondary dismiss-forever" data-task-id="{{ $task->id }}">Không nhắc lại</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- <h1 class="mb-4">Dashboard báo cáo công việc</h1> --}}

        <div class="card mb-4" style="background: linear-gradient(90deg, #3498db, #1abc9c); color: white;">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h4>Xin chào, {{ Auth::user()->name }}</h4>
                    <p id="currentDate"></p>
                    <p id="currentTime" style="font-weight: bold;"></p>
                </div>
                <div class="text-end">
                    <i class="bi bi-list-task" style="font-size: 2.5rem;"></i>
                    <p class="mb-0">Hôm nay có <strong>{{ $tasksToday ?? 0 }}</strong> công việc</p>
                    <small>Tiến độ trung bình: {{ isset($avgProgress) ? round($avgProgress, 2) : 0 }}%</small>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
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

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Thống kê trạng thái</div>
                    <div class="loading-spinner" id="taskChartSpinner">
                        <i class="bi bi-arrow-repeat"></i> Đang tải...
                    </div>
                    @php
                        $completed = $completed ?? 0;
                        $inProgress = $inProgress ?? 0;
                        $overdue = $overdue ?? 0;
                    @endphp
                    @if($completed + $inProgress + $overdue == 0)
                        <p class="text-muted p-4">Chưa có dữ liệu để hiển thị biểu đồ.</p>
                    @else
                        <div class="chart-container">
                            <canvas id="taskChart"></canvas>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Thống kê công việc theo nhân viên</div>
                    <div class="loading-spinner" id="userChartSpinner">
                        <i class="bi bi-arrow-repeat"></i> Đang tải...
                    </div>
                    @if(empty($userTasks))
                        <p class="text-muted p-4">Chưa có dữ liệu để hiển thị biểu đồ.</p>
                    @else
                        <div class="chart-container">
                            <canvas id="userChart"></canvas>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">Biểu đồ tiến độ công việc</div>
            <div class="loading-spinner" id="progressChartSpinner">
                <i class="bi bi-arrow-repeat"></i> Đang tải...
            </div>
            @if(!isset($avgProgress) || $avgProgress == 0)
                <p class="text-muted p-4">Chưa có dữ liệu để hiển thị biểu đồ.</p>
            @else
                <div class="chart-container">
                    <canvas id="progressChart" height="100"></canvas>
                </div>
            @endif
        </div>

        {{-- THÊM MỚI: Section Top 3 nhân viên ưu tú --}}
        <div class="card mt-4">
            <div class="card-header">Top 3 nhân viên ưu tú (Tỷ lệ hoàn thành cao nhất)</div>
            <div id="topEmployeesContainer">
                @if(empty($topEmployees))
                    <p class="text-muted p-4">Chưa có dữ liệu nhân viên ưu tú.</p>
                @else
                    <div class="row g-3 p-4">
                        @foreach($topEmployees as $index => $emp)
                            <div class="col-md-4">
                                <div class="card text-center border-primary">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">#{{ $index + 1 }}</h6>
                                        <h5 class="card-text">{{ $emp['name'] }}</h5>
                                        <div class="progress mb-2" style="height: 20px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $emp['avg_progress'] }}%" aria-valuenow="{{ $emp['avg_progress'] }}" aria-valuemin="0" aria-valuemax="100">{{ $emp['avg_progress'] }}%</div>
                                        </div>
                                        <small class="text-success">Hoàn thành trung bình</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">Hoạt động của nhân viên hôm nay</div>
            @php
                $inProgressTasks = $allRecentTasks->where('status', 'in_progress');
            @endphp
            @if($inProgressTasks->isEmpty())
                <p class="text-muted p-4">Chưa có công việc nào đang làm hôm nay.</p>
            @else
                <div class="row g-3 p-4">
                    @foreach($inProgressTasks as $task)
                        <div class="col-md-4">
                            <div class="wallboard-card">
                                <h6>{{ $task->user->name ?? 'Nhân viên' }}</h6>
                                <p><strong>Công việc:</strong> {{ $task->title }}</p>
                                <p><strong>Trạng thái:</strong>
                                    <span class="status-in_progress">Đang làm</span>
                                </p>
                                <p><strong>Tiến độ:</strong> {{ $task->progress ?? 0 }}%</p>
                                <p><strong>Hạn chót:</strong> {{ $task->deadline ? date('d/m/Y', strtotime($task->deadline)) : 'Chưa đặt' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <script>

        // Hàm hiển thị spinner
        function showSpinner(chartId) {
            document.getElementById(chartId + 'Spinner').style.display = 'block';
        }
        function hideSpinner(chartId) {
            document.getElementById(chartId + 'Spinner').style.display = 'none';
        }

        // Hàm tải dữ liệu từ server
        async function fetchTaskData() {
            showSpinner('taskChart');
            showSpinner('userChart');
            showSpinner('progressChart');

            try {
                const response = await fetch(`/api/tasks`, {
                    headers: { 
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                    }
                });
                if (!response.ok) throw new Error('Lỗi tải dữ liệu');
                const data = await response.json();

                // Fallback nếu API lỗi
                const taskData = {
                    completed: data.completed ?? {{ $completed ?? 0 }},
                    inProgress: data.inProgress ?? {{ $inProgress ?? 0 }},
                    overdue: data.overdue ?? {{ $overdue ?? 0 }},
                    userTasks: data.userTasks ?? @json($userTasks ?? []),
                    avgProgress: data.avgProgress ?? {{ isset($avgProgress) ? round($avgProgress, 2) : 0 }},
                    topEmployees: data.topEmployees ?? @json($topEmployees ?? [])
                };

                updateCharts(taskData);
            } catch (error) {
                console.error('Lỗi fetch dữ liệu:', error);
                // Dùng dữ liệu PHP fallback
                updateCharts({
                    completed: {{ $completed ?? 0 }},
                    inProgress: {{ $inProgress ?? 0 }},
                    overdue: {{ $overdue ?? 0 }},
                    userTasks: @json($userTasks ?? []),
                    avgProgress: {{ isset($avgProgress) ? round($avgProgress, 2) : 0 }},
                    topEmployees: @json($topEmployees ?? [])
                });
            } finally {
                hideSpinner('taskChart');
                hideSpinner('userChart');
                hideSpinner('progressChart');
            }
        }

        // Hàm cập nhật biểu đồ
        let taskChartInstance, userChartInstance, progressChartInstance;

        function updateCharts(data) {
            // Cập nhật biểu đồ trạng thái
            const ctxTask = document.getElementById('taskChart');
            if (ctxTask && data.completed + data.inProgress + data.overdue > 0) {
                if (taskChartInstance) taskChartInstance.destroy();
                taskChartInstance = new Chart(ctxTask, {
                    type: 'bar',
                    data: {
                        labels: ['Hoàn thành', 'Đang làm', 'Quá hạn'],
                        datasets: [{
                            label: 'Số lượng công việc',
                            data: [data.completed, data.inProgress, data.overdue],
                            backgroundColor: ['#27ae60', '#f39c12', '#c0392b'],
                            borderColor: ['#219653', '#e67e22', '#a93226'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Số lượng công việc', font: { size: 14 } }
                            },
                            x: {
                                title: { display: true, text: 'Trạng thái', font: { size: 14 } }
                            }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#2c3e50',
                                titleFont: { size: 14 },
                                bodyFont: { size: 12 },
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
            } else if (ctxTask) {
                ctxTask.parentNode.innerHTML = '<p class="text-muted p-4">Chưa có dữ liệu để hiển thị biểu đồ.</p>';
            }

            // Cập nhật biểu đồ nhân viên
            const ctxUser = document.getElementById('userChart');
            if (ctxUser && data.userTasks && data.userTasks.length > 0) {
                if (userChartInstance) userChartInstance.destroy();
                userChartInstance = new Chart(ctxUser, {
                    type: 'bar',
                    data: {
                        labels: data.userTasks.map(user => user.name),
                        datasets: [{
                            label: 'Số công việc',
                            data: data.userTasks.map(user => user.task_count),
                            backgroundColor: ['#3498db', '#1abc9c', '#9b59b6', '#e74c3c', '#f1c40f'],
                            borderColor: ['#2980b9', '#16a085', '#8e44ad', '#c0392b', '#e67e22'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Số công việc', font: { size: 14 } }
                            },
                            x: {
                                title: { display: true, text: 'Nhân viên', font: { size: 14 } }
                            }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#2c3e50',
                                titleFont: { size: 14 },
                                bodyFont: { size: 12 },
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
            } else if (ctxUser) {
                ctxUser.parentNode.innerHTML = '<p class="text-muted p-4">Chưa có dữ liệu để hiển thị biểu đồ.</p>';
            }

            // Cập nhật biểu đồ tiến độ
            const ctxProgress = document.getElementById('progressChart');
            if (ctxProgress && data.avgProgress > 0) {
                if (progressChartInstance) progressChartInstance.destroy();
                progressChartInstance = new Chart(ctxProgress, {
                    type: 'doughnut',
                    data: {
                        labels: ['Hoàn thành', 'Chưa hoàn thành'],
                        datasets: [{
                            data: [data.avgProgress, 100 - data.avgProgress],
                            backgroundColor: ['#27ae60', '#ecf0f1'],
                            borderColor: ['#219653', '#b0bec5'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { font: { size: 12 } } },
                            tooltip: {
                                backgroundColor: '#2c3e50',
                                titleFont: { size: 14 },
                                bodyFont: { size: 12 },
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        let value = context.parsed || 0;
                                        return `${label}: ${value}%`;
                                    }
                                }
                            }
                        }
                    }
                });
            } else if (ctxProgress) {
                ctxProgress.parentNode.innerHTML = '<p class="text-muted p-4">Chưa có dữ liệu để hiển thị biểu đồ.</p>';
            }

            // Cập nhật top 3 nhân viên
            updateTopEmployees(data.topEmployees || []);
        }

        // Hàm cập nhật top 3
        function updateTopEmployees(topEmployees) {
            const topContainer = document.getElementById('topEmployeesContainer');
            if (!topContainer) return;

            if (topEmployees.length === 0) {
                topContainer.innerHTML = '<p class="text-muted p-4">Chưa có dữ liệu nhân viên ưu tú.</p>';
                return;
            }

            let html = '<div class="row g-3 p-4">';
            topEmployees.forEach((emp, index) => {
                html += `
                    <div class="col-md-4">
                        <div class="card text-center border-primary">
                            <div class="card-body">
                                <h6 class="card-title text-primary">#${index + 1}</h6>
                                <h5 class="card-text">${emp.name}</h5>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: ${emp.avg_progress}%" aria-valuenow="${emp.avg_progress}" aria-valuemin="0" aria-valuemax="100">${emp.avg_progress}%</div>
                                </div>
                                <small class="text-success">Hoàn thành trung bình</small>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            topContainer.innerHTML = html;
        }

        // Khởi tạo biểu đồ và thông báo
        document.addEventListener('DOMContentLoaded', function () {
            // Tải dữ liệu ban đầu
            fetchTaskData();

            // Tự động làm mới dữ liệu mỗi 60 giây
            setInterval(fetchTaskData, 600000);

            // Init top 3 từ PHP
            updateTopEmployees(@json($topEmployees ?? []));

            // Hiển thị toast
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toast => {
                const taskId = toast.querySelector('.remind-again').dataset.taskId;
                // Kiểm tra nếu task đã bị tắt nhắc nhở
                if (!localStorage.getItem(`dismissed_${taskId}`)) {
                    new bootstrap.Toast(toast).show();
                }
            });

            // Xử lý nút "Nhắc lại"
            document.querySelectorAll('.remind-again').forEach(button => {
                button.addEventListener('click', function () {
                    const toast = this.closest('.toast');
                    bootstrap.Toast.getInstance(toast).hide();
                });
            });

            // Xử lý nút "Không nhắc lại"
            document.querySelectorAll('.dismiss-forever').forEach(button => {
                button.addEventListener('click', async function () {
                    const taskId = this.dataset.taskId;
                    localStorage.setItem(`dismissed_${taskId}`, 'true');
                    const toast = this.closest('.toast');
                    bootstrap.Toast.getInstance(toast).hide();

                    // Gửi AJAX để cập nhật server
                    try {
                        await fetch(`/api/tasks/${taskId}/dismiss-reminder`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ dismissed: true })
                        });
                    } catch (error) {
                        console.error('Lỗi cập nhật nhắc nhở:', error);
                    }
                });
            });
        });

        

        // Cập nhật ngày giờ
        function updateDateTime() {
            const now = new Date();
            const days = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
            const dayName = days[now.getDay()];
            const date = now.getDate();
            const month = now.getMonth() + 1;
            const year = now.getFullYear();
            const formattedDate = `${dayName}, ${date} tháng ${month}, ${year}`;
            const time = now.toLocaleTimeString('vi-VN');
            document.getElementById('currentDate').innerText = formattedDate;
            document.getElementById('currentTime').innerText = time;
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>