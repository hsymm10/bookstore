<template>
  <div class="card h-[calc(100vh-140px)] flex overflow-hidden shadow-glass border border-slate-200">
    
    <!-- User List Sidebar -->
    <div class="w-80 border-r border-slate-200 flex flex-col bg-slate-50/50 flex-shrink-0"
      :class="{ 'hidden md:flex': activeUser }">
      <div class="p-4 border-b border-slate-200 bg-white">
        <h3 class="font-bold text-slate-800 text-sm">Percakapan Pelanggan</h3>
        <p class="text-xs text-slate-400 mt-0.5">Kelola pesan dan keluhan pelanggan</p>
      </div>

      <!-- Search List -->
      <div class="flex-1 overflow-y-auto divide-y divide-slate-100/50 scrollbar-thin">
        <div v-if="loadingUsers" class="flex flex-col items-center justify-center p-8 text-slate-400 gap-2">
          <span class="w-5 h-5 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"></span>
          <span class="text-xs">Memuat daftar...</span>
        </div>

        <div v-else-if="users.length === 0" class="p-8 text-center text-slate-400 text-xs">
          Belum ada pelanggan yang menghubungi Anda.
        </div>

        <button v-else v-for="u in users" :key="u.id" @click="selectUser(u)"
          class="w-full p-4 flex gap-3 text-left hover:bg-slate-50 transition-colors"
          :class="activeUser?.id === u.id ? 'bg-primary-50/50 border-l-4 border-primary-600' : ''">
          
          <!-- Avatar -->
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center font-bold text-white text-sm flex-shrink-0 relative">
            {{ u.nama?.charAt(0)?.toUpperCase() }}
            <!-- Online indicator dot -->
            <span
              class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white"
              :class="u.is_online ? 'bg-emerald-500' : 'bg-slate-300'"
            ></span>
            <!-- Unread badge -->
            <span v-if="u.unread_count > 0"
              class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 border-2 border-white rounded-full text-[10px] font-bold text-white flex items-center justify-center animate-bounce">
              {{ u.unread_count }}
            </span>
          </div>

          <!-- Meta -->
          <div class="flex-1 min-w-0">
            <div class="flex justify-between items-baseline gap-2">
              <h4 class="font-bold text-slate-800 text-xs truncate">{{ u.nama }}</h4>
              <span class="text-[9px] text-slate-400 font-semibold flex-shrink-0">{{ u.last_time }}</span>
            </div>
            <p class="text-xs text-slate-500 truncate mt-0.5" :class="u.unread_count > 0 ? 'font-bold text-slate-800' : ''">
              {{ u.last_message || 'Tidak ada pesan' }}
            </p>
            <p class="text-[9px] mt-0.5" :class="u.is_online ? 'text-emerald-600 font-bold' : 'text-slate-400'">
              {{ u.is_online ? '● Online' : u.last_seen_label ?? 'Offline' }}
            </p>
          </div>
        </button>
      </div>
    </div>

    <!-- Active Chat Area -->
    <div class="flex-1 flex flex-col bg-white" :class="{ 'hidden': !activeUser && isMobile }">
      
      <!-- Chat Header -->
      <div v-if="activeUser" class="px-6 py-4 border-b border-slate-200 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <!-- Back button on mobile -->
          <button @click="backToList" class="md:hidden p-2 -ml-2 text-slate-400 hover:text-slate-600">
            <Icon name="mdi:chevron-left" class="text-xl" />
          </button>

          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center font-bold text-white text-sm relative">
            {{ activeUser.nama?.charAt(0)?.toUpperCase() }}
            <!-- Online indicator in header -->
            <span
              class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white"
              :class="activeUserStatus.is_online ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300'"
            ></span>
          </div>
          <div>
            <h3 class="font-bold text-slate-800 text-sm leading-snug">{{ activeUser.nama }}</h3>
            <p class="text-[10px] mt-0.5" :class="activeUserStatus.is_online ? 'text-emerald-600 font-bold' : 'text-slate-400'">
              {{ activeUserStatus.is_online ? '● Online sekarang' : activeUserStatus.last_seen_label ?? 'Offline' }}
            </p>
          </div>
        </div>
        <!-- Live clock -->
        <div class="flex items-center gap-1.5 text-xs text-slate-400 font-mono bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200">
          <Icon name="mdi:clock-time-four-outline" class="text-sm text-[#2E5AA7]" />
          <span>{{ liveClock }}</span>
        </div>
      </div>

      <!-- Transaction Integration Panel for Admin -->
      <div v-if="activeUser && activeUserLatestOrder" class="bg-blue-50 border-b border-blue-200 px-6 py-3 flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2">
          <Icon name="mdi:receipt-text" class="text-blue-700 text-base" />
          <div>
            <span class="font-bold text-slate-700">Terakhir: </span>
            <span class="font-mono font-black text-blue-800">{{ activeUserLatestOrder.invoice_number }}</span>
          </div>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-bold capitalize bg-blue-100 text-blue-800">
            {{ activeUserLatestOrder.status }}
          </span>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" 
            :class="activeUserLatestOrder.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'">
            {{ activeUserLatestOrder.payment_status_label }}
          </span>
        </div>
        <div class="flex gap-2">
          <NuxtLink :to="`/invoice/${activeUserLatestOrder.invoice_number}`" target="_blank" class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg font-bold text-[10px] text-slate-700 transition-colors uppercase">
            Detail Pesanan & Invoice
          </NuxtLink>
        </div>
      </div>

      <!-- Messages Stream -->
      <div ref="chatContainer" class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50/20">
        <div v-if="!activeUser" class="flex flex-col items-center justify-center h-full text-slate-400 text-center p-8">
          <Icon name="mdi:chat-outline" class="text-6xl text-slate-300" />
          <h4 class="font-bold text-slate-800 mt-4 text-sm">Pilih Percakapan</h4>
          <p class="text-xs mt-1 max-w-xs">Silakan pilih salah satu pelanggan di daftar kiri untuk mulai berbalas pesan.</p>
        </div>

        <div v-else-if="loadingMessages" class="flex flex-col items-center justify-center h-full text-slate-400 gap-2">
          <span class="w-5 h-5 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"></span>
          <span class="text-xs">Memuat pesan...</span>
        </div>

        <div v-else-if="messages.length === 0" class="text-center py-10 text-slate-400 text-xs">
          Belum ada percakapan. Kirim pesan pertama untuk memulai!
        </div>

        <div v-else v-for="msg in messages" :key="msg.id || msg.tempId" class="flex flex-col"
          :class="msg.sender_id === authStore.currentUser?.id ? 'items-end' : 'items-start'">
          <div class="max-w-[75%] rounded-2xl p-4 text-sm"
            :class="msg.sender_id === authStore.currentUser?.id
              ? 'bg-[#2E5AA7] text-white rounded-br-none shadow-md font-medium'
              : 'bg-slate-100 text-slate-800 rounded-bl-none border border-slate-200/50 shadow-sm font-medium'">
            <p class="leading-relaxed whitespace-pre-wrap">{{ msg.message }}</p>
          </div>
          <span class="text-[10px] text-slate-400 mt-1 px-1 flex items-center gap-1.5">
            <Icon name="mdi:clock-outline" class="text-xs" />
            <span>{{ msg.time || msg.time_relative || 'Baru saja' }}</span>

            <!-- Message Status for Admin -->
            <span v-if="msg.sender_id === authStore.currentUser?.id" class="flex items-center gap-0.5">
              <span v-if="msg.status === 'sending'" class="text-slate-400 italic">Mengirim...</span>
              <span v-else-if="msg.status === 'read'" class="text-blue-600 flex items-center font-bold">
                <Icon name="mdi:check-all" class="text-xs" /> Dibaca
              </span>
              <span v-else class="text-slate-400 flex items-center font-bold">
                <Icon name="mdi:check" class="text-xs" /> Terkirim
              </span>
            </span>
          </span>
        </div>
      </div>

      <!-- Input Send Form -->
      <form v-if="activeUser" @submit.prevent="sendMessage" class="p-4 border-t border-slate-200 flex items-center gap-3 bg-white">
        <textarea v-model="newMessage" @keydown.enter.prevent="handleEnter" rows="1" placeholder="Tulis pesan balasan..." required
          class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E5AA7] resize-none"
          :disabled="sending"></textarea>
        
        <button type="submit" class="btn-primary !p-3 rounded-xl flex-shrink-0" :disabled="sending || !newMessage.trim()">
          <span v-if="sending" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
          <Icon v-else name="mdi:send" class="w-5 h-5" />
        </button>
      </form>

    </div>

  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin', middleware: 'admin', ssr: false })

