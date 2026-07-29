<template>
  <!-- Full-height chat layout — no scroll on outer container -->
  <div class="flex-1 flex flex-col min-h-0 overflow-hidden -m-4 lg:-m-8 rounded-2xl lg:rounded-3xl overflow-hidden">

    <!-- ─── CHAT HEADER (WhatsApp-style) ──────────────────────────── -->
    <div class="flex-shrink-0 bg-gradient-to-r from-[#1a3a6b] to-[#2E5AA7] px-4 py-3.5 flex items-center gap-3 shadow-lg">
      <!-- Avatar with online ring -->
      <div class="relative flex-shrink-0">
        <div class="w-11 h-11 rounded-full bg-gradient-to-br from-blue-300 to-blue-500 flex items-center justify-center text-white font-black text-base shadow-md border-2 border-white/30">
          AD
        </div>
        <span
          class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-[#2E5AA7] transition-colors"
          :class="adminStatus.is_online ? 'bg-emerald-400' : 'bg-slate-400'"
        />
      </div>

      <!-- Name & status -->
      <div class="flex-1 min-w-0">
        <h3 class="font-black text-white text-sm leading-none truncate">Customer Service / Admin</h3>
        <p class="text-xs mt-0.5 font-semibold transition-colors" :class="adminStatus.is_online ? 'text-emerald-300' : 'text-blue-200'">
          {{ adminStatus.is_online ? '● Online' : adminStatus.last_seen_label || '● Offline' }}
        </p>
      </div>

      <!-- Latest order chip -->
      <div v-if="latestOrder" class="flex-shrink-0">
        <NuxtLink
          :to="`/invoice/${latestOrder.invoice_number}`"
          class="flex items-center gap-1.5 bg-white/15 hover:bg-white/25 border border-white/20 rounded-xl px-2.5 py-1.5 transition-all"
        >
          <Icon name="mdi:receipt-text-outline" class="text-white text-sm flex-shrink-0" />
          <div class="hidden sm:block">
            <p class="text-[10px] text-white/70 font-bold leading-none">Invoice</p>
            <p class="text-[11px] text-white font-black font-mono leading-none">{{ latestOrder.invoice_number }}</p>
          </div>
        </NuxtLink>
      </div>
    </div>

    <!-- ─── MESSAGES AREA ─────────────────────────────────────────── -->
    <div
      ref="messageContainer"
      class="chat-messages flex-1 overflow-y-auto px-3 sm:px-5 py-5 space-y-3"
    >
      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center h-full">
        <div class="bg-white rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3">
          <div class="w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin" />
          <span class="text-sm text-slate-500 font-semibold">Memuat pesan...</span>
        </div>
      </div>

      <!-- Empty -->
      <div v-else-if="messages.length === 0" class="flex flex-col items-center justify-center h-full gap-3">
        <div class="bg-white/90 rounded-3xl px-8 py-8 shadow-sm text-center max-w-[260px]">
          <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <Icon name="mdi:chat-processing-outline" class="text-4xl text-[#2E5AA7]" />
          </div>
          <p class="font-black text-slate-700 text-sm">Mulai Percakapan</p>
          <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">Hubungi admin kami untuk bantuan seputar pesanan atau buku.</p>
        </div>
      </div>

      <!-- Messages -->
      <template v-else>
        <!-- Date separator -->
        <div class="flex justify-center py-1">
          <span class="bg-white/80 text-slate-500 text-[10px] font-bold px-4 py-1.5 rounded-full shadow-sm backdrop-blur-sm">
            Percakapan
          </span>
        </div>

        <!-- ── MESSAGE ROW — Full width container, bubble floats left/right ── -->
        <div
          v-for="msg in messages"
          :key="msg.id || msg.tempId"
          class="w-full flex"
          :class="isMyMessage(msg) ? 'justify-end pl-12' : 'justify-start pr-12'"
        >
          <!-- Admin avatar -->
          <div v-if="!isMyMessage(msg)" class="flex-shrink-0 mr-2 self-end mb-5">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#2E5AA7] to-blue-500 flex items-center justify-center text-white text-[10px] font-black shadow-md border-2 border-white">
              AD
            </div>
          </div>

          <!-- Bubble group: name + bubble + time -->
          <div class="flex flex-col" :class="isMyMessage(msg) ? 'items-end' : 'items-start'">
            <span v-if="!isMyMessage(msg)" class="text-[10px] text-slate-400 font-bold ml-1 mb-0.5">Admin</span>

            <!-- The bubble itself — key fix: display inline-block so it wraps correctly -->
            <div
              class="chat-bubble px-4 py-2.5 text-sm font-medium shadow-sm"
              :class="isMyMessage(msg)
                ? 'chat-bubble-me bg-[#2E5AA7] text-white'
                : 'chat-bubble-them bg-white text-slate-800 border border-slate-100'"
            >{{ msg.message }}</div>

            <!-- Timestamp + tick -->
            <div
              class="flex items-center gap-1 mt-1 px-0.5"
              :class="isMyMessage(msg) ? 'flex-row-reverse' : 'flex-row'"
            >
              <span class="text-[10px] text-slate-400 font-semibold">{{ msg.time || msg.time_relative || 'Baru saja' }}</span>
              <template v-if="isMyMessage(msg)">
                <span v-if="msg.status === 'sending'" class="text-[10px] text-slate-300 italic">mengirim</span>
                <Icon v-else-if="msg.status === 'read'" name="mdi:check-all" class="text-xs text-blue-500" />
                <Icon v-else name="mdi:check-all" class="text-xs text-slate-400" />
              </template>
            </div>
          </div>
        </div>

        <!-- Typing dots (when admin online) -->
        <div v-if="adminStatus.is_online" class="w-full flex justify-start pr-12">
          <div class="flex-shrink-0 mr-2 self-end">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#2E5AA7] to-blue-500 flex items-center justify-center text-white text-[10px] font-black shadow-md border-2 border-white">AD</div>
          </div>
          <div class="bg-white border border-slate-100 shadow-sm rounded-2xl rounded-tl-sm px-4 py-3 flex gap-1 items-center">
            <span v-for="i in 3" :key="i" class="w-1.5 h-1.5 rounded-full bg-slate-400 animate-bounce" :style="`animation-delay:${(i-1)*150}ms`" />
          </div>
        </div>
      </template>
    </div>

    <!-- ─── INPUT BAR ──────────────────────────────────────────────── -->
    <div class="flex-shrink-0 bg-white border-t border-slate-200 px-3 py-3 shadow-[0_-2px_16px_rgba(0,0,0,0.04)]">
      <form @submit.prevent="sendMessage" class="flex items-center gap-2">
        <input
          v-model="newMessage"
          type="text"
          placeholder="Ketik pesan..."
          :disabled="sending"
          maxlength="1000"
          autocomplete="off"
          class="flex-1 min-w-0 px-5 py-3 bg-slate-50 border border-slate-200 rounded-3xl text-sm text-slate-700 font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#2E5AA7]/25 focus:border-[#2E5AA7]/50 focus:bg-white transition-all disabled:opacity-60"
          @keydown.enter.exact.prevent="sendMessage"
        />
        <button
          type="submit"
          :disabled="sending || !newMessage.trim()"
          class="w-11 h-11 flex-shrink-0 flex items-center justify-center rounded-full transition-all active:scale-90 disabled:cursor-not-allowed"
          :class="newMessage.trim()
            ? 'bg-[#2E5AA7] hover:bg-[#254b8c] text-white shadow-lg shadow-blue-300/40'
            : 'bg-slate-100 text-slate-300'"
        >
          <span v-if="sending" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
          <Icon v-else name="mdi:send" class="text-base translate-x-px" />
        </button>
      </form>
    </div>

  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'user', middleware: 'auth', ssr: false })

