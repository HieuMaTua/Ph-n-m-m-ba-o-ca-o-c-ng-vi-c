<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý công việc - Lịch</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@event-calendar/build/dist/event-calendar.min.css">
  <link rel="icon" href="{{ asset('favicon_io/favicon-32x32.png') }}" type="image/png">

  <script src="https://cdn.jsdelivr.net/npm/@event-calendar/build/dist/event-calendar.min.js"></script>
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
      padding: 0;
      min-height: 100vh;
    }
    h1 {
      font-size: 26px;
      font-weight: 600;
      color: #2c3e50;
      margin: 25px;
    }

    /* Calendar */
    #calendar {
      max-width: 100%;
      margin: 0;
      height: calc(100vh - 80px);
      padding: 20px;
    }

    /* Tùy chỉnh sự kiện */
    .ec-event {
      font-size: 11px; /* Giảm cỡ chữ để sự kiện gọn hơn */
      padding: 2px 4px; /* Giảm padding */
      border-radius: 3px; /* Bo góc nhẹ */
      line-height: 1.3; /* Giảm khoảng cách dòng */
      white-space: nowrap; /* Không xuống dòng */
      overflow: hidden; /* Ẩn nội dung tràn */
      text-overflow: ellipsis; /* Thêm dấu ... nếu nội dung dài */
      margin-bottom: 2px; /* Khoảng cách giữa các sự kiện */
      border: none; /* Bỏ viền mặc định */
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); /* Thêm bóng nhẹ */
    }

    /* Đảm bảo sự kiện hiển thị cạnh nhau trong chế độ tháng */
    .ec-day-grid .ec-event {
      display: inline-block; /* Hiển thị cạnh nhau */
      width: calc(50% - 4px); /* Chiếm 50% chiều rộng ô, trừ margin */
      margin-right: 2px; /* Khoảng cách giữa các sự kiện */
      vertical-align: top; /* Căn trên */
    }

    /* Tùy chỉnh cho chế độ tuần và ngày */
    .ec-time-grid .ec-event {
      font-size: 12px;
      padding: 4px;
      white-space: normal; /* Cho phép xuống dòng trong chế độ tuần/ngày */
    }

    /* Tùy chỉnh màu sắc theo trạng thái */
    .ec-event[style*="background-color: rgb(46, 204, 113)"] { /* Hoàn thành */
      background-color: #27ae60 !important;
      color: white !important;
    }
    .ec-event[style*="background-color: rgb(241, 196, 15)"] { /* Đang làm */
      background-color: #e67e22 !important;
      color: white !important;
    }
    .ec-event[style*="background-color: rgb(231, 76, 60)"] { /* Quá hạn */
      background-color: #c0392b !important;
      color: white !important;
    }
    .ec-event[style*="background-color: rgb(127, 140, 141)"] { /* Chờ xử lý */
      background-color: #6c757d !important;
      color: white !important;
    }

    /* Error message */
    .error-message {
      display: none;
      color: #e74c3c;
      font-size: 14px;
      margin-top: 5px;
    }

    /* Tùy chỉnh giao diện lịch giống hình ảnh */
    .ec-time-grid .ec-event {
      border: 1px solid rgba(0,0,0,0.1);
      border-radius: 4px;
      padding: 4px;
      font-size: 12px;
      color: #333;
    }

    .ec-time-grid .ec-resource-group {
      background-color: #f0f0f0;
      font-weight: bold;
    }

    .ec-time-grid .ec-resource {
      border-right: 1px solid #ddd;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div>
      <h2><i class="bi bi-kanban"></i> Quản lý</h2>
      <a href="{{ route('home') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <a href="{{ route('reports.index') }}"><i class="bi bi-clipboard-data"></i> Báo cáo</a>
      <a href="{{ route('tasks.calendar') }}" class="active"><i class="bi bi-list-task"></i> Công việc</a>
      <a href="#"><i class="bi bi-people"></i> Nhân sự</a>
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
    <div id="calendar"></div>

    <!-- Modal chỉnh sửa công việc -->
    <div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form id="editTaskForm" action="{{ route('tasks.update', ':id') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-header">
              <h5 class="modal-title">Chỉnh sửa công việc</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="id">
              <div class="mb-3">
                <label class="form-label">Tên công việc</label>
                <input type="text" name="title" class="form-control" required>
                <div class="error-message" id="edit-title-error">Vui lòng nhập tên công việc.</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-control">
                  <option value="pending">Chờ xử lý</option>
                  <option value="in_progress">Đang làm</option>
                  <option value="completed">Hoàn thành</option>
                  <option value="overdue">Quá hạn</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Thời gian bắt đầu</label>
                <input type="datetime-local" name="start" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Thời gian kết thúc</label>
                <input type="datetime-local" name="end" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Hạn chót</label>
                <input type="date" name="deadline" class="form-control" required>
                <div class="error-message" id="edit-deadline-error">Vui lòng chọn ngày hợp lệ.</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Tiến độ (%)</label>
                <input type="number" name="progress" min="0" max="100" class="form-control">
                <div class="error-message" id="edit-progress-error">Tiến độ phải từ 0 đến 100.</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Giao cho nhân viên</label>
                <select name="assigned_to" class="form-control" required>
                  @foreach($users ?? [['id' => 1, 'name' => 'User A'], ['id' => 2, 'name' => 'User B'], ['id' => 3, 'name' => 'User C']] as $user)
                    <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                  @endforeach
                </select>
                <div class="error-message" id="edit-assigned-to-error">Vui lòng chọn nhân viên.</div>
              </div>
            </div>
            <div class="modal-footer">
              <form action="{{ route('tasks.destroy', ':id') }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" id="deleteTask">Xóa</button>
              </form>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
              <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Dữ liệu nhân viên (từ $users hoặc mẫu)
    const resources = [
      { id: 'br-23', title: 'BR', dayGroup: '2025-09-23' },
      { id: 'jw-23', title: 'JW', dayGroup: '2025-09-23' },
      { id: 'ps-23', title: 'PS', dayGroup: '2025-09-23' },
      { id: 'br-24', title: 'BR', dayGroup: '2025-09-24' },
      { id: 'jw-24', title: 'JW', dayGroup: '2025-09-24' },
      { id: 'ps-24', title: 'PS', dayGroup: '2025-09-24' },
      { id: 'br-25', title: 'BR', dayGroup: '2025-09-25' },
      { id: 'jw-25', title: 'JW', dayGroup: '2025-09-25' },
      { id: 'ps-25', title: 'PS', dayGroup: '2025-09-25' },
      { id: 'br-26', title: 'BR', dayGroup: '2025-09-26' },
      { id: 'jw-26', title: 'JW', dayGroup: '2025-09-26' },
      { id: 'ps-26', title: 'PS', dayGroup: '2025-09-26' }
    ];

    // Dữ liệu công việc (từ $tasks hoặc mẫu, cập nhật với thời gian)
    const tasks = [
      @forelse($tasks as $task)
        {
          id: {{ $task->id }},
          title: '{{ $task->title }}',
          status: '{{ $task->status }}',
          start: '{{ $task->start ?? $task->deadline . 'T09:00' }}',
          end: '{{ $task->end ?? $task->deadline . 'T10:00' }}',
          progress: {{ $task->progress ?? 0 }},
          created_at: '{{ $task->created_at->format('Y-m-d') }}',
          assigned_to: '{{ $task->assigned_to ?? rand(1, 3) }}',
          user_name: '{{ $task->user->name ?? 'User ' . rand(1, 3) }}'
        },
      @empty
        { id: 1, title: 'Open Files Emergency Generator', start: '2025-09-23T06:00', end: '2025-09-23T08:30', resourceId: 'br-23', status: 'completed', backgroundColor: '#ffcccb' },
        { id: 2, title: 'Meeting w/ Josh David Main', start: '2025-09-23T05:30', end: '2025-09-23T12:00', resourceId: 'jw-23', status: 'in_progress', backgroundColor: '#90ee90' },
        { id: 3, title: 'Physio Lab', start: '2025-09-23T06:15', end: '2025-09-23T07:00', resourceId: 'ps-23', status: 'pending', backgroundColor: '#add8e6' },
        { id: 4, title: 'AC Current Overland Jo', start: '2025-09-24T07:00', end: '2025-09-24T10:00', resourceId: 'br-24', status: 'overdue', backgroundColor: '#ffa07a' },
        // Thêm các sự kiện mẫu khác dựa trên hình ảnh
        { id: 5, title: 'Confirm the server is', start: '2025-09-24T06:00', end: '2025-09-24T09:00', resourceId: 'jw-24', status: 'in_progress', backgroundColor: '#ffd700' },
        { id: 6, title: 'Leading pa. Call w/ D.', start: '2025-09-25T06:30', end: '2025-09-25T09:00', resourceId: 'ps-25', status: 'completed', backgroundColor: '#98fb98' },
        { id: 7, title: 'Review Operations IT Reviews', start: '2025-09-25T08:00', end: '2025-09-25T10:00', resourceId: 'br-25', status: 'pending', backgroundColor: '#87cefa' },
        { id: 8, title: 'Install I Build', start: '2025-09-26T06:00', end: '2025-09-26T09:30', resourceId: 'ps-26', status: 'in_progress', backgroundColor: '#ffb6c1' }
      @endforelse
    ];

    // Khởi tạo lịch
    document.addEventListener('DOMContentLoaded', function () {
      const calendarEl = document.getElementById('calendar');
      const calendar = EventCalendar.create(calendarEl, {
        initialView: 'resourceTimeGridWeek',
        views: {
          resourceTimeGridWeek: { type: 'resourceTimeGridWeek' },
          resourceTimeGridDay: { type: 'resourceTimeGridDay' }
        },
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'resourceTimeGridWeek,resourceTimeGridDay'
        },
        editable: false,
        locale: 'vi',
        resources: resources,
        resourceGroupField: 'dayGroup',
        resourceGroupText: function(groupValue) {
          const date = new Date(groupValue);
          const weekday = date.toLocaleDateString('vi-VN', { weekday: 'long' });
          const day = date.getDate();
          return `${weekday} ${day}`;
        },
        slotMinTime: '05:30:00',
        slotMaxTime: '18:00:00',
        events: tasks.map(task => ({
          id: task.id,
          title: task.title,
          start: task.start,
          end: task.end,
          resourceId: task.resourceId || task.assigned_to,
          backgroundColor: task.backgroundColor || (task.status === 'completed' ? '#27ae60' :
                          task.status === 'in_progress' ? '#e67e22' :
                          task.status === 'overdue' ? '#c0392b' : '#6c757d'),
          borderColor: task.backgroundColor || (task.status === 'completed' ? '#27ae60' :
                       task.status === 'in_progress' ? '#e67e22' :
                       task.status === 'overdue' ? '#c0392b' : '#6c757d'),
          extendedProps: { // Lưu thông tin bổ sung để hiển thị trong tooltip hoặc modal
            status: task.status,
            progress: task.progress,
            user_name: task.user_name
          }
        })),
        eventClick: function(info) {
          const task = tasks.find(t => t.id == info.event.id);
          const modal = new bootstrap.Modal(document.getElementById('editTaskModal'));
          const form = document.getElementById('editTaskForm');
          const deleteForm = document.querySelector('#editTaskModal form[action*="/tasks/destroy"]');
          form.action = form.action.replace(':id', task.id);
          deleteForm.action = deleteForm.action.replace(':id', task.id);
          form.querySelector('input[name="id"]').value = task.id;
          form.querySelector('input[name="title"]').value = task.title;
          form.querySelector('select[name="status"]').value = task.status;
          form.querySelector('input[name="start"]').value = task.start.replace(' ', 'T');
          form.querySelector('input[name="end"]').value = task.end.replace(' ', 'T');
          form.querySelector('input[name="deadline"]').value = task.deadline || task.start.split('T')[0];
          form.querySelector('input[name="progress"]').value = task.progress;
          form.querySelector('select[name="assigned_to"]').value = task.assigned_to;
          modal.show();
        },
        // Thêm tooltip khi hover vào sự kiện
        eventDidMount: function(info) {
          const task = info.event.extendedProps;
          const statusText = task.status === 'completed' ? 'Hoàn thành' :
                            task.status === 'in_progress' ? 'Đang làm' :
                            task.status === 'overdue' ? 'Quá hạn' : 'Chờ xử lý';
          info.el.setAttribute('title', 
            `Người thực hiện: ${task.user_name}\n` +
            `Trạng thái: ${statusText}\n` +
            `Tiến độ: ${task.progress}%`
          );
        }
      });

      // Kiểm tra form chỉnh sửa
      document.getElementById('editTaskForm').addEventListener('submit', function(e) {
        const title = this.querySelector('input[name="title"]').value;
        const start = this.querySelector('input[name="start"]').value;
        const end = this.querySelector('input[name="end"]').value;
        const deadline = this.querySelector('input[name="deadline"]').value;
        const progress = this.querySelector('input[name="progress"]').value || 0;
        const assignedTo = this.querySelector('select[name="assigned_to"]').value;
        const titleError = document.getElementById('edit-title-error');
        const deadlineError = document.getElementById('edit-deadline-error');
        const progressError = document.getElementById('edit-progress-error');
        const assignedToError = document.getElementById('edit-assigned-to-error');
        let hasError = false;

        titleError.style.display = 'none';
        deadlineError.style.display = 'none';
        progressError.style.display = 'none';
        assignedToError.style.display = 'none';

        if (!title.trim()) {
          titleError.style.display = 'block';
          hasError = true;
        }
        if (!deadline) {
          deadlineError.style.display = 'block';
          hasError = true;
        }
        if (progress && (progress < 0 || progress > 100)) {
          progressError.style.display = 'block';
          hasError = true;
        }
        if (!assignedTo) {
          assignedToError.style.display = 'block';
          hasError = true;
        }

        if (hasError) {
          e.preventDefault();
        }
      });
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>