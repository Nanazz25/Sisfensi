@extends('layouts.app')

@section('title', 'Ticket Details')

@section('afterAppStyles')
<style>
    /* Timeline Stepper */
    .timeline-steps { position: relative; padding-left: 30px; list-style: none; }
    .timeline-steps::before { content: ""; position: absolute; left: 14px; top: 0; bottom: 0; width: 2px; background: #e9ecef; }
    .step-item { position: relative; margin-bottom: 25px; }
    .step-item::before { 
        content: ""; position: absolute; left: -21px; top: 5px; width: 12px; height: 12px; 
        border-radius: 50%; background: #fff; border: 2px solid #adb5bd; z-index: 1;
    }
    .step-item.active::before { background: #007bff; border-color: #007bff; box-shadow: 0 0 0 3px rgba(0,123,255,0.2); }
    .step-item.completed::before { background: #28a745; border-color: #28a745; }
    .step-title { font-weight: 700; font-size: 0.9rem; color: #444; line-height: 1.2; display: block; }
    .step-time { font-size: 0.75rem; color: #888; display: block; }

    /* Info Cards Grid */
    .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .info-card { padding: 12px; background: #fff; border: 1px solid #f0f0f0; border-radius: 8px; }
    .info-card .label { font-size: 0.7rem; color: #888; text-transform: uppercase; font-weight: bold; display: block; margin-bottom: 3px; }
    .info-card .value { font-weight: 700; font-size: 0.85rem; color: #333; }

    /* PREMIUM CHAT DESIGN */
    .chat-widget .chat-scroll { height: 600px; overflow-y: auto; padding: 20px; background: #f0f2f5; border-radius: 0 0 12px 12px; }
    .chat-container { display: flex; flex-direction: column; gap: 15px; }
    
    .chat-row { display: flex; align-items: flex-end; gap: 10px; width: 100%; }
    .chat-row.right { flex-direction: row-reverse; }
    
    .avatar { width: 32px; height: 32px; border-radius: 50%; background: #ddd; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: bold; color: #fff; flex-shrink: 0; }
    .chat-row.left .avatar { background: #007bff; }
    .chat-row.right .avatar { background: #6c757d; }

    .chat-bubble { max-width: 80%; padding: 10px 14px; border-radius: 18px; font-size: 0.85rem; line-height: 1.5; position: relative; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
    .chat-row.left .chat-bubble { background: #fff; color: #333; border-bottom-left-radius: 4px; }
    .chat-row.right .chat-bubble { background: linear-gradient(135deg, #007bff, #0056b3); color: #fff; border-bottom-right-radius: 4px; }
    
    .chat-bubble .meta { font-size: 0.65rem; display: block; margin-bottom: 4px; opacity: 0.8; font-weight: bold; }
    .chat-row.left .meta { color: #007bff; }
    .chat-row.right .meta { color: rgba(255,255,255,0.9); }
    
    .chat-bubble .time { font-size: 0.6rem; display: block; text-align: right; margin-top: 4px; opacity: 0.7; }
    .chat-bubble img { max-width: 100%; border-radius: 8px; margin-top: 5px; cursor: pointer; transition: transform 0.2s; }
    .chat-bubble img:hover { transform: scale(1.02); }

    /* Modern Input */
    .chat-input-area { padding: 15px; background: #fff; border-top: 1px solid #eee; position: relative; }
    .input-wrapper { background: #f8f9fa; border-radius: 25px; padding: 5px 15px; display: flex; align-items: center; border: 1px solid #e9ecef; gap: 8px; }
    .input-wrapper textarea { background: transparent; border: none; flex-grow: 1; padding: 10px 5px; font-size: 0.9rem; resize: none; max-height: 100px; }
    .input-wrapper textarea:focus { outline: none; }
    
    .chat-btn-tool { background: none; border: none; color: #888; font-size: 1.1rem; padding: 5px; transition: color 0.2s; cursor: pointer; }
    .chat-btn-tool:hover { color: #007bff; }
    
    .btn-send { width: 35px; height: 35px; border-radius: 50%; background: #007bff; color: #fff; border: none; display: flex; align-items: center; justify-content: center; transition: all 0.2s; flex-shrink: 0; }
    .btn-send:hover { background: #0056b3; transform: scale(1.05); }

    .attachment-preview { display: none; padding: 8px; background: #fff; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px; position: relative; width: fit-content; }
    .attachment-preview img { height: 60px; border-radius: 4px; }
    .btn-remove-attachment { position: absolute; top: -10px; right: -10px; background: #dc3545; color: #fff; border-radius: 50%; width: 20px; height: 20px; font-size: 10px; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; cursor: pointer; }
</style>
@endsection

@section('content')
<div class="row clearfix">
    <!-- LEFT SIDE: Timeline & Info -->
    <div class="col-lg-7 col-md-12">
        <!-- Resolution Timeline -->
        <div class="card shadow-none border mb-3">
            <div class="header">
                <h2 class="font-weight-bold">Resolution Timeline</h2>
            </div>
            <div class="body">
                <ul class="timeline-steps">
                    <li class="step-item completed">
                        <span class="step-title">Ticket Submitted</span>
                        <span class="step-time text-muted">{{ $ticket->created_at->format('d M Y, H:i') }}</span>
                    </li>
                    <li class="step-item {{ $ticket->operator_id ? 'completed' : 'active' }}">
                        <span class="step-title">Assigned to Agent</span>
                        @if($ticket->operator_id)
                            <span class="step-time">{{ $ticket->responded_at ? $ticket->responded_at->format('d M Y, H:i') : '-' }}</span>
                        @else
                            <span class="step-time text-warning italic">Waiting for assignment...</span>
                        @endif
                    </li>
                    <li class="step-item {{ in_array($ticket->status, ['In-Progress', 'Resolved', 'Closed']) ? 'completed' : ($ticket->operator_id ? 'active' : '') }}">
                        <span class="step-title">In Progress</span>
                        @if(in_array($ticket->status, ['In-Progress', 'Resolved', 'Closed']))
                            <span class="step-time">{{ $ticket->responded_at ? $ticket->responded_at->format('d M Y, H:i') : '-' }}</span>
                        @else
                            <span class="step-time">Pending...</span>
                        @endif
                    </li>
                    <li class="step-item {{ in_array($ticket->status, ['Resolved', 'Closed']) ? 'completed' : ($ticket->status == 'In-Progress' ? 'active' : '') }}">
                        <span class="step-title">Resolved</span>
                        @if(in_array($ticket->status, ['Resolved', 'Closed']))
                            <span class="step-time text-success font-weight-bold">{{ $ticket->resolved_at ? $ticket->resolved_at->format('d M Y, H:i') : '-' }}</span>
                        @else
                            <span class="step-time">Not yet resolved</span>
                        @endif
                    </li>
                </ul>
            </div>
        </div>

        <!-- Info Cards Grid -->
        <div class="info-grid mb-3">
            <div class="info-card">
                <span class="label">Category</span>
                <span class="value">{{ $ticket->category->name }}</span>
            </div>
            <div class="info-card">
                <span class="label">Priority</span>
                <span class="value text-warning"><i class="fa fa-circle mr-1" style="font-size: 8px;"></i> {{ $ticket->priority }}</span>
            </div>
            <div class="info-card">
                <span class="label">Status</span>
                <span class="value">{{ $ticket->status }}</span>
            </div>
            <div class="info-card">
                <span class="label">Assigned Agent</span>
                <span class="value">{{ $ticket->operator->name ?? 'Unassigned' }}</span>
            </div>
        </div>

        @if(in_array(auth()->user()->role, ['helpdesk', 'admin']) && $ticket->status !== 'Closed')
            <div class="card shadow-none border mb-3">
                <div class="header">
                    <h2 class="font-weight-bold">Update Status</h2>
                </div>
                <div class="body">
                    <form action="{{ route('tickets.update-status', $ticket->id) }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <select name="status" class="form-control border">
                                <option value="Open" {{ $ticket->status == 'Open' ? 'selected' : '' }}>Open</option>
                                <option value="In-Progress" {{ $ticket->status == 'In-Progress' ? 'selected' : '' }}>In-Progress</option>
                                <option value="Resolved" {{ $ticket->status == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="Closed" {{ $ticket->status == 'Closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if(in_array($ticket->status, ['Resolved', 'Closed']) && auth()->user()->role === 'siswa')
            <div class="card shadow-none border border-success">
                <div class="header">
                    <h2 class="font-weight-bold">Penilaian Kepuasan</h2>
                </div>
                <div class="body">
                    @if($ticket->satisfactionRating)
                        <div class="text-center py-2">
                            <div class="h4 text-warning">
                                @for($i=1; $i<=5; $i++)
                                    <i class="fa fa-star{{ $i <= $ticket->satisfactionRating->rating ? '' : '-o' }}"></i>
                                @endfor
                            </div>
                            <p class="mb-0 text-muted small italic">"{{ $ticket->satisfactionRating->feedback }}"</p>
                        </div>
                    @else
                        <form action="{{ route('tickets.rate', $ticket->id) }}" method="POST">
                            @csrf
                            <div class="form-group text-center mb-2">
                                <div class="rating-stars" style="font-size: 1.5rem; cursor: pointer;">
                                    <i class="fa fa-star-o star-btn" data-value="1"></i>
                                    <i class="fa fa-star-o star-btn" data-value="2"></i>
                                    <i class="fa fa-star-o star-btn" data-value="3"></i>
                                    <i class="fa fa-star-o star-btn" data-value="4"></i>
                                    <i class="fa fa-star-o star-btn" data-value="5"></i>
                                </div>
                                <input type="hidden" name="rating" id="ratingValue" required>
                            </div>
                            <textarea name="feedback" class="form-control border mb-2" rows="2" placeholder="Tulis feedback singkat..."></textarea>
                            <button type="submit" class="btn btn-success btn-block">Kirim Rating</button>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- RIGHT SIDE: Chat Widget -->
    <div class="col-lg-5 col-md-12">
        <div class="card chat-widget shadow-none border h-100" style="border-radius: 12px; overflow: hidden; display: flex; flex-direction: column;">
            <div class="header bg-white py-3 px-3 border-bottom d-flex align-items-center">
                <div class="avatar bg-primary-light text-primary mr-2">
                    <i class="fa fa-comments-o"></i>
                </div>
                <div>
                    <h2 class="font-weight-bold mb-0" style="font-size: 1rem;">Percakapan Tiket</h2>
                    <small class="text-muted">{{ $ticket->responses->count() + 1 }} Pesan</small>
                </div>
            </div>
            
            <div class="chat-scroll" id="chatArea">
                <div class="chat-container">
                    <!-- Original Description -->
                    <div class="chat-row left">
                        <div class="avatar">{{ substr($ticket->reporter->name, 0, 1) }}</div>
                        <div class="chat-bubble border-primary" style="border-left: 3px solid #007bff;">
                            <span class="meta">{{ $ticket->reporter->name }} (Pelapor)</span>
                            <div class="font-weight-bold mb-1 text-dark">{{ $ticket->subject }}</div>
                            {!! nl2br(e($ticket->description)) !!}
                            <span class="time">{{ $ticket->created_at->format('H:i') }}</span>
                        </div>
                    </div>

                    <!-- Responses -->
                    @foreach($ticket->responses as $response)
                        @php $isMe = $response->responder_id === auth()->id(); @endphp
                        <div class="chat-row {{ $isMe ? 'right' : 'left' }}">
                            <div class="avatar">{{ substr($response->responder->name, 0, 1) }}</div>
                            <div class="chat-bubble">
                                <span class="meta">{{ $response->responder->name }}</span>
                                @if($response->attachment)
                                    <img src="{{ asset('storage/' . $response->attachment) }}" alt="attachment" class="d-block mb-1" onclick="window.open(this.src)">
                                @endif
                                {!! nl2br(e($response->message)) !!}
                                <span class="time">{{ $response->created_at->format('H:i') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($ticket->status !== 'Closed')
                <div class="chat-input-area">
                    <div id="attachmentPreview" class="attachment-preview">
                        <img id="previewImg" src="" alt="Preview">
                        <button type="button" class="btn-remove-attachment" id="removeAttachment"><i class="fa fa-times"></i></button>
                    </div>
                    <form id="chatForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="attachment" id="attachmentInput" hidden accept="image/*">
                        <div class="input-wrapper">
                            <button type="button" class="chat-btn-tool" id="emojiBtn">
                                <i class="fa fa-smile-o"></i>
                            </button>
                            <button type="button" class="chat-btn-tool" id="imageBtn">
                                <i class="fa fa-paperclip"></i>
                            </button>
                            <textarea name="message" id="messageArea" rows="1" placeholder="Tulis pesan..."></textarea>
                            <button class="btn-send" type="submit">
                                <i class="fa fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="p-3 text-center text-muted bg-white border-top italic small">
                    <i class="fa fa-lock mr-1"></i> Tiket telah ditutup pada {{ $ticket->resolved_at ? $ticket->resolved_at->format('d/m/Y') : '' }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('afterAppScripts')
<script src="https://cdn.jsdelivr.net/npm/@joeattardi/emoji-button@4.6.4/dist/index.min.js"></script>
<script>
    const reporterName = "{{ $ticket->reporter->name }}";
    const reporterTime = "{{ $ticket->created_at->format('H:i') }}";
    const reporterDesc = `{!! addslashes(nl2br(e($ticket->description))) !!}`;

    document.addEventListener('DOMContentLoaded', function() {
        const chatArea = document.getElementById('chatArea');
        const messageArea = document.getElementById('messageArea');
        const chatForm = document.getElementById('chatForm');
        const attachmentInput = document.getElementById('attachmentInput');
        const imageBtn = document.getElementById('imageBtn');
        const emojiBtn = document.getElementById('emojiBtn');
        const preview = document.getElementById('attachmentPreview');
        const previewImg = document.getElementById('previewImg');
        const removeAttachment = document.getElementById('removeAttachment');

        if (chatArea) chatArea.scrollTop = chatArea.scrollHeight;

        // Emoji Picker
        try {
            const picker = new EmojiButton({
                position: 'top-start'
            });
            picker.on('emoji', selection => {
                messageArea.value += selection;
            });
            if (emojiBtn) emojiBtn.addEventListener('click', () => picker.togglePicker(emojiBtn));
        } catch (e) {
            console.warn("Emoji picker failed to load:", e);
        }

        // Image Selection
        imageBtn.addEventListener('click', () => attachmentInput.click());
        attachmentInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        removeAttachment.addEventListener('click', () => {
            attachmentInput.value = '';
            preview.style.display = 'none';
        });

        // Auto-expand textarea
        if (messageArea) {
            messageArea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        }

        // Send Message Async
        if (chatForm) {
            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const msg = messageArea.value.trim();
                const hasFile = attachmentInput.files.length > 0;
                
                if (!msg && !hasFile) return;

                const formData = new FormData(this);

                fetch("{{ route('tickets.respond-async', $ticket->id) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        messageArea.value = '';
                        messageArea.style.height = 'auto';
                        attachmentInput.value = '';
                        preview.style.display = 'none';
                        fetchNewMessages();
                    }
                });
            });
        }

        // Polling for new messages
        setInterval(fetchNewMessages, 3000);

        const stars = document.querySelectorAll('.star-btn');
        const ratingInput = document.getElementById('ratingValue');
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const val = this.dataset.value;
                ratingInput.value = val;
                stars.forEach(s => {
                    if (s.dataset.value <= val) {
                        s.classList.remove('fa-star-o');
                        s.classList.add('fa-star', 'text-warning');
                    } else {
                        s.classList.remove('fa-star', 'text-warning');
                        s.classList.add('fa-star-o');
                    }
                });
            });
        });
    });

    function fetchNewMessages() {
        fetch("{{ route('tickets.responses.fetch', $ticket->id) }}")
            .then(res => res.json())
            .then(data => {
                renderChat(data.responses);
            });
    }

    function renderChat(responses) {
        const container = document.querySelector('.chat-container');
        let html = `
            <!-- Original Description -->
            <div class="chat-row left">
                <div class="avatar">${reporterName.substring(0, 1)}</div>
                <div class="chat-bubble border-primary" style="border-left: 3px solid #007bff;">
                    <span class="meta">${reporterName} (Pelapor)</span>
                    <div class="font-weight-bold mb-1 text-dark">{{ $ticket->subject }}</div>
                    <p class="mb-0">${reporterDesc}</p>
                    <span class="time">${reporterTime}</span>
                </div>
            </div>
        `;

        responses.forEach(resp => {
            const side = resp.is_me ? 'right' : 'left';
            let attachmentHtml = '';
            if (resp.attachment) {
                attachmentHtml = `<img src="${resp.attachment}" class="d-block mb-1" style="max-width: 100%; border-radius: 8px;" onclick="window.open(this.src)">`;
            }

            html += `
                <div class="chat-row ${side}">
                    <div class="avatar">${resp.responder_name.substring(0, 1)}</div>
                    <div class="chat-bubble">
                        <span class="meta">${resp.responder_name}</span>
                        ${attachmentHtml}
                        <p class="mb-0">${resp.message || ''}</p>
                        <span class="time">${resp.time}</span>
                    </div>
                </div>
            `;
        });

        const chatArea = document.getElementById('chatArea');
        const currentScroll = chatArea.scrollTop + chatArea.clientHeight;
        const isBottom = currentScroll >= chatArea.scrollHeight - 50;

        container.innerHTML = html;

        if (isBottom) {
            chatArea.scrollTop = chatArea.scrollHeight;
        }
    }
</script>
@endsection