const api = useApi()
const authStore = useAuthStore()

const messages = ref<any[]>([])
const loading = ref(true)
const sending = ref(false)
const newMessage = ref('')
const messageContainer = ref<HTMLElement | null>(null)
const latestOrder = ref<any | null>(null)
const adminStatus = ref({ is_online: false, last_seen_label: '' as string })

let pollInterval: ReturnType<typeof setInterval> | null = null

const isMyMessage = (msg: any) => msg.sender_id === authStore.currentUser?.id

const fetchLatestOrder = async () => {
  try {
    const res = await api.get('/orders?per_page=1')
    if (res.data.data.data.length > 0) {
      latestOrder.value = res.data.data.data[0]
    }
  } catch (e) { console.error(e) }
}

const fetchMessages = async (isFirst = false) => {
  if (isFirst) loading.value = true
  try {
    const res = await api.get('/user/chats')
    const payload = res.data.data
    const newMessages: any[] = Array.isArray(payload) ? payload : (payload.messages ?? [])
    if (payload.admin_status) adminStatus.value = payload.admin_status

    if (isFirst) {
      messages.value = newMessages
      nextTick(scrollToBottom)
    } else {
      const existingMap = new Map(messages.value.map((m: any) => [m.id, m]))
      newMessages.forEach((m: any) => {
        if (existingMap.has(m.id)) {
          const cur = existingMap.get(m.id)
          cur.status = m.status; cur.is_read = m.is_read
        } else {
          messages.value.push(m)
        }
      })
      nextTick(scrollToBottom)
    }
  } catch (e) { console.error(e) }
  finally { if (isFirst) loading.value = false }
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || sending.value) return
  sending.value = true
  const text = newMessage.value.trim()
  newMessage.value = ''
  const tempId = 'temp-' + Date.now()
  messages.value.push({
    tempId,
    sender_id: authStore.currentUser?.id,
    message: text,
    status: 'sending',
    time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
    created_at: new Date().toISOString()
  })
  nextTick(scrollToBottom)
  try {
    const res = await api.post('/user/chats', { message: text })
    const idx = messages.value.findIndex(m => m.tempId === tempId)
    if (idx !== -1) messages.value[idx] = res.data.data
    else messages.value.push(res.data.data)
    nextTick(scrollToBottom)
  } catch (e) {
    console.error(e)
    messages.value = messages.value.filter(m => m.tempId !== tempId)
    newMessage.value = text
  } finally { sending.value = false }
}

