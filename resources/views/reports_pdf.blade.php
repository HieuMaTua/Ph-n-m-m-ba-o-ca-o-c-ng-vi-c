<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo công việc</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        h1 { color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #343a40; color: white; }
        .stat { margin-bottom: 20px; }
        .stat span { font-weight: bold; }
    </style>
</head>
<body>
    <h1>Báo cáo công việc - {{ ucfirst($period) }}</h1>
    <div class="stat">
        <p><span>Tổng công việc:</span> {{ $data['total'] }}</p>
        <p><span>Hoàn thành đúng hạn:</span> {{ $data['completedOnTime'] }}</p>
        <p><span>Tiến độ trung bình:</span> {{ $data['avgProgress'] }}%</p>
        <p><span>Tỷ lệ quá hạn:</span> {{ $data['overdueRate'] }}%</p>
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
            @foreach($data['userStats'] as $stat)
                <tr>
                    <td>{{ $stat->user ? $stat->user->name : 'Không xác định' }}</td>
                    <td>{{ $stat->total }}</td>
                    <td>{{ $stat->avg_progress }}%</td>
                </tr>
            @endforeach
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
            @foreach($data['tasks'] as $task)
                <tr>
                    <td>{{ $task->id }}</td>
                    <td>{{ $task->title }}</td>
                    <td>{{ $task->status == 'completed' ? 'Hoàn thành' : ($task->status == 'in_progress' ? 'Đang làm' : ($task->status == 'overdue' ? 'Quá hạn' : 'Chờ xử lý')) }}</td>
                    <td>{{ $task->deadline }}</td>
                    <td>{{ $task->progress ?? 0 }}%</td>
                    <td>{{ $task->user ? $task->user->name : 'Không xác định' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>