const authStore = useAuthStore()
const api = useApi()

const users = ref<any[]>([])
const activeUser = ref<any | null>(null)
const messages = ref<any[]>([])
const newMessage = ref('')
const activeUserLatestOrder = ref<any | null>(null)
const activeUserStatus = ref({ is_online: false, last_seen_label: '' as string })

const loadingUsers = ref(true)
const loadingMessages = ref(false)
const sending = ref(false)

const chatContainer = ref<HTMLElement | null>(null)
const isMobile = ref(false)

const liveClock = ref('')
let clockInterval: any = null

const updateClock = () => {
  const now = new Date()
  const dateStr = new Intl.DateTimeFormat('id-ID', {
    weekday: 'short', day: '2-digit', month: 'short', year: 'numeric'
  }).format(now)
  const timeStr = new Intl.DateTimeFormat('id-ID', {
    hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
  }).format(now)
  liveClock.value = `${dateStr}, ${timeStr}`
}

let pollUsersInterval: any = null
let pollMessagesInterval: any = null

const scrollToBottom = () => {
  nextTick(() => {
    if (chatContainer.value) {
      chatContainer.value.scrollTop = chatContainer.value.scrollHeight
    }
  })
}

const fetchUsersList = async (isFirst = false) => {
  if (isFirst) loadingUsers.value = true
  try {
    const res = await api.get('/admin/chats')
    users.value = res.data.data
  } catch (e) {
    console.error(e)
  } finally {
    if (isFirst) loadingUsers.value = false
  }
}