const scrollToBottom = () => {
  if (messageContainer.value) {
    messageContainer.value.scrollTop = messageContainer.value.scrollHeight
  }
}

onMounted(() => {
  fetchMessages(true)
  fetchLatestOrder()
  pollInterval = setInterval(() => fetchMessages(false), 3000)
})
onUnmounted(() => { if (pollInterval) clearInterval(pollInterval) })
</script>

<style scoped>
/* ── Message area background (subtle pattern) ── */
.chat-messages {
  background-color: #eef3fb;
  background-image: repeating-linear-gradient(
    45deg,
    transparent,
    transparent 20px,
    rgba(197, 217, 240, 0.15) 20px,
    rgba(197, 217, 240, 0.15) 21px
  );
  scrollbar-width: thin;
  scrollbar-color: #c5d5ea transparent;
}
.chat-messages::-webkit-scrollbar { width: 4px; }
.chat-messages::-webkit-scrollbar-track { background: transparent; }
.chat-messages::-webkit-scrollbar-thumb { background: #c5d5ea; border-radius: 99px; }

/* ── Bubble base — KEY FIX: inline-block so the width = content, 
   not collapsed to zero from parent flex-shrink ── */
.chat-bubble {
  display: inline-block;
  max-width: 100%;
  word-break: break-word;
  overflow-wrap: break-word;
  white-space: pre-wrap;
  line-height: 1.5;
  border-radius: 18px;
  position: relative;
}

/* My messages — bubble tail bottom-right */
.chat-bubble-me {
  border-bottom-right-radius: 4px;
}
.chat-bubble-me::after {
  content: '';
  position: absolute;
  bottom: 0;
  right: -6px;
  width: 12px;
  height: 12px;
  background: #2E5AA7;
  clip-path: polygon(0 0, 0% 100%, 100% 100%);
}

/* Admin messages — bubble tail bottom-left */
.chat-bubble-them {
  border-bottom-left-radius: 4px;
}
.chat-bubble-them::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: -6px;
  width: 12px;
  height: 12px;
  background: #fff;
  clip-path: polygon(100% 0, 0% 100%, 100% 100%);
  filter: drop-shadow(-1px 0 0 #e2e8f0);
}

/* Typing dots bounce delay */
@keyframes bounce {
  0%, 60%, 100% { transform: translateY(0); }
  30% { transform: translateY(-5px); }
}
</style>
