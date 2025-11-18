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
    <div class="bg-white rounded-xl p-5 shadow mb-10">
        <h3 class="text-lg font-semibold mb-3">Data Mentah (CO, NH₃, NO₂)</h3>
        <canvas id="rawChart" height="120"></canvas>
    </div>

    <!-- {{-- ================= CO2E COMBINED ================= --}}
    <div class="bg-white rounded-xl p-5 shadow mb-10">
        <h3 class="text-lg font-semibold mb-3">Data Gabungan (CO₂e)</h3>
        <canvas id="co2eChart" height="120"></canvas>
    </div>
</div> -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // RAW SENSOR DATA
    const rawLabels = {!! json_encode($rawData->pluck('timestamp')->map(fn($t)=>$t->format('H:i:s'))) !!};

    const coData  = {!! json_encode($rawData->pluck('co_ppm')) !!};
    const nh3Data = {!! json_encode($rawData->pluck('nh3_ppm')) !!};
    const no2Data = {!! json_encode($rawData->pluck('no2_ppm')) !!};

    new Chart(document.getElementById('rawChart'), {
        type: 'line',
        data: {
            labels: rawLabels,
            datasets: [
                { label: 'CO (ppm)',  data: coData,  borderColor: '#ef4444', tension: 0.3 },
                { label: 'NH₃ (ppm)', data: nh3Data, borderColor: '#10b981', tension: 0.3 },
                { label: 'NO₂ (ppm)', data: no2Data, borderColor: '#3b82f6', tension: 0.3 },
            ]
        }
    });

    // CO2e DATA
    // const co2eLabels = {!! json_encode($co2eData->pluck('timestamp')->map(fn($t)=>$t->format('H:i:s'))) !!};
    // const co2eData = {!! json_encode($co2eData->pluck('co2e_mg_m3')) !!};

    // new Chart(document.getElementById('co2eChart'), {
    //     type: 'line',
    //     data: {
    //         labels: co2eLabels,
    //         datasets: [
    //             { 
    //                 label: 'CO₂e (mg/m³)',
    //                 data: co2eData,
    //                 borderColor: '#065f46',
    //                 tension: 0.3,
    //                 borderWidth: 2
    //             }
    //         ]
    //     }
    // });
</script>
@endsection
