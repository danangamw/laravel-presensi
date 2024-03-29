@extends('layout.presensi')
@section('header')
    <div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="javascript:;" class="headerButton goBack">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">E-Presensi</div>
        <div class="right"></div>
    </div>
@endsection

<style>
    .webcam-capture,
    .webcam-capture video {
        display: inline-block;
        width: 100% !important;
        margin: 15px 0 !important;
        height: auto !important;
        border-radius: 15px;

    }

    #map {
        height: 200px;
    }
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

@section('content')
    <div class="row" style="margin-top: 70px">
        <div class="col">
            <input type="hidden" id="lokasi">
            <div class="webcam-capture">

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            @if ($cek > 0)
               <button id="takepresensi" class="btn btn-danger btn-block">
                <ion-icon name="camera-outline"></ion-icon>
                Pulang
            </button>
            @else

            <button id="takepresensi" class="btn btn-primary btn-block">
                <ion-icon name="camera-outline"></ion-icon>
                Masuk
            </button>
            @endif

        </div>
    </div>

    <div class="row mt-2">
        <div class="col">
            <div id="map"></div>
        </div>
    </div>

    <audio id="notifikasi_in">
        <source src="{{ asset('assets/sound/notifikasi_in.mp3')}}" type="audio/mpeg">
    </audio>

    <audio id="notifikasi_out">
        <source src="{{ asset('assets/sound/notifikasi_out.mp3')}}" type="audio/mpeg">
    </audio>
@endsection

@push('myscript')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        var notifikasiIn = document.getElementById("notifikasi_in");
        var notifikasiOut = document.getElementById("notifikasi_out");

        Webcam.set({
            height: 480,
            width: 640,
            image_format: 'jpeg',
            jpeq_quality: 80
        });

        Webcam.attach('.webcam-capture');
        let lokasi = document.getElementById('lokasi');
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(successCallback, errorCallback)
        }

        function successCallback(position) {
            lokasi.value = `${position.coords.latitude},${position.coords.longitude}`;
            var map = L.map('map').setView([position.coords.latitude, position.coords.longitude], 16);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);
            var marker = L.marker([position.coords.latitude, position.coords.longitude]).addTo(map);

            var circle = L.circle([-7.752871, 110.450695], {
                color: 'red',
                fillColor: '#f03',
                fillOpacity: 0.5,
                radius: 50
            }).addTo(map);
        }

        function errorCallback() {

        }

        $("#takepresensi").click(function(e) {
            Webcam.snap(function(uri) {
                image = uri;
            });
            let lokasi = $("#lokasi").val();
            
            $.ajax({
                type: "POST",
                url: "/presensi/store",
                data: {
                    _token: "{{ csrf_token() }}",
                    image: image,
                    lokasi: lokasi
                },
                cache: false,
                success: function(response) {
                    var status = response.split("|");
                    
                    if (status[0] == "success") {
                        if(status[2] == "in") {
                            notifikasiIn.play();
                        } 

                        if (status[2] == "out") {
                            notifikasiOut.play();
                        }
                        Swal.fire({
                            title: 'Berhasil!',
                            text: status[1],
                            icon: 'success',
                        })
                        setTimeout("location.href='/dashboard'", 3000);
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: status[1],
                            icon: 'error',
                            confirmButtonText: 'OK'
                        })
                       
                    }
                } 
            });
            
        })
    </script>
@endpush
