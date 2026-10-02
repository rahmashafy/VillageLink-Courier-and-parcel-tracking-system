<x-village-link-layout title="AI Assistant" header="Village Link AI Assistant">
    <div class="mx-auto flex h-[560px] max-w-3xl flex-col overflow-hidden vl-panel" x-data="aiAssistant()">
        <div class="border-b border-white/10 pb-4">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-vl-peach/40 bg-vl-accent/20">
                    <i data-lucide="bot" class="vl-icon-lg"></i>
                </span>
                <div>
                    <h2 class="font-display text-lg font-semibold text-vl-peach">Courier Assistant</h2>
                    <p class="text-sm text-white/60">Ask about tracking, booking, payment, ETA, or complaints.</p>
                </div>
            </div>
        </div>

        <div class="flex-1 space-y-3 overflow-y-auto py-5">
            <template x-for="(msg, i) in messages" :key="i">
                <div :class="msg.role === 'user' ? 'text-right' : 'text-left'">
                    <span class="inline-block max-w-[85%] rounded-2xl px-4 py-3 text-sm leading-6"
                          :class="msg.role === 'user' ? 'bg-vl-accent text-white' : 'border border-white/10 bg-white/10 text-white/80'"
                          x-text="msg.text"></span>
                </div>
            </template>
        </div>

        <form @submit.prevent="send()" class="flex gap-2 border-t border-white/10 pt-4">
            <input type="text" x-model="input" placeholder="Ask with tracking ID if needed..." class="vl-input flex-1 text-sm">
            <button type="submit" class="vl-btn-primary px-4" aria-label="Send" :disabled="loading">
                <i data-lucide="send" class="vl-icon"></i>
            </button>
        </form>
    </div>

    @push('scripts')
        <script>
            function aiAssistant() {
                return {
                    input: '',
                    loading: false,
                    messages: [{ role: 'bot', text: 'Hello. I can check parcel status, explain payments, or help with complaints.' }],
                    async send() {
                        if (!this.input.trim() || this.loading) return;
                        const message = this.input;
                        this.messages.push({ role: 'user', text: message });
                        this.input = '';
                        this.loading = true;
                        try {
                            const { data } = await window.axios.post('{{ route('assistant.ask') }}', { message });
                            this.messages.push({ role: 'bot', text: data.reply });
                        } catch (error) {
                            this.messages.push({ role: 'bot', text: 'Sorry, I could not process that request right now.' });
                        } finally {
                            this.loading = false;
                        }
                    }
                }
            }
        </script>
    @endpush
</x-village-link-layout>
