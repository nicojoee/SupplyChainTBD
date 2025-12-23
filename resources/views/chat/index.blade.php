@extends('layouts.app')

@section('title', 'Chat')

@section('content')
<div class="chat-container" style="display: flex; height: calc(100vh - 120px); gap: 1rem;">
    <!-- Left Sidebar - Conversations -->
    <div class="chat-sidebar" style="width: 320px; min-width: 280px; background: var(--bg-glass); border-radius: 16px; display: flex; flex-direction: column; overflow: hidden;">
        <div style="padding: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <h3 style="margin: 0 0 0.75rem 0;">💬 Messages</h3>
            <div style="display: flex; gap: 0.5rem;">
                <button onclick="showNewChatModal()" class="btn btn-primary" style="flex: 1;">
                    ➕ New Chat
                </button>
                @if(auth()->user()->role === 'superadmin')
                <button onclick="showBroadcastModal()" class="btn" style="background: #f59e0b;">
                    📢
                </button>
                @endif
            </div>
        </div>

        <div id="conversation-list" style="flex: 1; overflow-y: auto; padding: 0.5rem;">
            <!-- Broadcasts Item -->
            @if(isset($broadcasts) && $broadcasts->count() > 0)
            <a href="{{ route('chat.index') }}?view=broadcasts" 
               class="conversation-item {{ request()->get('view') === 'broadcasts' ? 'active' : '' }}"
               style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border-radius: 10px; text-decoration: none; color: inherit; margin-bottom: 0.5rem; background: {{ request()->get('view') === 'broadcasts' ? 'rgba(245, 158, 11, 0.2)' : 'rgba(245, 158, 11, 0.1)' }}; border: 1px solid rgba(245, 158, 11, 0.3);">
                <div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #f59e0b, #d97706); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <span style="font-size: 1.25rem;">📢</span>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <strong style="font-size: 0.9rem;">Broadcasts</strong>
                    <div style="font-size: 0.75rem; color: rgba(255,255,255,0.5);">From Admin</div>
                    <div style="font-size: 0.8rem; color: rgba(255,255,255,0.6);">{{ $broadcasts->count() }} announcements</div>
                </div>
            </a>
            @endif

            @forelse($conversations as $conv)
            <a href="{{ route('chat.show', $conv['other_user']->id) }}" 
               class="conversation-item {{ isset($activeUser) && $activeUser->id == $conv['other_user']->id ? 'active' : '' }}"
               style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border-radius: 10px; text-decoration: none; color: inherit; margin-bottom: 0.25rem; {{ isset($activeUser) && $activeUser->id == $conv['other_user']->id ? 'background: rgba(99, 102, 241, 0.2);' : '' }}">
                <div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    @if($conv['other_user']->avatar)
                        <img src="{{ $conv['other_user']->avatar }}" alt="" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    @else
                        <span style="font-size: 1.25rem;">{{ strtoupper(substr($conv['other_user']->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <strong style="font-size: 0.9rem;">{{ $conv['other_user']->name }}</strong>
                        @if($conv['unread_count'] > 0)
                            <span style="background: #ef4444; color: #fff; font-size: 0.7rem; padding: 2px 6px; border-radius: 10px;">{{ $conv['unread_count'] }}</span>
                        @endif
                    </div>
                    <div style="font-size: 0.75rem; color: rgba(255,255,255,0.5); text-transform: capitalize;">{{ $conv['other_user']->role }}</div>
                    @if($conv['last_message'])
                        <div style="font-size: 0.8rem; color: rgba(255,255,255,0.6); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $conv['last_message']->is_deleted ? '🚫 Message deleted' : Str::limit($conv['last_message']->message ?? '📷 Image', 25) }}
                        </div>
                    @endif
                </div>
            </a>
            @empty
                @if(!isset($broadcasts) || $broadcasts->count() == 0)
                <div style="text-align: center; padding: 2rem; color: rgba(255,255,255,0.5);">
                    No conversations yet.<br>Start a new chat!
                </div>
                @endif
            @endforelse
        </div>
    </div>

    <!-- Right Panel - Chat Messages -->
    <div class="chat-panel" style="flex: 1; background: var(--bg-glass); border-radius: 16px; display: flex; flex-direction: column; overflow: hidden;">
        
        @if(request()->get('view') === 'broadcasts')
        <!-- Broadcasts View -->
        <div style="padding: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 0.75rem; background: rgba(245, 158, 11, 0.1);">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #f59e0b, #d97706); display: flex; align-items: center; justify-content: center;">
                <span>📢</span>
            </div>
            <div>
                <strong>Broadcasts</strong>
                <div style="font-size: 0.75rem; color: rgba(255,255,255,0.5);">Announcements from Admin (read-only)</div>
            </div>
        </div>

        <div id="broadcasts-container" style="flex: 1; overflow-y: auto; padding: 1rem;">
            @forelse($broadcasts as $broadcast)
            <div class="broadcast-item" id="broadcast-{{ $broadcast->id }}" style="margin-bottom: 1rem; padding: 1rem; background: rgba(245, 158, 11, 0.1); border-radius: 12px; border-left: 4px solid #f59e0b;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <strong style="color: #f59e0b;">{{ $broadcast->sender->name }}</strong>
                        <span style="font-size: 0.75rem; color: rgba(255,255,255,0.5);">{{ $broadcast->created_at->format('M d, H:i') }}</span>
                    </div>
                    <button onclick="dismissBroadcast({{ $broadcast->id }})" style="background: transparent; border: none; color: rgba(255,255,255,0.4); cursor: pointer; font-size: 0.8rem;" title="Dismiss">
                        ✕ Dismiss
                    </button>
                </div>
                @if($broadcast->image_path)
                    @if($broadcast->file_type === 'pdf')
                        {{-- PDF Download Button --}}
                        <a href="{{ $broadcast->image_path }}" 
                           download="{{ $broadcast->file_name ?? 'document.pdf' }}"
                           style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5); border-radius: 8px; color: #fff; text-decoration: none; margin-bottom: 0.5rem;">
                            📄 {{ $broadcast->file_name ?? 'Download PDF' }}
                            <span style="background: #ef4444; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">PDF</span>
                        </a>
                    @else
                        {{-- Image Display --}}
                        @if(str_starts_with($broadcast->image_path, 'data:'))
                            <img src="{{ $broadcast->image_path }}" style="max-width: 100%; max-height: 300px; border-radius: 8px; margin-bottom: 0.5rem;">
                        @else
                            <img src="/storage/{{ $broadcast->image_path }}" style="max-width: 100%; max-height: 300px; border-radius: 8px; margin-bottom: 0.5rem;">
                        @endif
                    @endif
                @endif
                @if($broadcast->message)
                    <div style="white-space: pre-wrap;">{{ $broadcast->message }}</div>
                @endif
            </div>
            @empty
            <div style="text-align: center; color: rgba(255,255,255,0.5); padding: 2rem;">
                No broadcasts yet.
            </div>
            @endforelse
        </div>

        <div style="padding: 1rem; border-top: 1px solid rgba(255,255,255,0.1); background: rgba(245, 158, 11, 0.05);">
            <div style="text-align: center; color: rgba(255,255,255,0.5); font-size: 0.9rem;">
                📢 Broadcasts are read-only. To chat with admin, start a new conversation.
            </div>
        </div>
        
        @elseif(isset($activeUser) && isset($activeConversation))
        <!-- Chat Header -->
        <div style="padding: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center;">
                @if($activeUser->avatar)
                    <img src="{{ $activeUser->avatar }}" alt="" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                @else
                    <span>{{ strtoupper(substr($activeUser->name, 0, 1)) }}</span>
                @endif
            </div>
            <div>
                <strong>{{ $activeUser->name }}</strong>
                <div style="font-size: 0.75rem; color: rgba(255,255,255,0.5); text-transform: capitalize;">{{ $activeUser->role }}</div>
            </div>
        </div>

        <!-- Messages Area -->
        <div id="messages-container" style="flex: 1; overflow-y: auto; padding: 1rem;" data-conversation-id="{{ $activeConversation->id }}">
            <div style="text-align: center; color: rgba(255,255,255,0.5);">Loading messages...</div>
        </div>

        <!-- Message Input -->
        <div style="padding: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
            <form id="message-form" enctype="multipart/form-data" style="display: flex; gap: 0.5rem; align-items: flex-end;">
                <input type="hidden" name="conversation_id" value="{{ $activeConversation->id }}">
                <label style="cursor: pointer; padding: 0.75rem; background: rgba(255,255,255,0.1); border-radius: 10px;">
                    📷
                    <input type="file" name="image" accept="image/*" style="display: none;" onchange="previewImage(this)">
                </label>
                <div style="flex: 1;">
                    <div id="image-preview" style="display: none; margin-bottom: 0.5rem; position: relative;">
                        <img src="" alt="Preview" style="max-height: 100px; border-radius: 8px;">
                        <button type="button" onclick="removeImagePreview()" style="position: absolute; top: -8px; right: -8px; background: #ef4444; border: none; color: #fff; width: 24px; height: 24px; border-radius: 50%; cursor: pointer;">×</button>
                    </div>
                    <input type="text" name="message" placeholder="Type a message..." class="form-control" style="width: 100%;" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem;">
                    Send ➤
                </button>
            </form>
        </div>
        @else
        <div style="flex: 1; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.5);">
            <div style="text-align: center;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">💬</div>
                <h3>Select a conversation</h3>
                <p>or start a new chat</p>
                @if(auth()->user()->role === 'superadmin')
                <button onclick="showBroadcastModal()" class="btn" style="background: #f59e0b; margin-top: 1rem;">
                    📢 Send Broadcast to All
                </button>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- New Chat Modal -->
<div id="new-chat-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: linear-gradient(135deg, #1e1b4b, #0f0a3c); border-radius: 16px; padding: 1.5rem; max-width: 400px; width: 90%; max-height: 70vh; display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="margin: 0;">Start New Chat</h3>
            <button onclick="closeNewChatModal()" style="background: transparent; border: none; color: #fff; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        <input type="text" id="contact-search" placeholder="Search contacts..." class="form-control" style="margin-bottom: 1rem;" oninput="filterContacts()">
        <div id="contacts-list" style="flex: 1; overflow-y: auto;">
            <div style="text-align: center; color: rgba(255,255,255,0.5);">Loading contacts...</div>
        </div>
    </div>
</div>

<!-- Broadcast Modal (superadmin only) -->
@if(auth()->user()->role === 'superadmin')
<div id="broadcast-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: linear-gradient(135deg, #1e1b4b, #0f0a3c); border-radius: 16px; padding: 1.5rem; max-width: 500px; width: 90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="margin: 0;">📢 Send Broadcast</h3>
            <button onclick="closeBroadcastModal()" style="background: transparent; border: none; color: #fff; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        <p style="color: rgba(255,255,255,0.6); margin-bottom: 1rem; font-size: 0.9rem;">
            This message will be sent to <strong>all users</strong> and cannot be replied to.
        </p>
        <form id="broadcast-form" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-control" rows="4" placeholder="Type your broadcast message..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Attachment (Image/PDF, optional)</label>
                <input type="file" name="attachment" accept="image/*,.pdf,application/pdf" class="form-control">
                <small style="color: rgba(255,255,255,0.4);">Image: auto-compress to 200KB. PDF: max 2MB.</small>
            </div>
            <button type="submit" class="btn" style="width: 100%; background: #f59e0b;">
                📢 Send Broadcast
            </button>
        </form>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
let contacts = [];
const currentUserId = {{ auth()->id() }};

// Dismiss broadcast
function dismissBroadcast(broadcastId) {
    const item = document.getElementById('broadcast-' + broadcastId);
    if (item) {
        item.style.opacity = '0.5';
        item.style.display = 'none';
        // Store dismissed broadcasts in localStorage
        let dismissed = JSON.parse(localStorage.getItem('dismissedBroadcasts') || '[]');
        if (!dismissed.includes(broadcastId)) {
            dismissed.push(broadcastId);
            localStorage.setItem('dismissedBroadcasts', JSON.stringify(dismissed));
        }
    }
}

// Hide already dismissed broadcasts on load + scroll to bottom
document.addEventListener('DOMContentLoaded', function() {
    let dismissed = JSON.parse(localStorage.getItem('dismissedBroadcasts') || '[]');
    dismissed.forEach(id => {
        const item = document.getElementById('broadcast-' + id);
        if (item) item.style.display = 'none';
    });
    
    // Auto-scroll broadcasts to bottom (newest)
    const broadcastsContainer = document.getElementById('broadcasts-container');
    if (broadcastsContainer) {
        broadcastsContainer.scrollTop = broadcastsContainer.scrollHeight;
    }
});

// Load contacts when modal opens
function showNewChatModal() {
    document.getElementById('new-chat-modal').style.display = 'flex';
    loadContacts();
}

function closeNewChatModal() {
    document.getElementById('new-chat-modal').style.display = 'none';
}

function loadContacts() {
    fetch('{{ route("chat.contacts") }}')
        .then(r => r.json())
        .then(data => {
            contacts = data;
            renderContacts(data);
        });
}

function renderContacts(list) {
    const container = document.getElementById('contacts-list');
    if (list.length === 0) {
        container.innerHTML = '<div style="text-align: center; color: rgba(255,255,255,0.5);">No contacts available</div>';
        return;
    }
    container.innerHTML = list.map(c => `
        <a href="/chat/${c.id}" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border-radius: 10px; text-decoration: none; color: inherit; margin-bottom: 0.25rem;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center;">
                ${c.avatar ? `<img src="${c.avatar}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">` : `<span>${c.name.charAt(0).toUpperCase()}</span>`}
            </div>
            <div>
                <strong>${c.name}</strong>
                <div style="font-size: 0.75rem; color: rgba(255,255,255,0.5); text-transform: capitalize;">${c.role}</div>
            </div>
        </a>
    `).join('');
}

function filterContacts() {
    const query = document.getElementById('contact-search').value.toLowerCase();
    const filtered = contacts.filter(c => c.name.toLowerCase().includes(query) || c.role.toLowerCase().includes(query));
    renderContacts(filtered);
}

// Broadcast functionality (superadmin only)
@if(auth()->user()->role === 'superadmin')
function showBroadcastModal() {
    document.getElementById('broadcast-modal').style.display = 'flex';
}

function closeBroadcastModal() {
    document.getElementById('broadcast-modal').style.display = 'none';
}

// Compress image to max 200KB using Canvas
async function compressImage(file, maxSizeKB = 200) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                
                // Scale down if image is very large
                const maxDimension = 1200;
                if (width > maxDimension || height > maxDimension) {
                    const ratio = Math.min(maxDimension / width, maxDimension / height);
                    width *= ratio;
                    height *= ratio;
                }
                
                canvas.width = width;
                canvas.height = height;
                
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                
                // Compress with reducing quality until under maxSizeKB
                let quality = 0.8;
                let dataUrl = canvas.toDataURL('image/jpeg', quality);
                
                while (dataUrl.length > maxSizeKB * 1024 * 1.37 && quality > 0.1) {
                    quality -= 0.1;
                    dataUrl = canvas.toDataURL('image/jpeg', quality);
                }
                
                // If still too large, scale down more
                if (dataUrl.length > maxSizeKB * 1024 * 1.37) {
                    const scaleFactor = Math.sqrt((maxSizeKB * 1024 * 1.37) / dataUrl.length);
                    canvas.width = width * scaleFactor;
                    canvas.height = height * scaleFactor;
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    dataUrl = canvas.toDataURL('image/jpeg', 0.7);
                }
                
                // Convert dataURL to Blob
                const byteString = atob(dataUrl.split(',')[1]);
                const mimeString = dataUrl.split(',')[0].split(':')[1].split(';')[0];
                const ab = new ArrayBuffer(byteString.length);
                const ia = new Uint8Array(ab);
                for (let i = 0; i < byteString.length; i++) {
                    ia[i] = byteString.charCodeAt(i);
                }
                const blob = new Blob([ab], { type: mimeString });
                const compressedFile = new File([blob], file.name, { type: mimeString });
                
                console.log(`📷 Compressed: ${(file.size/1024).toFixed(1)}KB → ${(compressedFile.size/1024).toFixed(1)}KB`);
                resolve(compressedFile);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

document.getElementById('broadcast-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '⏳ Processing...';
    submitBtn.disabled = true;
    
    try {
        const formData = new FormData(this);
        const attachmentInput = this.querySelector('input[name="attachment"]');
        
        // Handle attachment (image or PDF)
        if (attachmentInput.files && attachmentInput.files[0]) {
            const file = attachmentInput.files[0];
            const isImage = file.type.startsWith('image/');
            const isPDF = file.type === 'application/pdf';
            
            if (isImage && file.size > 200 * 1024) {
                // Compress images over 200KB
                submitBtn.innerHTML = '⏳ Compressing image...';
                const compressedFile = await compressImage(file, 200);
                formData.set('attachment', compressedFile);
            } else if (isPDF && file.size > 2 * 1024 * 1024) {
                // PDF too large (max 2MB)
                const sizeMB = (file.size / (1024 * 1024)).toFixed(1);
                alert(`❌ PDF file too large (${sizeMB}MB).\n\nMaximum 2MB allowed.\n\nTips to reduce PDF size:\n• Use online tools like ilovepdf.com\n• Reduce image quality in PDF\n• Remove unnecessary pages`);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                return;
            }
        }
        
        submitBtn.innerHTML = '⏳ Sending...';
        
        const response = await fetch('{{ route("chat.broadcast") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✅ Broadcast sent successfully!');
            this.reset();
            closeBroadcastModal();
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to send broadcast'));
        }
    } catch (err) {
        alert('Error: ' + err.message);
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

document.getElementById('broadcast-modal').addEventListener('click', function(e) {
    if (e.target === this) closeBroadcastModal();
});
@endif

// Messages functionality
@if(isset($activeConversation))
const conversationId = {{ $activeConversation->id }};

function loadMessages() {
    fetch('{{ route("chat.messages", $activeConversation->id) }}')
        .then(r => r.json())
        .then(messages => {
            renderMessages(messages);
        });
}

function renderMessages(messages) {
    const container = document.getElementById('messages-container');
    if (messages.length === 0) {
        container.innerHTML = '<div style="text-align: center; color: rgba(255,255,255,0.5);">No messages yet. Say hello!</div>';
        return;
    }
    container.innerHTML = messages.map(m => {
        if (m.is_deleted) {
            return `<div style="text-align: ${m.is_mine ? 'right' : 'left'}; margin-bottom: 0.75rem;">
                <div style="display: inline-block; padding: 0.75rem 1rem; border-radius: 16px; background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.4); font-style: italic;">
                    🚫 This message was deleted
                </div>
            </div>`;
        }
        return `
            <div style="text-align: ${m.is_mine ? 'right' : 'left'}; margin-bottom: 0.75rem;">
                <div style="display: inline-block; max-width: 70%; text-align: left; position: relative;" class="message-bubble">
                    ${m.image_path ? `<img src="${m.image_path.startsWith('data:') ? m.image_path : '/storage/' + m.image_path}" style="max-width: 100%; max-height: 300px; border-radius: 12px; margin-bottom: ${m.message ? '0.5rem' : '0'};">` : ''}
                    ${m.message ? `<div style="padding: 0.75rem 1rem; border-radius: 16px; background: ${m.is_mine ? 'linear-gradient(135deg, #6366f1, #8b5cf6)' : 'rgba(255,255,255,0.1)'};">${m.message}</div>` : ''}
                    <div style="font-size: 0.7rem; color: rgba(255,255,255,0.4); margin-top: 0.25rem; display: flex; justify-content: space-between; align-items: center;">
                        <span>${m.created_at}</span>
                        ${m.can_unsend ? `<button onclick="unsendMessage(${m.id})" style="background: transparent; border: none; color: rgba(255,255,255,0.4); cursor: pointer; font-size: 0.7rem; margin-left: 0.5rem;">Unsend</button>` : ''}
                    </div>
                </div>
            </div>
        `;
    }).join('');
    container.scrollTop = container.scrollHeight;
}

function unsendMessage(messageId) {
    if (!confirm('Unsend this message?')) return;
    fetch(`/chat/unsend/${messageId}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(() => loadMessages());
}

// Send message with loading state
let isMessageSending = false;

document.getElementById('message-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Prevent double send
    if (isMessageSending) {
        alert('⏳ Please wait, message is still sending...');
        return;
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    const messageInput = this.querySelector('input[name="message"]');
    const imageInput = this.querySelector('input[name="image"]');
    
    // Get values BEFORE disabling inputs
    const messageValue = messageInput.value.trim();
    const hasImage = imageInput && imageInput.files && imageInput.files[0];
    
    // Validate: require either message or image
    if (!messageValue && !hasImage) {
        alert('⚠️ Please enter a message or attach an image');
        return;
    }
    
    // Create FormData BEFORE disabling inputs (disabled inputs are excluded from FormData)
    const formData = new FormData(this);
    
    submitBtn.innerHTML = '⏳ Sending...';
    submitBtn.disabled = true;
    messageInput.disabled = true;
    isMessageSending = true;
    
    try {
        
        // Compress image if exists and > 200KB
        if (imageInput && imageInput.files && imageInput.files[0]) {
            const originalFile = imageInput.files[0];
            if (originalFile.size > 200 * 1024) {
                submitBtn.innerHTML = '⏳ Compressing...';
                const compressedFile = await compressImageForChat(originalFile, 200);
                formData.set('image', compressedFile);
            }
        }
        
        submitBtn.innerHTML = '⏳ Sending...';
        
        const response = await fetch('{{ route("chat.send") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            this.reset();
            removeImagePreview();
            loadMessages();
        } else {
            alert('Error: ' + (data.error || 'Failed to send message'));
        }
    } catch (err) {
        alert('Error: ' + err.message);
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        messageInput.disabled = false;
        isMessageSending = false;
    }
});

// Compress image for chat (shared function)
async function compressImageForChat(file, maxSizeKB = 200) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                
                const maxDimension = 1000;
                if (width > maxDimension || height > maxDimension) {
                    const ratio = Math.min(maxDimension / width, maxDimension / height);
                    width *= ratio;
                    height *= ratio;
                }
                
                canvas.width = width;
                canvas.height = height;
                
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                
                let quality = 0.7;
                let dataUrl = canvas.toDataURL('image/jpeg', quality);
                
                while (dataUrl.length > maxSizeKB * 1024 * 1.37 && quality > 0.1) {
                    quality -= 0.1;
                    dataUrl = canvas.toDataURL('image/jpeg', quality);
                }
                
                const byteString = atob(dataUrl.split(',')[1]);
                const mimeString = dataUrl.split(',')[0].split(':')[1].split(';')[0];
                const ab = new ArrayBuffer(byteString.length);
                const ia = new Uint8Array(ab);
                for (let i = 0; i < byteString.length; i++) {
                    ia[i] = byteString.charCodeAt(i);
                }
                const blob = new Blob([ab], { type: mimeString });
                const compressedFile = new File([blob], file.name, { type: mimeString });
                
                console.log(`📷 Chat compressed: ${(file.size/1024).toFixed(1)}KB → ${(compressedFile.size/1024).toFixed(1)}KB`);
                resolve(compressedFile);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

// Image preview
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.querySelector('#image-preview img').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImagePreview() {
    document.getElementById('image-preview').style.display = 'none';
    document.querySelector('input[name="image"]').value = '';
}

// Auto-refresh messages every 3 seconds
loadMessages();
setInterval(loadMessages, 3000);
@endif

// Close modal on outside click
document.getElementById('new-chat-modal').addEventListener('click', function(e) {
    if (e.target === this) closeNewChatModal();
});
</script>
@endsection
