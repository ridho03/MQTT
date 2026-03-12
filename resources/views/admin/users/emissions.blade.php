@extends('layouts.app')

@section('content')
<div class="p-6">

    <h2 class="text-2xl font-bold mb-4">
        Data Emisi – {{ $user->name }}
    </h2>

    <p class="text-gray-600 mb-6">
        Vehicle: {{ $carbonCredit->nrkb }} • Device ID: {{ $carbonCredit->device_id }}
    </p>

    {{-- ================= RAW SENSOR DATA ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

<div class="bg-white p-4 rounded-lg shadow">
<h5 class="font-semibold mb-2">Grafik CO</h5>
<canvas id="coChart"></canvas>
</div>

<div class="bg-white p-4 rounded-lg shadow">
<h5 class="font-semibold mb-2">Grafik NH3 dan NO2</h5>
<canvas id="gasChart"></canvas>
</div>

</div>

    

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // RAW SENSOR DATA
    const rawLabels = {!! json_encode($rawData->pluck('timestamp')->map(fn($t)=>$t->format('H:i:s'))) !!};

    const coData  = {!! json_encode($rawData->pluck('co_ppm')) !!};
    const nh3Data = {!! json_encode($rawData->pluck('nh3_ppm')) !!};
    const no2Data = {!! json_encode($rawData->pluck('no2_ppm')) !!};

    // ================= GRAFIK CO =================
new Chart(document.getElementById('coChart'), {
    type: 'line',
    data: {
        labels: rawLabels,
        datasets: [
            {
                label: 'CO (ppm)',
                data: coData,
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239,68,68,0.2)',
                tension: 0.3
            }
        ]
    }
});

// ================= GRAFIK NH3 & NO2 =================
new Chart(document.getElementById('gasChart'), {
    type: 'line',
    data: {
        labels: rawLabels,
        datasets: [
            {
                label: 'NH3 (ppm)',
                data: nh3Data,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,0.2)',
                tension: 0.3
            },
            {
                label: 'NO2 (ppm)',
                data: no2Data,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.2)',
                tension: 0.3
            }
        ]
    }
});

</script>
@endsection
