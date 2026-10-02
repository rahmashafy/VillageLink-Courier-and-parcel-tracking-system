<x-layouts.public :title="__('vl.about')">
    @php
        $images = [
            'hero' => 'https://images.pexels.com/photos/6169667/pexels-photo-6169667.jpeg?auto=compress&cs=tinysrgb&w=980',
            'round' => 'https://images.pexels.com/photos/6169132/pexels-photo-6169132.jpeg?auto=compress&cs=tinysrgb&w=480',
            'team' => 'https://images.pexels.com/photos/6169060/pexels-photo-6169060.jpeg?auto=compress&cs=tinysrgb&w=900',
            'planning' => 'https://images.pexels.com/photos/6169177/pexels-photo-6169177.jpeg?auto=compress&cs=tinysrgb&w=900',
            'delivery' => 'https://images.pexels.com/photos/4391470/pexels-photo-4391470.jpeg?auto=compress&cs=tinysrgb&w=1100',
        ];
    @endphp

    <div class="courier-about-page">
        <section class="courier-about-hero">
            <div class="courier-about-container courier-about-hero-grid">
                <div class="courier-about-intro">
                    <i data-lucide="package-check" class="courier-about-ghost courier-about-ghost-one"></i>
                    <h1>About Us</h1>
                    <p>Village Link brings simple booking, clear parcel tracking, secure payments, and dependable courier support into one smooth delivery experience.</p>
                </div>

                <div class="courier-about-hero-image">
                    <img src="{{ $images['hero'] }}" alt="Courier driver delivering parcels">
                </div>

                <div class="courier-about-round-image" aria-hidden="true">
                    <img src="{{ $images['round'] }}" alt="">
                </div>
            </div>
        </section>

        <section class="courier-about-overlap">
            <span class="courier-about-band courier-about-band-left"></span>
            <span class="courier-about-band courier-about-band-right"></span>
            <div class="courier-about-container courier-about-who-grid">
                <div class="courier-about-photo-card">
                    <img src="{{ $images['team'] }}" alt="Courier warehouse parcel handling">
                </div>

                <article class="courier-about-text-card">
                    <div class="courier-about-heading-row">
                        <span></span>
                        <h2>Who We Are</h2>
                    </div>
                    <p>We are a courier service platform built for everyday customers, small businesses, delivery drivers, and administrators who need parcels to move with confidence from pickup to doorstep.</p>
                    <p>Our team focuses on accurate booking details, smart delivery estimates, transparent parcel status updates, and practical support when customers need help. Every workflow is designed to keep the sender and receiver informed.</p>
                    <p>From Colombo city routes to village deliveries, Village Link keeps logistics organized, trackable, and easy to manage.</p>
                </article>
            </div>
        </section>

        <section class="courier-about-work">
            <div class="courier-about-container">
                <h2>What We Do</h2>
                <div class="courier-about-service-row">
                    @foreach ([
                        ['package-plus', 'Parcel Booking', 'Quick pickup details, delivery speed choices, and smart pricing for each shipment.'],
                        ['scan-search', 'Live Tracking', 'Clear status history so customers can follow the parcel from booking to delivery.'],
                        ['lock-keyhole', 'Secure Handling', 'Protected payment flows, controlled role access, and careful parcel processing.'],
                    ] as $item)
                        <article class="courier-about-mini-card">
                            <i data-lucide="{{ $item[0] }}"></i>
                            <h3>{{ $item[1] }}</h3>
                            <p>{{ $item[2] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="courier-about-plan">
            <div class="courier-about-container courier-about-plan-grid">
                <div class="courier-about-plan-copy">
                    <i data-lucide="boxes" class="courier-about-ghost courier-about-ghost-two"></i>
                    <h2>Plan a Delivery</h2>
                    <article>
                        <span></span>
                        <p>Book a parcel, choose the delivery speed, add sender and receiver details, and let the system guide the shipment through each step.</p>
                        <a href="{{ route('register') }}">Book A Parcel</a>
                    </article>
                </div>

                <div class="courier-about-plan-image">
                    <img src="{{ $images['planning'] }}" alt="Courier parcels arranged for dispatch">
                </div>
            </div>
        </section>

        <section class="courier-about-contact-strip">
            <div class="courier-about-container">
                <div class="courier-about-contact-items">
                    @foreach ([
                        ['truck', 'Send Parcels', 'Doorstep pickup and delivery support for personal and business parcels.'],
                        ['message-circle', 'Get In Touch', 'Fast help for tracking questions, delivery changes, and service requests.'],
                    ] as $item)
                        <article>
                            <i data-lucide="{{ $item[0] }}"></i>
                            <h3>{{ $item[1] }}</h3>
                            <p>{{ $item[2] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
            <img class="courier-about-bottom-image" src="{{ $images['delivery'] }}" alt="Courier vehicle ready for delivery">
        </section>
    </div>
</x-layouts.public>
