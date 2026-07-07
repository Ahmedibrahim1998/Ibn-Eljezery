<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">{{ trans('panel.supervisor.dash_title') }}</x-slot>
        <x-slot name="description">{{ trans('panel.supervisor.dash_sub') }}</x-slot>

        <x-slot name="headerEnd">
            <x-filament::button tag="a" size="sm" :href="\App\Filament\Supervisor\Resources\AttendanceResource::getUrl()" icon="heroicon-m-clipboard-document-check">
                {{ trans('panel.supervisor.take_attendance') }}
            </x-filament::button>
        </x-slot>

        {{-- Self-contained styles: Filament's precompiled CSS lacks many Tailwind
             utilities, so the dashboard layout/colors are defined here directly. --}}
        <style>
            .sv-tiles { display:grid; grid-template-columns:1fr; gap:1rem; margin-bottom:1.5rem; }
            .sv-cards { display:grid; grid-template-columns:1fr; gap:.75rem; }
            @media (min-width:640px){ .sv-tiles{grid-template-columns:repeat(3,1fr);} .sv-cards{grid-template-columns:repeat(2,1fr);} }
            @media (min-width:1280px){ .sv-cards{grid-template-columns:repeat(3,1fr);} }
            .sv-tile { display:flex; align-items:center; gap:1rem; border-radius:.85rem; padding:1rem 1.1rem; border:1px solid #e5e7eb; background:#fff; }
            .sv-tile .ic { width:2.75rem; height:2.75rem; border-radius:9999px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
            .sv-tile .ic svg { width:1.5rem; height:1.5rem; }
            .sv-tile .num { font-size:1.6rem; font-weight:800; line-height:1; }
            .sv-tile .lbl { font-size:.85rem; color:#6b7280; margin-top:.3rem; }
            .sv-course-head { display:flex; flex-wrap:wrap; align-items:center; gap:.5rem; margin-bottom:.85rem; padding-bottom:.55rem; border-bottom:1px solid #eef0f2; }
            .sv-course-head .title { font-size:1rem; font-weight:700; color:#111827; }
            .sv-course-head .count { margin-inline-start:auto; font-size:.85rem; color:#9ca3af; }
            .sv-course-head svg { width:1.25rem; height:1.25rem; color:#059669; }
            .sv-group { margin-bottom:1.5rem; }
            .sv-group:last-child { margin-bottom:0; }
            .sv-card { position:relative; overflow:hidden; border-radius:.85rem; border:1px solid #e5e7eb; background:#fff; padding:1rem 1rem 1rem 1.1rem; transition:box-shadow .15s ease, transform .15s ease; }
            .sv-card:hover { box-shadow:0 .5rem 1.5rem rgba(16,24,40,.09); transform:translateY(-2px); }
            .sv-accent { position:absolute; inset-block:0; inset-inline-start:0; width:.3rem; }
            .sv-row { display:flex; align-items:flex-start; gap:.75rem; }
            .sv-avatar { width:2.6rem; height:2.6rem; border-radius:9999px; color:#fff; font-weight:700; font-size:1.05rem; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
            .sv-name { font-weight:600; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
            .sv-phone { font-size:.85rem; color:#6b7280; }
            .sv-badges { margin-top:.85rem; display:flex; flex-wrap:wrap; align-items:center; gap:.4rem; }
            .sv-empty { border:1px dashed #d1d5db; border-radius:.85rem; padding:2rem; text-align:center; color:#6b7280; }
            .dark .sv-tile, .dark .sv-card { background:rgba(255,255,255,.04); border-color:rgba(255,255,255,.1); }
            .dark .sv-tile .lbl, .dark .sv-phone { color:#9ca3af; }
            .dark .sv-course-head { border-color:rgba(255,255,255,.1); }
            .dark .sv-course-head .title, .dark .sv-name { color:#f9fafb; }
            .dark .sv-empty { border-color:rgba(255,255,255,.15); color:#9ca3af; }
        </style>

        @php
            $stateColors = [
                'success' => ['fg' => '#059669', 'bg' => '#ecfdf5', 'accent' => '#10b981'],
                'info'    => ['fg' => '#0369a1', 'bg' => '#eff6ff', 'accent' => '#3b82f6'],
                'gray'    => ['fg' => '#6b7280', 'bg' => '#f9fafb', 'accent' => '#d1d5db'],
            ];
            $tiles = [
                ['icon' => 'heroicon-o-users',        'value' => $summary['total'],   'label' => trans('panel.supervisor.sum_total'),   'fg' => '#059669', 'bg' => '#ecfdf5'],
                ['icon' => 'heroicon-o-check-circle',  'value' => $summary['present'], 'label' => trans('panel.supervisor.sum_present'), 'fg' => '#0d9488', 'bg' => '#f0fdfa'],
                ['icon' => 'heroicon-o-banknotes',     'value' => $summary['due'],     'label' => trans('panel.supervisor.sum_due'),     'fg' => $summary['due'] > 0 ? '#dc2626' : '#9ca3af', 'bg' => $summary['due'] > 0 ? '#fef2f2' : '#f9fafb'],
            ];
            $avatarPalette = ['#059669', '#0d9488', '#7c3aed', '#0284c7', '#d97706', '#db2777'];
        @endphp

        {{-- Summary tiles --}}
        <div class="sv-tiles">
            @foreach ($tiles as $t)
                <div class="sv-tile">
                    <span class="ic" style="background: {{ $t['bg'] }}; color: {{ $t['fg'] }};">
                        <x-filament::icon :icon="$t['icon']" />
                    </span>
                    <div>
                        <div class="num" style="color: {{ $t['fg'] }};">{{ $t['value'] }}</div>
                        <div class="lbl">{{ $t['label'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        @forelse ($groups as $group)
            <div class="sv-group">
                <div class="sv-course-head">
                    <x-filament::icon icon="heroicon-s-book-open" />
                    <span class="title">{{ $group['title'] }}</span>
                    <x-filament::badge color="gray">{{ $group['type']->getLabel() }}</x-filament::badge>
                    <span class="count">{{ $group['students']->count() }}</span>
                </div>

                @if ($group['students']->isEmpty())
                    <div class="sv-empty">{{ trans('panel.supervisor.empty') }}</div>
                @else
                    <div class="sv-cards">
                        @foreach ($group['students'] as $i => $s)
                            @php($c = $stateColors[$s['color']] ?? $stateColors['gray'])
                            <div class="sv-card">
                                <span class="sv-accent" style="background: {{ $c['accent'] }};"></span>
                                <div class="sv-row">
                                    <div class="sv-avatar" style="background: {{ $avatarPalette[$i % count($avatarPalette)] }};">
                                        {{ mb_substr(trim($s['name']), 0, 1) }}
                                    </div>
                                    <div style="min-width:0; flex:1;">
                                        <div class="sv-name">{{ $s['name'] }}</div>
                                        <div class="sv-phone" dir="ltr">{{ $s['phone'] }}</div>
                                    </div>
                                    <x-filament::badge :color="$s['color']">
                                        {{ trans('panel.attendance.' . $s['state']) }}
                                    </x-filament::badge>
                                </div>
                                <div class="sv-badges">
                                    <x-filament::badge color="success" icon="heroicon-m-calendar-days">
                                        {{ trans('panel.attendance.attended_days') }}: {{ $s['attended'] }}
                                    </x-filament::badge>
                                    @if ($s['due'])
                                        <x-filament::badge color="danger" icon="heroicon-m-banknotes">
                                            {{ trans('panel.attendance.unpaid') }}
                                        </x-filament::badge>
                                    @else
                                        <x-filament::badge color="gray" icon="heroicon-m-check">
                                            {{ trans('panel.attendance.up_to_date') }}
                                        </x-filament::badge>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="sv-empty">{{ trans('panel.supervisor.empty') }}</div>
        @endforelse
    </x-filament::section>
</x-filament-widgets::widget>
