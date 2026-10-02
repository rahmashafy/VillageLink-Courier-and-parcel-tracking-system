<x-layouts.public :title="__('vl.contact')">
    <section class="vl-page-hero" style="background-image: linear-gradient(135deg, rgba(13,29,37,0.93), rgba(16,76,100,0.9)), url('https://images.unsplash.com/photo-1423666639047-f56000c27a91?w=1600&q=80');">
        <div class="relative z-10 mx-auto max-w-7xl px-4 text-center">
            <p class="mb-3 text-sm font-semibold uppercase tracking-widest text-vl-peach">Get In Touch</p>
            <h1 class="font-display text-5xl font-bold md:text-6xl">{{ __('vl.contact') }}</h1>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-20">
        <div class="grid gap-8 lg:grid-cols-2">
            <div class="vl-panel">
                <h2 class="mb-2 text-sm font-semibold uppercase tracking-wider text-vl-peach">Contact Us</h2>
                <h3 class="mb-6 font-display text-3xl font-bold">Get In Touch</h3>
                <p class="mb-10 leading-7 text-white/75">Have questions about booking, tracking, or partnerships? Our team is ready to help.</p>
                <div class="space-y-5">
                    @foreach ([['phone','Phone','+94 77 858 086 1'],['mail','Email','info@villagelink.lk'],['map-pin','Address','41, Digana, kandy, Sri Lanka'],['globe','Website','www.villagelink.lk']] as $c)
                        <div class="flex items-start gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-vl-peach/20 text-vl-peach">
                                <i data-lucide="{{ $c[0] }}" class="vl-icon-lg"></i>
                            </span>
                            <div>
                                <p class="text-sm text-white/50">{{ $c[1] }}</p>
                                <p class="font-medium text-white">{{ $c[2] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="vl-panel border-vl-peach/30">
                <h3 class="mb-1 font-semibold text-vl-peach">You have a question?</h3>
                <p class="mb-6 text-sm text-white/60">Fill the form and we will reply within 24 hours.</p>
                <form method="POST" action="{{ route('contact.store') }}" class="space-y-4">
                    @csrf
                    <input name="name" placeholder="Your Name" required class="vl-input">
                    <input type="email" name="email" placeholder="Your Email" required class="vl-input">
                    <textarea name="message" rows="5" placeholder="Your Message" required class="vl-input"></textarea>
                    <button type="submit" class="vl-btn-primary w-full">
                        <i data-lucide="send" class="vl-icon"></i>
                        Send Message
                    </button>
                </form>
            </div>
        </div>
        <div class="vl-glass mt-10 h-72 overflow-hidden rounded-2xl">
            <iframe title="Map" class="h-full w-full border-0 grayscale opacity-80"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d253682.7047587488!2d79.8612!3d6.9271!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae2593925694891%3A0x66b2a4c4e2c4e4e4!2sColombo!5e0!3m2!1sen!2slk!4v1" loading="lazy"></iframe>
        </div>
    </section>
</x-layouts.public>
