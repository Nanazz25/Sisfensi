@extends('layouts.app')

@section('title', 'Operator Console - Ticket #' . $ticket->id)

@section('afterAppStyles')
<style>
    /* 3-Column Specific Layout */
    .operator-console { display: flex; height: calc(100vh - 120px); gap: 15px; }
    .console-column { display: flex; flex-direction: column; overflow: hidden; border-radius: 12px; }
    
    /* Left: Info & Control */
    .col-info { flex: 0 0 280px; background: #f8f9fa; border: 1px solid #e9ecef; }
    /* Middle: Chat */
    .col-chat { flex: 1; background: #ffffff; border: 1px solid #e9ecef; position: relative; }
    /* Right: Suggestions */
    .col-ai { flex: 0 0 300px; background: #f0f4f8; border: 1px solid #d1d9e6; }

    /* Left Components */
    .status-badge-select { background: #fff; border-radius: 8px; padding: 15px; margin-bottom: 15px; border: 1px solid #eee; }
    .reporter-info { padding: 15px; background: #fff; border-radius: 8px; border: 1px solid #eee; margin-bottom: 15px; }
    .sla-tracker { padding: 15px; background: #fff; border-radius: 8px; border: 1px solid #eee; }

    /* Chat Styling (Enhanced) */
    .chat-scroll { flex: 1; overflow-y: auto; padding: 20px; background: #fcfcfc; }
    .chat-container { display: flex; flex-direction: column; gap: 12px; }
    .chat-row { display: flex; gap: 10px; width: 100%; }
    .chat-row.right { flex-direction: row-reverse; }
    .chat-bubble { max-width: 80%; padding: 10px 14px; border-radius: 15px; font-size: 0.85rem; line-height: 1.5; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .chat-row.left .chat-bubble { background: #fff; border: 1px solid #eee; border-bottom-left-radius: 2px; }
    .chat-row.right .chat-bubble { background: #007bff; color: #fff; border-bottom-right-radius: 2px; }
    
    /* Chat Input Tools */
    .chat-tools { display: flex; gap: 10px; padding: 10px 15px; border-top: 1px solid #eee; background: #fff; }
    .tool-btn { color: #6c757d; cursor: pointer; font-size: 1.1rem; transition: color 0.2s; }
    .tool-btn:hover { color: #007bff; }
    .input-box { padding: 10px 15px; background: #fff; border-top: 1px solid #eee; display: flex; gap: 10px; align-items: flex-end; position: relative; }
    .input-box textarea { flex: 1; border: none; background: #f8f9fa; border-radius: 20px; padding: 8px 15px; font-size: 0.9rem; resize: none; max-height: 100px; }
    .input-box textarea:focus { outline: none; box-shadow: inset 0 0 0 1px #007bff; }
    
    .attachment-preview { display: none; padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 8px; margin: 10px; position: absolute; bottom: 100%; left: 0; width: fit-content; z-index: 10; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); }
    .attachment-preview img { height: 80px; border-radius: 4px; }
    .btn-remove-attachment { position: absolute; top: -10px; right: -10px; background: #dc3545; color: #fff; border-radius: 50%; width: 20px; height: 20px; font-size: 10px; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; cursor: pointer; }
    
    /* Right Panel Suggestions */
    .ai-header { padding: 15px; background: #e2e8f0; border-bottom: 1px solid #cbd5e1; display: flex; align-items: center; gap: 8px; }
    .suggestion-list { padding: 15px; display: flex; flex-direction: column; gap: 12px; overflow-y: auto; }
    .suggestion-item { background: #fff; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.2s; }
    .suggestion-item:hover { border-color: #007bff; transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    .suggestion-label { font-size: 0.7rem; color: #007bff; font-weight: bold; text-transform: uppercase; display: block; margin-bottom: 4px; }
    .suggestion-text { font-size: 0.8rem; color: #444; line-height: 1.4; }

    /* Responsive adjustments */
    @media (max-width: 1200px) {
        .operator-console { flex-direction: column; height: auto; }
        .col-info, .col-ai { flex: 1; }
    }
</style>
@endsection

@section('content')
<div class="operator-console">
    <!-- LEFT COLUMN: CONTROL & INFO -->
    <div class="console-column col-info p-3">
        <!-- Status Dropdown -->
        <div class="status-badge-select shadow-xs">
            <label class="small font-weight-bold text-uppercase text-muted">Update Status</label>
            <form action="{{ route('tickets.update-status', $ticket->id) }}" method="POST">
                @csrf
                <select name="status" class="form-control form-control-sm border-primary" onchange="this.form.submit()">
                    <option value="Open" {{ $ticket->status == 'Open' ? 'selected' : '' }}>Open</option>
                    <option value="In-Progress" {{ $ticket->status == 'In-Progress' ? 'selected' : '' }}>In-Progress</option>
                    <option value="Resolved" {{ $ticket->status == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="Closed" {{ $ticket->status == 'Closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </form>
        </div>

        <!-- Reporter Info -->
        <div class="reporter-info shadow-xs">
            <h6 class="font-weight-bold mb-3 border-bottom pb-2">Ticket Info</h6>
            <div class="mb-3">
                <small class="text-muted d-block text-uppercase" style="font-size: 10px;">Reporter</small>
                <div class="d-flex align-items-center mt-1">
                    <div class="bg-primary text-white rounded-circle text-center mr-2" style="width: 25px; height: 25px; font-size: 10px; line-height: 25px;">{{ substr($ticket->reporter->name, 0, 1) }}</div>
                    <span class="font-weight-bold small text-dark">{{ $ticket->reporter->name }}</span>
                </div>
            </div>
            <div class="mb-3">
                <small class="text-muted d-block text-uppercase" style="font-size: 10px;">Priority</small>
                <span class="badge badge-{{ $ticket->priority == 'High' ? 'danger' : ($ticket->priority == 'Mid' ? 'warning' : 'info') }} mt-1">{{ $ticket->priority }}</span>
            </div>
            <div class="mb-0">
                <small class="text-muted d-block text-uppercase" style="font-size: 10px;">Created Date</small>
                <span class="small font-weight-bold text-dark">{{ $ticket->created_at->format('d M Y, H:i') }}</span>
            </div>
        </div>

        <!-- SLA Tracking -->
        <div class="sla-tracker shadow-xs mt-auto">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="font-weight-bold mb-0" style="font-size: 0.8rem;">SLA Tracking</h6>
                <span class="text-success small font-weight-bold">ON TRACK</span>
            </div>
            @php
                $hoursPassed = $ticket->created_at->diffInHours(now());
                $progress = min(100, ($hoursPassed / 24) * 100);
            @endphp
            <div class="progress progress-xs mb-2">
                <div class="progress-bar bg-{{ $progress > 80 ? 'danger' : ($progress > 50 ? 'warning' : 'success') }}" style="width: {{ $progress }}%"></div>
            </div>
            <small class="text-muted italic d-block" style="font-size: 10px;">Ticket open for {{ $hoursPassed }} hours</small>
        </div>
    </div>

    <!-- MIDDLE COLUMN: CHAT INTERFACE -->
    <div class="console-column col-chat">
        <div class="header p-3 border-bottom bg-white d-flex justify-content-between align-items-center">
            <div>
                <h6 class="font-weight-bold mb-0">Ticket #{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}</h6>
                <small class="text-muted">{{ $ticket->subject }}</small>
            </div>
            <div class="d-flex gap-2">
                <span class="badge badge-soft-info"><i class="fa fa-tag mr-1"></i> {{ $ticket->category->name }}</span>
            </div>
        </div>

        <div class="chat-scroll" id="chatArea">
            <div class="chat-container">
                <!-- Reporter Original Description -->
                <div class="chat-row left">
                    <div class="chat-bubble border-primary" style="border-left-width: 4px;">
                        <small class="text-primary font-weight-bold d-block mb-1">{{ $ticket->reporter->name }}</small>
                        <p class="mb-0">{!! nl2br(e($ticket->description)) !!}</p>
                        <small class="text-muted d-block text-right mt-1" style="font-size: 0.6rem;">{{ $ticket->created_at->format('H:i') }}</small>
                    </div>
                </div>

                @foreach($ticket->responses as $response)
                    <div class="chat-row {{ $response->responder_id === auth()->id() ? 'right' : 'left' }}">
                        <div class="chat-bubble">
                            @if($response->responder_id !== auth()->id())
                                <small class="text-info font-weight-bold d-block mb-1">{{ $response->responder->name }}</small>
                            @endif
                            @if($response->attachment)
                                <img src="{{ asset('storage/' . $response->attachment) }}" class="img-fluid rounded mb-2 d-block" style="max-height: 200px; cursor: pointer;" onclick="window.open(this.src)">
                            @endif
                            <p class="mb-0">{!! nl2br(e($response->message)) !!}</p>
                            <small class="text-{{ $response->responder_id === auth()->id() ? 'light' : 'muted' }} d-block text-right mt-1" style="font-size: 0.6rem;">{{ $response->created_at->format('H:i') }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Chat Controls -->
        <div class="chat-tools">
            <div class="tool-btn" id="imageBtn" title="Kirim Gambar"><i class="fa fa-image"></i></div>
            <div class="tool-btn" id="emojiBtn" title="Kirim Emoji"><i class="fa fa-smile-o"></i></div>
        </div>

        @if($ticket->status !== 'Closed')
            <form id="chatForm" class="input-box" method="POST" enctype="multipart/form-data">
                @csrf
                <div id="attachmentPreview" class="attachment-preview">
                    <img id="previewImg" src="" alt="Preview">
                    <button type="button" class="btn-remove-attachment" id="removeAttachment"><i class="fa fa-times"></i></button>
                </div>
                <input type="file" name="attachment" id="attachmentInput" hidden accept="image/*">
                <textarea name="message" id="messageArea" rows="1" placeholder="Type your response here..."></textarea>
                <button type="submit" class="btn btn-primary rounded-circle" style="width: 40px; height: 40px; flex-shrink: 0;">
                    <i class="fa fa-paper-plane"></i>
                </button>
            </form>
        @else
            <div class="p-3 text-center text-muted bg-light italic small">
                <i class="fa fa-lock mr-1"></i> Tiket telah ditutup. Tidak dapat mengirim pesan baru.
            </div>
        @endif
    </div>

    <!-- RIGHT COLUMN: AI SUGGESTIONS -->
    <div class="console-column col-ai">
        <div class="ai-header shadow-xs">
            <i class="fa fa-magic text-primary"></i>
            <h6 class="mb-0 font-weight-bold">Smart Replies</h6>
        </div>
        
        <div class="suggestion-list">
            @forelse($suggestions as $index => $s)
                @php 
                    $parts = explode(': ', $s, 2);
                    $label = $parts[0] ?? 'Saran';
                    $text = trim($parts[1] ?? $s, '"');
                @endphp
                <div class="suggestion-item shadow-xs" onclick="useSuggestion('{{ addslashes($text) }}')">
                    <span class="suggestion-label">{{ $label }}</span>
                    <p class="suggestion-text mb-0">{{ $text }}</p>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fa fa-lightbulb-o fa-3x text-muted mb-3 opacity-25"></i>
                    <p class="text-muted small">Belum ada saran tersedia untuk kategori ini.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-auto p-3 border-top bg-white">
            <small class="text-muted d-block mb-1 font-weight-bold">TIPS OPERATOR</small>
            <p class="small text-muted mb-0 italic">Gunakan bahasa yang ramah dan mintalah bukti berupa gambar untuk mempercepat proses investigasi.</p>
        </div>
    </div>
</div>
@endsection

@section('afterAppScripts')
<script src="https://cdn.jsdelivr.net/npm/@joeattardi/emoji-button@4.6.4/dist/index.min.js"></script>
<script>
    const ticketId = "{{ $ticket->id }}";
    const authId = {{ auth()->id() }};
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
        if (imageBtn) imageBtn.addEventListener('click', () => attachmentInput.click());
        if (attachmentInput) {
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
        }

        if (removeAttachment) {
            removeAttachment.addEventListener('click', () => {
                attachmentInput.value = '';
                preview.style.display = 'none';
            });
        }

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
                        if (preview) preview.style.display = 'none';
                        fetchNewMessages();
                    }
                });
            });
        }

        // Polling for new messages
        setInterval(fetchNewMessages, 3000);
    });

    function fetchNewMessages() {
        fetch("{{ route('tickets.responses.fetch', $ticket->id) }}")
            .then(res => res.json())
            .then(data => {
                renderChat(data.responses);
                renderSuggestions(data.suggestions);
            });
    }

    function renderSuggestions(suggestions) {
        const container = document.querySelector('.suggestion-list');
        if (!container) return;

        let html = '';
        if (suggestions.length === 0) {
            html = `
                <div class="text-center py-5">
                    <i class="fa fa-lightbulb-o fa-3x text-muted mb-3 opacity-25"></i>
                    <p class="text-muted small">Belum ada saran tersedia.</p>
                </div>
            `;
        } else {
            suggestions.forEach(s => {
                const parts = s.split(': ');
                const label = parts[0] || 'Saran';
                const text = (parts[1] || s).replace(/^"(.*)"$/, '$1'); // Remove outer quotes
                
                html += `
                    <div class="suggestion-item shadow-xs" onclick="useSuggestion('${text.replace(/'/g, "\\'")}')">
                        <span class="suggestion-label">${label}</span>
                        <p class="suggestion-text mb-0">${text}</p>
                    </div>
                `;
            });
        }
        container.innerHTML = html;
    }

    function renderChat(responses) {
        const container = document.querySelector('.chat-container');
        let html = `
            <div class="chat-row left">
                <div class="chat-bubble border-primary" style="border-left-width: 4px;">
                    <small class="text-primary font-weight-bold d-block mb-1">${reporterName}</small>
                    <p class="mb-0">${reporterDesc}</p>
                    <small class="text-muted d-block text-right mt-1" style="font-size: 0.6rem;">${reporterTime}</small>
                </div>
            </div>
        `;

        responses.forEach(resp => {
            const side = resp.is_me ? 'right' : 'left';
            const metaColor = resp.is_me ? 'light' : 'info';
            const timeColor = resp.is_me ? 'light' : 'muted';
            
            let attachmentHtml = '';
            if (resp.attachment) {
                attachmentHtml = `<img src="${resp.attachment}" class="img-fluid rounded mb-2 d-block" style="max-height: 200px; cursor: pointer;" onclick="window.open(this.src)">`;
            }

            html += `
                <div class="chat-row ${side}">
                    <div class="chat-bubble">
                        ${!resp.is_me ? `<small class="text-${metaColor} font-weight-bold d-block mb-1">${resp.responder_name}</small>` : ''}
                        ${attachmentHtml}
                        <p class="mb-0">${resp.message || ''}</p>
                        <small class="text-${timeColor} d-block text-right mt-1" style="font-size: 0.6rem;">${resp.time}</small>
                    </div>
                </div>
            `;
        });

        const currentScroll = chatArea.scrollTop + chatArea.clientHeight;
        const isBottom = currentScroll >= chatArea.scrollHeight - 50;

        container.innerHTML = html;

        if (isBottom) {
            chatArea.scrollTop = chatArea.scrollHeight;
        }
    }

    function useSuggestion(text) {
        const area = document.getElementById('messageArea');
        if (area) {
            area.value = text;
            area.style.height = 'auto';
            area.style.height = (area.scrollHeight) + 'px';
            area.focus();
        }
    }
</script>
@endsection
