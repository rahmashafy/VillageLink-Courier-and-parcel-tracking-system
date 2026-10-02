<x-layouts.public :title="__('vl.services')">
    @php
        $images = [
            'hero' => 'https://images.pexels.com/photos/6169177/pexels-photo-6169177.jpeg?auto=compress&cs=tinysrgb&w=1600',
            'agent' => 'https://images.pexels.com/photos/6169667/pexels-photo-6169667.jpeg?auto=compress&cs=tinysrgb&w=760',
            'parcel' => 'https://images.pexels.com/photos/6169132/pexels-photo-6169132.jpeg?auto=compress&cs=tinysrgb&w=520',
            'service' => 'https://images.pexels.com/photos/6169060/pexels-photo-6169060.jpeg?auto=compress&cs=tinysrgb&w=900',
            'driver' => 'https://images.pexels.com/photos/6699420/pexels-photo-6699420.jpeg?auto=compress&cs=tinysrgb&w=760',
            'project1' => 'https://images.pexels.com/photos/6407554/pexels-photo-6407554.jpeg?auto=compress&cs=tinysrgb&w=520',
            'project2' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=520&q=80',
            'project3' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=520&q=80',
        ];
    @endphp

    <div class="courier-services-page">
        <div class="courier-services-shell">
            <section class="courier-services-hero" style="background-image: linear-gradient(90deg, rgba(12, 23, 35, 0.96) 0%, rgba(12, 23, 35, 0.78) 42%, rgba(12, 23, 35, 0.12) 100%), url('{{ $images['hero'] }}');">
                <div class="courier-services-hero-copy">
                    <p>Welcome To Village Link</p>
                    <h1>Best Courier <span>Services Provider</span></h1>
                    <div class="courier-services-tags">
                        <span>Domestic</span>
                        <span>Commercial</span>
                        <span>Island Wide</span>
                    </div>
                    <a href="{{ route('register') }}">View Service Details</a>
                </div>
            </section>

            <section class="courier-services-feature-strip">
                @foreach ([
                    ['package-check', 'Large Number of Parcels Delivered', 'We manage daily courier movement with clear tracking and organized dispatch.'],
                    ['clock-3', 'Fast Pickup and Delivery Flow', 'Customers can choose standard, express, and priority delivery options.'],
                    ['users', 'A Large Number of Grateful Customers', 'Reliable updates help senders and receivers stay confident until delivery.'],
                ] as $item)
                    <article>
                        <span><i data-lucide="{{ $item[0] }}"></i></span>
                        <h3>{{ $item[1] }}</h3>
                        <p>{{ $item[2] }}</p>
                        <a href="{{ route('about') }}">More Info</a>
                    </article>
                @endforeach
            </section>

            <section class="courier-services-about">
                <div class="courier-services-collage">
                    <img class="courier-services-collage-main" src="{{ $images['agent'] }}" alt="Courier driver carrying parcels">
                    <img class="courier-services-collage-small" src="{{ $images['parcel'] }}" alt="Packed delivery boxes">
                </div>

                <article class="courier-services-about-copy">
                    <p>About Village Link</p>
                    <h2>We Deliver For Your Comfort</h2>
                    <p>We are a courier service built to make parcel booking, payment, tracking, and driver delivery updates feel simple from the first click to final handover.</p>
                    <p>Customers can create shipments, get calculated pricing, follow timeline updates, and reach support when something needs attention. Drivers and administrators get focused tools to keep daily operations moving.</p>
                    <a href="{{ route('about') }}">More About</a>
                    <div class="courier-services-badges">
                        @foreach ([
                            ['clock-3', 'On Time Service'],
                            ['truck', '24/7 Tracking'],
                            ['lock-keyhole', 'Secure Parcels'],
                        ] as $badge)
                            <span><i data-lucide="{{ $badge[0] }}"></i>{{ $badge[1] }}</span>
                        @endforeach
                    </div>
                </article>
            </section>

            <section class="courier-services-special">
                <article>
                    <p>Working With Reliable Delivery</p>
                    <h2>Our Special Services</h2>
                    <div class="courier-services-special-list">
                        @foreach ([
                            ['package-plus', 'Parcel Booking', 'Create a shipment with sender, receiver, parcel weight, and delivery speed details.'],
                            ['scan-search', 'Live Tracking', 'Track status history and delivery progress through the customer dashboard.'],
                        ] as $item)
                            <div>
                                <span><i data-lucide="{{ $item[0] }}"></i></span>
                                <div>
                                    <h3>{{ $item[1] }}</h3>
                                    <p>{{ $item[2] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
                <img src="{{ $images['service'] }}" alt="Courier warehouse service operations">
            </section>

            <section class="courier-services-proof">
                <img src="{{ $images['driver'] }}" alt="Courier delivery driver">
                <article>
                    <div class="courier-services-avatar">
                        <span>VL</span>
                    </div>
                    <h3>Village Link Team</h3>
                    <p>Reliable service, clear delivery updates, and friendly support for customers who need their parcels handled carefully.</p>
                    <div class="courier-services-stats">
                        @foreach ([
                            ['350+', 'Parcels Completed'],
                            ['120+', 'Active Customers'],
                            ['30+', 'Delivery Routes'],
                        ] as $stat)
                            <div>
                                <strong>{{ $stat[0] }}</strong>
                                <span>{{ $stat[1] }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>
            </section>

            <section class="courier-services-projects">
                <div class="courier-services-projects-head">
                    <div>
                        <p>Recently Completed</p>
                        <h2>Our Latest Deliveries</h2>
                    </div>
                    <div class="courier-services-filter">
                        <span>All</span>
                        <span>Express</span>
                        <span>Payment</span>
                        <span>Tracking</span>
                    </div>
                </div>
                <div class="courier-services-project-grid">
                    @foreach ([
                        [$images['project1'], 'Warehouse Dispatch'],
                        [$images['project2'], 'Secure Payment'],
                        [$images['project3'], 'Customer Support'],
                    ] as $project)
                        <figure>
                            <img src="{{ $project[0] }}" alt="{{ $project[1] }}">
                            <figcaption>{{ $project[1] }}</figcaption>
                        </figure>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-layouts.public>
