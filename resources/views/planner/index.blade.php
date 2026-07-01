@extends('layouts.app')

@section('title', 'Daily Planner')
@section('page_title', 'Daily Planner')

@push('styles')
<style>
/* ── Planner Mobile-First Layout ─────────────────────────────── */
.planner-wrap {
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-width: 900px;
    margin: 0 auto;
}

/* Date strip */
.planner-date-strip {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 14px 18px;
    box-shadow: var(--shadow);
}
.planner-date-strip .date-main {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text);
}
.planner-date-strip .date-sub {
    font-size: 0.75rem;
    color: var(--muted);
    margin-top: 2px;
}
.today-badge {
    background: linear-gradient(135deg, #5b5ef4, #8b5cf6);
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 20px;
    letter-spacing: 0.03em;
}

/* ── Unscheduled pool ─────────────────────────────────────────── */
.pool-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}
.pool-header h6 {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}
.pool-count {
    font-size: 0.72rem;
    background: rgba(91,94,244,.12);
    color: #5b5ef4;
    padding: 2px 9px;
    border-radius: 20px;
    font-weight: 600;
}

/* Horizontal scrollable row of task chips on mobile */
.pool-scroll {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding-bottom: 4px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.pool-scroll::-webkit-scrollbar { display: none; }

/* Task chip — tap or drag to assign */
.task-chip {
    flex-shrink: 0;
    background: var(--surface);
    border: 2px solid var(--border);
    border-radius: 12px;
    padding: 10px 14px;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s, transform .1s;
    min-width: 150px;
    max-width: 200px;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    position: relative;
    /* also draggable on desktop */
}
.task-chip:active { transform: scale(0.96); }
.task-chip.selected {
    border-color: #5b5ef4;
    box-shadow: 0 0 0 3px rgba(91,94,244,.18);
}
.task-chip:hover {
    border-color: #5b5ef4;
    box-shadow: var(--shadow);
}
.task-chip .chip-title {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 160px;
    display: block;
}
.task-chip .chip-meta {
    font-size: 0.68rem;
    color: var(--muted);
    margin-top: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.chip-recur {
    font-size: 0.62rem;
    padding: 1px 6px;
    border-radius: 8px;
    background: rgba(91,94,244,.1);
    color: #5b5ef4;
    font-weight: 600;
}
.tap-hint {
    font-size: 0.62rem;
    color: #5b5ef4;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 4px;
    font-weight: 500;
}

/* Empty pool */
.pool-empty {
    text-align: center;
    padding: 28px 16px;
    color: var(--muted);
    font-size: 0.82rem;
    border: 2px dashed var(--border);
    border-radius: 12px;
}

/* ── Timeline ──────────────────────────────────────────────────── */
.timeline-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: var(--shadow);
}
.timeline-header {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.timeline-header h6 {
    margin: 0;
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--text);
}
.current-time-indicator {
    font-size: 0.72rem;
    color: var(--muted);
}

.timeline-scroll {
    max-height: 65vh;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
}

/* Each hour row */
.hour-row {
    display: flex;
    min-height: 64px;
    border-bottom: 1px solid var(--border);
    position: relative;
    transition: background .15s;
}
.hour-row:last-child { border-bottom: none; }

/* Active-hour highlight */
.hour-row.is-current-hour {
    background: rgba(91,94,244,.04);
}
.hour-row.is-current-hour .hour-label {
    color: #5b5ef4;
    font-weight: 700;
}

/* Hour label column */
.hour-label {
    width: 64px;
    min-width: 64px;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--muted);
    padding: 10px 6px 10px 10px;
    border-right: 1px solid var(--border);
    text-align: right;
    line-height: 1.2;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
}
.hour-label .label-ampm {
    font-size: 0.6rem;
    font-weight: 500;
    color: var(--muted);
}

/* Slot area */
.hour-slot {
    flex: 1;
    padding: 8px 10px;
    min-height: 64px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-content: flex-start;
    cursor: pointer;
    transition: background .15s;
    position: relative;
}
.hour-slot:hover,
.hour-slot.dragover {
    background: rgba(91,94,244,.06);
}
.hour-slot.dragover {
    border: 2px dashed #5b5ef4;
    border-radius: 8px;
}

/* "Tap to assign" hint shown in empty slots when a task is selected */
.slot-tap-hint {
    display: none;
    position: absolute;
    inset: 0;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    color: #5b5ef4;
    font-weight: 600;
    pointer-events: none;
    gap: 4px;
    opacity: 0.7;
}
.has-selected-task .hour-slot:empty .slot-tap-hint,
.has-selected-task .hour-slot.slot-empty .slot-tap-hint {
    display: flex;
}

