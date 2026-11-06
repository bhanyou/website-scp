"""
Part 1 of SRS Content: Cover, Riwayat Perubahan, Bab 1, Bab 2, Bab 3
Disusun untuk School Class Progress (SCP)
Pengembang: Mahirsya Bhanu Adyatma (Absen 22)
"""

from reportlab.platypus import Paragraph, Spacer, PageBreak, KeepTogether, Table, TableStyle
from reportlab.lib import colors
from srs_base import TopRuleFlowable, BookmarkFlowable, USABLE_WIDTH, create_table, get_image_flowable

def build_cover(styles):
    story = []
    story.append(TopRuleFlowable(USABLE_WIDTH, height=3))
    story.append(Spacer(1, 80))
    story.append(Paragraph('Spesifikasi Kebutuhan<br/>Perangkat Lunak', styles['CoverTitle']))
    story.append(Spacer(1, 40))
    story.append(Paragraph('Untuk', styles['CoverSub']))
    story.append(Spacer(1, 20))
    story.append(Paragraph('School Class Progress (SCP)', styles['CoverAppName']))
    story.append(Spacer(1, 60))
    story.append(Paragraph('Versi 1.0 disetujui', styles['CoverMeta']))
    story.append(Spacer(1, 40))
    story.append(Paragraph('Disiapkan oleh Mahirsya Bhanu Adyatma (Absen 22)', styles['CoverMeta']))
    story.append(Spacer(1, 40))
    story.append(Paragraph('SMK Negeri 1 Bantul', styles['CoverMeta']))
    story.append(Spacer(1, 40))
    story.append(Paragraph('10 Oktober 2026', styles['CoverMeta']))
    story.append(PageBreak())
    return story

def build_revision_history(styles, store):
    story = []
    story.append(BookmarkFlowable('rev_hist', store))
    story.append(Paragraph('Riwayat Perubahan', styles['H1']))
    story.append(Spacer(1, 10))
    story.append(create_table(        [
            [Paragraph('Nama', styles['TH']), Paragraph('Tanggal', styles['TH']), Paragraph('Alasan Perubahan', styles['TH']), Paragraph('Versi', styles['TH'])],
            [Paragraph('Mahirsya Bhanu Adyatma (Absen 22)', styles['TD']), Paragraph('10 Oktober 2026', styles['TD']), Paragraph('Pembuatan Dokumen SRS awal berdasarkan soal UKK SCP. v1.0.0', styles['TD']), Paragraph('1.0', styles['TD'])]
        ], [110, 80, 250, 64]))
    story.append(PageBreak())
    return story

