import 'dart:typed_data';
import 'dart:ui' as ui;
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import '../theme/app_colors.dart';

/// Helper untuk menempelkan stempel watermark waktu realtime langsung pada data byte foto (Persis Desktop SCP)
Future<Uint8List> stampRealtimeWatermark({
  required Uint8List rawBytes,
  required DateTime timestamp,
}) async {
  try {
    final codec = await ui.instantiateImageCodec(rawBytes);
    final frame = await codec.getNextFrame();
    final image = frame.image;
    final w = image.width.toDouble();
    final h = image.height.toDouble();

    final recorder = ui.PictureRecorder();
    final canvas = Canvas(recorder, Rect.fromLTWH(0, 0, w, h));

    // 1. Gambar foto asli
    canvas.drawImage(image, Offset.zero, Paint());

    // 2. Banner gelap di bagian bawah foto (Identik Desktop)
    final barHeight = (h * 0.065).clamp(42.0, 110.0);
    final bgPaint = Paint()..color = const Color(0xCC0F172A);
    canvas.drawRect(Rect.fromLTWH(0, h - barHeight, w, barHeight), bgPaint);

    // 3. Teks stempel realtime
    final dateStr = DateFormat('dd MMM yyyy', 'id_ID').format(timestamp);
    final timeStr = DateFormat('HH:mm:ss').format(timestamp);
    final stampText = '📷 REALTIME • $dateStr $timeStr WIB • SCP';

    final fontSize = (barHeight * 0.38).clamp(13.0, 34.0);
    final textPainter = TextPainter(
      text: TextSpan(
        text: stampText,
        style: TextStyle(
          color: Colors.white,
          fontSize: fontSize,
          fontWeight: FontWeight.bold,
          letterSpacing: 0.5,
        ),
      ),
      textDirection: ui.TextDirection.ltr,
    );
    textPainter.layout(maxWidth: w - 32);
    final textOffset = Offset(20, h - barHeight + (barHeight - textPainter.height) / 2);
    textPainter.paint(canvas, textOffset);

    // 4. Export ke byte data gambar baru
    final picture = recorder.endRecording();
    final stampedImage = await picture.toImage(w.toInt(), h.toInt());
    final byteData = await stampedImage.toByteData(format: ui.ImageByteFormat.png);
    if (byteData != null) {
      return byteData.buffer.asUint8List();
    }
  } catch (e) {
    debugPrint('Gagal menambahkan watermark realtime otomatis: $e');
  }
  return rawBytes;
}

/// Widget input kamera realtime yang identik 1:1 dengan tampilan desktop School Class Progress:
/// `resources/views/components/realtime-camera-input.blade.php`.
class RealtimeCameraInput extends StatefulWidget {
  final String label;
  final String? helpText;
  final bool required;
  final Uint8List? initialBytes;
  final DateTime? initialTimestamp;
  final bool isRealtime;
  final void Function(Uint8List bytes, String filename, DateTime timestamp, bool isRealtime) onPhotoCaptured;
  final VoidCallback onPhotoCleared;

  const RealtimeCameraInput({
    super.key,
    this.label = 'Foto Realtime',
    this.helpText = 'Ambil foto langsung melalui kamera HP secara realtime.',
    this.required = false,
    this.initialBytes,
    this.initialTimestamp,
    this.isRealtime = true,
    required this.onPhotoCaptured,
    required this.onPhotoCleared,
  });

  @override
  State<RealtimeCameraInput> createState() => _RealtimeCameraInputState();
}

class _RealtimeCameraInputState extends State<RealtimeCameraInput> {
  final ImagePicker _picker = ImagePicker();
  bool _isProcessing = false;
  Uint8List? _photoBytes;
  DateTime? _photoTimestamp;
  bool _photoIsRealtime = true;

  @override
  void initState() {
    super.initState();
    _photoBytes = widget.initialBytes;
    _photoTimestamp = widget.initialTimestamp;
    _photoIsRealtime = widget.isRealtime;
  }

