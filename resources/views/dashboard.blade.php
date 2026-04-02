<!DOCTYPE html>
<html class="dark" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Query Pulse Beta | Dashboard</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&amp;family=Inter:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface-bright": "#31394d",
                        "primary-fixed": "#d8e2ff",
                        "on-tertiary-fixed": "#410004",
                        "on-error-container": "#ffdad6",
                        "on-warning-container": "#363533",
                        "on-success-container": "#2b4030",
                        "tertiary-fixed": "#ffdad7",
                        "on-surface": "#dae2fd",
                        "secondary-fixed": "#6ffbbe",
                        "secondary-fixed-dim": "#4edea3",
                        "primary-container": "#4d8eff",
                        "on-primary-container": "#00285d",
                        "tertiary": "#ffb3ad",
                        "on-error": "#690005",
                        "background": "#0b1326",
                        "surface-container-high": "#222a3d",
                        "on-primary-fixed": "#001a42",
                        "inverse-surface": "#dae2fd",
                        "primary": "#adc6ff",
                        "on-secondary-fixed-variant": "#005236",
                        "tertiary-fixed-dim": "#ffb3ad",
                        "secondary-container": "#00a572",
                        "surface-variant": "#2d3449",
                        "surface-container-lowest": "#060e20",
                        "error-container": "#93000a",
                        "warning-container": "#fff3cd",
                        "success-container": "#d4edda",
                        "surface-container-highest": "#2d3449",
                        "on-secondary-fixed": "#002113",
                        "surface-tint": "#adc6ff",
                        "inverse-on-surface": "#283044",
                        "on-tertiary": "#68000a",
                        "on-secondary": "#003824",
                        "on-tertiary-container": "#5c0008",
                        "on-background": "#dae2fd",
                        "surface-dim": "#0b1326",
                        "surface-container": "#171f33",
                        "on-primary-fixed-variant": "#004395",
                        "on-surface-variant": "#c2c6d6",
                        "tertiary-container": "#ff5451",
                        "on-primary": "#002e6a",
                        "primary-fixed-dim": "#adc6ff",
                        "inverse-primary": "#005ac2",
                        "surface-container-low": "#131b2e",
                        "secondary": "#4edea3",
                        "outline-variant": "#424754",
                        "outline": "#8c909f",
                        "on-tertiary-fixed-variant": "#930013",
                        "surface": "#0b1326",
                        "on-secondary-container": "#00311f",
                        "error": "#ffb4ab"
                    },
                    fontFamily: {
                        "headline": ["Space Grotesk"],
                        "body": ["Inter"],
                        "label": ["Inter"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #060e20;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #2d3449;
        }

        .glass-panel {
            background: rgba(49, 57, 77, 0.6);
            backdrop-filter: blur(12px);
        }

        .mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }
    </style>
</head>

