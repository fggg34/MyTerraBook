<x-filament-panels::page>
    <style>
        .admin-calendar-embed {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .admin-calendar-embed__toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .admin-calendar-embed__note {
            margin: 0;
            font-size: 0.875rem;
            color: #64748b;
        }

        .admin-calendar-embed__frame {
            width: 100%;
            min-height: 720px;
            border: 1px solid #d9dee7;
            border-radius: 0.75rem;
            background: #fff;
        }

        @media (max-width: 960px) {
            .admin-calendar-embed__frame {
                min-height: 560px;
            }
        }
    </style>

    <div class="admin-calendar-embed">
        <div class="admin-calendar-embed__toolbar">
            <p class="admin-calendar-embed__note">
                Unified bookings calendar across all guesthouses and vehicles.
            </p>
            <a
                href="{{ $this->calendarEmbedUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="fi-btn fi-btn-size-sm fi-color-gray"
            >
                Open full screen
            </a>
        </div>

        <iframe
            class="admin-calendar-embed__frame"
            src="{{ $this->calendarEmbedUrl }}"
            title="Reservations calendar"
            loading="lazy"
        ></iframe>
    </div>
</x-filament-panels::page>