  @override
  void didUpdateWidget(covariant RealtimeCameraInput oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.initialBytes != oldWidget.initialBytes) {
      _photoBytes = widget.initialBytes;
      _photoTimestamp = widget.initialTimestamp;
      _photoIsRealtime = widget.isRealtime;
    }
  }

  Future<void> _takePhoto() async {
    setState(() => _isProcessing = true);
    try {
      final XFile? photo = await _picker.pickImage(
        source: ImageSource.camera,
        preferredCameraDevice: CameraDevice.rear,
        maxWidth: 1600,
        maxHeight: 1600,
        imageQuality: 88,
      );

      if (photo != null) {
        final rawBytes = await photo.readAsBytes();
        final now = DateTime.now();

        // Stempel watermark langsung di byte gambar
        final stampedBytes = await stampRealtimeWatermark(
          rawBytes: rawBytes,
          timestamp: now,
        );

        setState(() {
          _photoBytes = stampedBytes;
          _photoTimestamp = now;
          _photoIsRealtime = true;
          _isProcessing = false;
        });

        widget.onPhotoCaptured(
          stampedBytes,
          'realtime_${now.millisecondsSinceEpoch}.jpg',
          now,
          true,
        );
      } else {
        setState(() => _isProcessing = false);
      }
    } catch (e) {
      setState(() => _isProcessing = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Tidak dapat mengakses kamera: $e'),
            backgroundColor: AppColors.alpa,
          ),
        );
      }
    }
  }

  Future<void> _pickFromGallery() async {
    setState(() => _isProcessing = true);
    try {
      final XFile? photo = await _picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 1600,
        maxHeight: 1600,
        imageQuality: 88,
      );

      if (photo != null) {
        final bytes = await photo.readAsBytes();
        final now = DateTime.now();

        setState(() {
          _photoBytes = bytes;
          _photoTimestamp = now;
          _photoIsRealtime = false;
          _isProcessing = false;
        });

        widget.onPhotoCaptured(
          bytes,
          photo.name,
          now,
          false,
        );
      } else {
        setState(() => _isProcessing = false);
      }
    } catch (e) {
      setState(() => _isProcessing = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Gagal memilih file galeri: $e'),
            backgroundColor: AppColors.alpa,
          ),
        );
      }
    }
  }

  void _clearPhoto() {
    setState(() {
      _photoBytes = null;
      _photoTimestamp = null;
    });
    widget.onPhotoCleared();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Label Header
        Row(
          children: [
            const Text('📷', style: TextStyle(fontSize: 14)),
            const SizedBox(width: 6),
            Text(
              widget.label,
              style: const TextStyle(
                fontWeight: FontWeight.w700,
                fontSize: 13.5,
                color: Color(0xFF0F172A),
              ),
            ),
            const SizedBox(width: 6),
            Text(
              widget.required ? '(Wajib)' : '(Opsional)',
              style: TextStyle(
                fontSize: 12,
                color: widget.required ? const Color(0xFFDC2626) : const Color(0xFF64748B),
                fontWeight: widget.required ? FontWeight.w600 : FontWeight.normal,
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),

        // Main Box (Idle vs Captured Preview)
        AnimatedSwitcher(
          duration: const Duration(milliseconds: 250),
          child: _photoBytes != null ? _buildPreviewState() : _buildIdleState(),
        ),

        // Help Text di Bawah Box
        if (widget.helpText != null && widget.helpText!.isNotEmpty) ...[
          const SizedBox(height: 6),
          Text(
            widget.helpText!,
            style: const TextStyle(
              fontSize: 11.5,
              color: Color(0xFF64748B),
              height: 1.35,
            ),
          ),
        ],
      ],
    );
  }

  /// Tampilan Kotak Awal (Persis Desktop: Icon Bulat + Judul + Tombol "Ambil Foto" + Link Galeri)
  Widget _buildIdleState() {
    return Container(
      key: const ValueKey('camera_idle_state'),
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: const Color(0xFFCBD5E1),
          width: 1.5,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Icon Bulat Indigo (Identik Desktop)
          Container(
            width: 56,
            height: 56,
            decoration: const BoxDecoration(
              color: Color(0x144F46E5), // rgba(99, 102, 241, 0.08)
              shape: BoxShape.circle,
            ),
            alignment: Alignment.center,
            child: const Text('📷', style: TextStyle(fontSize: 26)),
          ),
          const SizedBox(height: 12),

          // Judul
          const Text(
            'Ambil Foto Realtime Melalui Kamera',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 14.5,
              fontWeight: FontWeight.w700,
              color: Color(0xFF0F172A),
            ),
          ),
          const SizedBox(height: 6),

          // Deskripsi
          const Text(
            'Gunakan kamera HP Anda untuk mendokumentasikan kegiatan secara langsung dan otomatis diberi stempel waktu realtime.',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 12,
              color: Color(0xFF64748B),
              height: 1.4,
            ),
          ),
          const SizedBox(height: 18),

          // Tombol Primary Ambil Foto
          ElevatedButton.icon(
            onPressed: _isProcessing ? null : _takePhoto,
            icon: _isProcessing
                ? const SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                  )
                : const Text('📷', style: TextStyle(fontSize: 15)),
            label: Text(
              _isProcessing ? 'Membuka Kamera...' : 'Ambil Foto',
              style: const TextStyle(
                fontWeight: FontWeight.w600,
                fontSize: 13.5,
              ),
            ),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF4F46E5),
              foregroundColor: Colors.white,
              elevation: 2,
              shadowColor: const Color(0x404F46E5),
              padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 12),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
          ),
          const SizedBox(height: 8),

          // Tautan Cadangan Pilih Galeri
          InkWell(
            onTap: _isProcessing ? null : _pickFromGallery,
            child: const Padding(
              padding: EdgeInsets.symmetric(vertical: 4, horizontal: 8),
              child: Text(
                'atau pilih file dari perangkat / galeri',
                style: TextStyle(
                  fontSize: 11.5,
                  color: Color(0xFF64748B),
                  decoration: TextDecoration.underline,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  /// Tampilan Saat Foto Berhasil Diambil (Persis Desktop: Border Hijau, Badge Realtime, Preview Foto, Stempel Waktu & Tombol Aksi)
  Widget _buildPreviewState() {
    final dateStr = _photoTimestamp != null
        ? DateFormat('dd MMM yyyy', 'id_ID').format(_photoTimestamp!)
        : DateFormat('dd MMM yyyy', 'id_ID').format(DateTime.now());
    final timeStr = _photoTimestamp != null
        ? DateFormat('HH:mm:ss').format(_photoTimestamp!)
        : DateFormat('HH:mm:ss').format(DateTime.now());

    return Container(
      key: const ValueKey('camera_preview_state'),
      width: double.infinity,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: const Color(0xFFCBD5E1),
          width: 1.5,
        ),
      ),
      child: Column(
        children: [
          // Frame Pratinjau Foto Bergaris Hijau (Identik Desktop)
          Container(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(10),
              border: Border.all(
                color: _photoIsRealtime ? const Color(0xFF22C55E) : const Color(0xFF0EA5E9),
                width: 2.0,
              ),
              boxShadow: [
                BoxShadow(
                  color: (_photoIsRealtime ? const Color(0xFF22C55E) : const Color(0xFF0EA5E9)).withValues(alpha: 0.15),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: Stack(
                alignment: Alignment.topLeft,
                children: [
                  Image.memory(
                    _photoBytes!,
                    width: double.infinity,
                    height: 220,
                    fit: BoxFit.cover,
                  ),

                  // Badge Realtime Terverifikasi (Identik Desktop)
                  Positioned(
                    top: 10,
                    left: 10,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: _photoIsRealtime ? const Color(0xE616A34A) : const Color(0xE60284C7),
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.2),
                            blurRadius: 4,
                          ),
                        ],
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            _photoIsRealtime ? '✓' : '📁',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(width: 5),
                          Text(
                            _photoIsRealtime ? 'Foto Realtime Terverifikasi' : 'File Dokumen Terlampir',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 11,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 10),

          // Teks Keterangan Waktu Stempel
          Text(
            _photoIsRealtime
                ? '✓ Foto realtime berhasil diambil pada: $dateStr pukul $timeStr WIB'
                : 'Berkas berhasil dipilih: $dateStr pukul $timeStr WIB',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: _photoIsRealtime ? const Color(0xFF15803D) : const Color(0xFF0369A1),
            ),
          ),
          const SizedBox(height: 12),

          // Tombol Tindakan: Ambil Ulang & Batalkan Foto (Identik Desktop)
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              OutlinedButton.icon(
                onPressed: _isProcessing ? null : _takePhoto,
                icon: const Text('🔄', style: TextStyle(fontSize: 13)),
                label: const Text(
                  'Ambil Ulang Foto',
                  style: TextStyle(
                    fontSize: 12.5,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF334155),
                  ),
                ),
                style: OutlinedButton.styleFrom(
                  side: const BorderSide(color: Color(0xFFCBD5E1)),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(7)),
                ),
              ),
              const SizedBox(width: 8),
              OutlinedButton.icon(
                onPressed: _clearPhoto,
                icon: const Text('🗑️', style: TextStyle(fontSize: 13)),
                label: const Text(
                  'Batalkan Foto',
                  style: TextStyle(
                    fontSize: 12.5,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFFDC2626),
                  ),
                ),
                style: OutlinedButton.styleFrom(
                  backgroundColor: const Color(0xFFFFF5F5),
                  side: const BorderSide(color: Color(0xFFFECACA)),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(7)),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