/* Scheduled task pill inside a slot */
.sched-pill {
    background: linear-gradient(135deg, rgba(91,94,244,.12), rgba(139,92,246,.08));
    border: 1px solid rgba(91,94,244,.25);
    border-left: 3px solid #5b5ef4;
    border-radius: 8px;
    padding: 6px 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 130px;
    max-width: 100%;
    animation: pillIn .2s ease;
}
@keyframes pillIn {
    from { opacity: 0; transform: scale(0.9); }
    to   { opacity: 1; transform: scale(1); }
}
.sched-pill .pill-title {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 120px;
    flex: 1;
}
.sched-pill .pill-actions {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
}
.pill-check-btn {
    width: 22px; height: 22px;
    border-radius: 50%;
    border: 2px solid var(--border);
    background: transparent;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.7rem;
    transition: all .15s;
    color: var(--muted);
    padding: 0;
}
.pill-check-btn:hover { border-color: #22c55e; color: #22c55e; }
.pill-check-btn.done { background: #22c55e; border-color: #22c55e; color: #fff; }

/* ── Time-picker modal (mobile-native feel) ────────────────────── */
#timePickerModal .modal-dialog {
    margin: auto auto 0;
    max-width: 100%;
}
#timePickerModal .modal-content {
    border-radius: 20px 20px 0 0;
    border: none;
    max-height: 75vh;
}
@media (min-width: 576px) {
    #timePickerModal .modal-dialog {
        max-width: 420px;
        margin: auto;
    }
    #timePickerModal .modal-content {
        border-radius: 16px;
    }
}
.time-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    padding: 4px 0;
    max-height: 55vh;
    overflow-y: auto;
}
.time-btn {
    background: var(--surface-2);
    border: 2px solid var(--border);
    border-radius: 10px;
    padding: 12px 6px;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text);
    cursor: pointer;
    text-align: center;
    transition: all .15s;
    -webkit-tap-highlight-color: transparent;
}
.time-btn:hover, .time-btn:focus {
    border-color: #5b5ef4;
    background: rgba(91,94,244,.08);
    color: #5b5ef4;
    outline: none;
}
.time-btn.occupied {
    border-color: rgba(239,68,68,.25);
    background: rgba(239,68,68,.05);
    color: #ef4444;
    font-size: 0.72rem;
}
.time-btn.current-hour-btn {
    border-color: #5b5ef4;
    background: rgba(91,94,244,.08);
}

/* ── Dark mode tweaks ─────────────────────────────────────────── */
[data-bs-theme="dark"] .task-chip {
    background: #1e2235;
    border-color: #2a2f4a;
}
[data-bs-theme="dark"] .task-chip.selected {
    border-color: #5b5ef4;
}
[data-bs-theme="dark"] .hour-row.is-current-hour {
    background: rgba(91,94,244,.07);
}
[data-bs-theme="dark"] .time-btn {
    background: #161929;
    border-color: #2a2f4a;
    color: #e8eaf6;
}
[data-bs-theme="dark"] .time-btn:hover {
    background: rgba(91,94,244,.15);
}
</style>
@endpush

