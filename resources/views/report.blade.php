<!DOCTYPE html>

<html class="dark" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Query Pulse Beta | Query Pulse Inspector</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&amp;family=Inter:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet" />
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

    <aside class="fixed left-0 top-0 h-screen w-64 bg-[#060e20] border-r border-[#424754]/15 flex flex-col z-50" style="display: none;">

        <div class="px-6 py-8">
            <div class="text-[#adc6ff] font-black tracking-tighter text-xl font-headline">QUERY_ENGINE</div>
            <div class="text-[#2d3449] text-[10px] uppercase tracking-widest font-label mt-1">Node: 0x4F2A</div>
        </div>

        <nav class="flex-1 px-3 space-y-1">
            <div class="bg-[#131b2e] text-[#adc6ff] border-l-4 border-[#adc6ff] flex items-center px-3 py-3 transition-all duration-200 cursor-pointer group">
                <span class="material-symbols-outlined mr-3 text-sm" data-icon="speed">speed</span>
                <span class="font-['Inter'] text-xs uppercase tracking-widest">Real-time</span>
            </div>
            <div class="text-[#2d3449] hover:bg-[#131b2e]/50 hover:text-[#adc6ff] flex items-center px-3 py-3 transition-all duration-200 cursor-pointer group">
                <span class="material-symbols-outlined mr-3 text-sm" data-icon="history">history</span>
                <span class="font-['Inter'] text-xs uppercase tracking-widest">Historical</span>
            </div>
            <div class="text-[#2d3449] hover:bg-[#131b2e]/50 hover:text-[#adc6ff] flex items-center px-3 py-3 transition-all duration-200 cursor-pointer group">
                <span class="material-symbols-outlined mr-3 text-sm" data-icon="Timeline">timeline</span>
                <span class="font-['Inter'] text-xs uppercase tracking-widest">Drift</span>
            </div>
            <div class="text-[#2d3449] hover:bg-[#131b2e]/50 hover:text-[#adc6ff] flex items-center px-3 py-3 transition-all duration-200 cursor-pointer group">
                <span class="material-symbols-outlined mr-3 text-sm" data-icon="table_chart">table_chart</span>
                <span class="font-['Inter'] text-xs uppercase tracking-widest">Schema</span>
            </div>
            <div class="text-[#2d3449] hover:bg-[#131b2e]/50 hover:text-[#adc6ff] flex items-center px-3 py-3 transition-all duration-200 cursor-pointer group">
                <span class="material-symbols-outlined mr-3 text-sm" data-icon="verified_user">verified_user</span>
                <span class="font-['Inter'] text-xs uppercase tracking-widest">Security</span>
            </div>
        </nav>

        <div class="mt-auto p-4 space-y-4">
            <button class="w-full py-3 bg-surface-container-highest text-primary font-label text-[10px] uppercase tracking-widest hover:bg-surface-bright transition-all active:translate-x-1">
                OPTIMIZE_ENGINE
            </button>
            <div class="space-y-1">
                <div class="text-[#2d3449] hover:text-[#adc6ff] flex items-center px-2 py-2 text-[10px] uppercase tracking-widest font-label cursor-pointer transition-colors">
                    <span class="material-symbols-outlined mr-2 text-xs" data-icon="description">description</span> Documentation
                </div>
                <div class="text-[#2d3449] hover:text-[#adc6ff] flex items-center px-2 py-2 text-[10px] uppercase tracking-widest font-label cursor-pointer transition-colors">
                    <span class="material-symbols-outlined mr-2 text-xs" data-icon="help_outline">help_outline</span> Support
                </div>
            </div>
        </div>
    </aside>

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

        <div class="p-8 space-y-8 max-w-[1600px]">

            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2 bg-surface-container-low p-8 relative overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-12">
                            <div>
                                <h2 class="text-on-surface-variant text-[10px] font-label uppercase tracking-[0.2em] mb-2">Endpoint Diagnostic</h2>
                                <h3 class="text-4xl font-headline font-bold tracking-tighter text-on-surface">{{ $fullUrl }}</h3>
                                <h6 class="font-headline">Controller : </h6>
                            </div>
                            <div class="flex items-center space-x-4">
                                @if($result['status'] == 'CRITICAL')
                                <div class="bg-error-container text-on-error-container px-4 py-2 rounded-sm flex items-center space-x-2">
                                    <span class="material-symbols-outlined text-sm animate-pulse" data-icon="warning" data-weight="fill" style="font-variation-settings: 'FILL' 1;">warning</span>
                                    <span class="text-xs font-bold font-label tracking-widest">CRITICAL</span>
                                </div>
                                @elseif($result['status'] == 'POOR')
                                <div class="bg-warning-container text-on-warning-container px-4 py-2 rounded-sm flex items-center space-x-2">
                                    <span class="material-symbols-outlined text-sm animate-pulse" data-icon="warning" data-weight="fill" style="font-variation-settings: 'FILL' 1;">warning</span>
                                    <span class="text-xs font-bold font-label tracking-widest">POOR</span>
                                </div>
                                @elseif($result['status'] == 'WATCH')
                                <div class="bg-warning-container text-on-warning-container px-4 py-2 rounded-sm flex items-center space-x-2">
                                    <span class="material-symbols-outlined text-sm animate-pulse" data-icon="warning" data-weight="fill" style="font-variation-settings: 'FILL' 1;">warning</span>
                                    <span class="text-xs font-bold font-label tracking-widest">WATCH</span>
                                </div>
                                @else
                                <div class="bg-success-container text-on-success-container px-4 py-2 rounded-sm flex items-center space-x-2">
                                    <span class="material-symbols-outlined text-sm animate-pulse" data-icon="check_circle" data-weight="fill" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                    <span class="text-xs font-bold font-label tracking-widest">HEALTHY</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-12">
                            <div>
                                <p class="text-on-surface-variant text-[10px] font-label uppercase tracking-widest mb-4">Health Index</p>
                                <div class="flex items-baseline space-x-2">
                                    @if($result['status'] == 'CRITICAL')
                                    <span class="text-6xl font-headline font-bold text-error">{{ $result['score'] }}</span>
                                    @elseif($result['status'] == 'POOR')
                                    <span class="text-6xl font-headline font-bold text-warning">{{ $result['score'] }}</span>
                                    @elseif($result['status'] == 'WATCH')
                                    <span class="text-6xl font-headline font-bold text-warning">{{ $result['score'] }}</span>
                                    @else
                                    <span class="text-6xl font-headline font-bold text-success">{{ $result['score'] }}</span>
                                    @endif
                                    <span class="text-xl text-outline-variant">/ 100</span>
                                </div>
                                <div class="mt-4 w-full h-1 bg-surface-container-highest">
                                    <div class="bg-error h-full" style="width: {{$result['score']}}%;"></div>
                                </div>
                            </div>
                            <div>
                                <p class="text-on-surface-variant text-[10px] font-label uppercase tracking-widest mb-4">Avg Query Time</p>
                                <div class="flex items-baseline space-x-2">
                                    <span class="text-6xl font-headline font-bold text-primary">{{ $averageQueryTime }}</span>
                                    <span class="text-xl text-outline-variant mono">ms</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 h-24 w-full opacity-30" style="display: none;">
                        <div class="flex items-end h-full space-x-1">
                            <div class="bg-primary-container flex-1" style="height: 40%"></div>
                            <div class="bg-primary-container flex-1" style="height: 60%"></div>
                            <div class="bg-primary-container flex-1" style="height: 35%"></div>
                            <div class="bg-primary-container flex-1" style="height: 85%"></div>
                            <div class="bg-error flex-1" style="height: 100%"></div>
                            <div class="bg-error flex-1" style="height: 90%"></div>
                            <div class="bg-primary-container flex-1" style="height: 55%"></div>
                        </div>
                    </div>

                    <div class="mt-8 h-24 w-full opacity-30">
                        <div class="flex items-end h-full space-x-1">
                            @foreach($transformedQueryPulse as $query)
                            <div class="{{ $query['cross_threshold'] ? 'bg-error' : 'bg-primary-container' }} flex-1" style="height: {{ $query['percentage'] }}%">
                                <div class="text-white text-xs font-label tracking-widest mt-[-18px]">{{ $query['total_query_time'] }}ms</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-2">

                    <div class="bg-surface-container-lowest p-6 flex flex-col justify-between group hover:bg-surface-container-low transition-all">
                        <span class="text-outline text-[10px] font-label uppercase tracking-widest mb-1">Total Queries</span>
                        <div class="flex items-baseline space-x-3">
                            <span class="text-3xl font-headline font-bold text-on-surface">{{ $result['totalQueryCount'] }}</span>
                            <span class="material-symbols-outlined text-primary text-xl" data-icon="database">database</span>
                        </div>
                    </div>
                    <div class="bg-surface-container-lowest p-6 flex flex-col justify-between group hover:bg-surface-container-low transition-all">
                        <span class="text-outline text-[10px] font-label uppercase tracking-widest mb-1">Total Query Time</span>
                        <div class="flex items-baseline space-x-3">
                            <span class="text-3xl font-headline font-bold text-on-surface">{{ $result['totalQueryTime'] }}</span>
                            <span class="text-sm font-label text-outline mono">ms</span>
                        </div>
                    </div>
                    <div class="bg-surface-container-lowest p-6 flex flex-col justify-between group hover:bg-surface-container-low transition-all border-b-2 border-tertiary/20">
                        <div class="flex justify-between items-start">
                            <span class="text-outline text-[10px] font-label uppercase tracking-widest mb-1">Wildcard Fetches</span>
                            @if($result['suspiciousWildcardFetch'] > 0)
                            <span class="material-symbols-outlined text-tertiary animate-pulse" data-icon="warning">warning</span>
                            @else
                            <span class="material-symbols-outlined text-tertiary animate-pulse" data-icon="check_circle">check_circle</span>
                            @endif
                        </div>
                        <div class="flex items-baseline space-x-3">
                            <span class="text-3xl font-headline font-bold text-tertiary">{{ $result['suspiciousWildcardFetch'] }}</span>
                            @if($result['suspiciousWildcardFetch'] > 0)
                            <span class="text-sm font-label text-tertiary/60 uppercase tracking-tighter">Inefficient</span>
                            @endif
                        </div>
                    </div>
                </div>

            </section>
            <!-- Metrics Grid -->

            <section>

                <div class="bg-surface-container p-6 space-y-6">
                    <h2 class="text-on-surface-variant text-[15px] font-label uppercase tracking-[0.2em] flex items-center">
                        <span class="material-symbols-outlined text-sm mr-2" data-icon="bug_report">bug_report</span>
                        Detected Anomalies
                    </h2>
                    <div class="space-y-4">

                        @foreach($result['anomalies'] as $issue)
                        <div class="p-4 bg-surface-container-lowest border-l-2 border-error/50 group hover:bg-surface-container-high transition-colors cursor-pointer">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-md font-bold font-headline text-on-surface">{{ $issue['title'] }}</span>
                                <span class="bg-error/10 text-error px-2 py-0.5 text-md font-black uppercase">{{ $issue['count'] }} DETECTED</span>
                            </div>
                            @if($issue['type'] == 'slow_query')

                            <pre style="max-height: 210px; overflow-y: auto;" class="mt-2 p-3 bg-surface-container-highest border border-outline-variant/20 overflow-x-auto text-[12px] leading-relaxed font-mono text-on-surface">
