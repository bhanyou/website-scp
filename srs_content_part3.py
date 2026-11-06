"""
Part 3 of SRS Content: Appendix A (Model Analisa), Appendix B (Antarmuka), Appendix C (TBD)
Disusun untuk School Class Progress (SCP)
Pengembang: Mahirsya Bhanu Adyatma (Absen 22)
"""

from reportlab.platypus import Paragraph, Spacer, PageBreak, KeepTogether
from reportlab.lib import colors
from srs_base import BookmarkFlowable, create_table, get_image_flowable

def build_appendix_a(styles, store):
    story = []
    story.append(BookmarkFlowable('app_a', store))
    story.append(Paragraph('Appendix A: Model Analisa', styles['H1']))
    story.append(Paragraph('Model analisis berikut digunakan untuk menggambarkan arsitektur kebutuhan SCP secara visual melalui use case, deskripsi use case, activity diagram, dan class diagram.', styles['Normal']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('app_a_1', store))
    story.append(Paragraph('A.1 Usecase Diagram', styles['H2']))
    story.append(get_image_flowable('extracted_imgs/p36_0_IM244.jpg', 327, 300))
    story.append(Paragraph('Gambar A.1 Diagram Use Case School Class Progress', styles['Caption']))
    story.append(Spacer(1, 6))
    story.append(Paragraph('Daftar use case beserta aktor utama, prakondisi, dan ringkasan alur utama adalah sebagai berikut:', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(create_table(        [
            [Paragraph('ID', styles['TH']), Paragraph('Nama Use Case', styles['TH']), Paragraph('Aktor Utama', styles['TH']), Paragraph('Prakondisi', styles['TH']), Paragraph('Ringkasan Alur Utama', styles['TH'])],
            [Paragraph('UC1', styles['TDBold']), Paragraph('Login Sistem', styles['TD']), Paragraph('Seluruh Peran', styles['TD']), Paragraph('Akun terdaftar', styles['TD']), Paragraph('Pengguna memasukkan kredensial dan diarahkan ke dashboard sesuai role.', styles['TD'])],
            [Paragraph('UC2', styles['TDBold']), Paragraph('Ubah Profil & Password', styles['TD']), Paragraph('Seluruh Peran', styles['TD']), Paragraph('Sudah login', styles['TD']), Paragraph('Pengguna memperbarui nomor kontak atau mengganti kata sandi.', styles['TD'])],
            [Paragraph('UC3', styles['TDBold']), Paragraph('Kelola Master Data', styles['TD']), Paragraph('Waka Kurikulum', styles['TD']), Paragraph('Sudah login', styles['TD']), Paragraph('Waka mengelola data kelas, ruangan, mata pelajaran, dan profil guru/siswa.', styles['TD'])],
            [Paragraph('UC4', styles['TDBold']), Paragraph('Kelola Akun Pengguna', styles['TD']), Paragraph('Waka Kurikulum', styles['TD']), Paragraph('Sudah login', styles['TD']), Paragraph('Waka membuat akun dan menetapkan guru sebagai Wali Kelas binaan.', styles['TD'])],
            [Paragraph('UC5', styles['TDBold']), Paragraph('Ganti Putaran Siklus Blok', styles['TD']), Paragraph('Waka Kurikulum', styles['TD']), Paragraph('Sudah login', styles['TD']), Paragraph('Waka mengaktifkan Siklus 1 atau Siklus 2 dengan satu kali klik.', styles['TD'])],
            [Paragraph('UC6', styles['TDBold']), Paragraph('Kunci Jadwal Mengajar', styles['TD']), Paragraph('Waka Kurikulum', styles['TD']), Paragraph('Sudah login', styles['TD']), Paragraph('Waka mengaktifkan lock schedule sehingga aksi ubah jadwal guru ditutup.', styles['TD'])],
            [Paragraph('UC7', styles['TDBold']), Paragraph('Input Jadwal Mandiri', styles['TD']), Paragraph('Guru, Waka', styles['TD']), Paragraph('Jadwal belum dikunci', styles['TD']), Paragraph('Guru menginput jadwal, sistem memverifikasi irisan waktu bentrokan.', styles['TD'])],
            [Paragraph('UC8', styles['TDBold']), Paragraph('Import Jadwal Massal', styles['TD']), Paragraph('Guru, Waka', styles['TD']), Paragraph('Berkas import siap', styles['TD']), Paragraph('Pengguna mengunggah berkas jadwal spreadsheet dan sistem menyimpannya.', styles['TD'])],
            [Paragraph('UC9', styles['TDBold']), Paragraph('Input Jurnal Mengajar', styles['TD']), Paragraph('Guru Pengajar', styles['TD']), Paragraph('Ada jadwal KBM', styles['TD']), Paragraph('Guru mencatat materi KBM, upload foto kelas, dan memicu presensi otomatis.', styles['TD'])],
            [Paragraph('UC10', styles['TDBold']), Paragraph('Kelola Presensi Siswa', styles['TD']), Paragraph('Guru Pengajar', styles['TD']), Paragraph('Jurnal dibuat', styles['TD']), Paragraph('Guru menyesuaikan status kehadiran siswa (Hadir, Sakit, Izin, Alpa).', styles['TD'])],
            [Paragraph('UC11', styles['TDBold']), Paragraph('Pengajuan Izin Siswa', styles['TD']), Paragraph('Siswa, Wali Kelas', styles['TD']), Paragraph('Sudah login', styles['TD']), Paragraph('Siswa mengajukan izin/sakit online disertai unggah bukti surat.', styles['TD'])],
            [Paragraph('UC12', styles['TDBold']), Paragraph('Verifikasi Perizinan', styles['TD']), Paragraph('Staff Kesiswaan', styles['TD']), Paragraph('Ada izin Pending', styles['TD']), Paragraph('Kesiswaan memeriksa bukti dan menyetujui (Approve) atau menolak (Reject).', styles['TD'])],
            [Paragraph('UC13', styles['TDBold']), Paragraph('Lihat Alert Jurnal', styles['TD']), Paragraph('Guru, Waka', styles['TD']), Paragraph('Ada jadwal lewat >30m', styles['TD']), Paragraph('Sistem memunculkan banner pengingat jurnal di dashboard.', styles['TD'])],
            [Paragraph('UC14', styles['TDBold']), Paragraph('Cetak Laporan Presensi', styles['TD']), Paragraph('Waka, Wali Kelas, Kesiswaan', styles['TD']), Paragraph('Sudah login', styles['TD']), Paragraph('Pengguna mencetak rekapitulasi presensi kelas resmi ke kertas A4.', styles['TD'])],
            [Paragraph('UC15', styles['TDBold']), Paragraph('Akses REST API Mobile', styles['TD']), Paragraph('Siswa, Guru', styles['TD']), Paragraph('Token Sanctum valid', styles['TD']), Paragraph('Aplikasi Flutter melakukan sinkronisasi data jadwal dan perizinan.', styles['TD'])],
            [Paragraph('UC16', styles['TDBold']), Paragraph('Reset Password via OTP', styles['TD']), Paragraph('Seluruh Peran', styles['TD']), Paragraph('Lupa kata sandi', styles['TD']), Paragraph('Pengguna meminta kode OTP email dan mengatur kata sandi baru.', styles['TD'])]
        ], [26, 110, 80, 84, 204]))
    story.append(Spacer(1, 10))
    story.append(BookmarkFlowable('app_a_2', store))
    story.append(Paragraph('A.2 Usecase Description', styles['H2']))
    story.append(Paragraph('Deskripsi alur use case secara detail mengikuti spesifikasi kebutuhan fungsional pada Bab 4. Alur normal, alur alternatif, dan penanganan galat telah diintegrasikan pada arsitektur pengujian sistem.', styles['Normal']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('app_a_3', store))
    story.append(Paragraph('A.3 Activity Diagram', styles['H2']))
    story.append(Paragraph('Diagram aktivitas berikut mendeskripsikan alur kerja logika bisnis utama pada sistem:', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(get_image_flowable('extracted_imgs/p38_0_IM250.jpg', 298, 260))
    story.append(Paragraph('Gambar A.2 Activity Diagram Logika Pencegahan Bentrok Jadwal', styles['Caption']))
    story.append(Spacer(1, 6))
    story.append(get_image_flowable('extracted_imgs/p38_1_IM251.jpg', 440, 257))
    story.append(Paragraph('Gambar A.3 Activity Diagram Pengajuan dan Verifikasi Izin Siswa', styles['Caption']))
    story.append(Spacer(1, 10))
    story.append(BookmarkFlowable('app_a_4', store))
    story.append(Paragraph('A.4 Class Diagram / ERD', styles['H2']))
    story.append(Paragraph('Diagram kelas berikut mengilustrasikan relasi struktural antar entitas data pada basis data SCP:', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(get_image_flowable('extracted_imgs/p39_0_IM256.jpg', 405, 320))
    story.append(Paragraph('Gambar A.4 Class Diagram / Entity-Relationship Diagram (ERD)', styles['Caption']))
    story.append(Spacer(1, 10))
    return story

def build_appendix_b(styles, store):
    story = []
    story.append(BookmarkFlowable('app_b', store))
    story.append(Paragraph('Appendix B: Desain Antarmuka Sistem', styles['H1']))
    story.append(Paragraph('Bagian ini menampilkan rancangan tata letak dasar (wireframe) dan antarmuka akhir (User Interface) implementasi sistem SCP.', styles['Normal']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('app_b_1', store))
    story.append(Paragraph('B.1 Wireframe', styles['H2']))
    story.append(get_image_flowable('extracted_imgs/p40_0_IM261.jpg', 440, 224))
    story.append(Paragraph('Gambar B.1 Wireframe Halaman Login', styles['Caption']))
    story.append(Spacer(1, 6))
    story.append(get_image_flowable('extracted_imgs/p40_1_IM262.jpg', 440, 224))
    story.append(Paragraph('Gambar B.2 Wireframe Dashboard Waka Kurikulum', styles['Caption']))
    story.append(Spacer(1, 6))
    story.append(get_image_flowable('extracted_imgs/p41_0_IM265.jpg', 440, 224))
    story.append(Paragraph('Gambar B.3 Wireframe Form Input Jadwal Mengajar', styles['Caption']))
    story.append(Spacer(1, 6))
    story.append(get_image_flowable('extracted_imgs/p41_1_IM266.jpg', 440, 224))
    story.append(Paragraph('Gambar B.4 Wireframe Form Input Jurnal Mengajar', styles['Caption']))
    story.append(Spacer(1, 6))
    story.append(get_image_flowable('extracted_imgs/p42_0_IM269.jpg', 440, 223))
    story.append(Paragraph('Gambar B.5 Wireframe Form Verifikasi Izin Siswa', styles['Caption']))
    story.append(Spacer(1, 6))
    story.append(get_image_flowable('extracted_imgs/p42_1_IM270.jpg', 440, 224))
    story.append(Paragraph('Gambar B.6 Wireframe Laporan Presensi Siswa', styles['Caption']))
    story.append(Spacer(1, 6))
    story.append(BookmarkFlowable('app_b_2', store))
    story.append(Paragraph('B.2 User Interface', styles['H2']))
    story.append(get_image_flowable('extracted_imgs/p43_0_IM273.jpg', 440, 225))
    story.append(Paragraph('Gambar B.7 User Interface Halaman Login', styles['Caption']))
    story.append(Spacer(1, 6))
    story.append(get_image_flowable('extracted_imgs/p43_1_IM274.jpg', 440, 224))
    story.append(Paragraph('Gambar B.8 User Interface Dashboard Guru Pengajar', styles['Caption']))
    story.append(Spacer(1, 6))
    story.append(get_image_flowable('extracted_imgs/p44_0_IM278.jpg', 440, 225))
    story.append(Paragraph('Gambar B.9 User Interface Dashboard Waka Kurikulum', styles['Caption']))
    story.append(Spacer(1, 6))
    story.append(get_image_flowable('extracted_imgs/p44_1_IM279.jpg', 440, 223))
    story.append(Paragraph('Gambar B.10 User Interface Cetak Laporan Rekapitulasi Presensi', styles['Caption']))
    story.append(Spacer(1, 6))
    return story

def build_appendix_c(styles, store):
    story = []
    story.append(BookmarkFlowable('app_c', store))
    story.append(Paragraph('Appendix C: Daftar Yang Akan Ditentukan Kemudian (TBD)', styles['H1']))
    story.append(Paragraph('Daftar berikut memuat butir-butir teknis yang akan ditentukan lebih lanjut seiring implementasi operasional sekolah:', styles['Normal']))
    story.append(Spacer(1, 6))
    story.append(create_table(        [
            [Paragraph('No', styles['TH']), Paragraph('Item TBD', styles['TH']), Paragraph('Bagian Terkait', styles['TH']), Paragraph('Keterangan', styles['TH'])],
            [Paragraph('1', styles['TD']), Paragraph('Spesifikasi perangkat keras minimal server operasional', styles['TD']), Paragraph('3.2', styles['TD']), Paragraph('Menyesuaikan total beban transaksi siswa dan guru saat jam puncak KBM.', styles['TD'])],
            [Paragraph('2', styles['TD']), Paragraph('Versi sistem operasi dan DBMS definitif di server production', styles['TD']), Paragraph('2.4, 3.3', styles['TD']), Paragraph('Ditetapkan saat jadwal deployment ke server hosting sekolah.', styles['TD'])],
            [Paragraph('3', styles['TD']), Paragraph('Format buku panduan pengguna digital dan dokumentasi API', styles['TD']), Paragraph('2.6', styles['TD']), Paragraph('PDF interaktif atau portal dokumentasi Swagger / Postman Collection.', styles['TD'])],
            [Paragraph('4', styles['TD']), Paragraph('Kebijakan standar kompleksitas password pengguna', styles['TD']), Paragraph('4.1, 5.2', styles['TD']), Paragraph('Ditetapkan bersama tim IT sekolah (panjang minimal 8 karakter).', styles['TD'])],
            [Paragraph('5', styles['TD']), Paragraph('Kewajiban lampiran surat dokter pada jenis izin Sakit', styles['TD']), Paragraph('4.9', styles['TD']), Paragraph('Ditetapkan wajib jika sakit lebih dari 2 hari berturut-turut.', styles['TD'])],
            [Paragraph('6', styles['TD']), Paragraph('Formula kalkulasi persentase kepatuhan jurnal mengajar', styles['TD']), Paragraph('4.10', styles['TD']), Paragraph('Rasio perbandingan jurnal terisi terhadap jadwal tatap muka bulanan.', styles['TD'])],
            [Paragraph('7', styles['TD']), Paragraph('Target ketersediaan sistem (Service Level Agreement / Uptime)', styles['TD']), Paragraph('5.3', styles['TD']), Paragraph('Target 99.5% uptime selama jam operasional sekolah.', styles['TD'])],
            [Paragraph('8', styles['TD']), Paragraph('Estimasi jumlah pengguna serentak maksimal (concurrency)', styles['TD']), Paragraph('5.1', styles['TD']), Paragraph('Dihitung berdasarkan jumlah kelas aktif dikalikan kapasitas rombel.', styles['TD'])],
            [Paragraph('9', styles['TD']), Paragraph('Mekanisme retensi dan pembersihan berkas foto lama', styles['TD']), Paragraph('6.3', styles['TD']), Paragraph('Penjadwalan otomatis arsip berkas setiap pergantian tahun ajaran.', styles['TD'])],
            [Paragraph('10', styles['TD']), Paragraph('Standar pembulatan nilai persentase presensi laporan', styles['TD']), Paragraph('4.11', styles['TD']), Paragraph('Pembulatan dua angka di belakang koma (contoh: 98.75%).', styles['TD'])],
            [Paragraph('11', styles['TD']), Paragraph('Antarmuka konfirmasi preview sebelum commit import massal', styles['TD']), Paragraph('4.6', styles['TD']), Paragraph('Disesuaikan dengan format tabel jadwal yang disepakati Waka Kurikulum.', styles['TD'])]
        ], [24, 150, 70, 260]))
    story.append(Spacer(1, 10))
    return story

print("Part 3 builder loaded.")
