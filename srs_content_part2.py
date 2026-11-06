"""
Part 2 of SRS Content: Bab 4 (Fitur Sistem), Bab 5 (Non Fungsional), Bab 6 (Kebutuhan Lain)
Disusun untuk School Class Progress (SCP)
Pengembang: Mahirsya Bhanu Adyatma (Absen 22)
"""

from reportlab.platypus import Paragraph, Spacer, PageBreak, KeepTogether
from reportlab.lib import colors
from srs_base import BookmarkFlowable, create_table

def build_bab4(styles, store):
    story = []
    story.append(BookmarkFlowable('sec_4', store))
    story.append(Paragraph('4. Fitur Sistem', styles['H1']))
    story.append(Paragraph('Berikut dijelaskan fitur-fitur fungsional pada perangkat lunak SCP. Setiap fitur diuraikan berdasarkan deskripsi dan prioritas, urutan stimulus dan respons, serta butir kebutuhan fungsional (KF).', styles['Normal']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_4_1', store))
    story.append(Paragraph('4.1 Autentikasi dan Manajemen Profile', styles['H2']))
    story.append(Paragraph('<b>4.1.1 Description and Priority</b>', styles['H3']))
    story.append(Paragraph('Fitur ini memungkinkan seluruh pengguna masuk ke sistem menggunakan kredensial akun terdaftar (email/NIP/NIS) dan mengarahkan pengguna ke dashboard sesuai perannya, serta mengelola profil pribadi secara mandiri.<br/><b>Prioritas:</b> Tinggi', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.1.2 Stimulus/Response Sequences</b>', styles['H3']))
    story.append(create_table(        [
            [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
            [Paragraph('Pengguna memasukkan email/NIP/NIS dan kata sandi di halaman login.', styles['TD']), Paragraph('Sistem memverifikasi kredensial dan mengarahkan ke dashboard sesuai role pengguna.', styles['TD'])],
            [Paragraph('Pengguna memasukkan kredensial yang salah.', styles['TD']), Paragraph('Sistem menampilkan pesan galat umum tanpa mengungkap status keberadaan identitas.', styles['TD'])],
            [Paragraph('Pengguna membuka menu Profil dan mengubah nomor telepon atau foto profil.', styles['TD']), Paragraph('Sistem menyimpan perubahan dan menampilkan notifikasi keberhasilan.', styles['TD'])],
            [Paragraph('Pengguna mengganti kata sandi.', styles['TD']), Paragraph('Sistem memvalidasi kata sandi lama, memeriksa kecocokan konfirmasi kata sandi baru, dan menyimpannya.', styles['TD'])],
            [Paragraph('Sesi pengguna kedaluwarsa.', styles['TD']), Paragraph('Sistem otomatis menghentikan sesi dan mengarahkan ke halaman login.', styles['TD'])]
        ], [230, 274]))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.1.3 Functional Requirements</b>', styles['H3']))
    story.append(Paragraph('<b>KF1.</b> Sistem harus menyediakan form login dengan autentikasi multi-identitas (Email, NIP Guru, atau NIS Siswa).', styles['Bullet']))
    story.append(Paragraph('<b>KF2.</b> Sistem harus mengarahkan pengguna langsung ke panel dashboard yang sesuai dengan hak akses perannya.', styles['Bullet']))
    story.append(Paragraph('<b>KF3.</b> Sistem harus menampilkan pesan galat yang aman apabila kredensial tidak valid.', styles['Bullet']))
    story.append(Paragraph('<b>KF4.</b> Sistem harus menyediakan halaman Profil untuk memperbarui nomor telepon, alamat, dan foto profil pengguna.', styles['Bullet']))
    story.append(Paragraph('<b>KF5.</b> Sistem harus memvalidasi pergantian kata sandi dengan verifikasi kata sandi lama.', styles['Bullet']))
    story.append(Paragraph('<b>KF6.</b> Sistem harus melakukan logout otomatis ketika sesi pengguna kedaluwarsa.', styles['Bullet']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_4_2', store))
    story.append(Paragraph('4.2 Manajemen Data Master', styles['H2']))
    story.append(Paragraph('<b>4.2.1 Description and Priority</b>', styles['H3']))
    story.append(Paragraph('Fitur ini memungkinkan Waka Kurikulum mengelola entitas master akademik yang menjadi dasar operasional KBM: Guru, Siswa, Kelas (Rombel), Ruangan/Lab, dan Mata Pelajaran.<br/><b>Prioritas:</b> Tinggi', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.2.2 Stimulus/Response Sequences</b>', styles['H3']))
    story.append(create_table(        [
            [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
            [Paragraph('Waka Kurikulum menambah data rombel baru (contoh: XII RPL 1).', styles['TD']), Paragraph('Sistem memvalidasi keunikan nama kelas dan menyimpannya ke tabel classes.', styles['TD'])],
            [Paragraph('Waka Kurikulum mendaftarkan ruangan baru (Lab 16 RPL).', styles['TD']), Paragraph('Sistem menyimpan ruangan dan menampilkannya pada opsi pilihan form jadwal.', styles['TD'])],
            [Paragraph('Waka Kurikulum menghapus master data yang sedang berelasi aktif.', styles['TD']), Paragraph('Sistem menolak penghapusan dengan memunculkan pesan peringatan bahwa data masih digunakan.', styles['TD'])]
        ], [230, 274]))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.2.3 Functional Requirements</b>', styles['H3']))
    story.append(Paragraph('<b>KF7.</b> Sistem harus menyediakan operasi CRUD untuk data kelas/rombel lengkap dengan tingkatan dan jurusan.', styles['Bullet']))
    story.append(Paragraph('<b>KF8.</b> Sistem harus menyediakan operasi CRUD untuk data ruangan/laboratorium pembelajaran.', styles['Bullet']))
    story.append(Paragraph('<b>KF9.</b> Sistem harus menyediakan operasi CRUD untuk data mata pelajaran kejuruan produktif dan normatif-adaptif.', styles['Bullet']))
    story.append(Paragraph('<b>KF10.</b> Sistem harus menyediakan operasi CRUD untuk data profil guru lengkap dengan NIP dan kontak.', styles['Bullet']))
    story.append(Paragraph('<b>KF11.</b> Sistem harus menyediakan operasi CRUD untuk data siswa lengkap dengan NIS, kelas, dan orang tua.', styles['Bullet']))
    story.append(Paragraph('<b>KF12.</b> Sistem harus mencegah penghapusan master data yang telah berelasi dengan jadwal atau jurnal aktif.', styles['Bullet']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_4_3', store))
    story.append(Paragraph('4.3 Manajemen Pengguna dan Akun', styles['H2']))
    story.append(Paragraph('<b>4.3.1 Description and Priority</b>', styles['H3']))
    story.append(Paragraph('Fitur ini mengatur pembuatan akun pengguna, penentuan role sistem (waka, guru, kesiswaan, siswa), serta penugasan guru sebagai Wali Kelas pada rombel binaan.<br/><b>Prioritas:</b> Tinggi', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.3.2 Stimulus/Response Sequences</b>', styles['H3']))
    story.append(create_table(        [
            [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
            [Paragraph('Waka Kurikulum mendaftarkan akun guru dan mengaitkannya ke profil guru.', styles['TD']), Paragraph('Sistem menyimpan akun pengguna dengan role guru dan relasi user_id.', styles['TD'])],
            [Paragraph('Waka Kurikulum menetapkan guru tertentu sebagai Wali Kelas XII RPL 2.', styles['TD']), Paragraph('Sistem memperbarui field homeroom_teacher_id pada entitas rombel kelas terkait.', styles['TD'])],
            [Paragraph('Pengguna tanpa hak akses mencoba membuka route terproteksi via URL.', styles['TD']), Paragraph('Sistem menolak akses dan menampilkan halaman peringatan 403 Forbidden.', styles['TD'])]
        ], [230, 274]))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.3.3 Functional Requirements</b>', styles['H3']))
    story.append(Paragraph('<b>KF13.</b> Sistem harus menyediakan manajemen akun pengguna dengan atribut nama, email, password, role, dan telepon.', styles['Bullet']))
    story.append(Paragraph('<b>KF14.</b> Sistem harus mendukung 4 role utama: waka, guru, kesiswaan, dan siswa.', styles['Bullet']))
    story.append(Paragraph('<b>KF15.</b> Sistem harus mengaitkan akun guru dengan tugas Wali Kelas pada tabel classes.', styles['Bullet']))
    story.append(Paragraph('<b>KF16.</b> Sistem harus mengaitkan akun siswa dengan rombel kelas pada tabel students.', styles['Bullet']))
    story.append(Paragraph('<b>KF17.</b> Sistem harus menegakkan pembatasan hak akses berbasis middleware role pada route web dan API.', styles['Bullet']))
    story.append(Paragraph('<b>KF18.</b> Sistem harus mengenkripsi kata sandi menggunakan hashing Bcrypt bawaan Laravel.', styles['Bullet']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_4_4', store))
    story.append(Paragraph('4.4 Penjadwalan Pelajaran dan Pencegahan Bentrok', styles['H2']))
    story.append(Paragraph('<b>4.4.1 Description and Priority</b>', styles['H3']))
    story.append(Paragraph('Fitur ini memfasilitasi pembuatan jadwal mengajar dengan pemeriksaan otomatis irisan waktu terhadap tiga dimensi (ruangan, kelas, dan guru). Jadwal yang bertabrakan otomatis ditolak.<br/><b>Prioritas:</b> Tinggi', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.4.2 Stimulus/Response Sequences</b>', styles['H3']))
    story.append(create_table(        [
            [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
            [Paragraph('Guru memilih mapel, kelas, ruangan, hari, jam mulai, dan siklus blok.', styles['TD']), Paragraph('Sistem menghitung perkiraan jam selesai dan menampilkan preview jadwal.', styles['TD'])],
            [Paragraph('Guru menyimpan jadwal yang tidak bertabrakan dengan jadwal lain.', styles['TD']), Paragraph('Sistem menyimpan jadwal dan menampilkan notifikasi sukses.', styles['TD'])],
            [Paragraph('Guru menyimpan jadwal pada ruangan yang sedang dipakai jadwal lain.', styles['TD']), Paragraph('Sistem membatalkan simpan dan memunculkan pesan galat tabrakan ruangan.', styles['TD'])],
            [Paragraph('Guru menyimpan jadwal pada rombel kelas yang sedang belajar mapel lain.', styles['TD']), Paragraph('Sistem membatalkan simpan dan memunculkan pesan galat tabrakan kelas.', styles['TD'])]
        ], [230, 274]))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.4.3 Functional Requirements</b>', styles['H3']))
    story.append(Paragraph('<b>KF19.</b> Form jadwal harus memuat atribut guru, kelas, mapel, ruangan, hari, jam mulai, jam selesai, dan siklus blok.', styles['Bullet']))
    story.append(Paragraph('<b>KF20.</b> Jam mulai dan jam selesai harus dipilih dalam format waktu yang valid dengan kelipatan menit terstandar.', styles['Bullet']))
    story.append(Paragraph('<b>KF21.</b> Sistem harus memeriksa bentrokan dengan rumus irisan waktu: (Start_A < End_B) dan (End_A > Start_B).', styles['Bullet']))
    story.append(Paragraph('<b>KF22.</b> Pemeriksaan bentrokan wajib memvalidasi tiga dimensi: guru (teacher_id), kelas (class_id), dan ruangan (room_id).', styles['Bullet']))
    story.append(Paragraph('<b>KF23.</b> Pesan galat wajib menjelaskan secara spesifik dimensi mana yang mengalami tabrakan.', styles['Bullet']))
    story.append(Paragraph('<b>KF24.</b> Sistem harus menyediakan filter jadwal berdasarkan Siklus 1, Siklus 2, kelas, dan hari.', styles['Bullet']))
    story.append(Paragraph('<b>KF25.</b> Guru hanya dapat mengelola jadwal miliknya, sedangkan Waka Kurikulum dapat mengelola seluruh jadwal.', styles['Bullet']))
    story.append(Paragraph('<b>KF26.</b> Indeks komposit harus diterapkan pada database untuk menjaga performa validasi bentrokan.', styles['Bullet']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_4_5', store))
    story.append(Paragraph('4.5 Pengaturan Siklus Blok dan Kunci Jadwal', styles['H2']))
    story.append(Paragraph('<b>4.5.1 Description and Priority</b>', styles['H3']))
    story.append(Paragraph('Fitur manajerial bagi Waka Kurikulum untuk mengaktifkan siklus blok (Putaran 1 / Putaran 2) serta saklar kunci jadwal (Lock Schedule) agar jadwal KBM tidak dapat diubah setelah finalisasi.<br/><b>Prioritas:</b> Tinggi', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.5.2 Stimulus/Response Sequences</b>', styles['H3']))
    story.append(create_table(        [
            [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
            [Paragraph('Waka Kurikulum menekan tombol Ganti ke Putaran 2.', styles['TD']), Paragraph('Sistem mengaktifkan siklus 2 dan langsung memfilter seluruh tampilan jadwal serta dashboard.', styles['TD'])],
            [Paragraph('Waka Kurikulum menekan tombol Kunci Jadwal.', styles['TD']), Paragraph('Sistem menyetel status locked dan menonaktifkan tombol tambah/ubah jadwal bagi guru.', styles['TD'])],
            [Paragraph('Guru mencoba mengirim request POST jadwal saat status terkunci.', styles['TD']), Paragraph('Sistem menolak dengan respon HTTP 403: Jadwal pelajaran sedang dikunci oleh Waka Kurikulum.', styles['TD'])]
        ], [230, 274]))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.5.3 Functional Requirements</b>', styles['H3']))
    story.append(Paragraph('<b>KF27.</b> Sistem harus menyediakan saklar aktivasi siklus blok (Putaran 1 atau Putaran 2) pada dashboard Waka Kurikulum.', styles['Bullet']))
    story.append(Paragraph('<b>KF28.</b> Penggantian siklus aktif langsung memperbarui filter jadwal harian dan form jurnal secara real-time.', styles['Bullet']))
    story.append(Paragraph('<b>KF29.</b> Sistem harus menyediakan saklar kunci jadwal (Lock Schedule) yang hanya dapat diakses Waka Kurikulum.', styles['Bullet']))
    story.append(Paragraph('<b>KF30.</b> Saat jadwal terkunci, sistem wajib menonaktifkan aksi simpan, ubah, dan hapus jadwal bagi peran Guru.', styles['Bullet']))
    story.append(Paragraph('<b>KF31.</b> Kebijakan proteksi kunci jadwal wajib ditegakkan pada tingkat Policy backend Laravel maupun antarmuka.', styles['Bullet']))
    story.append(Paragraph('<b>KF32.</b> Sistem mencatat timestamp dan user ID pihak yang melakukan perubahan status siklus atau kunci jadwal.', styles['Bullet']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_4_6', store))
    story.append(Paragraph('4.6 Import Jadwal Pelajaran', styles['H2']))
    story.append(Paragraph('<b>4.6.1 Description and Priority</b>', styles['H3']))
    story.append(Paragraph('Fitur untuk mengunggah dan mengimpor jadwal mengajar secara massal dari berkas spreadsheet (Excel/CSV) guna mempercepat proses input jadwal awal semester.<br/><b>Prioritas:</b> Sedang', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.6.2 Stimulus/Response Sequences</b>', styles['H3']))
    story.append(create_table(        [
            [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
            [Paragraph('Pengguna mengunggah berkas template jadwal dalam format .csv atau .xlsx.', styles['TD']), Paragraph('Sistem memvalidasi struktur kolom, memproses setiap baris jadwal, dan melakukan cek bentrok.', styles['TD'])],
            [Paragraph('Berkas berisi jadwal yang bentrok dengan jadwal lain.', styles['TD']), Paragraph('Sistem mencatat baris tersebut sebagai error dan menampilkan ringkasan data yang gagal disimpan.', styles['TD'])],
            [Paragraph('Pengguna mengunduh template CSV.', styles['TD']), Paragraph('Sistem menyediakan berkas template standar yang siap diisi.', styles['TD'])]
        ], [230, 274]))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.6.3 Functional Requirements</b>', styles['H3']))
    story.append(Paragraph('<b>KF33.</b> Sistem harus mendukung pembacaan berkas import dalam format .xlsx dan .csv.', styles['Bullet']))
    story.append(Paragraph('<b>KF34.</b> Sistem harus mengenali kolom data Guru/NIP, Mapel, Kelas, Ruang, Hari, Jam, dan Siklus Blok.', styles['Bullet']))
    story.append(Paragraph('<b>KF35.</b> Setiap baris jadwal hasil import wajib melalui validasi bentrokan waktu yang sama dengan form mandiri.', styles['Bullet']))
    story.append(Paragraph('<b>KF36.</b> Sistem menyediakan tombol unduh format template import bagi pengguna.', styles['Bullet']))
    story.append(Paragraph('<b>KF37.</b> Guru hanya dapat mengimpor jadwal miliknya sendiri, sedangkan Waka Kurikulum dapat mengimpor jadwal semua guru.', styles['Bullet']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_4_7', store))
    story.append(Paragraph('4.7 Jurnal Mengajar dan Bukti Foto Kegiatan', styles['H2']))
    story.append(Paragraph('<b>4.7.1 Description and Priority</b>', styles['H3']))
    story.append(Paragraph('Fitur bagi guru pengajar untuk mencatat materi pembelajaran, ringkasan aktivitas, catatan kelas, dan mengunggah foto dokumentasi kelas sebagai bukti riil terlaksananya KBM.<br/><b>Prioritas:</b> Tinggi', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.7.2 Stimulus/Response Sequences</b>', styles['H3']))
    story.append(create_table(        [
            [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
            [Paragraph('Guru membuka form jurnal dan memilih jadwal mengajar hari ini.', styles['TD']), Paragraph('Sistem memuat informasi jadwal, kelas, dan otomatis menampilkan daftar presensi siswa.', styles['TD'])],
            [Paragraph('Guru mengisi materi KBM, mengunggah foto dokumentasi kelas, dan menyimpan.', styles['TD']), Paragraph('Sistem menyimpan data jurnal, menyimpan berkas foto, dan memicu sinkronisasi presensi.', styles['TD'])],
            [Paragraph('Guru mencoba menyimpan jurnal tanpa mengunggah foto bukti.', styles['TD']), Paragraph('Sistem menampilkan validasi bahwa foto bukti kelas wajib disertakan.', styles['TD'])]
        ], [230, 274]))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.7.3 Functional Requirements</b>', styles['H3']))
    story.append(Paragraph('<b>KF38.</b> Form jurnal harus menampilkan jadwal mengajar milik guru yang bersangkutan pada siklus blok aktif.', styles['Bullet']))
    story.append(Paragraph('<b>KF39.</b> Form jurnal wajib memuat field tanggal, materi pokok, deskripsi aktivitas, catatan kendala, dan foto bukti.', styles['Bullet']))
    story.append(Paragraph('<b>KF40.</b> Foto bukti kegiatan kelas berukuran maksimal 2 MB dengan format .jpg, .jpeg, atau .png.', styles['Bullet']))
    story.append(Paragraph('<b>KF41.</b> Foto kegiatan kelas disimpan secara aman pada storage publik dengan nama berkas unik.', styles['Bullet']))
    story.append(Paragraph('<b>KF42.</b> Penyimpanan jurnal berhasil harus otomatis memicu inisialisasi presensi siswa sesuai jadwal KBM.', styles['Bullet']))
    story.append(Paragraph('<b>KF43.</b> Waka Kurikulum memiliki hak akses untuk memantau seluruh jurnal guru dalam mode baca (view only).', styles['Bullet']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_4_8', store))
    story.append(Paragraph('4.8 Presensi dan Sinkronisasi Otomatis Dua Arah', styles['H2']))
    story.append(Paragraph('<b>4.8.1 Description and Priority</b>', styles['H3']))
    story.append(Paragraph('Fitur pencatatan kehadiran siswa yang terintegrasi dua arah dengan modul perizinan. Presensi dibuat otomatis berstatus Hadir, dan secara cerdas disinkronkan dengan permohonan izin/sakit yang telah disetujui.<br/><b>Prioritas:</b> Tinggi', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.8.2 Stimulus/Response Sequences</b>', styles['H3']))
    story.append(create_table(        [
            [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
            [Paragraph('Jurnal KBM baru disimpan oleh guru.', styles['TD']), Paragraph('Sistem membuat record presensi untuk seluruh siswa di kelas dengan status default Hadir, kecuali siswa yang telah memiliki izin Approved.', styles['TD'])],
            [Paragraph('Staff Kesiswaan menyetujui izin siswa setelah jurnal dibuat.', styles['TD']), Paragraph('Sistem otomatis memperbarui status presensi siswa terkait menjadi Sakit atau Izin pada tanggal tersebut.', styles['TD'])],
            [Paragraph('Guru mengubah status presensi siswa yang alpa atau terlambat.', styles['TD']), Paragraph('Sistem menyimpan pembaruan status presensi beserta catatan khusus dari guru.', styles['TD'])]
        ], [230, 274]))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.8.3 Functional Requirements</b>', styles['H3']))
    story.append(Paragraph('<b>KF44.</b> Sistem otomatis mencatat presensi seluruh siswa di rombel kelas saat jurnal KBM pertama kali dibuat.', styles['Bullet']))
    story.append(Paragraph('<b>KF45.</b> Status default presensi awal adalah Hadir.', styles['Bullet']))
    story.append(Paragraph('<b>KF46.</b> Siswa yang memiliki surat izin berstatus Approved pada tanggal KBM otomatis dicatat sebagai Sakit atau Izin.', styles['Bullet']))
    story.append(Paragraph('<b>KF47.</b> Perubahan status persetujuan izin di kemudian hari otomatis memperbarui record presensi pada jurnal terkait.', styles['Bullet']))
    story.append(Paragraph('<b>KF48.</b> Guru pengajar dapat memperbarui status kehadiran siswa secara manual (Hadir, Sakit, Izin, Alpa) di form jurnal.', styles['Bullet']))
    story.append(Paragraph('<b>KF49.</b> Database mencegah duplikasi absensi siswa pada jadwal dan tanggal yang sama melalui unique constraint.', styles['Bullet']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_4_9', store))
    story.append(Paragraph('4.9 Pengajuan dan Verifikasi Izin Siswa', styles['H2']))
    story.append(Paragraph('<b>4.9.1 Description and Priority</b>', styles['H3']))
    story.append(Paragraph('Fitur pengajuan permohonan izin/sakit secara online oleh siswa disertai unggah surat bukti, yang kemudian diverifikasi dan diputuskan (Approve/Reject) oleh staf Kesiswaan.<br/><b>Prioritas:</b> Tinggi', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.9.2 Stimulus/Response Sequences</b>', styles['H3']))
    story.append(create_table(        [
            [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
            [Paragraph('Siswa mengisi form izin, memilih tanggal dan tipe (Izin/Sakit), serta mengunggah surat bukti.', styles['TD']), Paragraph('Sistem menyimpan pengajuan dengan status awal Pending dan memunculkan notifikasi sukses.', styles['TD'])],
            [Paragraph('Staff Kesiswaan memeriksa berkas bukti pada antrean verifikasi izin.', styles['TD']), Paragraph('Sistem menampilkan preview detail surat keterangan dokter atau surat izin orang tua.', styles['TD'])],
            [Paragraph('Staff Kesiswaan menekan tombol Approve.', styles['TD']), Paragraph('Sistem mengubah status menjadi Approved, merekam verified_by, dan menyinkronkan status presensi.', styles['TD'])],
            [Paragraph('Staff Kesiswaan menekan tombol Reject dan memberi alasan.', styles['TD']), Paragraph('Sistem mengubah status menjadi Rejected beserta catatan evaluasi kesiswaan.', styles['TD'])]
        ], [230, 274]))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.9.3 Functional Requirements</b>', styles['H3']))
    story.append(Paragraph('<b>KF50.</b> Sistem menyediakan formulir permohonan izin dengan pilihan jenis: Sakit atau Izin.', styles['Bullet']))
    story.append(Paragraph('<b>KF51.</b> Formulir pengajuan wajib memuat tanggal izin, keterangan alasan, serta unggahan berkas bukti (PDF/gambar).', styles['Bullet']))
    story.append(Paragraph('<b>KF52.</b> Pengajuan izin baru otomatis mendapatkan status awal Pending.', styles['Bullet']))
    story.append(Paragraph('<b>KF53.</b> Halaman Kesiswaan menampilkan daftar perizinan masuk dilengkapi fitur filter status dan kelas.', styles['Bullet']))
    story.append(Paragraph('<b>KF54.</b> Kesiswaan dapat menyetujui (Approve) atau menolak (Reject) perizinan disertai modal konfirmasi.', styles['Bullet']))
    story.append(Paragraph('<b>KF55.</b> Sistem menyimpan user ID staf kesiswaan pemverifikasi dan waktu verifikasi secara otomatis.', styles['Bullet']))
    story.append(Paragraph('<b>KF56.</b> Wali Kelas dapat memantau status permohonan izin siswa di kelas binaannya secara real-time.', styles['Bullet']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_4_10', store))
    story.append(Paragraph('4.10 Alert Keterlambatan Pengisian Jurnal Mengajar', styles['H2']))
    story.append(Paragraph('<b>4.10.1 Description and Priority</b>', styles['H3']))
    story.append(Paragraph('Fitur pengingat real-time pada dashboard guru untuk mendeteksi jadwal mengajar hari ini yang telah selesai lebih dari 30 menit namun belum dibuat jurnalnya.<br/><b>Prioritas:</b> Tinggi', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.10.2 Stimulus/Response Sequences</b>', styles['H3']))
    story.append(create_table(        [
            [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
            [Paragraph('Guru membuka halaman dashboard dan memiliki jadwal selesai >30 menit lalu tanpa jurnal.', styles['TD']), Paragraph("Sistem menampilkan banner peringatan mencolok disertai tombol aksi cepat 'Isi Jurnal Sekarang'.", styles['TD'])],
            [Paragraph('Guru mengisi dan menyimpan jurnal untuk jadwal tersebut.', styles['TD']), Paragraph('Banner peringatan otomatis hilang dari dashboard guru.', styles['TD'])],
            [Paragraph('Waka Kurikulum membuka dashboard monitoring.', styles['TD']), Paragraph('Sistem menyajikan daftar rekapitulasi guru yang menunggak pengisian jurnal.', styles['TD'])]
        ], [230, 274]))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.10.3 Functional Requirements</b>', styles['H3']))
    story.append(Paragraph('<b>KF57.</b> Sistem harus memeriksa jadwal hari ini yang waktu selesainya telah lewat >30 menit dan belum memiliki jurnal.', styles['Bullet']))
    story.append(Paragraph('<b>KF58.</b> Pemeriksaan dilakukan secara efisien saat dashboard dimuat tanpa memerlukan background cron daemon berat.', styles['Bullet']))
    story.append(Paragraph('<b>KF59.</b> Dashboard guru menampilkan alert card berisi nama mapel, rombel kelas, jam mengajar, dan tombol Isi Jurnal.', styles['Bullet']))
    story.append(Paragraph('<b>KF60.</b> Alert otomatis terhapus setelah jurnal untuk sesi jadwal tersebut berhasil disimpan.', styles['Bullet']))
    story.append(Paragraph('<b>KF61.</b> Waka Kurikulum dapat melihat statistik kepatuhan dan daftar guru yang belum mengisi jurnal.', styles['Bullet']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_4_11', store))
    story.append(Paragraph('4.11 Rekapitulasi dan Cetak Laporan Presensi', styles['H2']))
    story.append(Paragraph('<b>4.11.1 Description and Priority</b>', styles['H3']))
    story.append(Paragraph('Fitur penyajian rekapitulasi kehadiran siswa per rombel dan rentang tanggal, dilengkapi format cetak resmi standar kertas A4 yang mencakup kop surat dan kolom tanda tangan pengesahan.<br/><b>Prioritas:</b> Tinggi', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.11.2 Stimulus/Response Sequences</b>', styles['H3']))
    story.append(create_table(        [
            [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
            [Paragraph('Pengguna memilih rombel kelas dan periode tanggal pada halaman laporan presensi.', styles['TD']), Paragraph('Sistem mengkalkulasi total pertemuan, jumlah H, S, I, A, serta persentase kehadiran setiap siswa.', styles['TD'])],
            [Paragraph('Pengguna menekan tombol Cetak Laporan.', styles['TD']), Paragraph('Sistem membuka layout cetak rapi dengan format kop sekolah dan kolom tanda tangan Wali Kelas & Waka.', styles['TD'])],
            [Paragraph('Pengguna melakukan perintah print di peramban.', styles['TD']), Paragraph('Peramban mencetak dokumen ke kertas A4 atau menyimpannya sebagai berkas PDF.', styles['TD'])]
        ], [230, 274]))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.11.3 Functional Requirements</b>', styles['H3']))
    story.append(Paragraph('<b>KF62.</b> Sistem menyediakan filter laporan berdasarkan rombel kelas dan rentang tanggal pencarian.', styles['Bullet']))
    story.append(Paragraph('<b>KF63.</b> Sistem menghitung akumulasi Hadir, Sakit, Izin, dan Alpa serta persentase kehadiran: (Hadir/Total)*100%.', styles['Bullet']))
    story.append(Paragraph('<b>KF64.</b> Sistem menyediakan halaman cetak terdedikasi tanpa navbar dan sidebar yang mengganggu hasil print.', styles['Bullet']))
    story.append(Paragraph('<b>KF65.</b> Halaman cetak mencakup judul resmi, identitas rombel, tabel daftar siswa, dan kolom tanda tangan pengesahan.', styles['Bullet']))
    story.append(Paragraph('<b>KF66.</b> Waka Kurikulum, Wali Kelas, dan Staff Kesiswaan memiliki akses mencetak laporan presensi.', styles['Bullet']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_4_12', store))
    story.append(Paragraph('4.12 Layanan REST API untuk Aplikasi Mobile Flutter', styles['H2']))
    story.append(Paragraph('<b>4.12.1 Description and Priority</b>', styles['H3']))
    story.append(Paragraph('Fitur penyedia layanan antarmuka pemrograman aplikasi (REST API) yang aman menggunakan Laravel Sanctum untuk dikonsumsi oleh aplikasi seluler Flutter (Siswa dan Guru).<br/><b>Prioritas:</b> Sedang', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.12.2 Stimulus/Response Sequences</b>', styles['H3']))
    story.append(create_table(        [
            [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
            [Paragraph('Aplikasi Flutter mengirim request login dengan email dan password.', styles['TD']), Paragraph('API memvalidasi kredensial dan merespons bearer token Sanctum dalam format JSON.', styles['TD'])],
            [Paragraph('Aplikasi Flutter meminta data jadwal atau presensi dengan bearer token valid.', styles['TD']), Paragraph('API mengembalikan dataset JSON sesuai hak akses akun yang terotentikasi.', styles['TD'])],
            [Paragraph('Aplikasi Flutter mengirim request tanpa token atau token kedaluwarsa.', styles['TD']), Paragraph('API merespons kode status HTTP 401 Unauthorized.', styles['TD'])]
        ], [230, 274]))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.12.3 Functional Requirements</b>', styles['H3']))
    story.append(Paragraph('<b>KF67.</b> Sistem menyediakan endpoint API Login, Logout, dan Get User Profile dengan Laravel Sanctum.', styles['Bullet']))
    story.append(Paragraph('<b>KF68.</b> Sistem menyediakan endpoint API untuk melihat jadwal KBM aktif dan riwayat kehadiran siswa.', styles['Bullet']))
    story.append(Paragraph('<b>KF69.</b> Sistem menyediakan endpoint API bagi siswa untuk mengirimkan permohonan izin beserta lampiran berkas.', styles['Bullet']))
    story.append(Paragraph('<b>KF70.</b> Sistem menyediakan endpoint API bagi guru untuk melihat jadwal mengajar dan menginput jurnal KBM.', styles['Bullet']))
    story.append(Paragraph('<b>KF71.</b> Seluruh respon API harus berformat JSON standar dengan struktur pesan dan status code yang konsisten.', styles['Bullet']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_4_13', store))
    story.append(Paragraph('4.13 Pemulihan Kata Sandi dengan OTP Email', styles['H2']))
    story.append(Paragraph('<b>4.13.1 Description and Priority</b>', styles['H3']))
    story.append(Paragraph('Fitur pemulihan kata sandi mandiri bagi pengguna yang lupa kata sandi dengan mengirimkan kode OTP 6-digit ke alamat email terdaftar untuk verifikasi sebelum penyetelan kata sandi baru.<br/><b>Prioritas:</b> Sedang', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.13.2 Stimulus/Response Sequences</b>', styles['H3']))
    story.append(create_table(        [
            [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
            [Paragraph('Pengguna memasukkan email terdaftar pada form Lupa Password.', styles['TD']), Paragraph('Sistem men-generate kode OTP acak 6 digit, menyimpannya di cache/database, dan mengirimkannya ke email.', styles['TD'])],
            [Paragraph('Pengguna memasukkan kode OTP pada form verifikasi.', styles['TD']), Paragraph('Sistem memvalidasi keabsahan OTP dan mengarahkan ke form reset password baru.', styles['TD'])],
            [Paragraph('Pengguna memasukkan kata sandi baru dan konfirmasinya.', styles['TD']), Paragraph('Sistem memperbarui hash password dan menghapus token OTP yang telah terpakai.', styles['TD'])]
        ], [230, 274]))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>4.13.3 Functional Requirements</b>', styles['H3']))
    story.append(Paragraph('<b>KF72.</b> Sistem harus menyediakan form permintaan reset password berbasis input alamat email terdaftar.', styles['Bullet']))
    story.append(Paragraph('<b>KF73.</b> Sistem harus menghasilkan kode OTP 6 digit dengan masa berlaku terbatas (contoh: 15 menit).', styles['Bullet']))
    story.append(Paragraph('<b>KF74.</b> Sistem memvalidasi kode OTP sebelum mengizinkan pengguna mengakses halaman penetapan kata sandi baru.', styles['Bullet']))
    story.append(Paragraph('<b>KF75.</b> Sistem menyimpan kata sandi baru dalam bentuk hash terenkripsi dan otomatis membatalkan OTP lama.', styles['Bullet']))
    story.append(Spacer(1, 10))
    return story

def build_bab5(styles, store):
    story = []
    story.append(BookmarkFlowable('sec_5', store))
    story.append(Paragraph('5. Kebutuhan Non Fungsional Lainnya', styles['H1']))
    story.append(BookmarkFlowable('sec_5_1', store))
    story.append(Paragraph('5.1 Kebutuhan Kinerja', styles['H2']))
    story.append(Paragraph('<b>KnF1.</b> Waktu muat halaman (Page Load Time) portal web rata-rata di bawah 1.5 detik pada jaringan LAN sekolah.', styles['Bullet']))
    story.append(Paragraph('<b>KnF2.</b> Seluruh kueri basis data pada modul jadwal, jurnal, dan laporan presensi wajib menerapkan eager loading (with) guna mengeliminasi problem N+1 query.', styles['Bullet']))
    story.append(Paragraph('<b>KnF3.</b> Validasi bentrokan jadwal harus tereksekusi dalam waktu kurang dari 100 milidetik dengan memanfaatkan indeks komposit basis data.', styles['Bullet']))
    story.append(Paragraph('<b>KnF4.</b> Sistem mampu melayani akses serentak puluhan guru saat pengisian jurnal pada jam pergantian pelajaran.', styles['Bullet']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_5_2', store))
    story.append(Paragraph('5.2 Kebutuhan Keamanan', styles['H2']))
    story.append(Paragraph('<b>KnF5.</b> Sistem mewajibkan otentikasi login sebelum pengguna diizinkan mengakses data akademik.', styles['Bullet']))
    story.append(Paragraph('<b>KnF6.</b> Kata sandi pengguna wajib disimpan dalam bentuk hash terenkripsi dengan algoritma Bcrypt bawaan Laravel.', styles['Bullet']))
    story.append(Paragraph('<b>KnF7.</b> Seluruh form transmisi POST/PUT/DELETE pada web portal dilindungi dari serangan Cross-Site Request Forgery (CSRF).', styles['Bullet']))
    story.append(Paragraph('<b>KnF8.</b> Sesi pengguna berakhir otomatis setelah masa inaktivitas (session timeout).', styles['Bullet']))
    story.append(Paragraph('<b>KnF9.</b> Berkas unggahan dibatasi maksimal 2048 KB (2 MB) dengan whitelist ekstensi gambar (.jpg, .jpeg, .png) dan dokumen PDF.', styles['Bullet']))
    story.append(Paragraph('<b>KnF10.</b> Penegakan otorisasi dua lapis: pemeriksaan route middleware dan Laravel Policy pada level controller/model.', styles['Bullet']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_5_3', store))
    story.append(Paragraph('5.3 Kebutuhan Perlindungan Keamanan', styles['H2']))
    story.append(Paragraph('<b>KnF11. Confidentiality (Kerahasiaan):</b> Siswa hanya dapat melihat presensi dan perizinan miliknya sendiri. Data siswa lain tidak dapat diakses secara publik.', styles['Bullet']))
    story.append(Paragraph('<b>KnF12. Integrity (Integritas Data):</b> Hanya guru pengajar yang dapat mengubah isi jurnal dan presensi sesi pelajarannya. Perubahan status izin penting wajib melalui modal konfirmasi.', styles['Bullet']))
    story.append(Paragraph('<b>KnF13. Availability (Ketersediaan):</b> Sistem dirancang dapat beroperasi dengan reliabilitas tinggi selama jam operasional sekolah.', styles['Bullet']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_5_4', store))
    story.append(Paragraph('5.4 Atribut Kualitas Perangkat Lunak', styles['H2']))
    story.append(Paragraph('<b>KnF14. Reliabilitas:</b> Data presensi dan perizinan selalu konsisten dan mutakhir (up-to-date) secara real-time.', styles['Bullet']))
    story.append(Paragraph('<b>KnF15. Portabilitas:</b> Portal web dapat diakses dengan tampilan optimal di berbagai browser (Chrome, Edge, Firefox, Safari), dan aplikasi seluler berjalan lancar di Android/iOS.', styles['Bullet']))
    story.append(Paragraph('<b>KnF16. Usability (Kemudahan Penggunaan):</b> Tata letak antarmuka intuitif, dilengkapi teks petunjuk (helper text) pada input waktu jadwal, dan tata letak print yang siap cetak di kertas A4.', styles['Bullet']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_5_5', store))
    story.append(Paragraph('5.5 Aturan Bisnis (Business Rules)', styles['H2']))
    story.append(Paragraph('Aturan bisnis merupakan prinsip operasional mutlak yang ditegakkan oleh sistem:', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(Paragraph('<b>BR-1 Anti-Tabrakan Waktu:</b> Guru dilarang membuat jadwal KBM yang bertabrakan waktu pada hari dan siklus blok yang sama pada dimensi ruangan, kelas, atau guru. Rumus: (Start_A < End_B) dan (End_A > Start_B).', styles['Bullet']))
    story.append(Paragraph('<b>BR-2 Saklar Putaran Blok:</b> Sekolah menerapkan jadwal blok dua mingguan (Siklus 1 & Siklus 2). Pergantian siklus aktif oleh Waka Kurikulum langsung memfilter seluruh tampilan operasional sekolah.', styles['Bullet']))
    story.append(Paragraph("<b>BR-3 Mekanisme Kunci Jadwal:</b> Saat Waka Kurikulum mengaktifkan 'Lock Schedule', seluruh aksi penambahan, perubahan, dan penghapusan jadwal oleh guru dibekukan secara mutlak.", styles['Bullet']))
    story.append(Paragraph('<b>BR-4 Sinkronisasi Presensi Dua Arah:</b> Presensi siswa terintegrasi langsung dengan modul perizinan. Izin yang berstatus Approved otomatis menyinkronkan status presensi pada jurnal menjadi Sakit atau Izin.', styles['Bullet']))
    story.append(Paragraph('<b>BR-5 Peringatan Keterlambatan Jurnal:</b> Sistem memunculkan alert peringatan pada dashboard guru bila sesi mengajar hari ini telah usai lebih dari 30 menit namun jurnal belum diisi.', styles['Bullet']))
    story.append(Paragraph('<b>BR-6 Batasan Hak Akses:</b> Guru tidak dapat memanipulasi master data atau jadwal guru lain. Siswa tidak memiliki akses ke rekaman presensi kelas siswa lain.', styles['Bullet']))
    story.append(Spacer(1, 10))
    return story

def build_bab6(styles, store):
    story = []
    story.append(BookmarkFlowable('sec_6', store))
    story.append(Paragraph('6. Kebutuhan Lain', styles['H1']))
    story.append(BookmarkFlowable('sec_6_1', store))
    story.append(Paragraph('6.1 Kebutuhan Basis Data', styles['H2']))
    story.append(Paragraph('SCP menggunakan basis data relasional MySQL/MariaDB dengan struktur tabel utama sebagai berikut:', styles['Normal']))
    story.append(Spacer(1, 6))
    story.append(create_table(        [
            [Paragraph('No', styles['TH']), Paragraph('Nama Tabel', styles['TH']), Paragraph('Fungsi Utama', styles['TH']), Paragraph('Relasi Kunci', styles['TH'])],
            [Paragraph('1', styles['TD']), Paragraph('users', styles['TDBold']), Paragraph('Menyimpan akun pengguna, role, dan kredensial', styles['TD']), Paragraph('-', styles['TD'])],
            [Paragraph('2', styles['TD']), Paragraph('teachers', styles['TDBold']), Paragraph('Menyimpan biodata guru dan NIP', styles['TD']), Paragraph('users (user_id)', styles['TD'])],
            [Paragraph('3', styles['TD']), Paragraph('parents', styles['TDBold']), Paragraph('Menyimpan data orang tua / wali siswa', styles['TD']), Paragraph('users (user_id)', styles['TD'])],
            [Paragraph('4', styles['TD']), Paragraph('classes', styles['TDBold']), Paragraph('Menyimpan data rombel kelas dan wali kelas', styles['TD']), Paragraph('teachers (homeroom_teacher_id)', styles['TD'])],
            [Paragraph('5', styles['TD']), Paragraph('students', styles['TDBold']), Paragraph('Menyimpan data siswa, NIS, dan kelas', styles['TD']), Paragraph('users, classes, parents', styles['TD'])],
            [Paragraph('6', styles['TD']), Paragraph('rooms', styles['TDBold']), Paragraph('Menyimpan data ruang kelas dan lab', styles['TD']), Paragraph('-', styles['TD'])],
            [Paragraph('7', styles['TD']), Paragraph('subjects', styles['TDBold']), Paragraph('Menyimpan data mata pelajaran kurikulum', styles['TD']), Paragraph('-', styles['TD'])],
            [Paragraph('8', styles['TD']), Paragraph('block_cycles', styles['TDBold']), Paragraph('Menyimpan status siklus blok dan saklar aktif/kunci', styles['TD']), Paragraph('users (locked_by)', styles['TD'])],
            [Paragraph('9', styles['TD']), Paragraph('schedules', styles['TDBold']), Paragraph('Menyimpan jadwal KBM dan putaran siklus', styles['TD']), Paragraph('teachers, classes, subjects, rooms, block_cycles', styles['TD'])],
            [Paragraph('10', styles['TD']), Paragraph('journals', styles['TDBold']), Paragraph('Menyimpan catatan jurnal harian dan foto bukti', styles['TD']), Paragraph('schedules, teachers', styles['TD'])],
            [Paragraph('11', styles['TD']), Paragraph('attendances', styles['TDBold']), Paragraph('Menyimpan rekaman presensi harian siswa', styles['TD']), Paragraph('students, schedules', styles['TD'])],
            [Paragraph('12', styles['TD']), Paragraph('permission_requests', styles['TDBold']), Paragraph('Menyimpan permohonan izin/sakit dan verifikasi', styles['TD']), Paragraph('students, users (verified_by)', styles['TD'])]
        ], [24, 110, 210, 160]))
    story.append(Spacer(1, 10))
    story.append(BookmarkFlowable('sec_6_2', store))
    story.append(Paragraph('6.2 Kebutuhan Internasionalisasi', styles['H2']))
    story.append(Paragraph('Antarmuka pengguna, format tanggal (contoh: 10 Oktober 2026), pesan validasi, dan laporan menggunakan Bahasa Indonesia dengan zona waktu default Waktu Indonesia Barat (WIB / UTC+7).', styles['Normal']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_6_3', store))
    story.append(Paragraph('6.3 Kebutuhan Penyimpanan Berkas', styles['H2']))
    story.append(Paragraph('Foto kegiatan pembelajaran (activity_photo) dan berkas surat perizinan siswa (attachment_photo/document) disimpan pada direktori public storage Laravel. Penamaan berkas diatur unik (menggunakan timestamp atau UUID) untuk mencegah penimpaan berkas (file overwrite).', styles['Normal']))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_6_4', store))
    story.append(Paragraph('6.4 Kebutuhan Pengujian dan Verifikasi', styles['H2']))
    story.append(Paragraph('Pengujian perangkat lunak dilakukan dengan uji fungsional otomatis (Feature Tests) dan skenario demonstrasi langsung di hadapan tim penguji UKK:', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(create_table(        [
            [Paragraph('File Pengujian', styles['TH']), Paragraph('Skenario Pengujian', styles['TH']), Paragraph('Keterkaitan Fitur', styles['TH'])],
            [Paragraph('tests/Feature/JournalAttendanceSyncTest.php', styles['TD']), Paragraph('Validasi pembuatan jurnal otomatis mengisi presensi siswa dan menyinkronkan izin yang Approved.', styles['TD']), Paragraph('4.7, 4.8', styles['TD'])],
            [Paragraph('tests/Feature/ScheduleConflictValidationTest.php', styles['TD']), Paragraph('Pemeriksaan irisan waktu jadwal pada guru, ruangan, dan kelas yang menolak jadwal bertabrakan.', styles['TD']), Paragraph('4.4', styles['TD'])],
            [Paragraph('tests/Feature/LockScheduleProtectionTest.php', styles['TD']), Paragraph('Pengujian pembekuan input jadwal saat status jadwal dikunci oleh Waka Kurikulum.', styles['TD']), Paragraph('4.5', styles['TD'])],
            [Paragraph('tests/Feature/UnfilledJournalAlertTest.php', styles['TD']), Paragraph('Verifikasi kemunculan banner alert pada dashboard guru untuk jadwal yang telah lewat >30 menit.', styles['TD']), Paragraph('4.10', styles['TD'])],
            [Paragraph('tests/Feature/PermissionVerificationTest.php', styles['TD']), Paragraph('Alur persetujuan perizinan oleh Kesiswaan dan pembaruan otomatis status presensi siswa.', styles['TD']), Paragraph('4.8, 4.9', styles['TD'])]
        ], [180, 244, 80]))
    story.append(Spacer(1, 8))
    story.append(BookmarkFlowable('sec_6_5', store))
    story.append(Paragraph('6.5 Akun Default Pengujian', styles['H2']))
    story.append(Paragraph('Sistem menyediakan akun uji awal melalui database seeder (DatabaseSeeder) untuk keperluan demonstrasi:', styles['Normal']))
    story.append(Spacer(1, 4))
    story.append(create_table(        [
            [Paragraph('Role Akun', styles['TH']), Paragraph('Email / Username', styles['TH']), Paragraph('Kata Sandi', styles['TH']), Paragraph('Tugas / Skenario Pengujian', styles['TH'])],
            [Paragraph('Waka Kurikulum', styles['TDBold']), Paragraph('waka@sch.com', styles['TD']), Paragraph('password', styles['TD']), Paragraph('Pengelolaan master data, siklus blok, kunci jadwal, monitoring KBM.', styles['TD'])],
            [Paragraph('Guru Pengajar', styles['TDBold']), Paragraph('guru@sch.com', styles['TD']), Paragraph('password', styles['TD']), Paragraph('Input jadwal mandiri, isi jurnal mengajar, unggah foto bukti, presensi.', styles['TD'])],
            [Paragraph('Staff Kesiswaan', styles['TDBold']), Paragraph('kesiswaan@sch.com', styles['TD']), Paragraph('password', styles['TD']), Paragraph('Verifikasi perizinan siswa (Approve/Reject), rekapitulasi kehadiran.', styles['TD'])],
            [Paragraph('Siswa', styles['TDBold']), Paragraph('siswa@sch.com', styles['TD']), Paragraph('password', styles['TD']), Paragraph('Akses jadwal, riwayat presensi pribadi, pengajuan surat izin online.', styles['TD'])],
            [Paragraph('Orang Tua', styles['TDBold']), Paragraph('orangtua@sch.com', styles['TD']), Paragraph('password', styles['TD']), Paragraph('Pemantauan kehadiran dan perizinan putra-putri secara berkala.', styles['TD'])]
        ], [94, 110, 70, 230]))
    story.append(Spacer(1, 10))
    return story

print("Part 2 builder loaded.")