@section('content')
<div class="planner-wrap">

    {{-- ── Date Strip ── --}}
    <div class="planner-date-strip">
        <div>
            <div class="date-main">{{ now()->format('l, F j') }}</div>
            <div class="date-sub">{{ now()->format('Y') }} &middot; Tap a task, then tap a time slot</div>
        </div>
        <span class="today-badge">TODAY</span>
    </div>

    {{-- ── Unscheduled Task Pool ── --}}
    <div>
        <div class="pool-header">
            <h6><i class="fa-solid fa-layer-group me-2 text-primary"></i>Task Pool</h6>
            <span class="pool-count">{{ $unscheduledTasks->count() }} unscheduled</span>
        </div>

        @if($unscheduledTasks->isEmpty())
            <div class="pool-empty">
                <i class="fa-regular fa-face-smile-beam d-block fs-2 mb-2 text-primary opacity-50"></i>
                All tasks scheduled! Great job.
                <a href="{{ route('tasks.create') }}" class="d-block mt-2 text-primary fw-semibold" style="font-size:.8rem;">+ Add Task</a>
            </div>
        @else
            <div class="pool-scroll" id="unscheduled-pool">
                @foreach ($unscheduledTasks as $task)
                    <div class="task-chip"
                         id="task-chip-{{ $task->id }}"
                         data-task-id="{{ $task->id }}"
                         data-task-title="{{ $task->title }}"
                         draggable="true"
                         onclick="selectTask({{ $task->id }}, '{{ addslashes($task->title) }}')"
                         ondragstart="dragStart(event)">

                        <span class="chip-title">{{ $task->title }}</span>
                        <div class="chip-meta">
                            <span class="chip-recur"><i class="fa-solid fa-arrows-spin"></i> {{ $task->repeat_type }}</span>
                            @if($task->estimated_minutes)
                                <span><i class="fa-regular fa-hourglass"></i> {{ $task->estimated_minutes }}m</span>
                            @endif
                        </div>
                        <div class="tap-hint">
                            <i class="fa-solid fa-hand-pointer"></i> Tap to select
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="text-muted mt-2 mb-0" style="font-size:0.72rem;">
                <i class="fa-solid fa-circle-info me-1"></i>
                <strong>Mobile:</strong> Tap a task → tap a time slot &nbsp;|&nbsp;
                <strong>Desktop:</strong> Drag &amp; drop onto timeline
            </p>
        @endif
    </div>

    {{-- ── Timeline ── --}}
    <div class="timeline-card" id="timeline-wrap">
        <div class="timeline-header">
            <h6><i class="fa-solid fa-clock me-2 text-secondary"></i>Today's Timeline</h6>
            <span class="current-time-indicator" id="live-clock"></span>
        </div>

        <div class="timeline-scroll" id="timeline-scroll">
            @foreach ($hours as $hour)
                @php
                    $hourTime     = \Carbon\Carbon::createFromFormat('H:i', $hour);
                    $formattedH   = $hourTime->format('g');
                    $formattedAMPM= $hourTime->format('A');
                    $hourTasks    = $tasksByHour[$hour] ?? [];
                    $isCurrentHr  = (int)$hourTime->format('H') === (int)now()->format('H');
                @endphp
                <div class="hour-row {{ $isCurrentHr ? 'is-current-hour' : '' }}" id="row-{{ $hour }}">
                    {{-- Hour label --}}
                    <div class="hour-label">
                        <span>{{ $formattedH }}</span>
                        <span class="label-ampm">{{ $formattedAMPM }}</span>
                    </div>

                    {{-- Drop / tap slot --}}
                    <div class="hour-slot {{ empty($hourTasks) ? 'slot-empty' : '' }}"
                         data-hour="{{ $hour }}"
                         ondragover="dragOver(event)"
                         ondragleave="dragLeave(event)"
                         ondrop="dropOnSlot(event)"
                         onclick="slotTapped('{{ $hour }}', this)">

                        {{-- Tap-to-assign ghost hint --}}
                        <div class="slot-tap-hint">
                            <i class="fa-solid fa-plus-circle"></i> Assign here
                        </div>

                        {{-- Scheduled tasks pills --}}
                        @foreach ($hourTasks as $task)
                            <div class="sched-pill" id="pill-{{ $task->id }}">
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="pill-title" title="{{ $task->title }}">{{ $task->title }}</div>
                                    @if($task->estimated_minutes)
                                        <div style="font-size:0.65rem;color:var(--muted);margin-top:2px;">
                                            <i class="fa-regular fa-hourglass"></i> {{ $task->estimated_minutes }}m
                                        </div>
                                    @endif
                                </div>
                                <div class="pill-actions">
                                    {{-- Toggle complete --}}
                                    <form action="{{ route('tasks.toggle', $task->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="pill-check-btn {{ $task->status === 'Completed' ? 'done' : '' }}"
                                                title="{{ $task->status === 'Completed' ? 'Mark pending' : 'Mark complete' }}">
                                            <i class="fa-solid fa-check" style="font-size:0.65rem;"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Time-Picker Bottom Sheet / Modal ──────────────────────────── --}}
<div class="modal fade" id="timePickerModal" tabindex="-1" aria-labelledby="timePickerLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h6 class="modal-title fw-bold" id="timePickerLabel">
                        <i class="fa-solid fa-clock me-2 text-primary"></i>Pick a Time Slot
                    </h6>
                    <p class="text-muted mb-0" style="font-size:0.75rem;" id="picker-task-name"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="time-grid" id="time-grid"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ── State ────────────────────────────────────────────────────────
let selectedTaskId   = null;
let selectedTaskTitle = null;
const CSRF = document.querySelector('meta[name=csrf-token]').content;
const BLOCK_URL = "{{ route('planner.block') }}";

