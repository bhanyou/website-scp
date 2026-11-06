@props([
    'inputName' => 'photo',
    'inputId' => 'photo',
    'label' => 'Foto Realtime',
    'helpText' => 'Ambil foto langsung melalui kamera HP/laptop secara realtime (Opsional).',
    'required' => false,
    'accept' => 'image/*',
    'currentPhoto' => null,
])

@php
    $uid = $inputId . '_' . uniqid();
@endphp

<div class="realtime-camera-wrapper" id="wrapper_{{ $uid }}" style="margin-bottom: 1.5rem;">
    {{-- Header & Label --}}
    <div style="margin-bottom: 0.5rem;">
        <label for="{{ $inputId }}" style="font-weight: 700; color: var(--text-primary); margin: 0; font-size: 0.92rem; display: flex; align-items: center; gap: 0.4rem;">
            <span>📷</span> {{ $label }}
            <span style="font-weight: normal; color: var(--text-muted); font-size: 0.8rem;">(Opsional)</span>
        </label>
    </div>

    {{-- Hidden File Input Asli (Dikirim via Form POST) --}}
    <input 
        type="file" 
        name="{{ $inputName }}" 
        id="{{ $inputId }}" 
        accept="{{ $accept }}" 
        style="display: none;"
        onchange="handleManualFile_{{ $uid }}(this)"
    >

    {{-- Main Dashed Card Sesuai Tampilan Pilihan --}}
    <div style="background: var(--bg-surface, #ffffff); border: 1.5px dashed #cbd5e1; border-radius: 14px; padding: 1.5rem 1rem; position: relative; transition: all 0.25s;" id="container_box_{{ $uid }}">
        
        {{-- State 1: Tampilan Awal (Icon Bulat + Judul + 1 Tombol "Ambil Foto") --}}
        <div id="cam_idle_{{ $uid }}" style="text-align: center;">
            <div style="width: 56px; height: 56px; margin: 0 auto 0.9rem; background: rgba(99, 102, 241, 0.08); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.6rem;">
                📷
            </div>
            
            <h4 style="margin: 0 0 0.35rem; font-size: 0.98rem; font-weight: 700; color: var(--text-primary);">
                Ambil Foto Realtime Melalui Kamera
            </h4>
            
            <p style="margin: 0 auto 1.25rem; max-width: 440px; font-size: 0.82rem; color: var(--text-muted); line-height: 1.5;">
                Gunakan kamera laptop atau HP Anda untuk mendokumentasikan kegiatan secara langsung dan otomatis diberi stempel waktu realtime.
            </p>

            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                <button 
                    type="button" 
                    id="btn_take_photo_{{ $uid }}" 
                    onclick="openCamera_{{ $uid }}()" 
                    class="btn btn-primary" 
                    style="font-weight: 600; padding: 0.55rem 1.45rem; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 8px; background: #4f46e5; border: none; box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25);"
                >
                    <span>📷</span> Ambil Foto
                </button>

                {{-- Tautan Cadangan jika perangkat tidak memiliki kamera --}}
                <label for="{{ $inputId }}" style="margin-top: 0.25rem; font-size: 0.76rem; color: var(--text-muted); cursor: pointer; text-decoration: underline;">
                    atau pilih file dari perangkat
                </label>
            </div>

            {{-- Pesan Error Akses Kamera --}}
            <div id="cam_error_{{ $uid }}" style="display: none; margin-top: 1rem; padding: 0.65rem 0.85rem; background: #fef2f2; border-left: 3px solid #ef4444; border-radius: 6px; font-size: 0.8rem; color: #991b1b; text-align: left;">
                <strong>Perhatian:</strong> <span id="cam_error_msg_{{ $uid }}">Tidak dapat mengakses kamera.</span> Silakan izinkan akses kamera di peramban Anda atau pilih file dari perangkat.
            </div>
        </div>

        {{-- State 2: Kamera Aktif (Viewfinder Video Live) --}}
        <div id="cam_live_{{ $uid }}" style="display: none;">
            <div style="position: relative; background: #0f172a; border-radius: 10px; overflow: hidden; aspect-ratio: 4/3; max-height: 380px; margin: 0 auto; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.15);">
                <video id="video_{{ $uid }}" autoplay playsinline muted style="width: 100%; height: 100%; object-fit: cover;"></video>
                
                {{-- Grid Viewfinder --}}
                <div style="position: absolute; inset: 15px; border: 1.5px dashed rgba(255,255,255,0.35); border-radius: 6px; pointer-events: none;"></div>

                {{-- Indikator Live Realtime --}}
                <div style="position: absolute; top: 10px; left: 10px; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); color: #ffffff; padding: 3px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; display: flex; align-items: center; gap: 6px; border: 1px solid rgba(255,255,255,0.2);">
                    <span style="width: 8px; height: 8px; background: #ef4444; border-radius: 50%; display: inline-block; animation: pulseDot_{{ $uid }} 1.2s infinite ease-in-out;"></span>
                    <span>KAMERA REALTIME</span>
                </div>

                {{-- Jam Realtime WIB --}}
                <div id="live_clock_{{ $uid }}" style="position: absolute; bottom: 10px; right: 10px; background: rgba(0, 0, 0, 0.65); color: #f8fafc; padding: 2px 8px; border-radius: 4px; font-size: 0.72rem; font-family: monospace;">
                    --:--:-- WIB
                </div>
            </div>

            {{-- Tombol Tindakan Kamera --}}
            <div style="display: flex; gap: 0.65rem; justify-content: center; align-items: center; margin-top: 1rem; flex-wrap: wrap;">
                <button type="button" onclick="takeSnapshot_{{ $uid }}()" class="btn btn-primary" style="padding: 0.55rem 1.4rem; font-size: 0.88rem; font-weight: 700; border-radius: 8px; display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, #4f46e5, #6366f1); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);">
                    <span>📸</span> Jepret Foto
                </button>
                
                <button type="button" id="btn_switch_cam_{{ $uid }}" onclick="switchCameraFacing_{{ $uid }}()" class="btn btn-secondary btn-sm" style="padding: 0.5rem 0.85rem; font-size: 0.8rem; font-weight: 600; border-radius: 7px; display: none;" title="Ganti kamera depan / belakang">
                    <span>🔄</span> Ganti Kamera
                </button>

                <button type="button" onclick="closeCamera_{{ $uid }}()" class="btn btn-secondary btn-sm" style="padding: 0.5rem 0.85rem; font-size: 0.8rem; font-weight: 600; border-radius: 7px; color: #dc2626; border-color: #fecaca; background: #fff5f5;">
                    ✕ Batal / Tutup
                </button>
            </div>
        </div>

        {{-- State 3: Foto Berhasil Diambil (Pratinjau) --}}
        <div id="cam_preview_box_{{ $uid }}" style="display: none; text-align: center;">
            <div style="position: relative; max-width: 420px; margin: 0 auto; border-radius: 10px; overflow: hidden; border: 2px solid #22c55e; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.15);">
                <img id="cam_preview_img_{{ $uid }}" src="" alt="Pratinjau Foto Realtime" style="width: 100%; display: block; max-height: 360px; object-fit: contain; background: #0f172a;">
                
                <div id="badge_realtime_{{ $uid }}" style="position: absolute; top: 10px; left: 10px; background: rgba(22, 163, 74, 0.9); color: white; padding: 3px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; display: flex; align-items: center; gap: 5px;">
                    <span>✓</span> Foto Realtime Terverifikasi
                </div>
            </div>

            <div style="margin-top: 0.75rem; font-size: 0.82rem; color: #15803d; font-weight: 600;" id="cam_stamp_info_{{ $uid }}">
                Foto berhasil diambil secara realtime!
            </div>

            <div style="display: flex; gap: 0.6rem; justify-content: center; margin-top: 0.75rem; flex-wrap: wrap;">
                <button type="button" onclick="retakePhoto_{{ $uid }}()" class="btn btn-secondary btn-sm" style="font-weight: 600; padding: 0.45rem 1rem; border-radius: 7px; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <span>🔄</span> Ambil Ulang Foto
                </button>
                <button type="button" onclick="clearPhoto_{{ $uid }}()" class="btn btn-secondary btn-sm" style="font-weight: 600; padding: 0.45rem 1rem; border-radius: 7px; color: #dc2626; border-color: #fecaca; background: #fff5f5;">
                    <span>🗑️</span> Batalkan Foto
                </button>
            </div>
        </div>

        {{-- Foto Lama yang Tersimpan (Jika ada di form edit) --}}
        @if($currentPhoto)
            <div id="current_photo_{{ $uid }}" style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px dashed var(--border-color); font-size: 0.8rem; color: var(--text-muted); text-align: center;">
                <span>Foto yang saat ini tersimpan:</span>
                <div style="margin-top: 0.35rem;">
                    <img src="{{ asset('storage/' . $currentPhoto) }}" alt="Foto tersimpan" style="max-height: 110px; border-radius: 6px; border: 1px solid var(--border-color);">
                </div>
            </div>
        @endif

    </div>

    {{-- Keterangan / Help Text di Bawah Kotak --}}
    @if($helpText)
        <small style="color: var(--text-muted); display: block; margin-top: 0.4rem; font-size: 0.775rem;">
            {{ $helpText }}
        </small>
    @endif