def build_bab1(styles, store):
    story = []
    story.append(BookmarkFlowable('sec_1', store))
    story.append(Paragraph('1. Pendahuluan', styles['H1']))
    story.append(BookmarkFlowable('sec_1_1', store))
    story.append(Paragraph('1.1 Tujuan', styles['H2']))
    story.append(Paragraph('Dokumen Spesifikasi Kebutuhan Perangkat Lunak (Software Requirements Specification / SRS) ini dibuat sebagai pedoman dalam pembuatan perangkat lunak <b>School Class Progress (SCP) Web & Mobile Platform</b> pada Uji Kompetensi Keahlian (UKK) Rekayasa Perangkat Lunak (RPL) Paket 3 Tahun 2026. Dokumen ini berisi penjabaran mengenai kebutuhan pengguna serta perancangan yang dibuat berdasarkan kebutuhan operasional sekolah kejuruan. Secara spesifik, dokumen ini bertujuan untuk:', styles['Normal']))
    story.append(Spacer(1, 6))
    story.append(Paragraph('1. Menjelaskan hak akses dan karakteristik dari peran pengguna (Waka Kurikulum, Guru Pengajar & Wali Kelas, Staff Kesiswaan, serta Siswa & Orang Tua).', styles['Bullet']))
    story.append(Paragraph('2. Menjabarkan logika bisnis utama sistem jadwal blok 2-mingguan, meliputi pencegahan jadwal bentrok (conflict prevention), saklar putaran blok (cycle toggle), penguncian jadwal (lock schedule), presensi otomatis, sinkronisasi perizinan dua arah, serta peringatan keterlambatan pengisian jurnal mengajar.', styles['Bullet']))
    story.append(Paragraph('3. Menjadi acuan teknis bagi pengembang (developer) dan tim penguji dalam memvalidasi kesesuaian fitur aplikasi web (Laravel Blade) maupun layanan REST API (Laravel Sanctum) untuk aplikasi seluler (Flutter) terhadap kebutuhan operasional sekolah.', styles['Bullet']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_1_2', store))
    story.append(Paragraph('1.2 Definisi dan Istilah', styles['H2']))
    story.append(Paragraph('Guna memberikan gambaran yang sama terhadap beberapa definisi, istilah, dan singkatan yang digunakan di dokumen ini, perlu dijelaskan sebagaimana berikut:', styles['Normal']))
    story.append(Spacer(1, 6))
    story.append(create_table(        [
            [Paragraph('Istilah', styles['TH']), Paragraph('Definisi', styles['TH'])],
            [Paragraph('SCP', styles['TDBold']), Paragraph('School Class Progress, nama sistem platform manajemen KBM, jurnal mengajar, dan presensi yang dikembangkan.', styles['TD'])],
            [Paragraph('Jadwal Blok (Siklus 1 & 2)', styles['TDBold']), Paragraph('Skema rotasi kegiatan belajar mengajar berkala (dua mingguan) antara mata pelajaran kejuruan produktif dan normatif-adaptif di SMK.', styles['TD'])],
            [Paragraph('RBAC', styles['TDBold']), Paragraph('Role-Based Access Control, mekanisme pembatasan hak akses sistem berdasarkan peran pengguna yang terverifikasi.', styles['TD'])],
            [Paragraph('NIP / NIS', styles['TDBold']), Paragraph('Nomor Induk Pegawai (identitas Guru/Staf) dan Nomor Induk Siswa (identitas akun Siswa).', styles['TD'])],
            [Paragraph('Rombel', styles['TDBold']), Paragraph('Rombongan Belajar atau entitas Kelas (Classroom) tempat siswa didaftarkan.', styles['TD'])],
            [Paragraph('Validasi Bentrokan', styles['TDBold']), Paragraph('Pemeriksaan otomatis oleh sistem untuk memastikan tidak ada irisan waktu jadwal pada guru, ruangan, atau kelas yang sama.', styles['TD'])],
            [Paragraph('Approval', styles['TDBold']), Paragraph('Persetujuan resmi yang diberikan oleh pihak Kesiswaan terhadap pengajuan izin/sakit, mengubah status dari Pending menjadi Approved.', styles['TD'])],
            [Paragraph('Mobile-First', styles['TDBold']), Paragraph('Pendekatan desain antarmuka yang mengutamakan kenyamanan pengguna pada layar smartphone sebelum disesuaikan ke layar desktop.', styles['TD'])],
            [Paragraph('Leave Request', styles['TDBold']), Paragraph('Pengajuan dispensasi perizinan atau sakit oleh siswa yang disertai surat dokter atau bukti pendukung.', styles['TD'])],
            [Paragraph('Laravel Framework', styles['TDBold']), Paragraph('Framework backend PHP berbasis MVC yang digunakan sebagai pusat kendali logika bisnis dan penyedia REST API.', styles['TD'])],
            [Paragraph('Laravel Sanctum', styles['TDBold']), Paragraph('Sistem otentikasi berbasis token ringan untuk mengamankan komunikasi REST API antara backend dan aplikasi Flutter Mobile.', styles['TD'])],
            [Paragraph('Flutter SDK', styles['TDBold']), Paragraph('Framework UI multiplatform berbasis Dart untuk aplikasi mobile siswa dan guru pengajar.', styles['TD'])],
            [Paragraph('OTP (One-Time Password)', styles['TDBold']), Paragraph('Kode verifikasi 6 digit sekali pakai yang dikirimkan ke email terdaftar untuk proses pemulihan kata sandi mandiri.', styles['TD'])]
        ], [130, 374]))
    story.append(Spacer(1, 10))
    story.append(BookmarkFlowable('sec_1_3', store))
    story.append(Paragraph('1.3 Pembaca yang Disarankan Membaca Dokumen Ini', styles['H2']))
    story.append(Paragraph('Dokumen ini ditujukan kepada berbagai pihak dengan kepentingan yang berbeda sebagai berikut:', styles['Normal']))
    story.append(Spacer(1, 6))
    story.append(create_table(        [
            [Paragraph('Kategori Pembaca', styles['TH']), Paragraph('Pihak yang Disarankan', styles['TH']), Paragraph('Fokus Utama yang Dibaca', styles['TH'])],
            [Paragraph('Tim Pengembangan Inti', styles['TDBold']), Paragraph('Programmer / Developer', styles['TD']), Paragraph('Kebutuhan Fungsional (FR), Arsitektur Perangkat Lunak, Class Diagram / ERD, dan REST API.', styles['TD'])],
            [Paragraph('Tim Pengembangan Inti', styles['TDBold']), Paragraph('Analis Sistem', styles['TD']), Paragraph('Seluruh dokumen untuk memastikan kebutuhan bisnis diterjemahkan secara akurat ke spesifikasi teknis.', styles['TD'])],
            [Paragraph('Tim Pengembangan Inti', styles['TDBold']), Paragraph('Manajer Proyek', styles['TD']), Paragraph('Tujuan, Ruang Lingkup, Kebutuhan Non-Fungsional (NFR), dan Prioritas Implementasi fitur.', styles['TD'])],
            [Paragraph('Tim Kualitas & Dokumentasi', styles['TDBold']), Paragraph('Tester / QA', styles['TD']), Paragraph('Kebutuhan Fungsional, Kebutuhan Non-Fungsional, dan Skenario Pengujian (Use Case & Test Cases).', styles['TD'])],
            [Paragraph('Tim Kualitas & Dokumentasi', styles['TDBold']), Paragraph('Penulis Dokumentasi', styles['TD']), Paragraph('Definisi istilah, Alur Kerja, dan Panduan Pengguna untuk penyusunan User Manual.', styles['TD'])],
            [Paragraph('Kepentingan Bisnis', styles['TDBold']), Paragraph('Pengguna (Waka, Guru, Kesiswaan, Siswa)', styles['TD']), Paragraph('Ruang Lingkup dan Kebutuhan Fungsional yang relevan dengan peran masing-masing.', styles['TD'])]
        ], [110, 110, 284]))
    story.append(Spacer(1, 10))
    story.append(BookmarkFlowable('sec_1_4', store))
    story.append(Paragraph('1.4 Ruang Lingkup Sistem', styles['H2']))
    story.append(Paragraph('Perangkat lunak yang dijelaskan pada dokumen spesifikasi ini merupakan aplikasi yang membantu pelaksanaan proses bisnis di Sekolah Menengah Kejuruan (SMK) yang menerapkan model pembelajaran Teaching Factory (TeFa) dengan sistem <b>Jadwal Blok 2-Mingguan (Siklus 1 & Siklus 2)</b>. Aktivitas yang ditangani meliputi pengelolaan master data akademik, penyusunan jadwal mandiri dengan validasi bentrok, pencatatan jurnal mengajar harian guru beserta bukti foto kegiatan, pengajuan dan verifikasi perizinan siswa, sinkronisasi otomatis presensi harian, serta rekapitulasi dan pencetakan laporan kehadiran siswa secara terpusat.', styles['Normal']))
    story.append(Spacer(1, 6))
    story.append(Paragraph('<b>Tujuan Utama Proyek:</b>', styles['Normal']))
    story.append(Paragraph('• <b>Mengotomatisasi Validasi Bentrokan Jadwal:</b> Mencegah tabrakan pemakaian waktu pada ruangan, kelas, maupun guru pengajar melalui pemeriksaan irisan waktu yang ketat.', styles['Bullet']))
    story.append(Paragraph('• <b>Menjamin Kedisiplinan & Akuntabilitas Jurnal:</b> Memfasilitasi pencatatan materi pembelajaran harian secara real-time dilengkapi bukti foto suasana kelas dan indikator pengingat.', styles['Bullet']))
    story.append(Paragraph('• <b>Mengintegrasikan Presensi & Perizinan (Two-Way Auto-Sync):</b> Mengotomatisasi rekonsiliasi kehadiran siswa dengan surat izin/sakit yang telah disetujui staf Kesiswaan.', styles['Bullet']))
    story.append(Paragraph('• <b>Mempermudah Pengendalian Manajerial Sekolah:</b> Menyediakan saklar siklus blok (Cycle Toggle) dan penguncian jadwal (Schedule Lock) bagi Waka Kurikulum, serta laporan rekapitulasi presensi siap cetak.', styles['Bullet']))
    story.append(Spacer(1, 10))
    story.append(BookmarkFlowable('sec_1_5', store))
    story.append(Paragraph('1.5 Sistem Penomoran', styles['H2']))
    story.append(Paragraph('Guna mempermudah audiens dalam pengidentifikasian, diberlakukan aturan penomoran sebagai berikut:', styles['Normal']))
    story.append(Spacer(1, 6))
    story.append(create_table(        [
            [Paragraph('Hal / Bagian', styles['TH']), Paragraph('Aturan Penomoran', styles['TH']), Paragraph('Contoh', styles['TH'])],
            [Paragraph('Use Case', styles['TDBold']), Paragraph('Nomor berbentuk UC99, dimana 99 adalah nomor urut use case.', styles['TD']), Paragraph('UC1, UC2, UC3', styles['TD'])],
            [Paragraph('Kebutuhan Fungsional', styles['TDBold']), Paragraph('Nomor berbentuk KF99, dimana 99 adalah nomor urut kebutuhan fungsional.', styles['TD']), Paragraph('KF1, KF2, KF15', styles['TD'])],
            [Paragraph('Kebutuhan Non Fungsional', styles['TDBold']), Paragraph('Nomor berbentuk KnF99, dimana 99 adalah nomor urut kebutuhan non-fungsional.', styles['TD']), Paragraph('KnF1, KnF2, KnF10', styles['TD'])],
            [Paragraph('Form Antarmuka', styles['TDBold']), Paragraph('Nomor berbentuk F99, dimana 99 adalah nomor urut form/tampilan.', styles['TD']), Paragraph('F1, F2, F10', styles['TD'])],
            [Paragraph('Laporan', styles['TDBold']), Paragraph('Nomor berbentuk L99, dimana 99 adalah nomor urut dokumen cetak/laporan.', styles['TD']), Paragraph('L1, L2', styles['TD'])]
        ], [120, 260, 124]))
    story.append(Spacer(1, 10))
    story.append(BookmarkFlowable('sec_1_6', store))
    story.append(Paragraph('1.6 Referensi', styles['H2']))
    story.append(Paragraph('• IEEE Std. 830-1998 / IEEE Std. 1233-1998, <i>Guide for Developing System Requirements Specifications</i>.', styles['Bullet']))
    story.append(Paragraph('• IEEE (2002), <i>Software Requirements Engineering</i>, Second Edition, IEEE Computer Society Press.', styles['Bullet']))
    story.append(Paragraph('• Tegarden, Wixon, dan Dennis (2005), <i>Systems Analysis and Design with UML Version 2.0 and Object Oriented Approach</i>, John Wiley & Sons.', styles['Bullet']))
    story.append(Paragraph('• Dokumen Product Requirement Document (PRD) School Class Progress (SCP), Versi 1.0.0.', styles['Bullet']))
    story.append(Paragraph('• Dokumentasi Resmi Laravel (laravel.com/docs) untuk Laravel 11.x / 13.x dan Laravel Sanctum.', styles['Bullet']))
    story.append(Paragraph('• Dokumentasi Resmi Flutter Framework (flutter.dev/docs) untuk pengembangan aplikasi seluler.', styles['Bullet']))
    story.append(Spacer(1, 10))
    story.append(BookmarkFlowable('sec_1_7', store))
    story.append(Paragraph('1.7 Ikhtisar Dokumen', styles['H2']))
    story.append(Paragraph('Dokumen ini terbagi menjadi 6 bab utama dan 3 bagian appendix: <b>Bab 1 Pendahuluan</b> menjabarkan tujuan, definisi, ruang lingkup, dan aturan penomoran. <b>Bab 2 Deskripsi Umum Sistem</b> menguraikan perspektif produk, fungsi, klasifikasi peran, dan batasan implementasi. <b>Bab 3 Kebutuhan Antarmuka Eksternal</b> mendefinisikan antarmuka pengguna, perangkat keras, perangkat lunak, dan komunikasi. <b>Bab 4 Fitur Sistem</b> mendeskripsikan 13 modul fitur fungsional secara rinci dengan urutan stimulus/respons. <b>Bab 5 Kebutuhan Non-Fungsional</b> merinci kinerja, keamanan, kualitas, dan aturan bisnis sistem. <b>Bab 6 Kebutuhan Lain</b> merangkum struktur basis data, penyimpanan berkas, dan akun pengujian. <b>Appendix A, B, dan C</b> memuat model analisis UML, rancangan wireframe/UI, serta daftar TBD.', styles['Normal']))
    story.append(Spacer(1, 10))
    return story

def build_bab2(styles, store):
    story = []
    story.append(BookmarkFlowable('sec_2', store))
    story.append(Paragraph('2. Deskripsi Umum Sistem', styles['H1']))
    story.append(BookmarkFlowable('sec_2_1', store))
    story.append(Paragraph('2.1 Perspektif Perangkat Lunak', styles['H2']))
    story.append(Paragraph('SCP merupakan produk baru yang berdiri sendiri (standalone) yang berfungsi sebagai pusat kendali operasional sekolah (command center) terpadu. Sistem menggabungkan entri data harian guru, validasi tabrakan ruang dan waktu secara otomatis, rekonsiliasi presensi berbasis pengajuan izin resmi, serta pemantauan manajerial oleh Waka Kurikulum dan Kesiswaan secara real-time. SCP terdiri dari empat komponen utama: (1) portal web responsif berbasis Laravel Blade dan CSS untuk admin dan guru, (2) lapisan logika bisnis dan observer berbasis Laravel, (3) REST API berbasis Laravel Sanctum, dan (4) aplikasi seluler Flutter bagi siswa dan guru beserta basis data relasional MySQL.', styles['Normal']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_2_2', store))
    story.append(Paragraph('2.2 Fungsi Perangkat Lunak', styles['H2']))
    story.append(Paragraph('Fungsi utama yang disediakan oleh sistem dijabarkan pada tabel berikut:', styles['Normal']))
    story.append(Spacer(1, 6))
    story.append(create_table(        [
            [Paragraph('No', styles['TH']), Paragraph('Fungsi Perangkat Lunak', styles['TH']), Paragraph('Referensi Fitur', styles['TH'])],
            [Paragraph('1', styles['TD']), Paragraph('Melakukan autentikasi pengguna dengan multi-identitas (Email, NIP, atau NIS)', styles['TD']), Paragraph('4.1', styles['TD'])],
            [Paragraph('2', styles['TD']), Paragraph('Mengubah data profil pribadi dan memperbarui kata sandi', styles['TD']), Paragraph('4.1', styles['TD'])],
            [Paragraph('3', styles['TD']), Paragraph('Mengelola data master (guru, siswa, kelas/rombel, ruangan, mata pelajaran)', styles['TD']), Paragraph('4.2', styles['TD'])],
            [Paragraph('4', styles['TD']), Paragraph('Mengelola akun pengguna dan penugasan wali kelas binaan', styles['TD']), Paragraph('4.3', styles['TD'])],
            [Paragraph('5', styles['TD']), Paragraph('Menyusun jadwal mengajar dengan pencegahan bentrok otomatis (ruang, kelas, guru)', styles['TD']), Paragraph('4.4', styles['TD'])],
            [Paragraph('6', styles['TD']), Paragraph('Mengatur aktivasi siklus blok (Siklus 1/2) dan penguncian jadwal (Lock Schedule)', styles['TD']), Paragraph('4.5', styles['TD'])],
            [Paragraph('7', styles['TD']), Paragraph('Mengimpor data jadwal pelajaran secara massal dari berkas spreadsheet', styles['TD']), Paragraph('4.6', styles['TD'])],
            [Paragraph('8', styles['TD']), Paragraph('Mencatat jurnal mengajar harian beserta unggah bukti foto kegiatan kelas', styles['TD']), Paragraph('4.7', styles['TD'])],
            [Paragraph('9', styles['TD']), Paragraph('Mencatat presensi siswa otomatis dan menyinkronkan izin yang telah disetujui', styles['TD']), Paragraph('4.8', styles['TD'])],
            [Paragraph('10', styles['TD']), Paragraph('Mengajukan surat permohonan izin siswa dan memverifikasi dokumen izin oleh Kesiswaan', styles['TD']), Paragraph('4.9', styles['TD'])],
            [Paragraph('11', styles['TD']), Paragraph('Memberikan alert pengingat bagi jadwal yang belum dibuat jurnalnya', styles['TD']), Paragraph('4.10', styles['TD'])],
            [Paragraph('12', styles['TD']), Paragraph('Merekapitulasi dan mencetak laporan presensi resmi kelas binaan', styles['TD']), Paragraph('4.11', styles['TD'])],
            [Paragraph('13', styles['TD']), Paragraph('Menyediakan antarmuka REST API aman (Sanctum) untuk aplikasi Flutter Mobile', styles['TD']), Paragraph('4.12', styles['TD'])],
            [Paragraph('14', styles['TD']), Paragraph('Memfasilitasi reset kata sandi mandiri menggunakan kode verifikasi OTP email', styles['TD']), Paragraph('4.13', styles['TD'])]
        ], [30, 394, 80]))
    story.append(Spacer(1, 10))
    story.append(BookmarkFlowable('sec_2_3', store))
    story.append(Paragraph('2.3 Karakteristik dan Klasifikasi Pengguna', styles['H2']))
    story.append(Paragraph('Pengguna dalam sistem diklasifikasikan ke dalam 4 peran utama berdasarkan struktur operasional sekolah:', styles['Normal']))
    story.append(Spacer(1, 6))
    story.append(Paragraph('1. <b>Waka Kurikulum:</b> Penanggung jawab operasional akademik sekolah. Mengelola master data kurikulum, mengatur siklus blok, mengunci jadwal KBM, dan memantau kepatuhan guru dalam mengisi jurnal mengajar.', styles['Bullet']))
    story.append(Paragraph('2. <b>Guru Pengajar & Wali Kelas:</b> Pengguna dengan frekuensi penggunaan tertinggi. Guru menginput jadwal mandiri, mengisi jurnal kelas dan foto dokumentasi, serta mengabsen siswa. Guru yang ditugaskan sebagai Wali Kelas dapat memantau perizinan dan presensi siswa kelas binaannya.', styles['Bullet']))
    story.append(Paragraph('3. <b>Staff Kesiswaan:</b> Penanggung jawab kedisiplinan dan absensi siswa. Berwenang memverifikasi, menyetujui, atau menolak surat permohonan izin/sakit siswa, serta memantau data ketidakhadiran per jurusan.', styles['Bullet']))
    story.append(Paragraph('4. <b>Siswa & Orang Tua:</b> Pengguna akhir yang mengakses informasi jadwal, memantau riwayat presensi pribadi, serta mengajukan permohonan izin/sakit melalui aplikasi mobile maupun web.', styles['Bullet']))
    story.append(Spacer(1, 8))
    story.append(Paragraph('<b>Matriks Hak Akses Fitur Berdasarkan Peran:</b>', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(create_table(        [
            [Paragraph('Modul / Fitur', styles['TH']), Paragraph('Waka Kurikulum', styles['TH']), Paragraph('Guru Pengajar', styles['TH']), Paragraph('Wali Kelas', styles['TH']), Paragraph('Kesiswaan', styles['TH']), Paragraph('Siswa / Ortu', styles['TH'])],
            [Paragraph('Login & Ganti Password', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD'])],
            [Paragraph('Lupa Password via OTP', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD'])],
            [Paragraph('Kelola Master Data', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD'])],
            [Paragraph('Saklar Siklus Blok (Cycle Toggle)', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD'])],
            [Paragraph('Kunci Jadwal (Lock Schedule)', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD'])],
            [Paragraph('Input Jadwal Mandiri (+ Cek Bentrok)', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya (saat terbuka)', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD'])],
            [Paragraph('Input Jurnal Mengajar (+ Foto)', styles['TD']), Paragraph('Lihat saja', styles['TD']), Paragraph('Ya (buat/ubah)', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD'])],
            [Paragraph('Presensi Siswa di Jurnal', styles['TD']), Paragraph('Lihat saja', styles['TD']), Paragraph('Ya (sesi KBM)', styles['TD']), Paragraph('Lihat saja', styles['TD']), Paragraph('Lihat saja', styles['TD']), Paragraph('Tidak', styles['TD'])],
            [Paragraph('Pengajuan Surat Izin Siswa', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Bantu Siswa', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Ya (mandiri)', styles['TD'])],
            [Paragraph('Verifikasi & Approval Izin', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Monitoring', styles['TD']), Paragraph('Ya (Verifikator)', styles['TD']), Paragraph('Tidak', styles['TD'])],
            [Paragraph('Monitoring Siswa per Jurusan', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Kelas Binaan', styles['TD']), Paragraph('Ya (Semua)', styles['TD']), Paragraph('Tidak', styles['TD'])],
            [Paragraph('Laporan Rekap Presensi & Cetak', styles['TD']), Paragraph('Ya (Semua)', styles['TD']), Paragraph('Kelas Terkait', styles['TD']), Paragraph('Kelas Binaan', styles['TD']), Paragraph('Ya (Semua)', styles['TD']), Paragraph('Tidak', styles['TD'])],
            [Paragraph('Akses Layanan REST API Mobile', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Ya', styles['TD'])]
        ], [150, 70, 70, 70, 74, 70]))
    story.append(Spacer(1, 10))
    story.append(BookmarkFlowable('sec_2_4', store))
    story.append(Paragraph('2.4 Lingkungan Operasi', styles['H2']))
    story.append(Paragraph('• <b>Komputer Server:</b> Sistem Operasi Linux (Ubuntu/Debian) atau Windows Server (Laragon/Apache/Nginx), PHP 8.2+, MySQL 8.0+ / MariaDB 10.4+.', styles['Bullet']))
    story.append(Paragraph('• <b>Komputer Klien:</b> Komputer desktop, laptop, atau tablet dengan peramban modern (Google Chrome, Microsoft Edge, Mozilla Firefox, Safari).', styles['Bullet']))
    story.append(Paragraph('• <b>Perangkat Seluler Klien:</b> Smartphone berbasis Android (minimal Android 8.0 Oreo) atau iOS (minimal iOS 13) yang menjalankan aplikasi Flutter Mobile.', styles['Bullet']))
    story.append(Paragraph('• <b>Jaringan:</b> Jaringan Local Area Network (LAN) sekolah atau koneksi Internet dengan protokol aman HTTPS.', styles['Bullet']))
    story.append(Spacer(1, 10))
    story.append(BookmarkFlowable('sec_2_5', store))
    story.append(Paragraph('2.5 Batasan Desain dan Implementasi', styles['H2']))
    story.append(Paragraph('• Web portal dibangun menggunakan framework Laravel dengan arsitektur Blade templates dan Vanilla CSS terstruktur.', styles['Bullet']))
    story.append(Paragraph('• REST API menggunakan Laravel Sanctum untuk otentikasi Bearer Token bagi aplikasi seluler Flutter.', styles['Bullet']))
    story.append(Paragraph('• Basis data menggunakan MySQL dengan indeks komposit pada tabel jadwal guna mendukung validasi bentrok yang instan.', styles['Bullet']))
    story.append(Paragraph('• Berkas unggahan (foto kegiatan jurnal dan surat izin) dibatasi maksimal 2 MB dengan ekstensi .jpg, .jpeg, .png, atau .pdf.', styles['Bullet']))
    story.append(Paragraph('• Zona waktu server diselaraskan ke Waktu Indonesia Barat (WIB / UTC+7).', styles['Bullet']))
    story.append(Spacer(1, 10))
    story.append(BookmarkFlowable('sec_2_6', store))
    story.append(Paragraph('2.6 Dokumentasi bagi Pengguna', styles['H2']))
    story.append(Paragraph('Dokumentasi yang disediakan meliputi buku petunjuk pengguna (User Manual) per peran, panduan instalasi/deployment server, teks bantuan pada setiap form isian, serta dokumentasi endpoint REST API.', styles['Normal']))
    story.append(Spacer(1, 10))
    story.append(BookmarkFlowable('sec_2_7', store))
    story.append(Paragraph('2.7 Asumsi dan Ketergantungan', styles['H2']))
    story.append(Paragraph('Sistem beroperasi dengan asumsi ketersediaan koneksi jaringan yang stabil di lingkungan sekolah, kepemilikan perangkat kerja bagi guru, serta ketersediaan data awal guru dan siswa untuk inisialisasi awal melalui database seeder.', styles['Normal']))
    story.append(Spacer(1, 10))
    return story

def build_bab3(styles, store):
    story = []
    story.append(BookmarkFlowable('sec_3', store))
    story.append(Paragraph('3. Kebutuhan Antarmuka Eksternal', styles['H1']))
    story.append(BookmarkFlowable('sec_3_1', store))
    story.append(Paragraph('3.1 Antarmuka Pengguna', styles['H2']))
    story.append(Paragraph('Antarmuka pengguna SCP dirancang responsif, modern, dan mudah dipelajari. Daftar antarmuka utama adalah sebagai berikut:', styles['Normal']))
    story.append(Spacer(1, 6))
    story.append(create_table(        [
            [Paragraph('No', styles['TH']), Paragraph('Form / Halaman Antarmuka', styles['TH']), Paragraph('Nomor Form', styles['TH']), Paragraph('Peran yang Mengakses', styles['TH'])],
            [Paragraph('1', styles['TD']), Paragraph('Form Login Multi-Identitas', styles['TD']), Paragraph('F1', styles['TD']), Paragraph('Seluruh peran', styles['TD'])],
            [Paragraph('2', styles['TD']), Paragraph('Form Lupa Password & Verifikasi OTP', styles['TD']), Paragraph('F2', styles['TD']), Paragraph('Seluruh peran', styles['TD'])],
            [Paragraph('3', styles['TD']), Paragraph('Form Edit Profil & Kata Sandi', styles['TD']), Paragraph('F3', styles['TD']), Paragraph('Seluruh peran', styles['TD'])],
            [Paragraph('4', styles['TD']), Paragraph('Dashboard Waka Kurikulum (Kontrol Siklus Blok & Kunci Jadwal)', styles['TD']), Paragraph('F4', styles['TD']), Paragraph('Waka Kurikulum', styles['TD'])],
            [Paragraph('5', styles['TD']), Paragraph('Dashboard Guru (Jadwal Hari Ini & Alert Keterlambatan)', styles['TD']), Paragraph('F5', styles['TD']), Paragraph('Guru Pengajar', styles['TD'])],
            [Paragraph('6', styles['TD']), Paragraph('Dashboard Kesiswaan (Statistik Presensi & Verifikasi Izin)', styles['TD']), Paragraph('F6', styles['TD']), Paragraph('Kesiswaan', styles['TD'])],
            [Paragraph('7', styles['TD']), Paragraph('Dashboard Siswa (Jadwal Aktif & Histori Kehadiran)', styles['TD']), Paragraph('F7', styles['TD']), Paragraph('Siswa & Orang Tua', styles['TD'])],
            [Paragraph('8', styles['TD']), Paragraph('Form Master Data (Guru, Siswa, Kelas, Ruang, Mapel)', styles['TD']), Paragraph('F8', styles['TD']), Paragraph('Waka Kurikulum', styles['TD'])],
            [Paragraph('9', styles['TD']), Paragraph('Form Input Jadwal Mandiri (dengan Cek Bentrok Realtime)', styles['TD']), Paragraph('F9', styles['TD']), Paragraph('Guru, Waka Kurikulum', styles['TD'])],
            [Paragraph('10', styles['TD']), Paragraph('Tabel Penjadwalan dengan Filter Siklus Blok', styles['TD']), Paragraph('F10', styles['TD']), Paragraph('Guru, Waka Kurikulum', styles['TD'])],
            [Paragraph('11', styles['TD']), Paragraph('Form Jurnal Mengajar & Unggah Foto KBM', styles['TD']), Paragraph('F11', styles['TD']), Paragraph('Guru Pengajar', styles['TD'])],
            [Paragraph('12', styles['TD']), Paragraph('Tabel Presensi Siswa di Form Jurnal', styles['TD']), Paragraph('F12', styles['TD']), Paragraph('Guru Pengajar', styles['TD'])],
            [Paragraph('13', styles['TD']), Paragraph('Form Pengajuan Izin Siswa (+ Unggah Surat Bukti)', styles['TD']), Paragraph('F13', styles['TD']), Paragraph('Siswa, Wali Kelas', styles['TD'])],
            [Paragraph('14', styles['TD']), Paragraph('Daftar Verifikasi Perizinan & Modal Persetujuan', styles['TD']), Paragraph('F14', styles['TD']), Paragraph('Kesiswaan, Wali Kelas', styles['TD'])],
            [Paragraph('15', styles['TD']), Paragraph('Monitoring Siswa per Jurusan', styles['TD']), Paragraph('F15', styles['TD']), Paragraph('Kesiswaan, Waka Kurikulum', styles['TD'])],
            [Paragraph('16', styles['TD']), Paragraph('Laporan Rekapitulasi Presensi Kelas', styles['TD']), Paragraph('F16', styles['TD']), Paragraph('Waka, Guru, Kesiswaan', styles['TD'])],
            [Paragraph('17', styles['TD']), Paragraph('Halaman Cetak Laporan Presensi (Print Layout)', styles['TD']), Paragraph('F17', styles['TD']), Paragraph('Waka, Guru, Kesiswaan', styles['TD'])],
            [Paragraph('18', styles['TD']), Paragraph('Antarmuka Aplikasi Flutter Mobile (Jadwal & Izin)', styles['TD']), Paragraph('F18', styles['TD']), Paragraph('Siswa, Guru Pengajar', styles['TD'])]
        ], [26, 230, 68, 180]))
    story.append(Spacer(1, 10))
    story.append(BookmarkFlowable('sec_3_2', store))
    story.append(Paragraph('3.2 Antarmuka Perangkat Keras', styles['H2']))
    story.append(Paragraph('Perangkat keras yang dibutuhkan terdiri dari Komputer Server (CPU multi-core, RAM min 4GB, SSD), Komputer Klien (Laptop/PC), Perangkat Seluler (Smartphone Android/iOS), Printer A4 untuk cetak laporan, serta perangkat jaringan LAN/Wi-Fi.', styles['Normal']))
    story.append(Spacer(1, 6))
    story.append(get_image_flowable('extracted_imgs/p15_0_IM144.jpg', 440, 245))
    story.append(Paragraph('Gambar 3.1 Gambaran Antarmuka Perangkat Keras', styles['Caption']))
    story.append(Spacer(1, 10))
    story.append(BookmarkFlowable('sec_3_3', store))
    story.append(Paragraph('3.3 Antarmuka Perangkat Lunak', styles['H2']))
    story.append(Paragraph('Komponen perangkat lunak yang terpasang pada komputer server dan klien ditunjukkan pada tabel berikut:', styles['Normal']))
    story.append(Spacer(1, 6))
    story.append(create_table(        [
            [Paragraph('Komponen', styles['TH']), Paragraph('Spesifikasi', styles['TH']), Paragraph('Fungsi dalam Sistem', styles['TH'])],
            [Paragraph('Sistem Operasi Server', styles['TDBold']), Paragraph('Linux Ubuntu / Windows Server', styles['TD']), Paragraph('Menjalankan web server dan layanan basis data', styles['TD'])],
            [Paragraph('PHP Runtime', styles['TDBold']), Paragraph('PHP 8.2 atau lebih baru', styles['TD']), Paragraph('Bahasa pemrograman utama backend Laravel', styles['TD'])],
            [Paragraph('Laravel Framework', styles['TDBold']), Paragraph('Versi 11.x / 13.x', styles['TD']), Paragraph('Penyedia routing, Eloquent ORM, auth, dan REST API', styles['TD'])],
            [Paragraph('Laravel Sanctum', styles['TDBold']), Paragraph('Versi kompatibel', styles['TD']), Paragraph('Autentikasi token untuk REST API aplikasi mobile', styles['TD'])],
            [Paragraph('Database Engine', styles['TDBold']), Paragraph('MySQL 8.0+ / MariaDB 10.4+', styles['TD']), Paragraph('Penyimpanan data relasional transaksional', styles['TD'])],
            [Paragraph('Web Server', styles['TDBold']), Paragraph('Nginx / Apache 2.4+', styles['TD']), Paragraph('Melayani protokol HTTP/HTTPS', styles['TD'])],
            [Paragraph('Mobile Framework', styles['TDBold']), Paragraph('Flutter SDK 3.x (Dart)', styles['TD']), Paragraph('Antarmuka aplikasi seluler bagi Siswa dan Guru', styles['TD'])],
            [Paragraph('Peramban Klien', styles['TDBold']), Paragraph('Chrome, Edge, Firefox, Safari', styles['TD']), Paragraph('Akses portal web oleh pengguna', styles['TD'])]
        ], [120, 140, 244]))
    story.append(Spacer(1, 6))
    story.append(get_image_flowable('extracted_imgs/p16_0_IM148.jpg', 440, 245))
    story.append(Paragraph('Gambar 3.2 Diagram Arsitektur Komponen Perangkat Lunak', styles['Caption']))
    story.append(Spacer(1, 10))
    story.append(BookmarkFlowable('sec_3_4', store))
    story.append(Paragraph('3.4 Antarmuka Komunikasi', styles['H2']))
    story.append(Paragraph('Komunikasi data antara klien web/mobile dan server dilakukan melalui jaringan komputer dengan ketentuan:', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(Paragraph('• Protokol HTTP/HTTPS standar melalui port 80/443.', styles['Bullet']))
    story.append(Paragraph('• Proteksi CSRF Token pada setiap form transmisi data pada portal web.', styles['Bullet']))
    story.append(Paragraph('• Autentikasi Bearer Token (Laravel Sanctum) pada setiap panggilan REST API mobile dengan format data JSON.', styles['Bullet']))
    story.append(Paragraph('• Unggahan berkas media foto dan PDF menggunakan format multipart/form-data dengan batas ukuran 2 MB.', styles['Bullet']))
    story.append(Spacer(1, 6))
    story.append(get_image_flowable('extracted_imgs/p17_0_IM152.jpg', 440, 245))
    story.append(Paragraph('Gambar 3.3 Diagram Antarmuka Komunikasi Jaringan', styles['Caption']))
    story.append(Spacer(1, 10))
    return story

print("Part 1 builder loaded.")