<body class="bg-background text-on-surface font-body selection:bg-primary selection:text-on-primary">
    <main class="min-h-screen flex flex-col">
        <header class="bg-[#0b1326] flex justify-between items-center w-full px-8 h-16 border-b border-[#424754]/15 sticky top-0 z-40">
            <div class="flex items-center space-x-8">
                <div class="text-2xl font-bold tracking-tighter text-[#adc6ff] font-['Space_Grotesk']">
                    <a href="{{ route('query-pulse.index') }}">
                        Laravel Query Pulse <sub class="text-xs text-on-surface-variant">Beta</sub>
                    </a>
                </div>
            </div>
            <div class="flex items-center space-x-4">
            </div>
        </header>

        <div class="mx-auto w-full px-8 py-8 space-y-6">
            <section class="bg-surface-container-low overflow-hidden">
                <div class="p-6 border-b border-outline-variant/15 flex justify-between items-center flex-wrap gap-4">
                    <div>
                        <h2 class="font-headline font-bold text-lg tracking-tight">Endpoints</h2>
                        <div class="text-xs text-on-surface-variant mt-1">
                            Total endpoints: <span class="mono text-primary-fixed">{{ isset($endpoints) ? $endpoints->count() : 0 }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            class="bg-surface-container-highest border border-outline-variant/20 text-on-surface text-sm px-4 py-2 w-[320px] max-w-full focus:outline-none focus:ring-1 focus:ring-primary/60"
                            type="search"
                            placeholder="Search endpoint..."
                            data-dashboard-search />
                        <button class="px-4 py-2 bg-surface-container-high text-xs font-label uppercase tracking-widest hover:text-primary transition-colors" type="button" data-dashboard-reset>Reset</button>
                    </div>
                </div>

                @if(empty($endpoints) || $endpoints->count() === 0)
                <div class="p-6 text-sm text-on-surface-variant">
                    Belum ada data. Pastikan middleware Query Pulse aktif dan ada request yang masuk.
                </div>
                @else
                <div class="p-6 text-xs text-on-surface-variant">
                    Showing <span class="mono text-primary-fixed" data-dashboard-count>0</span> of <span class="mono text-primary-fixed">{{ $endpoints->count() }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" data-dashboard-table>
                        <thead class="bg-surface-container-lowest text-[10px] font-label uppercase tracking-widest text-outline">
                            <tr>
                                <th class="px-6 py-4 font-medium cursor-pointer select-none hover:text-primary transition-colors" data-sort-key="url">
                                    Endpoint <span class="ml-1 opacity-60" data-sort-indicator>↕</span>
                                </th>
                                <th class="px-6 py-4 font-medium cursor-pointer select-none hover:text-primary transition-colors" data-sort-key="status">
                                    Status <span class="ml-1 opacity-60" data-sort-indicator>↕</span>
                                </th>
                                <th class="px-6 py-4 font-medium text-right cursor-pointer select-none hover:text-primary transition-colors" data-sort-key="avg">
                                    Avg Total Time <span class="ml-1 opacity-60" data-sort-indicator>↕</span>
                                </th>
                                <th class="px-6 py-4 font-medium text-right cursor-pointer select-none hover:text-primary transition-colors" data-sort-key="latest">
                                    Latest Total Time <span class="ml-1 opacity-60" data-sort-indicator>↕</span>
                                </th>
                                <th class="px-6 py-4 font-medium text-right cursor-pointer select-none hover:text-primary transition-colors" data-sort-key="last">
                                    Last Seen <span class="ml-1 opacity-60" data-sort-indicator>↕</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-sm" data-dashboard-tbody>
                            @foreach($endpoints as $endpoint)
                            <tr
                                class="hover:bg-surface-container-high transition-colors group"
                                data-row
                                data-url="{{ $endpoint['url'] }}"
                                data-status="{{ $endpoint['status'] ?? '' }}"
                                data-avg="{{ $endpoint['avg_total_query_time'] }}"
                                data-latest="{{ $endpoint['latest_total_query_time'] }}"
                                data-last="{{ $endpoint['last_seen_at'] ?? '' }}">
                                <td class="px-6 py-4 border-b border-outline-variant/5">
                                    <a class="inline-flex items-center gap-2 group-hover:text-primary transition-colors" href="{{ route('query-pulse.report', ['reportId' => $endpoint['report_id']]) }}">
                                        <span class="material-symbols-outlined text-sm opacity-70" aria-hidden="true">arrow_forward</span>
                                        <span class="mono text-on-surface-variant">{{ $endpoint['url'] }}</span>
                                    </a>
                                </td>
                                <td class="px-6 py-4 border-b border-outline-variant/5">
                                    @php($status = $endpoint['status'] ?? '')
                                    @if($status === 'CRITICAL')
                                    <div class="bg-error-container text-on-error-container px-3 py-1 rounded-sm inline-flex items-center space-x-2">
                                        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">warning</span>
                                        <span class="text-[10px] font-bold font-label tracking-widest">CRITICAL</span>
                                    </div>
                                    @elseif($status === 'POOR')
                                    <div class="bg-warning-container text-on-warning-container px-3 py-1 rounded-sm inline-flex items-center space-x-2">
                                        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">warning</span>
                                        <span class="text-[10px] font-bold font-label tracking-widest">POOR</span>
                                    </div>
                                    @elseif($status === 'WATCH')
                                    <div class="bg-warning-container text-on-warning-container px-3 py-1 rounded-sm inline-flex items-center space-x-2">
                                        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">warning</span>
                                        <span class="text-[10px] font-bold font-label tracking-widest">WATCH</span>
                                    </div>
                                    @elseif($status !== '')
                                    <div class="bg-success-container text-on-success-container px-3 py-1 rounded-sm inline-flex items-center space-x-2">
                                        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                        <span class="text-[10px] font-bold font-label tracking-widest">{{ $status }}</span>
                                    </div>
                                    @else
                                    <span class="text-xs text-on-surface-variant">-</span>
                                    @endif
                                </td>
                                <td class="
                                
                                @if($endpoint['avg_total_query_time'] > \Alfinprdht\QueryPulse\Support\Thresholds::getSlowQueryTime())
                                px-6 py-4 border-b border-outline-variant/5 text-right font-headline font-bold text-error
                                @else 
                                px-6 py-4 border-b border-outline-variant/5 text-right mono text-xs text-on-surface-variant
                                @endif
                                
                                ">{{ number_format($endpoint['avg_total_query_time'], 2) }}ms</td>
                                <td class="px-6 py-4 border-b border-outline-variant/5 text-right mono text-xs text-on-surface-variant">{{ number_format($endpoint['latest_total_query_time'], 2) }}ms</td>
                                <td class="px-6 py-4 border-b border-outline-variant/5 text-right text-xs text-on-surface-variant">{{ $endpoint['last_seen_at'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-6 text-sm text-on-surface-variant hidden" data-dashboard-empty>
                    Tidak ada endpoint yang cocok.
                </div>
                @endif
            </section>
        </div>

        <footer class="mt-auto px-8 py-6 border-t border-outline-variant/10 flex justify-between items-center bg-surface-container-lowest">
            <div class="text-[10px] font-label text-on-surface-variant uppercase tracking-widest">
                Query Pulse Dashboard
            </div>
            <div class="text-[10px] font-label text-outline uppercase tracking-[0.3em]">
                Secure Protocol: TLS 1.3 / AES-256
            </div>
        </footer>
    </main>

    <script>
        (function() {
            var table = document.querySelector('[data-dashboard-table]');
            if (!table) return;

            var tbody = table.querySelector('[data-dashboard-tbody]');
            var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr[data-row]'));
            var search = document.querySelector('[data-dashboard-search]');
            var reset = document.querySelector('[data-dashboard-reset]');
            var countEl = document.querySelector('[data-dashboard-count]');
            var emptyEl = document.querySelector('[data-dashboard-empty]');
            var thead = table.querySelector('thead');

            var sortState = {
                key: null,
                dir: 'asc'
            };

            function setCount(n) {
                if (countEl) countEl.textContent = String(n);
            }

            function applyFilter() {
                var q = (search && search.value ? search.value : '').trim().toLowerCase();
                var visible = 0;

                rows.forEach(function(row) {
                    var url = (row.getAttribute('data-url') || '').toLowerCase();
                    var ok = q === '' || url.indexOf(q) !== -1;
                    row.classList.toggle('hidden', !ok);
                    if (ok) visible++;
                });

                setCount(visible);
                if (emptyEl) emptyEl.classList.toggle('hidden', visible !== 0);
            }

            function getSortVal(row, key) {
                if (key === 'url') return row.getAttribute('data-url') || '';
                if (key === 'status') return row.getAttribute('data-status') || '';
                if (key === 'snapshots') return parseFloat(row.getAttribute('data-snapshots') || '0') || 0;
                if (key === 'avg') return parseFloat(row.getAttribute('data-avg') || '0') || 0;
                if (key === 'latest') return parseFloat(row.getAttribute('data-latest') || '0') || 0;
                if (key === 'last') return row.getAttribute('data-last') || '';
                return '';
            }

            function sortRows(key, dir) {
                var visibleRows = rows.filter(function(r) {
                    return !r.classList.contains('hidden');
                });
                var hiddenRows = rows.filter(function(r) {
                    return r.classList.contains('hidden');
                });

                function cmp(a, b) {
                    var av = getSortVal(a, key);
                    var bv = getSortVal(b, key);
                    var c = 0;
                    if (typeof av === 'number' && typeof bv === 'number') c = av - bv;
                    else c = String(av).localeCompare(String(bv), undefined, {
                        numeric: true,
                        sensitivity: 'base'
                    });
                    return dir === 'desc' ? -c : c;
                }

                visibleRows.sort(cmp);
                hiddenRows.sort(cmp);

                visibleRows.concat(hiddenRows).forEach(function(r) {
                    tbody.appendChild(r);
                });
            }

            function resetIndicators() {
                Array.prototype.forEach.call(thead.querySelectorAll('th[data-sort-key]'), function(th) {
                    var ind = th.querySelector('[data-sort-indicator]');
                    if (ind) ind.textContent = '↕';
                });
            }

            if (search) {
                search.addEventListener('input', function() {
                    applyFilter();
                    if (sortState.key) sortRows(sortState.key, sortState.dir);
                });
            }

            if (reset) {
                reset.addEventListener('click', function() {
                    if (search) search.value = '';
                    applyFilter();
                    sortState.key = null;
                    sortState.dir = 'asc';
                    resetIndicators();
                });
            }

            if (thead) {
                thead.addEventListener('click', function(e) {
                    var th = e.target && e.target.closest ? e.target.closest('th[data-sort-key]') : null;
                    if (!th) return;
                    var key = th.getAttribute('data-sort-key');
                    if (!key) return;

                    if (sortState.key === key) sortState.dir = sortState.dir === 'asc' ? 'desc' : 'asc';
                    else {
                        sortState.key = key;
                        sortState.dir = 'asc';
                    }

                    resetIndicators();
                    var ind = th.querySelector('[data-sort-indicator]');
                    if (ind) ind.textContent = sortState.dir === 'asc' ? '↑' : '↓';

                    applyFilter();
                    sortRows(sortState.key, sortState.dir);
                });
            }

            // initial state
            applyFilter();
            setCount(rows.length);
        })();
    </script>
</body>

</html>