</div>

{{-- Hidden Canvas --}}
<canvas id="canvas_{{ $uid }}" style="display: none;"></canvas>

<style>
@keyframes pulseDot_{{ $uid }} {
    0% { transform: scale(0.95); opacity: 0.6; }
    50% { transform: scale(1.3); opacity: 1; }
    100% { transform: scale(0.95); opacity: 0.6; }
}
</style>

@push('scripts')
<script>
(function() {
    let stream_{{ $uid }} = null;
    let currentFacingMode_{{ $uid }} = 'environment';
    let clockInterval_{{ $uid }} = null;

    const inputActual = document.getElementById('{{ $inputId }}');
    const video = document.getElementById('video_{{ $uid }}');
    const canvas = document.getElementById('canvas_{{ $uid }}');
    const camIdle = document.getElementById('cam_idle_{{ $uid }}');
    const camLive = document.getElementById('cam_live_{{ $uid }}');
    const camPreviewBox = document.getElementById('cam_preview_box_{{ $uid }}');
    const camPreviewImg = document.getElementById('cam_preview_img_{{ $uid }}');
    const camStampInfo = document.getElementById('cam_stamp_info_{{ $uid }}');
    const badgeRealtime = document.getElementById('badge_realtime_{{ $uid }}');
    const camError = document.getElementById('cam_error_{{ $uid }}');
    const camErrorMsg = document.getElementById('cam_error_msg_{{ $uid }}');
    const liveClock = document.getElementById('live_clock_{{ $uid }}');
    const btnSwitchCam = document.getElementById('btn_switch_cam_{{ $uid }}');

    // Langsung buka kamera saat tombol "Ambil Foto" diklik
    window.openCamera_{{ $uid }} = async function() {
        camError.style.display = 'none';

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            camErrorMsg.innerText = 'Browser tidak mendukung akses kamera langsung. Silakan pilih foto dari file perangkat.';
            camError.style.display = 'block';
            return;
        }

        try {
            if (stream_{{ $uid }}) {
                stream_{{ $uid }}.getTracks().forEach(track => track.stop());
            }

            const constraints = {
                video: {
                    facingMode: currentFacingMode_{{ $uid }},
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            };

            stream_{{ $uid }} = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream_{{ $uid }};
            await video.play();

            camIdle.style.display = 'none';
            camPreviewBox.style.display = 'none';
            camLive.style.display = 'block';

            // Cek kamera ganda (depan/belakang pada HP)
            const devices = await navigator.mediaDevices.enumerateDevices();
            const videoDevices = devices.filter(d => d.kind === 'videoinput');
            if (videoDevices.length > 1) {
                btnSwitchCam.style.display = 'inline-flex';
            }

            // Jalankan Jam Realtime
            if (clockInterval_{{ $uid }}) clearInterval(clockInterval_{{ $uid }});
            clockInterval_{{ $uid }} = setInterval(updateClock, 1000);
            updateClock();

        } catch (err) {
            console.error('Kamera error:', err);
            let msg = 'Izin kamera ditolak atau perangkat kamera tidak ditemukan.';
            if (err.name === 'NotAllowedError') {
                msg = 'Akses kamera diblokir. Harap izinkan akses kamera di pengaturan peramban Anda.';
            } else if (err.name === 'NotFoundError') {
                msg = 'Perangkat kamera tidak ditemukan pada laptop/komputer ini.';
            }
            camErrorMsg.innerText = msg;
            camError.style.display = 'block';
        }
    };

    function updateClock() {
        if (!liveClock) return;
        const now = new Date();
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
        liveClock.innerText = timeStr;
    }

    // Ganti Kamera Depan / Belakang
    window.switchCameraFacing_{{ $uid }} = function() {
        currentFacingMode_{{ $uid }} = currentFacingMode_{{ $uid }} === 'environment' ? 'user' : 'environment';
        window.openCamera_{{ $uid }}();
    };

    // Tutup / Matikan Kamera
    window.closeCamera_{{ $uid }} = function() {
        if (clockInterval_{{ $uid }}) {
            clearInterval(clockInterval_{{ $uid }});
            clockInterval_{{ $uid }} = null;
        }
        if (stream_{{ $uid }}) {
            stream_{{ $uid }}.getTracks().forEach(t => t.stop());
            stream_{{ $uid }} = null;
        }
        video.srcObject = null;
        camLive.style.display = 'none';

        if (camPreviewImg.src && camPreviewImg.src.startsWith('data:image')) {
            camPreviewBox.style.display = 'block';
            camIdle.style.display = 'none';
        } else {
            camIdle.style.display = 'block';
            camPreviewBox.style.display = 'none';
        }
    };

    // Ambil Snapshot & Beri Watermark Realtime
    window.takeSnapshot_{{ $uid }} = function() {
        if (!video.videoWidth || !video.videoHeight) return;

        const w = video.videoWidth;
        const h = video.videoHeight;
        canvas.width = w;
        canvas.height = h;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, w, h);

        // Watermark Banner Waktu Realtime
        const now = new Date();
        const dateStr = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
        const stampText = `📷 REALTIME • ${dateStr} ${timeStr}`;

        const barHeight = Math.max(34, Math.round(h * 0.065));
        ctx.fillStyle = 'rgba(15, 23, 42, 0.78)';
        ctx.fillRect(0, h - barHeight, w, barHeight);

        ctx.fillStyle = '#ffffff';
        ctx.font = `bold ${Math.max(13, Math.round(barHeight * 0.42))}px sans-serif`;
        ctx.textAlign = 'left';
        ctx.textBaseline = 'middle';
        ctx.fillText(stampText, 16, h - (barHeight / 2));

        canvas.toBlob(function(blob) {
            if (!blob) return;

            const filename = `realtime_${Date.now()}.jpg`;
            const file = new File([blob], filename, { type: 'image/jpeg' });

            if (window.DataTransfer) {
                const dt = new DataTransfer();
                dt.items.add(file);
                inputActual.files = dt.files;
            }

            const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
            camPreviewImg.src = dataUrl;
            badgeRealtime.style.display = 'flex';
            camStampInfo.innerText = `✓ Foto realtime berhasil diambil pada: ${dateStr} pukul ${timeStr}`;

            window.closeCamera_{{ $uid }}();
            camPreviewBox.style.display = 'block';
            camIdle.style.display = 'none';
        }, 'image/jpeg', 0.88);
    };

    // Ambil Ulang Foto
    window.retakePhoto_{{ $uid }} = function() {
        camPreviewImg.src = '';
        camPreviewBox.style.display = 'none';
        window.openCamera_{{ $uid }}();
    };

    // Batalkan / Hapus Foto
    window.clearPhoto_{{ $uid }} = function() {
        inputActual.value = '';
        camPreviewImg.src = '';
        camPreviewBox.style.display = 'none';
        camIdle.style.display = 'block';
    };

    // Handler jika memilih file manual
    window.handleManualFile_{{ $uid }} = function(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                camPreviewImg.src = e.target.result;
                badgeRealtime.style.display = 'none';
                camStampInfo.innerText = `File dipilih: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                
                camIdle.style.display = 'none';
                camLive.style.display = 'none';
                camPreviewBox.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            // Berkas dokumen / PDF
            camPreviewImg.src = '';
            badgeRealtime.style.display = 'none';
            camStampInfo.innerText = `Dokumen dipilih: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
            camIdle.style.display = 'none';
            camLive.style.display = 'none';
            camPreviewBox.style.display = 'block';
        }
    };

    window.addEventListener('beforeunload', function() {
        if (stream_{{ $uid }}) {
            stream_{{ $uid }}.getTracks().forEach(t => t.stop());
        }
    });
})();
</script>
@endpush
