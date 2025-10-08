<div class="modal fade" id="fileModal{{ $task->id }}" tabindex="-1" aria-labelledby="fileModalLabel{{ $task->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileModalLabel{{ $task->id }}">Danh sách file của công việc: {{ Str::limit($task->title, 50) }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                @if($task->files->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 40%;">File</th>
                                    <th style="width: 30%;">Ngày giờ upload</th>
                                    <th style="width: 30%;">Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($task->files as $file)
                                    <tr>
                                        <td><a href="{{ asset('storage/' . $file->file_path) }}" target="_blank">{{ basename($file->file_path) }}</a></td>
                                        <td>{{ $file->uploaded_at ? $file->uploaded_at->format('d/m/Y H:i') : 'Không có' }}</td>
                                        <td>{{ $file->note ?? 'Không có ghi chú' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">Chưa có file nào được upload.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>