// ── Live clock in header ──────────────────────────────────────────
function updateClock() {
    const el = document.getElementById('live-clock');
    if (el) {
        const now = new Date();
        const h = now.getHours(), m = now.getMinutes();
        const ampm = h >= 12 ? 'PM' : 'AM';
        const hh = h % 12 || 12;
        const mm = String(m).padStart(2, '0');
        el.textContent = `Now: ${hh}:${mm} ${ampm}`;
    }
}
setInterval(updateClock, 10000);
updateClock();

// ── Auto-scroll to current hour on load ──────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const currentRow = document.querySelector('.hour-row.is-current-hour');
    if (currentRow) {
        const scroll = document.getElementById('timeline-scroll');
        if (scroll) {
            const offset = currentRow.offsetTop - 80;
            scroll.scrollTo({ top: Math.max(0, offset), behavior: 'smooth' });
        }
    }
});

// ── Task Selection (mobile tap) ────────────────────────────────────
function selectTask(taskId, title) {
    // Deselect previous
    document.querySelectorAll('.task-chip').forEach(c => c.classList.remove('selected'));

    if (selectedTaskId === taskId) {
        // Toggle off
        selectedTaskId = null;
        selectedTaskTitle = null;
        document.getElementById('timeline-wrap').classList.remove('has-selected-task');
        return;
    }

    selectedTaskId    = taskId;
    selectedTaskTitle = title;

    const chip = document.getElementById(`task-chip-${taskId}`);
    if (chip) chip.classList.add('selected');

    document.getElementById('timeline-wrap').classList.add('has-selected-task');
    showToast(`"${title}" selected — tap a time slot to assign`, 'primary');
}

// ── Slot tapped (mobile assign) ───────────────────────────────────
function slotTapped(hour, slotEl) {
    if (!selectedTaskId) return; // nothing selected → ignore
    assignTaskToHour(selectedTaskId, hour);
}

// ── Core assign function (shared by tap & drag) ───────────────────
function assignTaskToHour(taskId, hour) {
    fetch(BLOCK_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ task_id: taskId, time: hour })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 400);
        } else {
            showToast(data.message || 'Error scheduling task', 'danger');
        }
    })
    .catch(() => showToast('Network error. Try again.', 'danger'));

    // Immediately clear selection
    selectedTaskId = null;
    selectedTaskTitle = null;
    document.getElementById('timeline-wrap').classList.remove('has-selected-task');
    document.querySelectorAll('.task-chip').forEach(c => c.classList.remove('selected'));
}

// ── Time Picker Modal ─────────────────────────────────────────────
// hours available in the timeline
const TIMELINE_HOURS = @json($hours);

function openTimePicker(taskId, title, occupiedHours) {
    selectedTaskId    = taskId;
    selectedTaskTitle = title;

    document.getElementById('picker-task-name').textContent = `Assigning: "${title}"`;

    const grid = document.getElementById('time-grid');
    grid.innerHTML = '';

    const nowH = new Date().getHours();

    TIMELINE_HOURS.forEach(hour => {
        const hInt   = parseInt(hour.split(':')[0]);
        const dt     = new Date();
        dt.setHours(hInt, 0, 0, 0);
        const label  = dt.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
        const isOcc  = occupiedHours.includes(hour);
        const isCurr = hInt === nowH;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'time-btn' + (isOcc ? ' occupied' : '') + (isCurr ? ' current-hour-btn' : '');
        btn.innerHTML = label + (isOcc ? '<br><span style="font-size:.6rem">occupied</span>' : '');
        btn.onclick = () => {
            bootstrap.Modal.getInstance(document.getElementById('timePickerModal'))?.hide();
            assignTaskToHour(taskId, hour);
        };
        grid.appendChild(btn);
    });

    new bootstrap.Modal(document.getElementById('timePickerModal')).show();
}

// ── Drag-and-drop (Desktop) ────────────────────────────────────────
function dragStart(ev) {
    const chip = ev.currentTarget;
    ev.dataTransfer.setData('task_id',    chip.dataset.taskId);
    ev.dataTransfer.setData('task_title', chip.dataset.taskTitle);
    ev.dataTransfer.effectAllowed = 'move';
}

function dragOver(ev) {
    ev.preventDefault();
    ev.currentTarget.classList.add('dragover');
}

function dragLeave(ev) {
    ev.currentTarget.classList.remove('dragover');
}

function dropOnSlot(ev) {
    ev.preventDefault();
    const slot   = ev.currentTarget;
    slot.classList.remove('dragover');
    const taskId = ev.dataTransfer.getData('task_id');
    const hour   = slot.dataset.hour;
    if (!taskId || !hour) return;
    assignTaskToHour(parseInt(taskId), hour);
}
</script>
@endpush