const fetchMessagesStream = async (isFirst = false) => {
  if (!activeUser.value) return
  if (isFirst) loadingMessages.value = true
  try {
    const res = await api.get(`/admin/chats/${activeUser.value.id}`)
    // Backend now returns { messages: [...], user_status: {...} }
    const payload = res.data.data
    const newMessages: any[] = Array.isArray(payload) ? payload : (payload.messages ?? [])
    if (payload.user_status) {
      activeUserStatus.value = payload.user_status
    }

    if (isFirst) {
      messages.value = newMessages
      scrollToBottom()
    } else {
      const existingMap = new Map(messages.value.map((m: any) => [m.id, m]))
      
      newMessages.forEach((m: any) => {
        if (existingMap.has(m.id)) {
          const current = existingMap.get(m.id)
          current.status = m.status
          current.is_read = m.is_read
        } else {
          messages.value.push(m)
        }
      })
      scrollToBottom()
    }
  } catch (e) {
    console.error(e)
  } finally {
    if (isFirst) loadingMessages.value = false
  }
}

const fetchActiveUserLatestOrder = async () => {
  if (!activeUser.value) return
  try {
    const res = await api.get(`/orders?user_id=${activeUser.value.id}&per_page=1`)
    if (res.data.data.data.length > 0) {
      activeUserLatestOrder.value = res.data.data.data[0]
    } else {
      activeUserLatestOrder.value = null
    }
  } catch (e) {
    console.error(e)
    activeUserLatestOrder.value = null
  }
}

const selectUser = (user: any) => {
  activeUser.value = user
  messages.value = []
  activeUserLatestOrder.value = null
  
  if (pollMessagesInterval) clearInterval(pollMessagesInterval)
  
  fetchMessagesStream(true)
  fetchActiveUserLatestOrder()
  
  pollMessagesInterval = setInterval(() => {
    fetchMessagesStream(false)
  }, 3000)
  
  user.unread_count = 0
}

const backToList = () => {
  activeUser.value = null
  activeUserLatestOrder.value = null
  if (pollMessagesInterval) {
    clearInterval(pollMessagesInterval)
    pollMessagesInterval = null
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || sending.value || !activeUser.value) return
  sending.value = true
  const msgText = newMessage.value
  newMessage.value = ''
  
  const tempId = 'temp-' + Date.now()
  const tempMsg = {
    tempId,
    sender_id: authStore.currentUser?.id,
    message: msgText,
    status: 'sending',
    time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
    created_at: new Date().toISOString()
  }
  
  messages.value.push(tempMsg)
  scrollToBottom()
  
  try {
    const res = await api.post('/admin/chats', {
      message: msgText,
      receiver_id: activeUser.value.id
    })
    
    const idx = messages.value.findIndex(m => m.tempId === tempId)
    if (idx !== -1) {
      messages.value[idx] = res.data.data
    } else {
      messages.value.push(res.data.data)
    }
    
    scrollToBottom()
    fetchUsersList(false)
  } catch (e) {
    console.error(e)
    messages.value = messages.value.filter(m => m.tempId !== tempId)
    newMessage.value = msgText
  } finally {
    sending.value = false
  }
}

const handleEnter = (e: KeyboardEvent) => {
  if (!e.shiftKey) {
    sendMessage()
  }
}

const checkWidth = () => {
  if (process.client) {
    isMobile.value = window.innerWidth < 768
  }
}

onMounted(() => {
  fetchUsersList(true)
  checkWidth()
  updateClock()
  clockInterval = setInterval(updateClock, 1000)
  if (process.client) {
    window.addEventListener('resize', checkWidth)
  }
  
  pollUsersInterval = setInterval(() => fetchUsersList(false), 5000)
})

onUnmounted(() => {
  if (pollUsersInterval) clearInterval(pollUsersInterval)
  if (pollMessagesInterval) clearInterval(pollMessagesInterval)
  if (clockInterval) clearInterval(clockInterval)
  if (process.client) {
    window.removeEventListener('resize', checkWidth)
  }
})
</script>
