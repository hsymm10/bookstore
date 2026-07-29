<template>
  <div class="pt-24 pb-20 bg-slate-50 min-h-screen">
    <div class="section-container max-w-4xl">
      
      <!-- Card Container -->
      <div class="card h-[600px] flex flex-col shadow-glass border border-white/60 overflow-hidden">
        
        <!-- Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-primary-600 to-purple-600 text-white flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-xl font-bold">
              👑
            </div>
            <div>
              <h2 class="font-bold text-sm">Customer Service</h2>
              <p class="text-xs text-blue-100 flex items-center gap-1.5 mt-0.5">
                <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                Tanya Admin BookStore
              </p>
            </div>
          </div>
        </div>

        <!-- Chat Area -->
        <div ref="chatContainer" class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50/50">
          <div v-if="loading" class="flex flex-col items-center justify-center h-full text-slate-400 gap-2">
            <span class="w-6 h-6 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"></span>
            <span class="text-xs">Memuat pesan...</span>
          </div>
          
          <div v-else-if="messages.length === 0" class="flex flex-col items-center justify-center h-full text-slate-400 text-center p-8">
            <span class="text-5xl">💬</span>
            <h4 class="font-bold text-slate-800 mt-4 text-sm">Belum Ada Percakapan</h4>
            <p class="text-xs mt-1 max-w-xs">Tulis pesan pertama Anda untuk mulai berkonsultasi dengan Admin.</p>
          </div>

          <div v-else v-for="msg in messages" :key="msg.id" class="flex flex-col"
            :class="msg.sender_id === authStore.currentUser?.id ? 'items-end' : 'items-start'">
            <div class="max-w-[70%] rounded-2xl p-4 text-sm"
              :class="msg.sender_id === authStore.currentUser?.id
                ? 'bg-primary-600 text-white rounded-br-none shadow-md'
                : 'bg-white text-slate-800 rounded-bl-none shadow-sm border border-slate-100'">
              <p class="leading-relaxed whitespace-pre-wrap">{{ msg.message }}</p>
            </div>
           <span class="text-[10px] text-slate-400 mt-1 px-1 flex items-center gap-1">
              <Icon name="mdi:clock-outline" class="text-xs" />
              {{ msg.created_at }}
            </span>
          </div>
        </div>

        <!-- Input Bar -->
        <form @submit.prevent="sendMessage" class="p-4 bg-white border-t border-slate-100 flex items-center gap-3">
          <textarea v-model="newMessage" @keydown.enter.prevent="handleEnter" rows="1" placeholder="Tulis pesan..." required
            class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"
            :disabled="sending"></textarea>
          
          <button type="submit" class="btn-primary !p-3 rounded-xl flex-shrink-0" :disabled="sending || !newMessage.trim()">
            <span v-if="sending" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <Icon v-else name="mdi:send" class="w-5 h-5" />
          </button>
        </form>

      </div>

    </div>
  </div>
</template>

<script setup lang="ts">


definePageMeta({ layout: 'default', middleware: 'auth', ssr: false })

const authStore = useAuthStore()
const router = useRouter()
const api = useApi()

// Guard: Admin yang membuka /chat harus diarahkan ke /admin/chat
onMounted(() => {
  if (authStore.isAdmin) {
    router.replace('/admin/chat')
  }
})

const messages = ref<any[]>([])
const newMessage = ref('')
const loading = ref(true)
const sending = ref(false)
const chatContainer = ref<HTMLElement | null>(null)

let pollInterval: any = null

const scrollToBottom = () => {
  nextTick(() => {
    if (chatContainer.value) {
      chatContainer.value.scrollTop = chatContainer.value.scrollHeight
    }
  })
}

const fetchMessages = async (isFirst = false) => {
  if (isFirst) loading.value = true
  try {
    const res = await api.get('/user/chats')
    const newMessages = res.data.data
    
    // Check if new message count differs, scroll only if changed or first load
    const countChanged = newMessages.length !== messages.value.length
    messages.value = newMessages
    
    if (isFirst || countChanged) {
      scrollToBottom()
    }
  } catch (e) {
    console.error(e)
  } finally {
    if (isFirst) loading.value = false
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || sending.value) return
  sending.value = true
  const msgText = newMessage.value
  newMessage.value = ''
  
  try {
    const res = await api.post('/user/chats', { message: msgText })
    messages.value.push(res.data.data)
    scrollToBottom()
  } catch (e) {
    console.error(e)
  } finally {
    sending.value = false
  }
}

const handleEnter = (e: KeyboardEvent) => {
  if (!e.shiftKey) {
    sendMessage()
  }
}

onMounted(() => {
  fetchMessages(true)
  pollInterval = setInterval(() => fetchMessages(false), 3000)
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})
</script>
