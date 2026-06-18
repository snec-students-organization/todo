@extends('layouts.app')

@section('title', 'Calendar')
@section('page_title', 'Productivity Calendar')

@push('styles')
<!-- FullCalendar styling tweaks (can be inline since we load main CSS in JS) -->
<style>
    #calendar {
        background-color: var(--card-light);
        border: 1px solid var(--border-light);
        border-radius: 12px;
        padding: 1.5rem;
    }
    .fc-event {
        cursor: pointer;
        padding: 2px 4px;
        font-size: 0.85rem;
    }
    .fc-theme-standard th {
        background-color: rgba(0,0,0,0.02);
        padding: 8px 0;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        color: #64748b;
    }
</style>
@endpush

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <p class="text-secondary mb-0">Drag and drop events to reschedule tasks. Click a date to add a new task, or click an event to manage it.</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="shadow-sm p-4 rounded-3 border-0 bg-card" id="calendar"></div>
    </div>
</div>
@endsection

@push('scripts')
<!-- FullCalendar JS library CDN -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            themeSystem: 'standard',
            editable: true,
            droppable: true,
            selectable: true,
            events: "{{ route('calendar.feed') }}",
            
            // 1. Reschedule Event via Drag and Drop
            eventDrop: function(info) {
                const taskId = info.event.id;
                let startStr = info.event.start.toISOString();
                
                // Adjust for timezone differences since ISOString returns UTC
                const tzOffset = info.event.start.getTimezoneOffset() * 60000;
                const localISOTime = (new Date(info.event.start - tzOffset)).toISOString().slice(0, -1);

                fetch("{{ route('calendar.reschedule') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        task_id: taskId,
                        start: localISOTime
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                    } else {
                        showToast(data.message, 'danger');
                        info.revert();
                    }
                })
                .catch(err => {
                    console.error("Reschedule error:", err);
                    showToast("Error rescheduling task.", 'danger');
                    info.revert();
                });
            },

            // 2. Click Empty Date to Add a Task
            dateClick: function(info) {
                const clickedDate = info.dateStr; // Format is YYYY-MM-DD
                const createUrl = "{{ route('tasks.create') }}?due_date=" + clickedDate;
                window.location.href = createUrl;
            },

            // 3. Custom styles on load
            eventDidMount: function(info) {
                // Add a small tooltip or visual cues for status/priority
                const priority = info.event.extendedProps.priority;
                const status = info.event.extendedProps.status;
                if (status === 'Completed') {
                    info.el.style.opacity = '0.7';
                }
            }
        });

        calendar.render();
    });
</script>
@endpush
