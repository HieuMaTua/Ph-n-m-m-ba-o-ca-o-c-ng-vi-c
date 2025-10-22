<div class="sidebar">
    <div>
        <h2><i class="bi bi-kanban"></i> Quản lý</h2>

        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="bi bi-clipboard-data"></i> Báo cáo
        </a>

        <div class="dropdown">
            <a href="#" class="dropdown-toggle {{ request()->routeIs('tasks.*') ? 'active' : '' }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-list-task"></i> Công việc
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item {{ request()->routeIs('tasks.calendar') ? 'active' : '' }}" href="{{ route('tasks.calendar') }}">Thêm công việc</a></li>
                <li><a class="dropdown-item {{ request()->routeIs('tasks.report') ? 'active' : '' }}" href="{{ route('tasks.report') }}">Báo cáo công việc</a></li>
                <li><a class="dropdown-item {{ request()->routeIs('tasks.handle') ? 'active' : '' }}" href="{{ route('task_requests.index') }}">Xử lý yêu cầu công việc</a></li>
            </ul>
        </div>

        <a href="{{ route('nhansu.index') }}" class="{{ request()->routeIs('nhansu.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Nhân sự
        </a>

        <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
            <i class="bi bi-gear"></i> Cài đặt
        </a>
    </div>

    <div class="profile">
        <img src="https://i.pravatar.cc/100" alt="Avatar" class="me-2">
        <div class="flex-grow-1">
            <h6>{{ Auth::user()->name ?? 'Người dùng' }}</h6>
            <small>
                @if(Auth::user() && Auth::user()->role)
                    @if(Auth::user()->role == 'director')
                        Cấp Cao
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