<code class="whitespace-pre">{{ $issue['description'] }}</code>
</pre>
                            @else
                            <p class="text-sm text-on-surface-variant font-label leading-relaxed">{{ $issue['description'] }}</p>
                            @endif
                        </div>
                        @endforeach

                    </div>
                </div>
            </section>

            <!-- Queries Collection Table -->
            <section class="bg-surface-container-low overflow-hidden">
                <div class="p-6 border-b border-outline-variant/15 flex justify-between items-center">
                    <h2 class="font-headline font-bold text-lg tracking-tight">Classification</h2>
                    <div class="flex space-x-2" style="display: none;">
                        <button class="px-4 py-2 bg-surface-container-high text-xs font-label uppercase tracking-widest hover:text-primary transition-colors">Export .LOG</button>
                        <button class="px-4 py-2 bg-primary text-on-primary text-xs font-bold font-label uppercase tracking-widest rounded-sm">Snapshot Trace</button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" data-sort-table="classification">
                        <thead class="bg-surface-container-lowest text-[10px] font-label uppercase tracking-widest text-outline">
                            <tr>
                                <th class="w-12 px-3 py-4 font-medium" aria-hidden="true"></th>
                                <th class="px-6 py-4 font-medium cursor-pointer select-none hover:text-primary transition-colors" data-sort-key="fingerprint">
                                    Query Pattern <span class="ml-1 opacity-60" data-sort-indicator>↕</span>
                                </th>
                                <th class="px-6 py-4 font-medium cursor-pointer select-none hover:text-primary transition-colors" data-sort-key="type">
                                    Classification <span class="ml-1 opacity-60" data-sort-indicator>↕</span>
                                </th>
                                <th class="px-6 py-4 font-medium cursor-pointer select-none hover:text-primary transition-colors" data-sort-key="count">
                                    Count <span class="ml-1 opacity-60" data-sort-indicator>↕</span>
                                </th>
                                <th class="px-6 py-4 font-medium text-right cursor-pointer select-none hover:text-primary transition-colors" data-sort-key="time">
                                    Total Latency <span class="ml-1 opacity-60" data-sort-indicator>↕</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @if(!empty($result['issues']))
                            @foreach($result['issues'] as $issue)
                            <tr class="hover:bg-surface-container-high transition-colors group cursor-pointer" data-issue-toggle role="button" tabindex="0" aria-expanded="false">
                                <td class="w-12 px-3 py-4 border-b border-outline-variant/5 align-middle text-center">
                                    <span class="material-symbols-outlined text-lg text-on-surface-variant inline-block transition-transform duration-200" data-issue-chevron aria-hidden="true">chevron_right</span>
                                </td>
                                <td class="px-6 py-4 border-b border-outline-variant/5">
                                    <div class="mono text-on-surface-variant truncate max-w-md">{{ $issue['fingerprint'] }}</div>
                                </td>
                                <td class="px-6 py-4 border-b border-outline-variant/5">
                                    <div class="flex space-x-2">
                                        @foreach($issue['type'] as $type)
                                            @if($type == 'slow_query')
                                            <span class="px-2 py-0.5 bg-warning-container/20 text-[9px] font-bold tracking-widest text-warning uppercase">SLOW QUERY</span>
                                            @elseif($type == 'duplicate_burst')
                                            <span class="px-2 py-0.5 bg-warning-container/20 text-[9px] font-bold tracking-widest text-warning uppercase">DUPLICATE BURST</span>
                                            @elseif($type == 'probable_n_plus_1')
                                            <span class="px-2 py-0.5 bg-warning-container/20 text-[9px] font-bold tracking-widest text-warning uppercase">PROBABLE N+1</span>
                                            @else
                                            <span class="px-2 py-0.5 bg-surface-container-highest text-[9px] font-bold tracking-widest text-on-surface-variant uppercase">SUSPICIOUS WILDCARD FETCH</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 border-b border-outline-variant/5 mono text-xs">{{ $issue['count'] }}</td>
                                <td class="
                                @if($issue['time'] > \Alfinprdht\QueryPulse\Support\Thresholds::getSlowQueryTime())
                                px-6 py-4 border-b border-outline-variant/5 text-right font-headline font-bold text-error
                                @else 
                                px-6 py-4 border-b border-outline-variant/5 text-right font-headline font-bold text-on-surface
                                @endif
                                ">
                                    {{ $issue['time'] }}ms
                                </td>
                            </tr>
                            <tr class="hidden bg-surface-container-lowest/40" data-issue-detail aria-hidden="true">
                                <td colspan="5" class="px-6 py-4 border-b border-outline-variant/10">
                                    <div class="rounded-lg border border-outline-variant/15 bg-surface-container p-4 space-y-4 text-sm">
                                        <div>
                                            <div class="text-[10px] font-label uppercase tracking-widest text-outline mb-2">Trace</div>
                                            @if(!empty($issue['trace']))
                                            <code>{{ $issue['trace'] }}</code>
                                            @else
                                            <code>- No trace available -</code>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-[10px] font-label uppercase tracking-widest text-outline mb-2">Query pattern (full)</div>
                                            <pre class="p-3 rounded-md bg-surface-container-highest border border-outline-variant/10 overflow-x-auto text-xs font-mono text-on-surface whitespace-pre-wrap break-words"><code>{{ $issue['fingerprint'] }}</code></pre>
                                        </div>
                                        @if(!empty($issue['suggestion']))
                                        <div>
                                            <div class="text-[10px] font-label uppercase tracking-widest text-outline mb-2">Suggestion</div>
                                            @foreach($issue['suggestion'] as $suggestion)
                                            <p class="text-on-surface-variant leading-relaxed">{{ $suggestion }}</p>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        <footer class="mt-auto px-8 py-6 border-t border-outline-variant/10 flex justify-between items-center bg-surface-container-lowest">
            <div class="flex items-center space-x-6">
                <div class="flex items-center space-x-2">
                    <div class="w-1.5 h-1.5 rounded-full bg-secondary animate-pulse"></div>
                    <span class="text-[10px] font-label text-on-surface-variant uppercase tracking-widest">Cluster: AWS-US-EAST-1</span>
                </div>
                <div class="h-4 w-px bg-outline-variant/30"></div>
                <span class="text-[10px] font-label text-on-surface-variant uppercase tracking-widest">Last Sync: {{ $lastSync }}</span>
            </div>
            <div class="text-[10px] font-label text-outline uppercase tracking-[0.3em]">
                
            </div>
        </footer>
    </main>

    <div class="fixed bottom-8 right-8 z-50">
        <button class="group relative flex items-center justify-center w-14 h-14 bg-primary text-on-primary rounded-full shadow-2xl hover:scale-105 active:scale-95 transition-all">
            <span class="material-symbols-outlined" data-icon="terminal">terminal</span>
            <div class="absolute right-16 px-4 py-2 bg-surface-bright text-on-surface text-[10px] font-bold uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap">
                RUN_DEBUG_TRACE
            </div>
        </button>
    </div>
    <script>
        (function() {
            function parseNumber(text) {
                var n = parseFloat(String(text || '').replace(/[^\d.\-]/g, ''));
                return isNaN(n) ? 0 : n;
            }

            function getRowValue(row, key) {
                var cells = row.querySelectorAll('td');
                if (!cells || cells.length < 5) return '';
                if (key === 'fingerprint') return (cells[1].innerText || '').trim();
                if (key === 'type') return (cells[2].innerText || '').trim();
                if (key === 'count') return parseNumber((cells[3].innerText || '').trim());
                if (key === 'time') return parseNumber((cells[4].innerText || '').trim());
                return '';
            }

            function sortIssueTable(table, key, direction) {
                var tbody = table.querySelector('tbody');
                if (!tbody) return;

                var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr[data-issue-toggle]'));
                var pairs = rows.map(function(row, idx) {
                    var detail = row.nextElementSibling && row.nextElementSibling.matches('[data-issue-detail]') ?
                        row.nextElementSibling :
                        null;
                    return {
                        row: row,
                        detail: detail,
                        idx: idx,
                        value: getRowValue(row, key)
                    };
                });

                pairs.sort(function(a, b) {
                    var av = a.value;
                    var bv = b.value;
                    var cmp = 0;

                    if (typeof av === 'number' && typeof bv === 'number') {
                        cmp = av - bv;
                    } else {
                        cmp = String(av).localeCompare(String(bv), undefined, {
                            numeric: true,
                            sensitivity: 'base'
                        });
                    }

                    if (cmp === 0) cmp = a.idx - b.idx; // stable
                    return direction === 'desc' ? -cmp : cmp;
                });

                pairs.forEach(function(p) {
                    tbody.appendChild(p.row);
                    if (p.detail) tbody.appendChild(p.detail);
                });
            }

            function toggleIssueRow(toggleRow) {
                var detail = toggleRow.nextElementSibling;
                if (!detail || !detail.matches || !detail.matches('[data-issue-detail]')) return;
                var open = detail.classList.toggle('hidden') === false;
                toggleRow.setAttribute('aria-expanded', open ? 'true' : 'false');
                detail.setAttribute('aria-hidden', open ? 'false' : 'true');
                var chevron = toggleRow.querySelector('[data-issue-chevron]');
                if (chevron) {
                    if (open) {
                        chevron.classList.add('rotate-90');
                    } else {
                        chevron.classList.remove('rotate-90');
                    }
                }
            }

            (function initSorting() {
                var table = document.querySelector('table[data-sort-table="classification"]');
                if (!table) return;

                var thead = table.querySelector('thead');
                if (!thead) return;

                var state = {
                    key: null,
                    dir: 'asc'
                };

                thead.addEventListener('click', function(e) {
                    var th = e.target && e.target.closest ? e.target.closest('th[data-sort-key]') : null;
                    if (!th) return;

                    var key = th.getAttribute('data-sort-key');
                    if (!key) return;

                    if (state.key === key) {
                        state.dir = state.dir === 'asc' ? 'desc' : 'asc';
                    } else {
                        state.key = key;
                        state.dir = 'asc';
                    }

                    // reset indicators
                    Array.prototype.forEach.call(thead.querySelectorAll('th[data-sort-key]'), function(el) {
                        var ind = el.querySelector('[data-sort-indicator]');
                        if (ind) ind.textContent = '↕';
                    });

                    var indicator = th.querySelector('[data-sort-indicator]');
                    if (indicator) indicator.textContent = state.dir === 'asc' ? '↑' : '↓';

                    sortIssueTable(table, state.key, state.dir);
                });
            })();

            document.addEventListener('click', function(e) {
                var row = e.target && e.target.closest ? e.target.closest('[data-issue-toggle]') : null;
                if (!row) return;
                e.preventDefault();
                toggleIssueRow(row);
            });
            document.addEventListener('keydown', function(e) {
                if (e.key !== 'Enter' && e.key !== ' ') return;
                var row = e.target && e.target.closest ? e.target.closest('[data-issue-toggle]') : null;
                if (!row || e.target !== row) return;
                e.preventDefault();
                toggleIssueRow(row);
            });
        })();
    </script>
</body>

</html>