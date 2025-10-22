<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo công việc</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #2c3e50;
        }
        h1 {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        h2 {
            font-size: 16px;
            color: #2c3e50;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .stat {
            margin-bottom: 20px;
        }
        .stat p {
            margin: 5px 0;
        }
        .stat span {
            font-weight: bold;
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #2c3e50;
            color: white;
        }
        .badge {
            display: inline-block;
            padding: 5px 8px;
            border-radius: 4px;
            color: white;
            font-size: 10px;
        }
        .bg-success { background-color: #2ecc71; }
        .bg-warning { background-color: #f1c40f; color: #000; }
        .bg-danger { background-color: #e74c3c; }
        .bg-secondary { background-color: #7f8c8d; }
    </style>
</head>
<body>
    <h1>Báo cáo công việc</h1>
    <p><strong>Khoảng thời gian:</strong> {{ $period === 'today' ? 'Hôm nay' : ($period === 'week' ? 'Tuần này' : 'Tháng này') }}</p>
    <p><strong>Trạng thái:</strong> {{ $status === 'all' ? 'Tất cả' : ($status === 'completed' ? 'Hoàn thành' : ($status === 'in_progress' ? 'Đang làm' : 'Quá hạn')) }}</p>

    <div class="stat">
        <p><span>Tổng công việc:</span> {{ $data['total'] }}</p>
        <p><span>Hoàn thành:</span> {{ $data['completed'] }}</p>
        <p><span>Đang thực hiện:</span> {{ $data['inProgress'] }}</p>
        <p><span>Quá hạn:</span> {{ $data['overdue'] }}</p>
    </div>

    <h2>Thống kê theo người phụ trách</h2>
    <table>
        <thead>
            <tr>
                <th>Người phụ trách</th>
                <th>Số công việc</th>
                <th>Tiến độ trung bình</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['userStats'] as $stat)
                <tr>
                    <td>{{ $stat->user && $stat->user->manager ? $stat->user->manager->name : 'Không xác định' }}</td>
                    <td>{{ $stat->total }}</td>
                    <td>{{ round($stat->avg_progress, 2) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">Chưa có dữ liệu.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Danh sách công việc</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên công việc</th>
                <th>Trạng thái</th>
                <th>Hạn chót</th>
                <th>Tiến độ</th>
                <th>Người phụ trách</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['tasks'] as $task)
                <tr>
                    <td>{{ $task->id }}</td>
                    <td>{{ $task->title }}</td>
                    <td>
                        <span class="badge {{ $task->status == 'completed' ? 'bg-success' : ($task->status == 'in_progress' ? 'bg-warning' : ($task->status == 'overdue' ? 'bg-danger' : 'bg-secondary')) }}">
                            {{ $task->status == 'completed' ? 'Hoàn thành' : ($task->status == 'in_progress' ? 'Đang làm' : ($task->status == 'overdue' ? 'Quá hạn' : 'Chờ xử lý')) }}
                        </span>
                    </td>
                    <td>{{ $task->deadline ? date('d/m/Y', strtotime($task->deadline)) : 'Chưa đặt' }}</td>
                    <td>{{ $task->progress ?? 0 }}%</td>
                    <td>{{ $task->user && $task->user->manager ? $task->user->manager->name : 'Không xác định' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Chưa có công việc nào.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>