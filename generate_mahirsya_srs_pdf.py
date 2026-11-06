"""
generate_mahirsya_srs_pdf.py
Generates the complete 45-page IEEE-standard SRS PDF for School Class Progress (SCP).
Tailored specifically for Mahirsya Bhanu Adyatma's SCP system at SMK Negeri 1 Bantul.
Architecture: Laravel 13.x, Blade Templates, Tailwind CSS, Alpine.js, Laravel Sanctum, MySQL/MariaDB.
"""

import os
import sys
from reportlab.lib.pagesizes import letter
from reportlab.lib import colors
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, PageBreak, Table, TableStyle
)
from reportlab.pdfgen import canvas
from srs_base import (
    PAGE_WIDTH, PAGE_HEIGHT, MARGIN, USABLE_WIDTH,
    TopRuleFlowable, get_image_flowable, create_table
)
from srs_styles import get_styles

styles = get_styles()


class NumberedSRS45(canvas.Canvas):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self._saved_page_states = []

    def showPage(self):
        self._saved_page_states.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        num_pages = len(self._saved_page_states)
        for state in self._saved_page_states:
            self.__dict__.update(state)
            self.draw_header_footer(num_pages)
            super().showPage()
        super().save()

    def to_roman(self, n):
        val = [1000, 900, 500, 400, 100, 90, 50, 40, 10, 9, 5, 4, 1]
        syb = ['m', 'cm', 'd', 'cd', 'c', 'xc', 'l', 'xl', 'x', 'ix', 'v', 'iv', 'i']
        roman = ''
        for i in range(len(val)):
            count = int(n / val[i])
            roman += syb[i] * count
            n -= val[i] * count
        return roman

    def draw_header_footer(self, page_count):
        p = self._pageNumber
        if p == 1:
            # Cover page Karl Wiegers copyright notice
            self.saveState()
            self.setFont('Helvetica-Oblique', 8)
            self.setFillColor(colors.HexColor('#333333'))
            self.drawString(
                MARGIN, 36,
                'Copyright \u00a9 1999 by Karl E. Wiegers. Permission is granted to use, modify, and distribute this document.'
            )
            self.restoreState()
            return
        elif p in (2, 3):
            # Preliminary pages (TOC & Revision History)
            self.saveState()
            self.setFont('Helvetica-Oblique', 8.5)
            self.setFillColor(colors.HexColor('#444444'))
            self.drawString(MARGIN, PAGE_HEIGHT - 36, 'Software Requirements Specification for SCP')
            roman = self.to_roman(p)
            self.drawRightString(PAGE_WIDTH - MARGIN, PAGE_HEIGHT - 36, f'Page {roman}')
            self.restoreState()
            return
        elif p == 4:
            # Blank separator page (no header/footer)
            return
        else:
            # Body pages: Page 5 -> Page 1, Page 6 -> Page 2, ..., Page 45 -> Page 41
            arabic_page = p - 4
            self.saveState()
            self.setFont('Helvetica-Oblique', 8.5)
            self.setFillColor(colors.HexColor('#444444'))
            self.drawString(MARGIN, PAGE_HEIGHT - 36, 'Software Requirements Specification for SCP')
            self.drawRightString(PAGE_WIDTH - MARGIN, PAGE_HEIGHT - 36, f'Page {arabic_page}')
            self.restoreState()


def get_all_pages():
    pages = []

    # ==========================================
    # PAGE 1: COVER
    # ==========================================
    p1 = [
        TopRuleFlowable(USABLE_WIDTH, height=3),
        Spacer(1, 100),
        Paragraph('Spesifikasi Kebutuhan<br/>Perangkat Lunak', styles['CoverTitle']),
        Spacer(1, 30),
        Paragraph('Untuk', styles['CoverSub']),
        Spacer(1, 20),
        Paragraph('School Class Progress (SCP)', styles['CoverAppName']),
        Spacer(1, 80),
        Paragraph('Versi 1.0 disetujui', styles['CoverMeta']),
        Spacer(1, 30),
        Paragraph('Disiapkan oleh Mahirsya Bhanu Adyatma', styles['CoverMeta']),
        Spacer(1, 30),
        Paragraph('SMK Negeri 1 Bantul', styles['CoverMeta']),
        Spacer(1, 30),
        Paragraph('10 Oktober 2026', styles['CoverMeta']),
    ]
    pages.append(p1)

    # ==========================================
    # PAGE 2: TABLE OF CONTENTS (Page ii)
    # ==========================================
    toc_data = [
        ('Table of Contents', 'ii', True, 0),
        ('Revision History', 'iii', True, 0),
        ('1. Pendahuluan', '1', True, 0),
        ('1.1 Tujuan', '1', False, 1),
        ('1.2 Definisi dan Istilah', '1', False, 1),
        ('1.3 Suggestions Pembaca yang disarankan membaca dokumen ini', '1', False, 1),
        ('1.4 Ruang Lingkup Sistem', '2', False, 1),
        ('1.5 Sistem Penomoran', '2', False, 1),
        ('1.6 Referensi', '3', False, 1),
        ('1.7 Ikhtisar Dokumen', '3', False, 1),
        ('2. Deskripsi Umum Sistem', '4', True, 0),
        ('2.1 Perspektif Perangkat Lunak', '4', False, 1),
        ('2.2 Fungsi Perangkat Lunak', '4', False, 1),
        ('2.3 Karakteristik dan Klasifikasi Pengguna', '4', False, 1),
        ('2.4 Lingkungan Operasi', '5', False, 1),
        ('2.5 Batasan Desain dan Implementasi', '5', False, 1),
        ('2.6 Dokumentasi bagi Pengguna', '6', False, 1),
        ('2.7 Asumsi dan Ketergantungan', '6', False, 1),
        ('3. Kebutuhan Antarmuka Eksternal', '6', True, 0),
        ('3.1 Antarmuka Pengguna', '6', False, 1),
        ('3.2 Antarmuka Perangkat Keras', '6', False, 1),
        ('3.3 Antarmuka Perangkat Lunak', '8', False, 1),
        ('3.4 Antarmuka Komunikasi', '8', False, 1),
        ('4. Fitur Sistem', '9', True, 0),
        ('4.1 Fitur 1', '9', False, 1),
        ('4.2 Fitur 2', '10', False, 1),
        ('5. Kebutuhan Non Fungsional Lainnya', '10', True, 0),
        ('5.1 Kebutuhan Kinerja', '10', False, 1),
        ('5.2 Kebutuhan Keamanan', '10', False, 1),
        ('5.3 Kebutuhan Perlindungan Keamanan', '11', False, 1),
        ('5.4 Atribut Kualitas Perangkat Lunak', '11', False, 1),
        ('5.5 Aturan Bisnis', '12', False, 1),
        ('6. Kebutuhan Lain', '12', True, 0),
        ('Appendix A: Istilah', '12', True, 0),
        ('Appendix B: Model Analisa', '12', True, 0),
        ('Appendix C: Daftar Yang Akan Ditentukan Kemudian', '12', True, 0),
    ]

    toc_rows = []
    for title, pnum, is_bold, indent_level in toc_data:
        title_p = Paragraph(title, styles['TOCItemBold' if is_bold else 'TOCItem'])
        pnum_p = Paragraph(pnum, styles['TOCPageBold' if is_bold else 'TOCPage'])
        toc_rows.append([title_p, pnum_p])

    t_toc = Table(toc_rows, colWidths=[460, 44])
    t_toc.setStyle(TableStyle([
        ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
        ('TOPPADDING', (0, 0), (-1, -1), 0),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 0),
        ('LEFTPADDING', (0, 0), (-1, -1), 0),
        ('RIGHTPADDING', (0, 0), (-1, -1), 0),
    ]))

    p2 = [
        Paragraph('Table of Contents', styles['H1']),
        Spacer(1, 4),
        t_toc
    ]
    pages.append(p2)

    # ==========================================
    # PAGE 3: RIWAYAT PERUBAHAN (Page iii)
    # ==========================================
    rh_table = create_table([
        [Paragraph('Nama', styles['TH']), Paragraph('Tanggal', styles['TH']), Paragraph('Alasan Perubahan', styles['TH']), Paragraph('Versi', styles['TH'])],
        [Paragraph('Mahirsya Bhanu Adyatma', styles['TD']), Paragraph('10 Oktober 2026', styles['TD']), Paragraph('Pembuatan Dokumen SRS awal berdasarkan soal UKK SCP. v1.0.0', styles['TD']), Paragraph('1.0', styles['TD'])],
    ], [110, 80, 250, 64])

    p3 = [
        Paragraph('Riwayat Perubahan', styles['H1']),
        Spacer(1, 10),
        rh_table
    ]
    pages.append(p3)

    # ==========================================
    # PAGE 4: BLANK SEPARATOR PAGE
    # ==========================================
    pages.append([Spacer(1, 100)])

    # ==========================================
    # PAGE 5 (Body Page 1): 1. PENDAHULUAN, 1.1, 1.2 INITIAL
    # ==========================================
    t_def_1 = create_table([
        [Paragraph('Istilah', styles['TH']), Paragraph('Definisi', styles['TH'])],
        [Paragraph('SCP', styles['TDBold']), Paragraph('School Class Progress, platform pengelolaan presensi, jurnal mengajar guru, dan perizinan siswa yang dikembangkan.', styles['TD'])],
        [Paragraph('Jadwal Blok (Putaran 1 & 2)', styles['TDBold']), Paragraph('Skema pergantian jadwal teori dan praktik kejuruan setiap dua minggu sekali.', styles['TD'])],
        [Paragraph('RBAC', styles['TDBold']), Paragraph('Role-Based Access Control, mekanisme pembatasan hak akses fitur dan antarmuka sistem berdasarkan peran pengguna.', styles['TD'])],
        [Paragraph('NIP / NISN', styles['TDBold']), Paragraph('Nomor Induk Pegawai (identitas Guru/Staf) / Nomor Induk Siswa Nasional (identitas Siswa).', styles['TD'])],
        [Paragraph('Rombel', styles['TDBold']), Paragraph('Rombongan Belajar atau entitas Kelas (Classroom).', styles['TD'])],
        [Paragraph('Validasi Bentrokan', styles['TDBold']), Paragraph('Proses otomatis yang dilakukan sistem saat guru menginput jadwal mengajar untuk mendeteksi dan menolak jadwal apabila terdapat irisan waktu, ruang, atau', styles['TD'])],
    ], [130, 374])

    p5 = [
        Paragraph('1. Pendahuluan', styles['H1']),
        Spacer(1, 4),
        Paragraph('1.1 Tujuan', styles['H2']),
        Paragraph('Dokumen Spesifikasi Kebutuhan Perangkat Lunak (<i>Software Requirements Specification</i> / SRS) ini dibuat sebagai pedoman dalam pembangunan perangkat lunak <b>School Class Progress (SCP) - Presensi dan Jurnal Sekolah</b> pada Uji Kompetensi Keahlian (UKK) Rekayasa Perangkat Lunak (RPL) Paket 3 Tahun Ajaran 2026/2027. Dokumen ini berisi penjabaran mengenai kebutuhan pengguna serta perancangan sistem yang dibuat berdasarkan spesifikasi soal praktik kejuruan.', styles['Normal']),
        Spacer(1, 4),
        Paragraph('Secara spesifik, dokumen ini bertujuan untuk:', styles['Normal']),
        Spacer(1, 4),
        Paragraph('1. Menjelaskan hak akses (<i>Role-Based Access Control</i>) dari 6 role pengguna yang disederhanakan menjadi 4 kelompok antarmuka utama, yaitu Waka Kurikulum, Guru, Tim Kesiswaan (Wali Kelas & Guru BK), serta Siswa & Orang Tua.', styles['Bullet']),
        Paragraph('2. Menjabarkan 5 logika bisnis inti (kunci kelulusan) pada sistem jadwal blok 2-mingguan, meliputi: (1) Logika Validasi Tabrakan Waktu (<i>Conflict Prevention</i>), (2) Sistem Blok & Pergantian Putaran (<i>Cycle Toggle</i>), (3) Mekanisme Kunci Jadwal (<i>Lock Schedule</i>), (4) Alur Verifikasi Perizinan Absensi (<i>Sync Logic</i>), dan (5) <i>Dashboard Alert Reminder</i> keterlambatan jurnal (>30 menit).', styles['Bullet']),
        Paragraph('3. Menjadi acuan teknis dalam implementasi struktur basis data, antarmuka <i>Mobile-First Design</i>, serta pengujian dan evaluasi sistem sesuai rubrik penilaian UKK.', styles['Bullet']),
        Spacer(1, 6),
        Paragraph('1.2 Definisi dan Istilah', styles['H2']),
        Paragraph('Guna memberikan gambaran yang sama terhadap beberapa definisi, istilah dan singkatan yang digunakan di dokumen ini, perlu dijelaskan sebagaimana berikut:', styles['Normal']),
        Spacer(1, 6),
        t_def_1
    ]
    pages.append(p5)

    # ==========================================
    # PAGE 6 (Body Page 2): 1.2 CONT & 1.3 INITIAL
    # ==========================================
    t_def_2 = create_table([
        [Paragraph('', styles['TD']), Paragraph('kelas pada siklus putaran dan hari yang sama.', styles['TD'])],
        [Paragraph('Approval', styles['TDBold']), Paragraph('Persetujuan resmi yang diberikan oleh Petugas terhadap pengajuan peminjaman, yang mengubah status peminjaman dari Pending menjadi Approved.', styles['TD'])],
        [Paragraph('Mobile-First', styles['TDBold']), Paragraph('Pendekatan desain yang memprioritaskan pengalaman pada layar ponsel, lalu disesuaikan untuk layar lebih besar', styles['TD'])],
        [Paragraph('Leave Request', styles['TDBold']), Paragraph('Pengajuan izin atau sakit dari siswa/orang tua', styles['TD'])],
        [Paragraph('Blade & Tailwind', styles['TDBold']), Paragraph('Framework antarmuka web responsif berbasis Blade templating dan Tailwind CSS bawaan sistem SCP.', styles['TD'])],
        [Paragraph('Laravel Sanctum', styles['TDBold']), Paragraph('Paket autentikasi berbasis token yang digunakan untuk REST API aplikasi SCP Mobile.', styles['TD'])],
    ], [130, 374], is_header=False)

    t_sug_1 = create_table([
        [Paragraph('Kategori Pembaca', styles['TH']), Paragraph('Pihak yang Disarankan', styles['TH']), Paragraph('Fokus Utama yang Dibaca', styles['TH'])],
        [Paragraph('Tim Pengembangan Inti', styles['TDBold']), Paragraph('Programmer / Developer', styles['TD']), Paragraph('Kebutuhan Fungsional (KF), Aturan Bisnis & Algoritma, Skema Basis Data (Blueprint), Activity Diagram, dan Class Diagram.', styles['TD'])],
        [Paragraph('', styles['TD']), Paragraph('Analis Sistem', styles['TD']), Paragraph('Seluruh dokumen. Untuk memastikan kebutuhan pengguna diterjemahkan dengan benar ke dalam spesifikasi teknis (FR/NFR).', styles['TD'])],
        [Paragraph('', styles['TD']), Paragraph('Manajer Proyek', styles['TD']), Paragraph('Tujuan, Ruang Lingkup, Kebutuhan Non-Fungsional (NFR), dan Prioritas Persyaratan. Untuk perencanaan sumber daya dan jadwal.', styles['TD'])],
        [Paragraph('', styles['TD']), Paragraph('Pihak Pengembang (Perusahaan/Tim)', styles['TD']), Paragraph('Tujuan, Ruang Lingkup, dan Kebutuhan Non-Fungsional (terutama kinerja dan deployment).', styles['TD'])],
        [Paragraph('Tim Kualitas dan Dokumentasi', styles['TDBold']), Paragraph('Tester / Quality Assurance (QA)', styles['TD']), Paragraph('Kebutuhan Fungsional (FR), Kebutuhan Non-Fungsional (NFR), dan Skenario (Use Case). Untuk merancang test case dan skenario pengujian.', styles['TD'])],
        [Paragraph('', styles['TD']), Paragraph('Penulis Dokumentasi', styles['TD']), Paragraph('Definisi dan Istilah, Kebutuhan Fungsional, dan Kebutuhan Non-Fungsional. Untuk membuat user manual dan dokumentasi teknis sistem.', styles['TD'])],
        [Paragraph('Kepentingan Bisnis', styles['TDBold']), Paragraph('Pengguna (Waka, Siswa, Guru, Admin,', styles['TD']), Paragraph('Tujuan, Penjelasan Sistem, Ruang Lingkup,', styles['TD'])],
    ], [110, 110, 284])

    p6 = [
        t_def_2,
        Spacer(1, 10),
        Paragraph('1.3 Suggestions Pembaca yang disarankan membaca dokumen ini', styles['H2']),
        Spacer(1, 6),
        t_sug_1
    ]
    pages.append(p6)

    # ==========================================
    # PAGE 7 (Body Page 3): 1.3 CONT & 1.4 RUANG LINGKUP
    # ==========================================
    t_sug_3 = create_table([
        [Paragraph('', styles['TD']), Paragraph('Walikelas / Guru BK.)', styles['TD']), Paragraph('Kebutuhan Fungsional (FR) yang relevan dengan peran mereka. Untuk memverifikasi apakah sistem memenuhi ekspektasi mereka.', styles['TD'])],
    ], [110, 110, 284], is_header=False)

    p7 = [
        t_sug_3,
        Spacer(1, 14),
        Paragraph('1.4 Ruang Lingkup Sistem', styles['H1']),
        Spacer(1, 6),
        Paragraph('Perangkat lunak School Class Progress (SCP) merupakan platform pengelolaan presensi, jurnal mengajar guru, dan perizinan siswa yang dirancang khusus untuk mendukung pembelajaran berbasis <i>Teaching Factory</i> dengan siklus jadwal blok 2 (dua) mingguan di Sekolah Menengah Kejuruan (SMK). Aplikasi dirancang dengan pendekatan <i>Mobile-First Design</i> agar optimal saat diakses oleh guru di kelas maupun siswa dan orang tua di rumah.', styles['Normal']),
        Spacer(1, 8),
        Paragraph('Aktivitas utama yang ditangani oleh perangkat lunak ini meliputi:', styles['Normal']),
        Spacer(1, 4),
        Paragraph('• <b>Otomatisasi Validasi Bentrokan Jadwal (Conflict Prevention):</b> Mencegah terjadinya tabrakan waktu, ruang, atau kelas saat guru menginput rencana jadwal mengajar mandiri pada siklus putaran dan hari yang sama melalui rumus irisan waktu Start_A < End_B AND End_A > Start_B.', styles['Bullet']),
        Paragraph('• <b>Pengendalian Siklus Blok & Kunci Jadwal Manajerial:</b> Menyediakan panel bagi Waka Kurikulum untuk mengelola data master, mengatur pergantian siklus aktif (<i>Putaran 1 / Putaran 2</i>) secara global, serta mengunci jadwal (<i>Lock Schedule</i>) ketika masa pengisian jadwal selesai.', styles['Bullet']),
        Paragraph('• <b>Pencatatan Jurnal Mengajar Realtime & Alert Kedisiplinan:</b> Memfasilitasi guru mengisi jurnal pembelajaran dan presensi secara <i>real-time</i> beserta unggahan foto kegiatan kelas, dilengkapi pemindaian otomatis tanpa <i>Cron Job</i> saat <i>dashboard</i> dimuat untuk memberi peringatan apabila jadwal telah melewati end_time lebih dari 30 menit tetapi jurnal belum diisi.', styles['Bullet']),
        Paragraph('• <b>Manajemen Perizinan & Sinkronisasi Presensi (Sync Logic):</b> Memfasilitasi siswa dan orang tua mengajukan surat izin/sakit beserta unggahan dokumen bukti, verifikasi (<i>Approve/Reject</i>) oleh Tim Kesiswaan (Wali Kelas & Guru BK), serta sinkronisasi otomatis ke tabel attendances saat guru mata pelajaran membuka kelas dan membuat jurnal pada hari tersebut.', styles['Bullet']),
    ]
    pages.append(p7)

    # ==========================================
    # PAGE 8 (Body Page 4): 1.5 SISTEM PENOMORAN & 1.6 REFERENSI
    # ==========================================
    t_num = create_table([
        [Paragraph('Hal/Bagian', styles['TH']), Paragraph('Aturan Penomoran', styles['TH'])],
        [Paragraph('Use Case', styles['TDBold']), Paragraph('Nomor berbentuk UC99, dimana 99 adalah nomor urut usecase<br/>Contoh: UC1, UC2, UC3 dan sebagainya', styles['TD'])],
        [Paragraph('Kebutuhan Fungsional', styles['TDBold']), Paragraph('Nomor berbentuk KF999.x, dimana 999 adalah nomor urut struktur butir-butir pada kebutuhan fungsional.<br/>Contoh : KF1, KF2, KF3 dan Sebagainya', styles['TD'])],
        [Paragraph('Kebutuhan Non Fungsional', styles['TDBold']), Paragraph('Nomor berbentuk KnF99.x, dimana 99 adalah nomor urut struktur butir-butir pada kebutuhan non fungsional.<br/>Contoh : KnF1, KnF2, KnF3 dan Sebagainya', styles['TD'])],
        [Paragraph('Form', styles['TDBold']), Paragraph('Form berbentuk F99.x, dimana 99 adalah nomor urut Form<br/>Contoh : F1, F2, F3 dan Sebagainya', styles['TD'])],
        [Paragraph('Laporan', styles['TDBold']), Paragraph('Laporan berbentuk L99.x, dimana 99 adalah nomor urut Laporan<br/>Contoh : L1, L2, L3 dan Sebagainya', styles['TD'])],
    ], [120, 384])

    p8 = [
        Paragraph('1.5 Sistem Penomoran', styles['H1']),
        Spacer(1, 4),
        Paragraph('Ada beberapa hal/bagian dalam dokumen ini yang diberlakukan sistem penomoran. Maksud penomoran ini untuk mempermudah <i>audience</i> dalam pengidentifikasian. Adapun aturan penomorannya sebagaimana tabel berikut:', styles['Normal']),
        Spacer(1, 6),
        t_num,
        Spacer(1, 10),
        Paragraph('1.6 Referensi', styles['H1']),
        Spacer(1, 4),
        Paragraph('• IEEE Std. 1233 Edition IEEE (1998), <i>Guide for Developing System Requirements Specifications</i>.', styles['Bullet']),
        Paragraph('• IEEE (2002), <i>Software Requirements Engineering</i>, Second Edition, IEEE Computer Society Press.', styles['Bullet']),
        Paragraph('• Tegarden, Wixon, dan Dennis (2005), <i>Systems Analysis and Design with UML Version 2.0 and Object Oriented Approach</i>, Second Edition, John Wiley & Sons Ltd.', styles['Bullet']),
        Paragraph('• Dokumen Product Requirement Document (PRD) School Class Progress (SCP) Web Application, Versi 1.0.0.', styles['Bullet']),
        Paragraph('• Dokumentasi resmi Laravel (laravel.com/docs) untuk Laravel 13.x, termasuk Sanctum dan Policies.', styles['Bullet']),
        Paragraph('• Dokumentasi resmi Tailwind CSS (tailwindcss.com/docs) untuk antarmuka web responsif.', styles['Bullet']),
        Paragraph('• Dokumentasi resmi Alpine.js (alpinejs.dev) untuk interaktivitas komponen klien.', styles['Bullet']),
    ]
    pages.append(p8)

    # ==========================================
    # PAGE 9 (Body Page 5): 1.7 IKHTISAR & 2. DESKRIPSI UMUM & 2.1 PERSPEKTIF
    # ==========================================
    p9 = [
        Paragraph('1.7 Ikhtisar Dokumen', styles['H1']),
        Spacer(1, 4),
        Paragraph('Penulisan dokumen ini dibagi menjadi beberapa bagian sebagai berikut:', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>1. Pendahuluan:</b> berisi tujuan penulisan dokumen, ruang lingkup perangkat lunak, pembaca yang disarankan, definisi dan istilah, sistem penomoran, referensi, serta ikhtisar dokumen.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>2. Deskripsi Umum Sistem:</b> berisi perspektif perangkat lunak, fungsi perangkat lunak, karakteristik dan klasifikasi pengguna, lingkungan operasi, batasan desain dan implementasi, dokumentasi bagi pengguna, serta asumsi dan ketergantungan.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>3. Kebutuhan Antarmuka Eksternal:</b> berisi antarmuka pengguna, antarmuka perangkat keras, antarmuka perangkat lunak, dan antarmuka komunikasi.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>4. Fitur Sistem:</b> berisi fitur-fitur utama yang terdapat pada sistem beserta kebutuhan fungsionalnya.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>5. Kebutuhan Non Fungsional Lainnya:</b> berisi kebutuhan kinerja, kebutuhan keamanan, kebutuhan perlindungan keamanan, atribut kualitas perangkat lunak, dan aturan bisnis.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>6. Kebutuhan Lain:</b> berisi kebutuhan basis data, kebutuhan internasionalisasi, kebutuhan penyimpanan berkas, serta kebutuhan pengujian dan verifikasi.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('Bagian Appendix berisi model analisis (use case, activity, sequence, dan class diagram), desain antarmuka sistem, serta daftar hal yang akan ditentukan kemudian (TBD).', styles['Normal']),
        Spacer(1, 10),
        Paragraph('2. Deskripsi Umum Sistem', styles['H1']),
        Spacer(1, 6),
        Paragraph('2.1 Perspektif Perangkat Lunak', styles['H2']),
        Spacer(1, 4),
        Paragraph('SCP merupakan aplikasi berbasis web mandiri (<i>standalone</i>) dengan pendekatan <i>Mobile-First Design</i> yang berfungsi sebagai pusat pengelolaan kegiatan belajar mengajar harian di sekolah. Sistem ini mengintegrasikan manajemen jadwal blok 2 mingguan, pencatatan jurnal mengajar dan presensi harian oleh guru, pengajuan surat izin oleh siswa/orang tua, serta verifikasi perizinan dan pemantauan absensi oleh Tim Kesiswaan (Wali Kelas & Guru BK) dan Waka Kurikulum dalam satu basis data terpusat.', styles['Normal']),
        Spacer(1, 4),
        Paragraph('Sistem terdiri dari tiga komponen utama: (1) Antarmuka web responsif (<i>Mobile-First UI</i>) yang terbagi ke dalam 4 kelompok antarmuka pengguna, (2) Lapisan logika <i>backend</i> yang menangani autentikasi RBAC, validasi tabrakan jadwal, manajemen siklus blok & kunci jadwal, <i>alert</i> pengisian jurnal, serta sinkronisasi izin ke presensi, dan (3) Sistem manajemen basis data relasional beserta penyimpanan dokumen/foto bukti kegiatan. Keterkaitan antar komponen ditunjukkan pada gambar berikut:', styles['Normal']),
    ]
    pages.append(p9)

    # ==========================================
    # PAGE 10 (Body Page 6): 2.2 FUNGSI PERANGKAT LUNAK & 2.3 KARAKTERISTIK (1-2)
    # ==========================================
    t_func = create_table([
        [Paragraph('No', styles['TH']), Paragraph('Fungsi', styles['TH']), Paragraph('Referensi Fitur', styles['TH'])],
        [Paragraph('1', styles['TD']), Paragraph('Melakukan log in dengan username', styles['TD']), Paragraph('4.1', styles['TD'])],
        [Paragraph('2', styles['TD']), Paragraph('Mengubah profil dan kata sandi', styles['TD']), Paragraph('4.1', styles['TD'])],
        [Paragraph('3', styles['TD']), Paragraph('Mengelola data master (kelas, ruangan, mata pelajaran, tahun ajaran)', styles['TD']), Paragraph('4.2', styles['TD'])],
        [Paragraph('4', styles['TD']), Paragraph('Mengelola pengguna, role, dan asosiasi kelas binaan', styles['TD']), Paragraph('4.3', styles['TD'])],
        [Paragraph('5', styles['TD']), Paragraph('Menyusun jadwal mengajar dengan pencegahan bentrok otomatis', styles['TD']), Paragraph('4.4', styles['TD'])],
        [Paragraph('6', styles['TD']), Paragraph('Mengganti putaran blok aktif dan mengunci jadwal', styles['TD']), Paragraph('4.5', styles['TD'])],
        [Paragraph('7', styles['TD']), Paragraph('Mengimpor jadwal pelajaran secara massal dari Excel, CSV, atau PDF', styles['TD']), Paragraph('4.6', styles['TD'])],
        [Paragraph('8', styles['TD']), Paragraph('Mencatat jurnal mengajar beserta bukti foto kegiatan', styles['TD']), Paragraph('4.7', styles['TD'])],
        [Paragraph('9', styles['TD']), Paragraph('Membuat presensi otomatis dan menyinkronkan izin yang disetujui', styles['TD']), Paragraph('4.8', styles['TD'])],
        [Paragraph('10', styles['TD']), Paragraph('Mengajukan dan memverifikasi surat izin siswa', styles['TD']), Paragraph('4.9', styles['TD'])],
        [Paragraph('11', styles['TD']), Paragraph('Memberi peringatan jurnal yang belum diisi', styles['TD']), Paragraph('4.10', styles['TD'])],
        [Paragraph('12', styles['TD']), Paragraph('Merekap dan mencetak laporan presensi siap cetak', styles['TD']), Paragraph('4.11', styles['TD'])],
        [Paragraph('13', styles['TD']), Paragraph('Menampilkan dashboard dan widget sesuai peran', styles['TD']), Paragraph('4.12', styles['TD'])],
        [Paragraph('14', styles['TD']), Paragraph('Menyediakan REST API untuk aplikasi SCP Mobile', styles['TD']), Paragraph('4.13', styles['TD'])],
    ], [30, 414, 60])

    p10 = [
        Paragraph('2.2 Fungsi Perangkat Lunak', styles['H1']),
        Spacer(1, 4),
        Paragraph('Fungsi utama yang harus dapat dilakukan oleh SCP, dan dijabarkan lebih detail pada Bagian 4, adalah sebagai berikut:', styles['Normal']),
        Spacer(1, 6),
        t_func,
        Spacer(1, 10),
        Paragraph('2.3 Karakteristik dan Klasifikasi Pengguna', styles['H1']),
        Spacer(1, 4),
        Paragraph('Klasifikasi pengguna dalam SCP dibedakan berdasarkan role yang melekat pada setiap akun. Role menentukan fungsi utama dan hak akses pengguna. Terdapat enam kelas pengguna dengan karakteristik sebagai berikut:', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>1. Waka Kurikulum:</b> penanggung jawab kalender akademik, master kelas, ruangan, mata pelajaran, dan tahun ajaran aktif. Mengatur saklar putaran blok, mengunci jadwal setelah masa penyusunan selesai, dan memantau kepatuhan guru dalam mengisi jurnal. Kelas pengguna ini sangat penting karena mengendalikan jadwal sekolah.', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>2. Guru Pengajar:</b> pengguna dengan frekuensi tertinggi. Menginput jadwal mengajar mandiri sesuai SK pembagian tugas, mengisi jurnal harian beserta foto kegiatan, dan melihat serta mengoreksi presensi siswa di kelasnya.', styles['Normal']),
    ]
    pages.append(p10)

    # ==========================================
    # PAGE 11 (Body Page 7): 2.3 CONT & MATRIKS HAK AKSES
    # ==========================================
    t_matrix = create_table([
        [Paragraph('Modul / Fitur', styles['TH']), Paragraph('Waka Kurikulum', styles['TH']), Paragraph('Guru', styles['TH']), Paragraph('Wali Kelas', styles['TH']), Paragraph('Guru BK', styles['TH']), Paragraph('Siswa / Ortu', styles['TH'])],
        [Paragraph('Authentifikasi login email & password', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD'])],
        [Paragraph('Edit profil dan ganti password', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD'])],
        [Paragraph('Kelola master data (kelas, ruang, mapel, siswa, guru)', styles['TD']), Paragraph('Ya (CRUD Lengkap)', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD'])],
        [Paragraph('Kelola pengguna (users dan roles)', styles['TD']), Paragraph('Ya (Terintegrasi)', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD'])],
        [Paragraph('Putaran blok (Cycle Toggle)', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD'])],
        [Paragraph('Kunci jadwal (Lock Schedule)', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD'])],
        [Paragraph('Input jadwal mandiri (+ cek bentrok)', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Tidak (hanya melihat)', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD'])],
        [Paragraph('Input jurnal mengajar (+ foto)', styles['TD']), Paragraph('Ya (lihat)', styles['TD']), Paragraph('Ya (buat dan ubah)', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD'])],
        [Paragraph('Pengajuan surat izin (+ bukti)', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Ya', styles['TD'])],
        [Paragraph('Verifikasi dan approval surat izin', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak (hanya melihat)', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Tidak', styles['TD'])],
        [Paragraph('Alert jurnal belum diisi (> 30 menit)', styles['TD']), Paragraph('Ya (semua guru)', styles['TD']), Paragraph('Ya (jadwal sendiri)', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Tidak', styles['TD'])],
        [Paragraph('Laporan rekap presensi dan cetak', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya (kelasnya)', styles['TD']), Paragraph('Ya (kelas binaan)', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Tidak', styles['TD'])],
        [Paragraph('Akses REST API mobile (Sanctum)', styles['TD']), Paragraph('Tidak', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD']), Paragraph('Ya', styles['TD'])],
    ], [114, 78, 78, 78, 78, 78])

    p11 = [
        Paragraph('<b>3. Wali Kelas:</b> mengawasi kehadiran siswa di rombel binaannya, memeriksa keabsahan surat izin siswa, memberikan approval atau rejection, serta melihat dan mencetak rekap presensi kelas binaan.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>4. Guru BK:</b> memantau data ketidakhadiran siswa di seluruh kelas, memantau siswa rawan (Alpa 3 kali atau lebih dalam satu bulan berjalan) untuk ditindaklanjuti dengan pemanggilan atau konseling, serta memverifikasi surat izin dari sisi kesiswaan.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>5. Siswa & Orang Tua:</b> pengguna yang mengakses portal dengan NISN untuk melihat rekap kehadiran pribadi, mengajukan permohonan izin, dan mengunggah berkas surat dokter atau surat pernyataan. Kelas pengguna ini memiliki hak akses paling terbatas.', styles['Normal']),
        Spacer(1, 6),
        t_matrix
    ]
    pages.append(p11)

    # ==========================================
    # PAGE 12 (Body Page 8): 2.4 LINGKUNGAN OPERASI & 2.5 BATASAN DESAIN
    # ==========================================
    p12 = [
        Paragraph('2.4 Lingkungan Operasi', styles['H1']),
        Spacer(1, 4),
        Paragraph('SCP dijalankan sebagai aplikasi berbasis web yang dapat diakses melalui peramban (browser) pada perangkat klien, dengan lingkungan operasi sebagai berikut:', styles['Normal']),
        Spacer(1, 4),
        Paragraph('• <b>Komputer server:</b> sistem operasi Linux (disarankan) atau Windows Server, dengan PHP 8.3 atau lebih baru, web server (Apache atau Nginx), dan basis data MySQL atau MariaDB. Versi detail akan ditetapkan pada Appendix C.', styles['Bullet']),
        Paragraph('• <b>Komputer klien:</b> laptop, komputer, atau tablet dengan peramban modern (Google Chrome, Microsoft Edge, Mozilla Firefox, atau Safari) versi terbaru.', styles['Bullet']),
        Paragraph('• <b>Perangkat seluler:</b> smartphone Android atau iOS untuk pengguna guru dan siswa, baik melalui peramban maupun aplikasi SCP Mobile yang menggunakan REST API.', styles['Bullet']),
        Paragraph('• <b>Jaringan:</b> Local Area Network (LAN) sekolah yang menghubungkan seluruh komputer klien dengan server.', styles['Bullet']),
        Paragraph('• <b>Perangkat lunak pendukung:</b> Laravel 13.x, Tailwind CSS, Alpine.js, Livewire, serta Laravel Sanctum.', styles['Bullet']),
        Spacer(1, 8),
        Paragraph('2.5 Batasan Desain dan Implementasi', styles['H1']),
        Spacer(1, 4),
        Paragraph('Perangkat lunak ini memiliki batasan sebagai berikut:', styles['Normal']),
        Spacer(1, 4),
        Paragraph('• SCP berbasis web dan responsif. Tidak tersedia aplikasi desktop terpisah. Pengguna mengakses sistem melalui peramban.', styles['Bullet']),
        Paragraph('• Framework yang digunakan wajib Laravel 13.x dengan PHP 8.3 atau lebih baru, dan antarmuka admin wajib responsif.', styles['Bullet']),
        Paragraph('• Basis data menggunakan MySQL atau MariaDB dengan indeks komposit pada tabel jadwal untuk mendukung validasi bentrok yang cepat.', styles['Bullet']),
        Paragraph('• Alert keterlambatan jurnal tidak menggunakan background cron job. Pemeriksaan dilakukan saat halaman dashboard dimuat.', styles['Bullet']),
        Paragraph('• Ukuran berkas unggahan dibatasi maksimal 2 MB dengan ekstensi yang telah ditentukan.', styles['Bullet']),
        Paragraph('• Hak akses ditegakkan pada dua lapis, yaitu antarmuka dan controller (Laravel Middleware & Policies).', styles['Bullet']),
        Paragraph('• Zona waktu server harus disesuaikan dengan zona waktu sekolah (WIB), karena perhitungan alert dan jadwal hari ini bergantung pada waktu sistem.', styles['Bullet']),
        Paragraph('• Akun default pada Bagian 6.5 hanya digunakan untuk pengujian dan wajib diganti sebelum sistem digunakan secara operasional.', styles['Bullet']),
        Spacer(1, 8),
        Paragraph('2.6 Dokumentasi bagi Pengguna', styles['H1']),
        Spacer(1, 4),
        Paragraph('Komponen dokumentasi pengguna yang akan dikirimkan bersama perangkat lunak adalah sebagai berikut:', styles['Normal']),
        Spacer(1, 4),
        Paragraph('• <b>Panduan pengguna per peran</b> (Super Admin, Waka Kurikulum, Guru, Wali Kelas, Guru BK, Siswa & Orang Tua) dalam format PDF dan dapat diakses dari halaman bantuan. Format final: TBD.', styles['Bullet']),
    ]
    pages.append(p12)

    # ==========================================
    # PAGE 13 (Body Page 9): 2.6 CONT, 2.7 ASUMSI, 3. ANTARMUKA EKSTERNAL
    # ==========================================
    p13 = [
        Paragraph('• <b>Panduan instalasi dan pengaturan awal</b> untuk administrator, termasuk perintah migrasi dan seeder basis data (migrate:fresh --seed).', styles['Bullet']),
        Paragraph('• <b>Teks bantuan (helper text) dan indikator durasi</b> yang tampil langsung pada form jadwal dan form izin.', styles['Bullet']),
        Paragraph('• <b>Dokumentasi REST API</b> untuk pengembang aplikasi SCP Mobile. Format dokumentasi: TBD.', styles['Bullet']),
        Spacer(1, 8),
        Paragraph('2.7 Asumsi dan Ketergantungan', styles['H1']),
        Spacer(1, 4),
        Paragraph('<b>Asumsi:</b>', styles['Normal']),
        Spacer(1, 3),
        Paragraph('1. Jaringan LAN sekolah tersedia dan stabil selama jam kegiatan belajar mengajar.', styles['Bullet']),
        Paragraph('2. Setiap guru, wali kelas, dan guru BK memiliki perangkat (laptop atau smartphone) dengan peramban modern.', styles['Bullet']),
        Paragraph('3. Data master awal (daftar guru, siswa, kelas, ruangan, dan mata pelajaran) tersedia dalam bentuk Excel, CSV, atau PDF untuk proses import.', styles['Bullet']),
        Paragraph('4. Pemetaan jenis izin ke status presensi ditetapkan sebagai berikut: izin Sakit menjadi status Sakit, sedangkan izin Izin, Pulang Cepat, dan Kegiatan Luar menjadi status Izin.', styles['Bullet']),
        Paragraph('5. Surat dokter atau surat bukti fisik dipindai atau difoto oleh siswa dan diunggah sebagai berkas digital.', styles['Bullet']),
        Paragraph('6. Jadwal pelajaran sudah disusun berdasarkan SK pembagian tugas mengajar sehingga guru dapat menginput jadwal mandirinya.', styles['Bullet']),
        Spacer(1, 6),
        Paragraph('<b>Ketergantungan:</b>', styles['Normal']),
        Spacer(1, 3),
        Paragraph('1. Ketersediaan framework Laravel 13.x, Tailwind CSS, Alpine.js, serta Laravel Sanctum.', styles['Bullet']),
        Paragraph('2. Ketersediaan server basis data MySQL atau MariaDB dan storage publik untuk penyimpanan foto dan lampiran.', styles['Bullet']),
        Paragraph('3. Ketersediaan peramban dengan dukungan fungsi cetak untuk menyimpan laporan sebagai PDF.', styles['Bullet']),
        Spacer(1, 8),
        Paragraph('3. Kebutuhan Antarmuka Eksternal', styles['H1']),
        Spacer(1, 6),
        Paragraph('3.1 Antarmuka Pengguna', styles['H2']),
        Spacer(1, 4),
        Paragraph('Antarmuka SCP dibedakan berdasarkan hak akses masing-masing pengguna. Seluruh halaman menggunakan Bahasa Indonesia, responsif, dan mengikuti tata letak modern. Standar antarmuka umum yang berlaku pada seluruh halaman adalah sebagai berikut:', styles['Normal']),
        Spacer(1, 4),
        Paragraph('• Pesan galat ditampilkan di bawah field yang bermasalah dengan teks berwarna merah.', styles['Bullet']),
    ]
    pages.append(p13)

    # ==========================================
    # PAGE 14 (Body Page 10): 3.1 ANTARMUKA PENGGUNA (F1-F11)
    # ==========================================
    t_f1 = create_table([
        [Paragraph('No', styles['TH']), Paragraph('Form / Halaman Antarmuka', styles['TH']), Paragraph('Nomor Form', styles['TH']), Paragraph('Peran yang Mengakses', styles['TH'])],
        [Paragraph('1', styles['TD']), Paragraph('Form Login (identitas dinamis NISN / NIP / Username)', styles['TD']), Paragraph('F1', styles['TD']), Paragraph('Seluruh peran', styles['TD'])],
        [Paragraph('2', styles['TD']), Paragraph('Form Edit Profil dan Ganti Kata Sandi', styles['TD']), Paragraph('F2', styles['TD']), Paragraph('Seluruh peran', styles['TD'])],
        [Paragraph('3', styles['TD']), Paragraph('Dashboard Waka Kurikulum dan Super Admin (SystemConfigWidget, WakaStatsWidget)', styles['TD']), Paragraph('F3', styles['TD']), Paragraph('Waka Kurikulum, Super Admin', styles['TD'])],
        [Paragraph('4', styles['TD']), Paragraph('Dashboard Guru (UnfilledJournalAlertWidget, TeacherStatsWidget)', styles['TD']), Paragraph('F4', styles['TD']), Paragraph('Guru', styles['TD'])],
        [Paragraph('5', styles['TD']), Paragraph('Dashboard Wali Kelas dan Guru BK (HomeroomStatsWidget)', styles['TD']), Paragraph('F5', styles['TD']), Paragraph('Wali Kelas, Guru BK', styles['TD'])],
        [Paragraph('6', styles['TD']), Paragraph('Dashboard Siswa & Orang Tua (StudentAttendanceWidget)', styles['TD']), Paragraph('F6', styles['TD']), Paragraph('Siswa & Orang Tua', styles['TD'])],
        [Paragraph('7', styles['TD']), Paragraph('Form Master Data (Kelas, Ruangan, Mata Pelajaran, Tahun Ajaran)', styles['TD']), Paragraph('F7', styles['TD']), Paragraph('Waka Kurikulum, Super Admin', styles['TD'])],
        [Paragraph('8', styles['TD']), Paragraph('Form Pengguna dan Penugasan Kelas Binaan', styles['TD']), Paragraph('F8', styles['TD']), Paragraph('Super Admin, Waka Kurikulum (user saja)', styles['TD'])],
        [Paragraph('9', styles['TD']), Paragraph('Form Jadwal Mengajar Mandiri (dengan cek bentrok)', styles['TD']), Paragraph('F9', styles['TD']), Paragraph('Guru (saat tidak terkunci), Waka Kurikulum, Super Admin', styles['TD'])],
        [Paragraph('10', styles['TD']), Paragraph('Form Import Jadwal Massal (Excel, CSV, PDF)', styles['TD']), Paragraph('F10', styles['TD']), Paragraph('Guru (milik sendiri), Waka Kurikulum, Super Admin', styles['TD'])],
        [Paragraph('11', styles['TD']), Paragraph('Tabel Jadwal dengan Tab Putaran', styles['TD']), Paragraph('F11', styles['TD']), Paragraph('Guru, Waka Kurikulum, Super Admin', styles['TD'])],
    ], [25, 275, 54, 150])

    p14 = [
        Paragraph('• Setiap aksi yang mengubah status data penting (Approve, Reject, Kunci Jadwal, Ganti Putaran) harus menampilkan modal konfirmasi.', styles['Bullet']),
        Paragraph('• Notifikasi keberhasilan ditampilkan setelah setiap penyimpanan data berhasil.', styles['Bullet']),
        Paragraph('• Tombol tambah data hanya tampil bagi pengguna yang memiliki hak akses terhadap aksi tersebut.', styles['Bullet']),
        Spacer(1, 4),
        Paragraph('Daftar form dan halaman antarmuka beserta peran yang mengaksesnya adalah sebagai berikut:', styles['Normal']),
        Spacer(1, 6),
        t_f1
    ]
    pages.append(p14)

    # ==========================================
    # PAGE 15 (Body Page 11): 3.1 CONT (F12-F18) & 3.2 PERANGKAT KERAS
    # ==========================================
    t_f2 = create_table([
        [Paragraph('No', styles['TH']), Paragraph('Form / Halaman Antarmuka', styles['TH']), Paragraph('Nomor Form', styles['TH']), Paragraph('Peran yang Mengakses', styles['TH'])],
        [Paragraph('12', styles['TD']), Paragraph('Form Jurnal Mengajar (dengan unggah foto)', styles['TD']), Paragraph('F12', styles['TD']), Paragraph('Guru (buat dan ubah), Waka Kurikulum (lihat), Super Admin', styles['TD'])],
        [Paragraph('13', styles['TD']), Paragraph('Relation Manager Presensi Siswa di dalam jurnal', styles['TD']), Paragraph('F13', styles['TD']), Paragraph('Guru, Waka Kurikulum (lihat), Super Admin', styles['TD'])],
        [Paragraph('14', styles['TD']), Paragraph('Form Pengajuan Surat Izin (dengan unggah bukti)', styles['TD']), Paragraph('F14', styles['TD']), Paragraph('Siswa & Orang Tua, Wali Kelas (bantuan siswa)', styles['TD'])],
        [Paragraph('15', styles['TD']), Paragraph('Daftar Antrean Izin dan Modal Verifikasi (Approve / Reject)', styles['TD']), Paragraph('F15', styles['TD']), Paragraph('Wali Kelas, Guru BK, Super Admin', styles['TD'])],
        [Paragraph('16', styles['TD']), Paragraph('Halaman Laporan Rekap Presensi', styles['TD']), Paragraph('F16', styles['TD']), Paragraph('Waka Kurikulum, Wali Kelas, Guru, Guru BK, Super Admin', styles['TD'])],
        [Paragraph('17', styles['TD']), Paragraph('Halaman Cetak Laporan (Print Layout)', styles['TD']), Paragraph('F17', styles['TD']), Paragraph('Waka Kurikulum, Wali Kelas, Guru, Guru BK, Super Admin', styles['TD'])],
        [Paragraph('18', styles['TD']), Paragraph('Halaman Manajemen Role dan Permission (RBAC)', styles['TD']), Paragraph('F18', styles['TD']), Paragraph('Super Admin', styles['TD'])],
    ], [25, 275, 54, 150])

    p15 = [
        t_f2,
        Spacer(1, 10),
        Paragraph('3.2 Antarmuka Perangkat Keras', styles['H1']),
        Spacer(1, 4),
        Paragraph('Untuk menjalankan SCP diperlukan dua jenis perangkat keras utama, yaitu:', styles['Normal']),
        Spacer(1, 4),
        Paragraph('• <b>Komputer server:</b> komputer yang berfungsi menyimpan, memproses, menyediakan, dan mendistribusikan seluruh data serta informasi, termasuk layanan panel web, REST API, basis data, dan storage berkas. Spesifikasi minimal: TBD.', styles['Bullet']),
        Paragraph('• <b>Komputer klien:</b> komputer, laptop, atau smartphone yang berfungsi sebagai pengakses dan pemasok data dan informasi bagi pengguna.', styles['Bullet']),
        Spacer(1, 4),
        Paragraph('Jenis perangkat keras pendukung yang dibutuhkan adalah sebagai berikut:', styles['Normal']),
        Spacer(1, 3),
        Paragraph('1. <b>Alat input:</b> keyboard dan mouse pada komputer klien, serta layar sentuh pada smartphone. Alat input digunakan untuk memasukkan data jadwal, jurnal, dan pengajuan izin.', styles['Bullet']),
        Paragraph('2. <b>Alat pemroses:</b> server dan klien yang memproses instruksi program. Server memproses data dan logika bisnis, sedangkan klien memproses tampilan antarmuka. Alat pemroses terdiri dari CPU dan memori utama.', styles['Bullet']),
        Paragraph('3. <b>Alat output:</b> monitor, layar smartphone, dan printer. Printer digunakan untuk mencetak laporan presensi melalui halaman cetak (print layout) pada kertas A4.', styles['Bullet']),
        Paragraph('4. <b>Perangkat keras komunikasi data:</b> hub atau switch jaringan yang menghubungkan komputer server dengan komputer klien di jaringan LAN sekolah, serta access point Wi-Fi bagi perangkat seluler.', styles['Bullet']),
        Spacer(1, 4),
        Paragraph('Gambaran antarmuka perangkat keras yang digunakan adalah sebagai berikut:', styles['Normal']),
    ]
    pages.append(p15)

    # ==========================================
    # PAGE 16 (Body Page 12): GAMBAR 3.1 & 3.3 ANTARMUKA PERANGKAT LUNAK
    # ==========================================
    t_sw = create_table([
        [Paragraph('Komponen', styles['TH']), Paragraph('Versi', styles['TH']), Paragraph('Fungsi dalam SCP', styles['TH'])],
        [Paragraph('Sistem operasi server', styles['TD']), Paragraph('Linux (disarankan) atau Windows Server; versi TBD', styles['TD']), Paragraph('Menjalankan layanan web dan basis data', styles['TD'])],
        [Paragraph('PHP', styles['TD']), Paragraph('8.3 atau lebih baru', styles['TD']), Paragraph('Runtime untuk Laravel', styles['TD'])],
        [Paragraph('Laravel Framework', styles['TD']), Paragraph('13.x', styles['TD']), Paragraph('Framework backend, routing, validasi, policy, dan observer', styles['TD'])],
        [Paragraph('Blade & Tailwind CSS', styles['TD']), Paragraph('v3 / v4', styles['TD']), Paragraph('Panel admin dan operasi untuk seluruh peran pengguna', styles['TD'])],
        [Paragraph('Livewire & Alpine.js', styles['TD']), Paragraph('3 / Sesuai versi', styles['TD']), Paragraph('Komponen reaktif dan interaksi tata letak antarmuka', styles['TD'])],
        [Paragraph('Role Middleware & Policies', styles['TD']), Paragraph('Versi kompatibel Laravel 13.x', styles['TD']), Paragraph('Pengelolaan role dan permission', styles['TD'])],
        [Paragraph('Laravel Sanctum', styles['TD']), Paragraph('Versi kompatibel dengan Laravel 13.x', styles['TD']), Paragraph('Autentikasi token untuk REST API aplikasi SCP Mobile', styles['TD'])],
        [Paragraph('MySQL atau MariaDB', styles['TD']), Paragraph('Versi TBD', styles['TD']), Paragraph('Sistem manajemen basis data relasional', styles['TD'])],
        [Paragraph('Web server (Apache atau Nginx)', styles['TD']), Paragraph('Versi TBD', styles['TD']), Paragraph('Melayani permintaan HTTP dan HTTPS', styles['TD'])],
        [Paragraph('Peramban web', styles['TD']), Paragraph('Versi terbaru Chrome, Edge,', styles['TD']), Paragraph('Akses antarmuka oleh pengguna', styles['TD'])],
    ], [140, 110, 254])

    p16 = [
        get_image_flowable('extracted_imgs/p15_0_IM144.jpg', 420, 180),
        Paragraph('3.3 Antarmuka Perangkat Lunak', styles['H1']),
        Spacer(1, 4),
        Paragraph('Antarmuka perangkat lunak SCP dibedakan menjadi dua, yaitu untuk komputer klien dan komputer server. Pada komputer klien, cukup tersedia peramban modern. Pada komputer server, harus terpasang komponen perangkat lunak sebagai berikut:', styles['Normal']),
        Spacer(1, 6),
        t_sw
    ]
    pages.append(p16)

    # ==========================================
    # PAGE 17 (Body Page 13): 3.3 CONT, DATA FLOW, GAMBAR 3.2, 3.4
    # ==========================================
    t_sw_cont = create_table([
        [Paragraph('', styles['TD']), Paragraph('Firefox, atau Safari', styles['TD']), Paragraph('', styles['TD'])],
    ], [140, 110, 254], is_header=False)

    p17 = [
        t_sw_cont,
        Spacer(1, 8),
        Paragraph('Data yang masuk ke sistem dan data yang keluar dari sistem beserta tujuannya adalah sebagai berikut:', styles['Normal']),
        Spacer(1, 4),
        Paragraph('• <b>Data masuk:</b> data form (jadwal, jurnal, pengajuan izin, profil), berkas unggahan (jpg, jpeg, png, dan pdf), berkas import (xlsx, xls, csv, dan pdf), serta permintaan JSON dari REST API.', styles['Bullet']),
        Paragraph('• <b>Data keluar:</b> halaman HTML dan notifikasi di panel web, laporan rekap dan halaman cetak, serta respons JSON dari REST API.', styles['Bullet']),
        Paragraph('• <b>Data bersama:</b> data jadwal, jurnal, presensi, dan izin dibagikan antar komponen melalui basis data yang sama. Sinkronisasi presensi dijalankan oleh JournalObserver dan LeaveRequestObserver setiap kali data terkait dibuat atau diperbarui.', styles['Bullet']),
        Spacer(1, 6),
        get_image_flowable('extracted_imgs/p16_0_IM148.jpg', 400, 170),
        Spacer(1, 4),
        Paragraph('3.4 Antarmuka Komunikasi', styles['H1']),
        Spacer(1, 4),
        Paragraph('Jaringan komunikasi dalam SCP memerlukan jaringan LAN yang digunakan sebagai sarana komunikasi antara komputer klien dan server di lingkungan sekolah. Aplikasi SCP Mobile dapat mengakses server melalui jaringan yang sama atau melalui jaringan yang dapat menjangkau server.', styles['Normal']),
        Spacer(1, 4),
        Paragraph('Persyaratan komunikasi yang berlaku adalah sebagai berikut:', styles['Normal']),
    ]
    pages.append(p17)

    # ==========================================
    # PAGE 18 (Body Page 14): PERSYARATAN KOMUNIKASI & GAMBAR 3.3
    # ==========================================
    p18 = [
        Paragraph('• Protokol komunikasi menggunakan HTTP. Penggunaan HTTPS wajib diterapkan apabila sistem diakses dari luar jaringan LAN sekolah.', styles['Bullet']),
        Paragraph('• Autentikasi panel web menggunakan session cookie yang terenkripsi, dilindungi token CSRF pada setiap form entri.', styles['Bullet']),
        Paragraph('• REST API menggunakan format JSON dan autentikasi Bearer Token dari Laravel Sanctum. Permintaan tanpa token yang valid ditolak dengan HTTP 401.', styles['Bullet']),
        Paragraph('• Unggahan berkas menggunakan format multipart/form-data dengan batas ukuran 2 MB per berkas.', styles['Bullet']),
        Paragraph('• Format tanggal dan waktu pada API menggunakan standar ISO 8601 dengan zona waktu WIB.', styles['Bullet']),
        Paragraph('• Sinkronisasi data bersifat realtime melalui permintaan dan respons (request-response). Layanan <i>websocket</i> tidak digunakan pada versi 1.0.', styles['Bullet']),
        Spacer(1, 8),
        get_image_flowable('extracted_imgs/p17_0_IM152.jpg', 380, 180),
        Spacer(1, 8),
        Paragraph('4. Fitur Sistem', styles['H1']),
        Spacer(1, 4),
        Paragraph('Berikut dijelaskan fitur-fitur yang terdapat pada perangkat lunak SCP. Setiap fitur memiliki deskripsi dan prioritas, urutan stimulus dan respons, serta kebutuhan fungsional yang harus dipenuhi.', styles['Normal']),
    ]
    pages.append(p18)

    # ==========================================
    # PAGE 19 (Body Page 15): 4. FITUR SISTEM, 4.1
    # ==========================================
    t_f41 = create_table([
        [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
        [Paragraph('Pengguna membuka halaman login dan memasukkan identitas serta kata sandi.', styles['TD']), Paragraph('Sistem memverifikasi kredensial dan mengarahkan pengguna ke dashboard sesuai perannya.', styles['TD'])],
        [Paragraph('Pengguna memasukkan kredensial yang salah.', styles['TD']), Paragraph('Sistem menampilkan pesan galat umum tanpa mengungkap apakah identitas terdaftar.', styles['TD'])],
        [Paragraph('Pengguna membuka menu Edit Profil dan mengubah data.', styles['TD']), Paragraph('Sistem menyimpan perubahan dan menampilkan notifikasi keberhasilan.', styles['TD'])],
        [Paragraph('Pengguna mengganti kata sandi.', styles['TD']), Paragraph('Sistem memverifikasi kata sandi lama, memastikan konfirmasi kata sandi baru cocok, dan menyimpannya.', styles['TD'])],
        [Paragraph('Sesi pengguna kedaluwarsa.', styles['TD']), Paragraph('Sistem melakukan logout otomatis dan meminta pengguna login ulang.', styles['TD'])],
    ], [240, 264])

    p19 = [
        Paragraph('4.1 Autentikasi dan Manajemen Profile', styles['H1']),
        Spacer(1, 4),
        Paragraph('4.1.1 Description and Priority', styles['H2']),
        Paragraph('Fitur ini memungkinkan seluruh pengguna masuk ke sistem menggunakan identitas dinamis (NISN untuk siswa, NIP atau username untuk guru dan staf), mengarahkan pengguna ke dashboard sesuai perannya, serta mengelola profil dan kata sandi secara mandiri.', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>Prioritas:</b> Tinggi', styles['Normal']),
        Spacer(1, 6),
        Paragraph('4.1.2 Stimulus/Response Sequences', styles['H2']),
        Spacer(1, 4),
        t_f41,
        Spacer(1, 8),
        Paragraph('4.1.3 Functional Requirements', styles['H2']),
        Spacer(1, 4),
        Paragraph('<b>KF1.</b> Sistem harus menyediakan form login dengan satu field identitas dinamis yang menerima NISN (untuk Siswa) atau NIP/username (untuk Guru dan Staf).', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF2.</b> Sistem harus mengarahkan pengguna langsung ke panel dashboard yang sesuai dengan hak aksesnya setelah login berhasil.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF3.</b> Sistem harus menampilkan pesan galat umum apabila kredensial tidak valid, tanpa mengungkap keberadaan identitas di dalam basis data.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF4.</b> Sistem harus menyediakan halaman Edit Profil dengan kartu profil visual, serta memungkinkan pengguna mengubah nama, email, nomor HP, dan foto profil (avatar).', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF5.</b> Sistem harus memvalidasi pergantian kata sandi dengan memastikan kata sandi lama benar dan kata sandi baru dikonfirmasi ulang dengan benar.', styles['Normal']),
    ]
    pages.append(p19)

    # ==========================================
    # PAGE 20 (Body Page 16): KF6 & 4.2 MANAJEMEN DATA MASTER
    # ==========================================
    t_f42 = create_table([
        [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
        [Paragraph('Waka Kurikulum menambah data kelas, misalnya XII RPL 1.', styles['TD']), Paragraph('Sistem menyimpan data kelas baru dan menampilkannya di daftar kelas.', styles['TD'])],
        [Paragraph('Waka Kurikulum menambah ruangan, misalnya Lab 16 RPL.', styles['TD']), Paragraph('Sistem menyimpan ruangan baru dan tersedia untuk dipilih pada form jadwal.', styles['TD'])],
        [Paragraph('Waka Kurikulum menetapkan tahun ajaran aktif.', styles['TD']), Paragraph('Sistem menandai tahun ajaran tersebut sebagai aktif dan menonaktifkan tahun ajaran lain.', styles['TD'])],
        [Paragraph('Waka Kurikulum menghapus master data yang masih dipakai jadwal.', styles['TD']), Paragraph('Sistem menolak penghapusan dan menampilkan pesan bahwa data masih digunakan.', styles['TD'])],
    ], [240, 264])

    p20 = [
        Paragraph('<b>KF6.</b> Sistem harus melakukan logout otomatis ketika sesi kedaluwarsa dan mengharuskan pengguna login ulang.', styles['Normal']),
        Spacer(1, 8),
        Paragraph('4.2 Manajemen Data Master', styles['H1']),
        Spacer(1, 4),
        Paragraph('4.2.1 Description and Priority', styles['H2']),
        Paragraph('Fitur ini memungkinkan Waka Kurikulum dan Super Admin mengelola data master akademik yang menjadi dasar penyusunan jadwal, yaitu kelas (rombel), ruangan atau laboratorium, mata pelajaran, dan tahun ajaran.', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>Prioritas:</b> Tinggi', styles['Normal']),
        Spacer(1, 6),
        Paragraph('4.2.2 Stimulus/Response Sequences', styles['H2']),
        Spacer(1, 4),
        t_f42,
        Spacer(1, 8),
        Paragraph('4.2.3 Functional Requirements', styles['H2']),
        Spacer(1, 4),
        Paragraph('<b>KF7.</b> Sistem harus menyediakan fitur tambah, ubah, dan hapus (CRUD) untuk data kelas (rombel) dengan atribut nama, misalnya XII RPL 1.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF8.</b> Sistem harus menyediakan fitur CRUD untuk data ruangan dengan atribut nama, misalnya Lab 16 RPL, Lab 18 RPL, atau Ruang 13 Normada.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF9.</b> Sistem harus menyediakan fitur CRUD untuk data mata pelajaran kejuruan dan umum dengan nama yang terstandarisasi.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF10.</b> Sistem harus menyediakan fitur CRUD untuk tahun ajaran beserta semester (Ganjil atau Genap), misalnya 2026/2027 Ganjil, dan penanda tahun ajaran aktif.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF11.</b> Sistem harus memastikan hanya satu tahun ajaran yang berstatus aktif pada satu waktu.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF12.</b> Sistem harus menolak penghapusan master data yang masih direferensikan oleh jadwal, jurnal, atau data kelas binaan, serta menampilkan pesan galat yang jelas.', styles['Normal']),
    ]
    pages.append(p20)

    # ==========================================
    # PAGE 21 (Body Page 17): 4.3 MANAJEMEN PENGGUNA DAN HAK AKSES
    # ==========================================
    t_f43 = create_table([
        [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
        [Paragraph('Super Admin membuat akun guru baru dan memberikan role Guru.', styles['TD']), Paragraph('Sistem menyimpan akun dan memberikan hak akses sesuai role Guru.', styles['TD'])],
        [Paragraph('Waka Kurikulum membuka menu pengguna.', styles['TD']), Paragraph('Sistem menampilkan daftar pengguna tanpa menu pengelolaan role.', styles['TD'])],
        [Paragraph('Super Admin mengaitkan siswa dengan kelas tertentu pada tahun ajaran aktif.', styles['TD']), Paragraph('Sistem menyimpan relasi pada tabel user_class dengan status Aktif.', styles['TD'])],
        [Paragraph('Super Admin menonaktifkan akun pengguna.', styles['TD']), Paragraph('Sistem mengubah status akun menjadi Nonaktif dan menolak login dari akun tersebut.', styles['TD'])],
        [Paragraph('Pengguna tanpa hak akses membuka menu tertentu melalui URL.', styles['TD']), Paragraph('Sistem menolak akses dan menampilkan halaman tidak diizinkan (403).', styles['TD'])],
    ], [240, 264])

    p21 = [
        Paragraph('4.3 Manajemen Pengguna dan Hak Akses', styles['H1']),
        Spacer(1, 4),
        Paragraph('4.3.1 Description and Priority', styles['H2']),
        Paragraph('Fitur ini memungkinkan pembuatan akun pengguna untuk seluruh peran, pengaturan role dan permission, serta penetapan kelas binaan bagi wali kelas dan siswa melalui tabel pivot user_class.', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>Prioritas:</b> Tinggi', styles['Normal']),
        Spacer(1, 6),
        Paragraph('4.3.2 Stimulus/Response Sequences', styles['H2']),
        Spacer(1, 4),
        t_f43,
        Spacer(1, 8),
        Paragraph('4.3.3 Functional Requirements', styles['H2']),
        Spacer(1, 4),
        Paragraph('<b>KF13.</b> Super Admin harus dapat membuat, mengubah, dan menghapus akun pengguna untuk seluruh peran, sedangkan Waka Kurikulum hanya dapat mengelola akun pengguna tanpa mengubah role.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF14.</b> Sistem harus menyediakan atribut akun berupa nama lengkap, username unik, NIP atau NISN, email, nomor telepon, foto profil, dan status akun (Aktif atau Nonaktif).', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF15.</b> Sistem harus mencegah akun berstatus Nonaktif melakukan login ke panel maupun REST API.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF16.</b> Sistem harus mengasosiasikan siswa dan wali kelas dengan kelas binaan melalui tabel pivot user_class, lengkap dengan status keanggotaan (Aktif, Alumni, atau Pindah) dan tahun ajaran.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF17.</b> Sistem harus membatasi akses menu, resource, dan aksi berdasarkan role menggunakan middleware otorisasi serta Laravel Policies.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF18.</b> Sistem harus menerapkan penghapusan lunak (soft delete) pada data pengguna sehingga riwayat presensi dan jurnal tetap dapat ditelusuri.', styles['Normal']),
    ]
    pages.append(p21)

    # ==========================================
    # PAGE 22 (Body Page 18): 4.4 PENJADWALAN PELAJARAN (KF19-KF22)
    # ==========================================
    t_f44 = create_table([
        [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
        [Paragraph('Guru membuka form jadwal dan memilih mata pelajaran, kelas, ruangan, hari, jam mulai, dan putaran.', styles['TD']), Paragraph('Sistem menghitung jam selesai secara otomatis (jam mulai ditambah 2 jam) dan menampilkan durasi dalam menit.', styles['TD'])],
        [Paragraph('Guru menyimpan jadwal yang tidak memiliki irisan waktu.', styles['TD']), Paragraph('Sistem menyimpan jadwal dan menampilkan notifikasi keberhasilan.', styles['TD'])],
        [Paragraph('Guru menyimpan jadwal yang beririsan dengan ruangan yang sudah dipakai.', styles['TD']), Paragraph('Sistem menolak penyimpanan dan menampilkan pesan bahwa ruangan telah digunakan oleh mata pelajaran tertentu pada jam tersebut.', styles['TD'])],
        [Paragraph('Guru menyimpan jadwal yang beririsan dengan kelas atau guru yang sama.', styles['TD']), Paragraph('Sistem menolak penyimpanan dan menampilkan pesan galat sesuai dimensi bentrok.', styles['TD'])],
        [Paragraph('Pengguna membuka tabel jadwal.', styles['TD']), Paragraph('Sistem menampilkan tab Semua Putaran, Siklus Aktif, Putaran 1, dan Putaran 2 beserta jumlah jadwal pada setiap tab.', styles['TD'])],
    ], [240, 264])

    p22 = [
        Paragraph('4.4 Penjadwalan Pelajaran dan Pencegahan Bentrok', styles['H1']),
        Spacer(1, 4),
        Paragraph('4.4.1 Description and Priority', styles['H2']),
        Paragraph('Fitur ini memungkinkan guru menyusun jadwal mengajar mandiri dengan pilihan waktu yang cerdas. Sistem secara otomatis memeriksa irisan waktu terhadap ruangan, kelas, dan guru, sehingga jadwal yang bentrok tidak dapat disimpan.', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>Prioritas:</b> Tinggi', styles['Normal']),
        Spacer(1, 6),
        Paragraph('4.4.2 Stimulus/Response Sequences', styles['H2']),
        Spacer(1, 4),
        t_f44,
        Spacer(1, 8),
        Paragraph('4.4.3 Functional Requirements', styles['H2']),
        Spacer(1, 4),
        Paragraph('<b>KF19.</b> Form jadwal harus memuat field mata pelajaran, kelas, ruangan, hari (Senin sampai Jumat), jam mulai, jam selesai, dan putaran siklus (Putaran 1 atau Putaran 2).', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF20.</b> Jam mulai harus dipilih dari daftar preset kelipatan 15 menit, mulai pukul 06:30 sampai 18:00.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF21.</b> Sistem harus mengisi jam selesai secara otomatis sebesar jam mulai ditambah 2 jam, menampilkan durasi belajar dalam menit, dan memungkinkan pengguna menyesuaikan jam selesai bila diperlukan.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF22.</b> Sistem harus menolak penyimpanan jadwal apabila terdapat irisan waktu dengan jadwal lain pada hari dan putaran yang sama. Irisan dinyatakan bila Start_A lebih kecil dari End_B dan End_A lebih besar dari Start_B.', styles['Normal']),
    ]
    pages.append(p22)

    # ==========================================
    # PAGE 23 (Body Page 19): KF23-KF26 & 4.5 SAKLAR PUTARAN DAN KUNCI JADWAL
    # ==========================================
    t_f45 = create_table([
        [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
        [Paragraph('Waka Kurikulum menekan tombol Ganti ke Putaran 2.', styles['TD']), Paragraph('Sistem memperbarui putaran aktif, menampilkan notifikasi sukses, dan menyaring seluruh tabel serta widget sesuai putaran baru.', styles['TD'])],
        [Paragraph('Waka Kurikulum menekan tombol Kunci Jadwal dan mengonfirmasi.', styles['TD']), Paragraph('Sistem mengaktifkan status terkunci dan menyembunyikan tombol tambah, ubah, dan hapus jadwal bagi Guru.', styles['TD'])],
        [Paragraph('Guru mencoba menambah jadwal saat jadwal terkunci.', styles['TD']), Paragraph('Sistem menolak aksi tersebut. Pada REST API, sistem merespons HTTP 403 dengan pesan Jadwal pelajaran telah dikunci oleh Waka Kurikulum.', styles['TD'])],
        [Paragraph('Guru membuka form jurnal.', styles['TD']), Paragraph('Sistem hanya menampilkan jadwal milik guru tersebut pada putaran yang sedang aktif.', styles['TD'])],
    ], [240, 264])

    p23 = [
        Paragraph('<b>KF23.</b> Pemeriksaan irisan waktu harus dilakukan untuk tiga dimensi, yaitu ruangan (room_id), kelas (classroom_id), dan guru (teacher_id).', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF24.</b> Pesan galat bentrok harus menyebutkan dimensi yang bertabrakan (ruangan, kelas, atau guru) serta mata pelajaran yang sudah terjadwal pada waktu tersebut.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF25.</b> Sistem harus menampilkan tab filter putaran berupa Semua Putaran, Siklus Aktif (Putaran X), Putaran 1, dan Putaran 2 dengan badge jumlah jadwal pada setiap tab.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF26.</b> Guru hanya dapat melihat dan mengelola jadwal miliknya sendiri, sedangkan Waka Kurikulum dan Super Admin dapat melihat jadwal seluruh guru.', styles['Normal']),
        Spacer(1, 8),
        Paragraph('4.5 Saklar Putaran dan Kunci Jadwal', styles['H1']),
        Spacer(1, 4),
        Paragraph('4.5.1 Description and Priority', styles['H2']),
        Paragraph('Fitur ini menyediakan saklar global untuk mengganti putaran aktif (Putaran 1 atau Putaran 2) serta saklar penguncian jadwal (Lock Schedule). Perubahan pada saklar langsung memengaruhi seluruh tampilan operasional sistem dan hak akses Guru.', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>Prioritas:</b> Tinggi', styles['Normal']),
        Spacer(1, 6),
        Paragraph('4.5.2 Stimulus/Response Sequences', styles['H2']),
        Spacer(1, 4),
        t_f45
    ]
    pages.append(p23)

    # ==========================================
    # PAGE 24 (Body Page 20): KF27-KF32 & 4.6 IMPORT JADWAL
    # ==========================================
    t_f46 = create_table([
        [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
        [Paragraph('Pengguna mengunggah berkas jadwal dengan format yang didukung.', styles['TD']), Paragraph('Sistem membaca berkas, memproses setiap baris, dan menampilkan ringkasan jumlah jadwal yang berhasil maupun gagal.', styles['TD'])],
        [Paragraph('Pengguna mengunduh template CSV.', styles['TD']), Paragraph('Sistem menyediakan berkas template CSV standar yang dapat diunduh.', styles['TD'])],
        [Paragraph('Berkas berisi guru yang belum terdaftar.', styles['TD']), Paragraph('Sistem membuat akun guru baru dengan kata sandi default yang aman.', styles['TD'])],
        [Paragraph('Berkas berisi jadwal yang bentrok.', styles['TD']), Paragraph('Sistem menandai baris tersebut sebagai gagal dan tidak menyimpannya.', styles['TD'])],
    ], [240, 264])

    p24 = [
        Paragraph('4.5.3 Functional Requirements', styles['H2']),
        Spacer(1, 4),
        Paragraph('<b>KF27.</b> Sistem harus menyediakan saklar putaran aktif (Putaran 1 atau Putaran 2) yang dapat diubah oleh Waka Kurikulum atau Super Admin hanya dengan satu aksi.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF28.</b> Perubahan putaran aktif harus langsung memfilter tabel jadwal, widget dashboard, dan daftar jadwal yang tersedia pada form jurnal, tanpa perlu memuat ulang filter secara manual.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF29.</b> Sistem harus menyimpan identitas pengguna yang terakhir mengubah konfigurasi putaran atau kunci jadwal pada atribut updated_by.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF30.</b> Sistem harus menyediakan saklar kunci jadwal (Lock Schedule) yang hanya dapat diubah oleh Waka Kurikulum atau Super Admin.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF31.</b> Ketika jadwal terkunci, sistem harus menonaktifkan aksi tambah, ubah, dan hapus jadwal bagi role Guru, baik pada antarmuka maupun pada lapisan backend melalui SchedulePolicy.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF32.</b> Pada REST API, sistem harus merespons permintaan POST jadwal dari Guru saat terkunci dengan HTTP 403 dan pesan Jadwal pelajaran telah dikunci oleh Waka Kurikulum.', styles['Normal']),
        Spacer(1, 8),
        Paragraph('4.6 Import Jadwal', styles['H1']),
        Spacer(1, 4),
        Paragraph('4.6.1 Description and Priority', styles['H2']),
        Paragraph('Fitur ini memungkinkan jadwal pelajaran diimpor sekaligus dari berkas Excel, CSV, atau PDF. Sistem juga mendaftarkan akun guru baru secara otomatis apabila data guru belum terdaftar.', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>Prioritas:</b> Sedang', styles['Normal']),
        Spacer(1, 6),
        Paragraph('4.6.2 Stimulus/Response Sequences', styles['H2']),
        Spacer(1, 4),
        t_f46
    ]
    pages.append(p24)

    # ==========================================
    # PAGE 25 (Body Page 21): KF33-KF39 & 4.7 JURNAL MENGAJAR DAN BUKTI FOTO
    # ==========================================
    t_f47 = create_table([
        [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
        [Paragraph('Guru menekan tombol Buat Jurnal dan memilih jadwal, tanggal, serta mengisi materi.', styles['TD']), Paragraph('Sistem menampilkan hanya jadwal milik guru pada putaran aktif.', styles['TD'])],
        [Paragraph('Guru mengunggah foto kegiatan kelas dan menyimpan jurnal.', styles['TD']), Paragraph('Sistem menyimpan jurnal dan foto ke storage publik, lalu membuat presensi seluruh siswa kelas secara otomatis.', styles['TD'])],
        [Paragraph('Guru menyimpan jurnal tanpa foto.', styles['TD']), Paragraph('Sistem menolak penyimpanan dan menampilkan pesan bahwa foto wajib diunggah.', styles['TD'])],
        [Paragraph('Waka Kurikulum membuka jurnal guru.', styles['TD']), Paragraph('Sistem menampilkan jurnal dalam mode lihat saja (view).', styles['TD'])],
    ], [240, 264])

    p25 = [
        Paragraph('4.6.3 Functional Requirements', styles['H2']),
        Spacer(1, 4),
        Paragraph('<b>KF33.</b> Sistem harus menerima berkas import dengan format .xlsx, .xls, .csv, dan .pdf.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF34.</b> Sistem harus mengenali kolom Guru, NIP, Mata Pelajaran, Kelas, Ruangan, Hari, Jam Mulai, Jam Selesai, dan Putaran secara otomatis dari berkas yang diunggah.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF35.</b> Apabila nama atau NIP guru belum terdaftar dalam basis data, sistem harus membuat akun guru baru dengan kata sandi default yang aman.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF36.</b> Setiap baris jadwal hasil import harus melewati pemeriksaan bentrok yang sama dengan penyimpanan jadwal mandiri.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF37.</b> Sistem harus menyediakan tombol unduh template CSV standar yang memuat seluruh kolom yang diperlukan.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF38.</b> Guru hanya dapat mengimpor jadwal miliknya sendiri, sedangkan Waka Kurikulum dan Super Admin dapat mengimpor jadwal untuk seluruh guru.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF39.</b> Sistem harus menampilkan ringkasan hasil import berupa jumlah baris yang berhasil disimpan dan daftar baris yang gagal beserta alasannya.', styles['Normal']),
        Spacer(1, 8),
        Paragraph('4.7 Jurnal Mengajar dan Bukti Foto', styles['H1']),
        Spacer(1, 4),
        Paragraph('4.7.1 Description and Priority', styles['H2']),
        Paragraph('Fitur ini memungkinkan guru mencatat jurnal mengajar harian secara realtime, meliputi materi pembelajaran, catatan kendala, dan foto kegiatan kelas sebagai bukti fisik pembelajaran. Penyimpanan jurnal akan memicu pembuatan presensi siswa secara otomatis.', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>Prioritas:</b> Tinggi', styles['Normal']),
        Spacer(1, 6),
        Paragraph('4.7.2 Stimulus/Response Sequences', styles['H2']),
        Spacer(1, 4),
        t_f47,
        Spacer(1, 6),
        Paragraph('4.7.3 Functional Requirements', styles['H2'])
    ]
    pages.append(p25)

    # ==========================================
    # PAGE 26 (Body Page 22): KF40-KF46 & 4.8 PRESENSI DUA ARAH (KF47)
    # ==========================================
    t_f48 = create_table([
        [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
        [Paragraph('Jurnal baru dibuat dan disimpan.', styles['TD']), Paragraph('Sistem membuat presensi seluruh siswa kelas. Siswa dengan izin Approved pada tanggal tersebut berstatus Sakit atau Izin, dan siswa lain berstatus Hadir.', styles['TD'])],
        [Paragraph('Wali Kelas menyetujui surat izin setelah jurnal dibuat.', styles['TD']), Paragraph('Sistem memperbarui presensi siswa tersebut pada tanggal izin menjadi Sakit atau Izin.', styles['TD'])],
        [Paragraph('Guru membuka tab Daftar Presensi Siswa di dalam jurnal.', styles['TD']), Paragraph('Sistem menampilkan nama seluruh siswa kelas, status kehadiran, dan catatan.', styles['TD'])],
        [Paragraph('Guru mengubah status presensi siswa yang terlambat atau alpa.', styles['TD']), Paragraph('Sistem menyimpan perubahan status dan catatan presensi.', styles['TD'])],
    ], [240, 264])

    p26 = [
        Paragraph('<b>KF40.</b> Dropdown jadwal pada form jurnal harus hanya menampilkan jadwal milik guru yang sedang login dan sesuai dengan putaran aktif.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF41.</b> Form jurnal harus memuat field tanggal pembelajaran, materi pembelajaran yang wajib diisi, catatan atau kendala kelas yang bersifat opsional, dan unggah foto.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF42.</b> Foto bukti kegiatan kelas harus berukuran maksimal 2 MB dengan ekstensi .jpg, .jpeg, atau .png, dan disimpan ke storage publik.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF43.</b> Foto bukti kegiatan kelas wajib diunggah setiap kali jurnal disimpan.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF44.</b> Guru hanya dapat membuat dan mengubah jurnal miliknya, sedangkan Waka Kurikulum hanya dapat melihat jurnal.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF45.</b> Setelah jurnal berhasil disimpan, sistem harus menjalankan proses sinkronisasi presensi sesuai 4.8.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF46.</b> Sistem harus menampilkan notifikasi keberhasilan setelah jurnal tercatat.', styles['Normal']),
        Spacer(1, 8),
        Paragraph('4.8 Presensi dan Sinkronisasi Otomatis Dua Arah', styles['H1']),
        Spacer(1, 4),
        Paragraph('4.8.1 Description and Priority', styles['H2']),
        Paragraph('Fitur ini mengisi presensi siswa secara otomatis berdasarkan jurnal dan pengajuan izin yang disetujui. Presensi dapat diperbarui secara dua arah, yaitu saat jurnal dibuat (menarik data izin yang sudah ada) dan saat izin disetujui setelah jurnal dibuat (memperbarui presensi yang sudah ada).', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>Prioritas:</b> Tinggi', styles['Normal']),
        Spacer(1, 6),
        Paragraph('4.8.2 Stimulus/Response Sequences', styles['H2']),
        Spacer(1, 4),
        t_f48,
        Spacer(1, 8),
        Paragraph('4.8.3 Functional Requirements', styles['H2']),
        Spacer(1, 4),
        Paragraph('<b>KF47.</b> Saat jurnal dibuat, sistem harus secara otomatis membuat record presensi untuk setiap siswa aktif di kelas terkait dengan status default Hadir.', styles['Normal']),
    ]
    pages.append(p26)

    # ==========================================
    # PAGE 27 (Body Page 23): KF48-KF52 & 4.9 PENGAJUAN IZIN (KF53-KF55)
    # ==========================================
    t_f49 = create_table([
        [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
        [Paragraph('Siswa membuat pengajuan izin dengan memilih jenis izin, tanggal, keterangan, dan unggahan bukti.', styles['TD']), Paragraph('Sistem menyimpan pengajuan dengan status Pending dan menampilkan notifikasi pengajuan berhasil dikirim.', styles['TD'])],
        [Paragraph('Wali Kelas membuka antrean izin dan memeriksa bukti.', styles['TD']), Paragraph('Sistem menampilkan pratinjau surat izin dan gambar lampiran.', styles['TD'])],
        [Paragraph('Wali Kelas menekan tombol Approve dan mengonfirmasi melalui modal.', styles['TD']), Paragraph('Sistem mengubah status menjadi Approved, mencatat verified_by, dan memperbarui presensi terkait.', styles['TD'])],
        [Paragraph('Guru BK menekan tombol Reject dan mengonfirmasi melalui modal.', styles['TD']), Paragraph('Sistem mengubah status menjadi Rejected dan mencatat verified_by.', styles['TD'])],
    ], [240, 264])

    p27 = [
        Paragraph('<b>KF48.</b> Apabila siswa memiliki pengajuan izin berstatus Approved yang mencakup tanggal jurnal, sistem harus membuat presensi dengan status Sakit atau Izin sesuai pemetaan pada Bagian 2.7, dengan catatan Otomatis tersinkronisasi dari Pengajuan Izin.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF49.</b> Apabila izin disetujui setelah jurnal dibuat, sistem harus memperbarui presensi siswa pada tanggal terkait di jurnal yang sudah ada menjadi Sakit atau Izin.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF50.</b> Wali kelas tidak termasuk dalam daftar presensi siswa dan tidak diabsen secara otomatis.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF51.</b> Guru harus dapat mengubah status presensi siswa secara manual menjadi Hadir, Sakit, Izin, atau Alpa beserta catatannya.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF52.</b> Presensi harus menampilkan seluruh siswa di kelas, status kehadiran, dan catatan dalam bentuk tabel di dalam halaman jurnal.', styles['Normal']),
        Spacer(1, 8),
        Paragraph('4.9 Pengajuan dan Verifikasi Izin Siswa', styles['H1']),
        Spacer(1, 4),
        Paragraph('4.9.1 Description and Priority', styles['H2']),
        Paragraph('Fitur ini memungkinkan siswa atau orang tua mengajukan izin sakit, izin, pulang cepat, atau kegiatan luar beserta bukti dokumen. Wali Kelas dan Guru BK memverifikasi pengajuan tersebut dengan approval atau rejection yang tercatat.', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>Prioritas:</b> Tinggi', styles['Normal']),
        Spacer(1, 6),
        Paragraph('4.9.2 Stimulus/Response Sequences', styles['H2']),
        Spacer(1, 4),
        t_f49,
        Spacer(1, 8),
        Paragraph('4.9.3 Functional Requirements', styles['H2']),
        Spacer(1, 4),
        Paragraph('<b>KF53.</b> Sistem harus menyediakan form pengajuan izin dengan pilihan jenis: Sakit, Izin, Pulang Cepat, atau Kegiatan Luar.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF54.</b> Untuk jenis Sakit dan Izin, form harus meminta rentang tanggal awal (start_date) sampai tanggal akhir (end_date), dan tanggal akhir tidak boleh lebih awal dari tanggal awal.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF55.</b> Untuk jenis Pulang Cepat dan Kegiatan Luar, form harus menampilkan field jam izin (leave_time).', styles['Normal']),
    ]
    pages.append(p27)

    # ==========================================
    # PAGE 28 (Body Page 24): KF56-KF63 & 4.10 ALERT KETERLAMBATAN (KF64)
    # ==========================================
    t_f410 = create_table([
        [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
        [Paragraph('Guru membuka dashboard dan memiliki jadwal hari ini yang telah selesai lebih dari 30 menit tanpa jurnal.', styles['TD']), Paragraph('Sistem menampilkan banner merah atau kuning di bagian paling atas dashboard beserta mapel, kelas, jam mengajar, dan tombol Isi Jurnal Sekarang.', styles['TD'])],
        [Paragraph('Guru mengisi jurnal untuk jadwal tersebut dan kembali ke dashboard.', styles['TD']), Paragraph('Sistem tidak lagi menampilkan banner untuk jadwal tersebut.', styles['TD'])],
        [Paragraph('Waka Kurikulum atau Super Admin membuka dashboard.', styles['TD']), Paragraph('Sistem menampilkan daftar seluruh guru yang menunggak pengisian jurnal.', styles['TD'])],
    ], [240, 264])

    p28 = [
        Paragraph('<b>KF56.</b> Form pengajuan harus memuat keterangan rinci alasan izin serta fitur unggah lampiran surat dokter atau surat bukti dengan ekstensi .jpg, .jpeg, .png, atau .pdf dan ukuran maksimal 2 MB. Kewajiban lampiran: TBD.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF57.</b> Setiap pengajuan baru harus tersimpan dengan status awal Pending.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF58.</b> Wali Kelas dapat mengajukan izin atas nama siswa binaannya sebagai bantuan.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF59.</b> Wali Kelas melihat antrean izin Pending dari siswa binaannya, sedangkan Guru BK melihat antrean izin Pending dari seluruh kelas.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF60.</b> Sistem harus menampilkan pratinjau surat izin dan gambar lampiran sebelum keputusan diambil.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF61.</b> Sistem harus menyediakan aksi Approve dan Reject yang masing-masing menampilkan modal konfirmasi sebelum status diubah.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF62.</b> Sistem harus merekam ID staf pemverifikasi (verified_by) setiap kali status pengajuan diubah dari Pending.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF63.</b> Setiap perubahan status pengajuan izin harus memicu LeaveRequestObserver untuk sinkronisasi presensi sesuai 4.8.', styles['Normal']),
        Spacer(1, 8),
        Paragraph('4.10 Alert Keterlambatan Jurnal', styles['H1']),
        Spacer(1, 4),
        Paragraph('4.10.1 Description and Priority', styles['H2']),
        Paragraph('Fitur ini mendeteksi secara realtime jadwal mengajar guru pada hari ini yang telah selesai lebih dari 30 menit namun belum memiliki jurnal, lalu menampilkan banner peringatan pada dashboard. Deteksi dilakukan saat halaman dimuat tanpa background cron job.', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>Prioritas:</b> Tinggi', styles['Normal']),
        Spacer(1, 6),
        Paragraph('4.10.2 Stimulus/Response Sequences', styles['H2']),
        Spacer(1, 4),
        t_f410,
        Spacer(1, 8),
        Paragraph('4.10.3 Functional Requirements', styles['H2']),
        Spacer(1, 4),
        Paragraph('<b>KF64.</b> Sistem harus mendeteksi jadwal guru pada hari ini yang waktu selesainya (end_time) lebih awal dari waktu sekarang dikurangi 30 menit dan belum memiliki jurnal pada tanggal yang sama.', styles['Normal']),
    ]
    pages.append(p28)

    # ==========================================
    # PAGE 29 (Body Page 25): KF65-KF69 & 4.11 REKAP DAN CETAK (KF70-KF72)
    # ==========================================
    t_f411 = create_table([
        [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
        [Paragraph('Pengguna memilih kelas dan rentang tanggal pada laporan.', styles['TD']), Paragraph('Sistem menampilkan rekap presensi per siswa dengan total pertemuan, jumlah H, S, I, A, dan persentase kehadiran.', styles['TD'])],
        [Paragraph('Pengguna membuka laporan tanpa memilih rentang tanggal.', styles['TD']), Paragraph('Sistem menampilkan data dari tanggal awal hingga tanggal akhir bulan berjalan.', styles['TD'])],
        [Paragraph('Pengguna menekan tombol Cetak.', styles['TD']), Paragraph('Sistem membuka halaman /admin/attendance-report/print dengan kop surat, tabel data, dan kolom tanda tangan.', styles['TD'])],
        [Paragraph('Pengguna mencetak halaman melalui fungsi cetak peramban.', styles['TD']), Paragraph('Peramban mencetak halaman tanpa elemen navigasi web, atau menyimpannya sebagai PDF.', styles['TD'])],
    ], [240, 264])

    p29 = [
        Paragraph('<b>KF65.</b> Deteksi harus dilakukan setiap halaman dashboard dimuat, tanpa menggunakan background cron job.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF66.</b> Sistem harus menampilkan banner peringatan berwarna merah atau kuning pada posisi paling atas dashboard guru, dengan judul Peringatan: Jurnal Mengajar Belum Diisi!.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF67.</b> Banner harus memuat informasi mata pelajaran, kelas, jam mengajar, dan tombol aksi Isi Jurnal Sekarang yang langsung membuka form jurnal.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF68.</b> Banner harus hilang ketika jurnal untuk jadwal tersebut telah dibuat.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF69.</b> Super Admin dan Waka Kurikulum harus dapat melihat daftar seluruh guru yang menunggak pengisian jurnal.', styles['Normal']),
        Spacer(1, 8),
        Paragraph('4.11 Rekap dan Cetak Laporan Presensi', styles['H1']),
        Spacer(1, 4),
        Paragraph('4.11.1 Description and Priority', styles['H2']),
        Paragraph('Fitur ini menyajikan rekapitulasi presensi kelas beserta persentase kehadiran, serta menyediakan halaman cetak resmi yang siap dicetak atau disimpan sebagai PDF. Laporan membantu wali kelas dan Waka Kurikulum menyusun laporan bulanan atau semester dengan cepat.', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>Prioritas:</b> Tinggi', styles['Normal']),
        Spacer(1, 6),
        Paragraph('4.11.2 Stimulus/Response Sequences', styles['H2']),
        Spacer(1, 4),
        t_f411,
        Spacer(1, 8),
        Paragraph('4.11.3 Functional Requirements', styles['H2']),
        Spacer(1, 4),
        Paragraph('<b>KF70.</b> Sistem harus menyediakan filter kelas binaan dan rentang tanggal pada laporan rekap (L1), dengan nilai default awal bulan hingga akhir bulan berjalan.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF71.</b> Sistem harus menghitung total pertemuan kelas dan akumulasi status Hadir (H), Sakit (S), Izin (I), serta Alpa (A) untuk setiap siswa.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF72.</b> Sistem harus menghitung persentase kehadiran dengan rumus: Persentase = (Hadir / Total Pertemuan) x 100%.', styles['Normal']),
    ]
    pages.append(p29)

    # ==========================================
    # PAGE 30 (Body Page 26): KF73-KF75 & 4.12 REST API MOBILE (KF76-KF80)
    # ==========================================
    t_f412 = create_table([
        [Paragraph('Stimulus (Aksi Pengguna)', styles['TH']), Paragraph('Respons Sistem', styles['TH'])],
        [Paragraph('Aplikasi mobile mengirim kredensial untuk login.', styles['TD']), Paragraph('API memvalidasi kredensial dan mengembalikan token Sanctum dalam format JSON.', styles['TD'])],
        [Paragraph('Aplikasi mobile mengirim permintaan tanpa token atau dengan token tidak valid.', styles['TD']), Paragraph('API merespons HTTP 401 Unauthorized.', styles['TD'])],
        [Paragraph('Guru mengirim permintaan POST jadwal saat jadwal terkunci.', styles['TD']), Paragraph('API merespons HTTP 403 Forbidden dengan pesan Jadwal pelajaran telah dikunci oleh Waka Kurikulum.', styles['TD'])],
    ], [240, 264])

    p30 = [
        Paragraph('<b>KF73.</b> Sistem harus menyediakan halaman cetak (L2) yang dapat dibuka dari laporan rekap, berisi kop surat resmi, tabel data terstruktur, dan kolom tanda tangan Wali Kelas serta Waka Kurikulum.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF74.</b> Halaman cetak harus dapat dicetak ke kertas A4, baik potret maupun lanskap, dan dapat disimpan sebagai PDF melalui fungsi cetak peramban.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF75.</b> Laporan dapat diakses oleh Waka Kurikulum, Wali Kelas (kelas binaan), Guru (kelas yang diajarnya), Guru BK, dan Super Admin. Siswa tidak memiliki akses ke laporan rekap kelas.', styles['Normal']),
        Spacer(1, 8),
        Paragraph('4.12 REST API Mobile', styles['H1']),
        Spacer(1, 4),
        Paragraph('4.12.1 Description and Priority', styles['H2']),
        Paragraph('Fitur ini menyediakan REST API bagi aplikasi SCP Mobile. API menggunakan Laravel Sanctum untuk autentikasi berbasis token dan mengikuti aturan hak akses serta aturan bisnis yang sama dengan panel web.', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>Prioritas:</b> Sedang', styles['Normal']),
        Spacer(1, 6),
        Paragraph('4.12.2 Stimulus/Response Sequences', styles['H2']),
        Spacer(1, 4),
        t_f412,
        Spacer(1, 8),
        Paragraph('4.12.3 Functional Requirements', styles['H2']),
        Spacer(1, 4),
        Paragraph('<b>KF76.</b> Sistem harus menyediakan REST API untuk aplikasi SCP Mobile yang menggunakan Laravel Sanctum sebagai autentikasi berbasis token.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF77.</b> API harus menyediakan endpoint sesuai matriks hak akses untuk peran Super Admin, Guru, Wali Kelas, Guru BK, dan Siswa & Orang Tua. Waka Kurikulum tidak memiliki akses API.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF78.</b> API harus menolak permintaan tanpa token yang valid dengan HTTP 401 dan memberikan respons JSON yang konsisten.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF79.</b> Endpoint pembuatan jadwal oleh Guru harus menerapkan aturan penguncian jadwal dan pemeriksaan bentrok yang sama dengan panel web, dan mengembalikan HTTP 403 atau 422 sesuai kondisi.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KF80.</b> API harus menerapkan aturan pengajuan izin dan sinkronisasi presensi yang sama dengan panel web.', styles['Normal']),
    ]
    pages.append(p30)

    # ==========================================
    # PAGE 31 (Body Page 27): 5. KEBUTUHAN NON FUNGSIONAL, 5.1, 5.2, 5.3 (1)
    # ==========================================
    p31 = [
        Paragraph('5. Kebutuhan Non Fungsional Lainnya', styles['H1']),
        Spacer(1, 6),
        Paragraph('5.1 Kebutuhan Kinerja', styles['H2']),
        Spacer(1, 4),
        Paragraph('Sistem harus memenuhi persyaratan kinerja berikut agar pengembang dapat menentukan pilihan desain yang sesuai:', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>KnF1.</b> Waktu muat halaman (Page Load Time) dashboard rata-rata kurang dari 1,5 detik pada koneksi LAN sekolah.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF2.</b> Seluruh query jadwal, jurnal, dan laporan presensi harus menggunakan eager loading (with) untuk menghilangkan masalah N+1 query.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF3.</b> Validasi bentrok jadwal (NoScheduleConflict) harus dieksekusi dalam waktu kurang dari 100 milidetik dengan memanfaatkan indeks komposit pada basis data.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF4.</b> Sistem harus dapat beroperasi selama jam kegiatan sekolah dengan pengguna yang mengakses secara bersamaan. Jumlah pengguna serentak: TBD.', styles['Normal']),
        Spacer(1, 8),
        Paragraph('5.2 Kebutuhan Keamanan', styles['H2']),
        Spacer(1, 4),
        Paragraph('Persyaratan keamanan berikut bertujuan mencegah kehilangan atau penyalahgunaan data akademik sekolah:', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>KnF5.</b> Sistem harus memiliki menu login untuk verifikasi pengguna sebelum mengakses data.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF6.</b> Setiap pengguna harus memiliki username dan password untuk mendapatkan hak akses sesuai perannya.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF7.</b> Kata sandi harus disimpan dalam bentuk hash menggunakan algoritma Bcrypt bawaan Laravel.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF8.</b> Seluruh form entri harus dilindungi token CSRF.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF9.</b> Sesi pengguna harus berakhir secara otomatis dan pengguna harus login ulang setelah sesi kedaluwarsa.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF10.</b> Form unggahan berkas harus membatasi ekstensi hanya gambar (.jpg, .jpeg, .png) dan dokumen PDF, dengan ukuran maksimal 2048 KB (2 MB).', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF11.</b> Otorisasi harus diterapkan pada dua lapis, yaitu antarmuka dan controller (Laravel Policies).', styles['Normal']),
        Spacer(1, 8),
        Paragraph('5.3 Kebutuhan Perlindungan Keamanan', styles['H2']),
        Spacer(1, 4),
        Paragraph('Persyaratan perlindungan keamanan dan privasi data pada SCP adalah sebagai berikut:', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>1. Confidentiality (kerahasiaan)</b>', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF12.</b> Akses terhadap informasi harus dilakukan melalui mekanisme otorisasi (authorization).', styles['Normal']),
    ]
    pages.append(p31)

    # ==========================================
    # PAGE 32 (Body Page 28): 5.3 (2 & 3) & 5.4 ATRIBUT KUALITAS (1 & 2)
    # ==========================================
    p32 = [
        Paragraph('<b>KnF13.</b> Siswa hanya dapat melihat data kehadiran dan pengajuan izin miliknya sendiri. Data siswa lain tidak boleh tampil.', styles['Normal']),
        Spacer(1, 6),
        Paragraph('<b>2. Integrity (integritas)</b>', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF14.</b> Hanya pengguna dengan peran yang berwenang yang dapat memasukkan atau mengubah data. Jurnal dan presensi hanya dapat diubah oleh Guru pengajar, sedangkan jadwal dan master data hanya oleh Waka Kurikulum atau Super Admin.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF15.</b> Saat mengubah status penting (Approve, Reject, perubahan putaran aktif, dan perubahan kunci jadwal), sistem harus menampilkan modal konfirmasi untuk memastikan perubahan dilakukan secara sengaja.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF16.</b> Data pengguna yang dihapus harus menggunakan penghapusan lunak (soft delete) agar riwayat dapat ditelusuri.', styles['Normal']),
        Spacer(1, 6),
        Paragraph('<b>3. Availability (ketersediaan)</b>', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF17.</b> Target ketersediaan sistem (uptime) dan prosedur pemulihan data: TBD.', styles['Normal']),
        Spacer(1, 10),
        Paragraph('5.4 Atribut Kualitas Perangkat Lunak', styles['H1']),
        Spacer(1, 4),
        Paragraph('Atribut kualitas berikut menjadi prioritas bagi pengembang dan pengguna. Urutan prioritas relatif: kemudahan penggunaan lebih diutamakan daripada kemudahan pembelajaran karena pengguna guru memiliki waktu terbatas di kelas.', styles['Normal']),
        Spacer(1, 6),
        Paragraph('<b>1. Kehandalan atau Reliabilitas</b>', styles['Normal']),
        Spacer(1, 3),
        Paragraph('SCP harus memiliki kehandalan yang cukup karena digunakan secara bersamaan oleh banyak pengguna (multi-user):', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF18.</b> Data dapat diakses secara bersamaan oleh setiap pengguna sesuai hak aksesnya.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF19.</b> Data yang sedang diubah oleh satu pengguna tidak boleh tampil sebagai data final bagi pengguna lain sebelum perubahan disimpan.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF20.</b> Pengguna harus selalu dapat mengakses data yang paling mutakhir (up to date).', styles['Normal']),
        Spacer(1, 6),
        Paragraph('<b>2. Portabilitas</b>', styles['Normal']),
        Spacer(1, 3),
        Paragraph('SCP harus memiliki tingkat portabilitas sebagai berikut:', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF21.</b> Sistem dapat diakses dari seluruh perangkat yang memiliki peramban dan terhubung ke jaringan.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF22.</b> Data keluaran (laporan dan tampilan) harus sama untuk setiap perangkat dan peramban yang digunakan.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF23.</b> Setiap fungsi yang tersedia harus dapat digunakan pada seluruh perangkat, baik komputer maupun smartphone.', styles['Normal']),
    ]
    pages.append(p32)

    # ==========================================
    # PAGE 33 (Body Page 29): 5.4 (3), 5.5 ATURAN BISNIS, 6. KEBUTUHAN LAIN & 6.1 BASIS DATA
    # ==========================================
    p33 = [
        Paragraph('<b>3. Kegunaan (Usability)</b>', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF24.</b> Seluruh halaman panel harus responsif dan dapat diakses dari laptop maupun smartphone Android dan iOS (mobile-first).', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF25.</b> Formulir presensi dan input jadwal harus dilengkapi teks bantuan (helper text) dan indikator durasi jam pembelajaran yang jelas.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>KnF26.</b> Template cetak laporan presensi harus disesuaikan untuk kertas A4 potret maupun lanskap tanpa elemen antarmuka web yang mengganggu.', styles['Normal']),
        Spacer(1, 8),
        Paragraph('5.5 Aturan Bisnis', styles['H1']),
        Spacer(1, 4),
        Paragraph('Aturan bisnis berikut merupakan prinsip operasi SCP. Aturan ini bukan kebutuhan fungsional tersendiri, tetapi menyiratkan kebutuhan fungsional tertentu untuk menegakkannya:', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>BR-1 Anti-tabrakan waktu.</b> Guru dilarang membuat jadwal yang saling tumpang tindih waktu pada putaran dan hari yang sama. Pemeriksaan dilakukan pada dimensi ruangan, kelas, dan guru dengan rumus irisan waktu: Bentrok jika (Start_A < End_B) dan (End_A > Start_B). Lihat KF pada 4.4.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>BR-2 Saklar putaran blok.</b> Sekolah menggunakan skema blok dua mingguan. Waka Kurikulum dapat mengganti putaran aktif antara Putaran 1 dan Putaran 2 dengan satu aksi, dan seluruh tampilan operasional mengikuti putaran aktif tersebut. Lihat 4.5.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>BR-3 Mekanisme kunci jadwal.</b> Setelah batas waktu input jadwal mandiri berakhir, Waka Kurikulum mengaktifkan status terkunci. Selama terkunci, Guru tidak dapat membuat, mengubah, atau menghapus jadwal, baik melalui panel web maupun REST API. Lihat 4.5.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>BR-4 Sinkronisasi presensi dua arah.</b> Presensi siswa tidak boleh diisi terpisah dari perizinan. Saat jurnal dibuat, sistem menarik izin yang sudah disetujui. Saat izin disetujui setelah jurnal dibuat, sistem memperbarui presensi yang sudah ada. Lihat 4.8 dan 4.9.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>BR-5 Peringatan keterlambatan jurnal.</b> Sistem menampilkan peringatan pada dashboard guru apabila jadwal hari ini telah selesai lebih dari 30 menit dan jurnalnya belum diisi. Lihat 4.10.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>BR-6 Pembatasan peran.</b> Guru tidak dapat mengubah master data, konfigurasi putaran, atau hak akses pengguna. Wali Kelas dan Guru BK tidak dapat mengubah jadwal. Siswa tidak dapat melihat data siswa lain.', styles['Normal']),
        Spacer(1, 8),
        Paragraph('6. Kebutuhan Lain', styles['H1']),
        Spacer(1, 4),
        Paragraph('6.1 Kebutuhan Basis Data', styles['H2']),
        Spacer(1, 4),
        Paragraph('SCP menggunakan basis data relasional MySQL atau MariaDB. Tabel utama yang diperlukan beserta fungsinya adalah sebagai berikut. Kamus data lengkap dengan tipe data dan nullable tercantum pada PRD SCP v1.0.0, bagian 7.1.', styles['Normal']),
    ]
    pages.append(p33)

    # ==========================================
    # PAGE 34 (Body Page 30): TABEL BASIS DATA, 6.2 INTERNASIONALISASI, 6.3 BERKAS
    # ==========================================
    t_db = create_table([
        [Paragraph('No', styles['TH']), Paragraph('Tabel', styles['TH']), Paragraph('Fungsi', styles['TH']), Paragraph('Relasi Utama', styles['TH'])],
        [Paragraph('1', styles['TD']), Paragraph('users', styles['TDBold']), Paragraph('Menyimpan seluruh akun pengguna, email, username, password ter-hash, dan role (waka, guru, kesiswaan, siswa)', styles['TD']), Paragraph('-', styles['TD'])],
        [Paragraph('2', styles['TD']), Paragraph('teachers', styles['TDBold']), Paragraph('Menyimpan data identitas guru pengajar dan NIP unik', styles['TD']), Paragraph('users (user_id)', styles['TD'])],
        [Paragraph('3', styles['TD']), Paragraph('classes', styles['TDBold']), Paragraph('Menyimpan data rombongan belajar/kelas dan penugasan wali kelas', styles['TD']), Paragraph('teachers (homeroom_teacher_id), majors (major_id)', styles['TD'])],
        [Paragraph('4', styles['TD']), Paragraph('students', styles['TDBold']), Paragraph('Menyimpan data siswa, NISN, NIS, dan status keaktifan belajar', styles['TD']), Paragraph('classes (class_id), users (user_id)', styles['TD'])],
        [Paragraph('5', styles['TD']), Paragraph('parents', styles['TDBold']), Paragraph('Menyimpan data orang tua/wali siswa', styles['TD']), Paragraph('users (user_id)', styles['TD'])],
        [Paragraph('6', styles['TD']), Paragraph('subjects', styles['TDBold']), Paragraph('Menyimpan data mata pelajaran kejuruan dan umum', styles['TD']), Paragraph('-', styles['TD'])],
        [Paragraph('7', styles['TD']), Paragraph('rooms', styles['TDBold']), Paragraph('Menyimpan data ruangan teori dan laboratorium praktik', styles['TD']), Paragraph('-', styles['TD'])],
        [Paragraph('8', styles['TD']), Paragraph('majors', styles['TDBold']), Paragraph('Menyimpan program keahlian / jurusan (RPL, DKV, TKJ, dll.)', styles['TD']), Paragraph('-', styles['TD'])],
        [Paragraph('9', styles['TD']), Paragraph('block_cycles', styles['TDBold']), Paragraph('Menyimpan siklus putaran blok, status aktif, dan kunci jadwal', styles['TD']), Paragraph('-', styles['TD'])],
        [Paragraph('10', styles['TD']), Paragraph('lesson_hours', styles['TDBold']), Paragraph('Menyimpan alokasi jam pelajaran harian dan mode upacara', styles['TD']), Paragraph('-', styles['TD'])],
        [Paragraph('11', styles['TD']), Paragraph('schedules', styles['TDBold']), Paragraph('Menyimpan jadwal pelajaran mingguan per putaran siklus', styles['TD']), Paragraph('teachers, subjects, rooms, classes, block_cycles', styles['TD'])],
        [Paragraph('12', styles['TD']), Paragraph('journals', styles['TDBold']), Paragraph('Menyimpan catatan jurnal mengajar guru dan foto bukti kegiatan', styles['TD']), Paragraph('schedules, teachers', styles['TD'])],
        [Paragraph('13', styles['TD']), Paragraph('attendances', styles['TDBold']), Paragraph('Menyimpan rekap kehadiran siswa per jurnal dan per tanggal', styles['TD']), Paragraph('journals/schedules, students', styles['TD'])],
        [Paragraph('14', styles['TD']), Paragraph('permission_requests', styles['TDBold']), Paragraph('Menyimpan pengajuan izin siswa, bukti surat, dan status approval', styles['TD']), Paragraph('students, parents', styles['TD'])],
        [Paragraph('15', styles['TD']), Paragraph('settings', styles['TDBold']), Paragraph('Menyimpan konfigurasi parameter aplikasi secara dinamis', styles['TD']), Paragraph('-', styles['TD'])],
    ], [25, 110, 225, 144])

    p34 = [
        t_db,
        Spacer(1, 4),
        Paragraph('Indeks komposit pada tabel schedules harus dibuat pada kolom teacher_id, classroom_id, room_id, day_of_week, cycle_type, start_time, dan end_time untuk mendukung KnF pada 5.1.', styles['Normal']),
        Spacer(1, 6),
        Paragraph('6.2 Kebutuhan Internasionalisasi', styles['H1']),
        Spacer(1, 4),
        Paragraph('Seluruh antarmuka pengguna, pesan galat, dan laporan menggunakan Bahasa Indonesia. Format tanggal menggunakan format Indonesia (contoh: 19 Oktober 2026) dan zona waktu yang digunakan adalah WIB (UTC+7). Dukungan bahasa lain tidak termasuk dalam ruang lingkup versi 1.0.', styles['Normal']),
        Spacer(1, 6),
        Paragraph('6.3 Kebutuhan Penyimpanan Berkas', styles['H1']),
        Spacer(1, 4),
        Paragraph('• Foto bukti kegiatan jurnal dan lampiran surat izin disimpan pada storage publik dengan path tersimpan pada kolom activity_photo dan attachment_photo.', styles['Bullet']),
        Paragraph('• Foto profil (avatar) disimpan pada storage publik dengan path tersimpan pada kolom profile_photo.', styles['Bullet']),
        Paragraph('• Nama berkas yang diunggah harus unik untuk mencegah tertimpanya berkas lain. Format penamaan berkas: TBD.', styles['Bullet']),
    ]
    pages.append(p34)

    # ==========================================
    # PAGE 35 (Body Page 31): 6.3 CONT & 6.4 KEBUTUHAN PENGUJIAN DAN VERIFIKASI
    # ==========================================
    t_test = create_table([
        [Paragraph('File Uji', styles['TH']), Paragraph('Skenario Pengujian', styles['TH']), Paragraph('Keterkaitan', styles['TH'])],
        [Paragraph('tests/Feature/JournalAttendanceSyncTest.php', styles['TD']), Paragraph('Pembuatan jurnal otomatis membuat presensi seluruh siswa dan mensinkronkan izin Approved. Wali kelas tidak ikut diabsen.', styles['TD']), Paragraph('4.7, 4.8', styles['TD'])],
        [Paragraph('tests/Feature/UnfilledJournalAlertTest.php', styles['TD']), Paragraph('Alert muncul pada jadwal yang telah lewat lebih dari 30 menit tanpa jurnal, dan hilang setelah jurnal diisi.', styles['TD']), Paragraph('4.10', styles['TD'])],
        [Paragraph('tests/Feature/RolePermissionTest.php', styles['TD']), Paragraph('Proteksi akses Waka Kurikulum, Guru, dan Super Admin terhadap data master dan role.', styles['TD']), Paragraph('4.3', styles['TD'])],
        [Paragraph('tests/Feature/DocumentImportTest.php', styles['TD']), Paragraph('Parser CSV dan Excel serta import jadwal yang otomatis mendaftarkan akun guru baru tanpa bentrok.', styles['TD']), Paragraph('4.6', styles['TD'])],
        [Paragraph('tests/Feature/DashboardWidgetsTest.php', styles['TD']), Paragraph('Setiap role hanya melihat widget yang diizinkan.', styles['TD']), Paragraph('4.12', styles['TD'])],
        [Paragraph('tests/Feature/ProfileTest.php', styles['TD']), Paragraph('Pengubahan profil dan pergantian kata sandi.', styles['TD']), Paragraph('4.1', styles['TD'])],
    ], [180, 254, 70])

    p35 = [
        Paragraph('• Berkas yang tidak lagi direferensikan oleh data (misalnya setelah pengguna atau jurnal dihapus) harus dibersihkan secara berkala. Mekanisme pembersihan: TBD.', styles['Bullet']),
        Spacer(1, 8),
        Paragraph('6.4 Kebutuhan Pengujian dan Verifikasi', styles['H1']),
        Spacer(1, 4),
        Paragraph('Sistem diuji menggunakan automated test suite (PHPUnit atau Pest) dan skenario demonstrasi manual. Pengujian otomatis yang wajib tersedia dan lulus adalah sebagai berikut:', styles['Normal']),
        Spacer(1, 6),
        t_test,
        Spacer(1, 8),
        Paragraph('Skenario demonstrasi manual yang harus dapat diperagakan di hadapan penguji adalah sebagai berikut:', styles['Normal']),
        Spacer(1, 4),
        Paragraph('<b>1. Anti-tabrakan waktu:</b> login sebagai guru1, simpan jadwal Senin di Lab 16 RPL pukul 07:30 sampai 09:30 pada Putaran 1, lalu coba simpan jadwal Senin di Lab 16 RPL pukul 08:30 sampai 10:30. Hasil yang diharapkan: form menolak simpan dengan pesan bahwa ruangan telah digunakan.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>2. Saklar putaran blok:</b> login sebagai waka, klik Ganti ke Putaran 2. Hasil yang diharapkan: notifikasi sukses, status berubah menjadi Putaran 2, dan seluruh query jadwal mengikuti putaran tersebut.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>3. Kunci jadwal:</b> login sebagai waka dan klik Kunci Jadwal, lalu login sebagai guru1. Hasil yang diharapkan: tombol Tambah Jadwal hilang, form edit tidak dapat diakses, dan API menolak permintaan POST dengan HTTP 403.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>4. Perizinan dan sinkronisasi presensi:</b> login sebagai siswa1 dan ajukan izin Sakit untuk hari ini, login sebagai walikelas1 dan Approve, lalu login sebagai guru1 dan buat jurnal hari ini untuk kelas siswa1. Hasil yang diharapkan: siswa1 berstatus Sakit dengan catatan Otomatis tersinkronisasi dari Pengajuan Izin, sedangkan siswa lain berstatus Hadir.', styles['Normal']),
        Spacer(1, 3),
        Paragraph('<b>5. Alert keterlambatan jurnal:</b> atur jadwal guru dengan jam selesai 45 menit sebelum waktu sistem saat ini, lalu buka dashboard guru. Hasil yang diharapkan: banner Peringatan: Jurnal Mengajar Belum Diisi! muncul di posisi paling atas dengan tombol Isi Jurnal Sekarang.', styles['Normal']),
    ]
    pages.append(p35)

    # ==========================================
    # PAGE 36 (Body Page 32): 6.5 AKUN DEFAULT & APPENDIX A
    # ==========================================
    t_acc = create_table([
        [Paragraph('Role Akun', styles['TH']), Paragraph('Username', styles['TH']), Paragraph('Kata Sandi', styles['TH']), Paragraph('Tugas Pengujian', styles['TH'])],
        [Paragraph('Super Admin / Waka', styles['TD']), Paragraph('waka', styles['TD']), Paragraph('password', styles['TD']), Paragraph('Kendali sistem penuh, manajemen data master, saklar putaran, kunci jadwal, pemantauan kepatuhan guru', styles['TD'])],
        [Paragraph('Guru Pengajar', styles['TD']), Paragraph('guru1', styles['TD']), Paragraph('password', styles['TD']), Paragraph('Input jadwal mandiri, input jurnal dan foto, alert keterlambatan', styles['TD'])],
        [Paragraph('Wali Kelas', styles['TD']), Paragraph('guru1', styles['TD']), Paragraph('password', styles['TD']), Paragraph('Verifikasi izin siswa rombel, pemantauan dan cetak rekap presensi kelas binaan', styles['TD'])],
        [Paragraph('Tim Kesiswaan / BK', styles['TD']), Paragraph('kesiswaan', styles['TD']), Paragraph('password', styles['TD']), Paragraph('Verifikasi izin siswa global, pemantauan siswa rawan alpa', styles['TD'])],
        [Paragraph('Siswa & Orang Tua', styles['TD']), Paragraph('siswa1', styles['TD']), Paragraph('password', styles['TD']), Paragraph('Pengajuan izin online, unggah surat dokter, pantau absensi pribadi', styles['TD'])],
        [Paragraph('Orang Tua', styles['TD']), Paragraph('orangtua', styles['TD']), Paragraph('password', styles['TD']), Paragraph('Pemantauan kehadiran dan presensi siswa secara terpadu', styles['TD'])],
    ], [100, 70, 74, 260])

    p36 = [
        Paragraph('6.5 Akun Default Pengujian', styles['H1']),
        Spacer(1, 4),
        Paragraph('Akun berikut disiapkan melalui database seeder (php artisan migrate:fresh --seed) untuk keperluan pengujian. Kata sandi default wajib diganti sebelum sistem digunakan secara operasional.', styles['Normal']),
        Spacer(1, 6),
        t_acc,
        Spacer(1, 14),
        Paragraph('Appendix A: Model Analisa', styles['H1']),
        Spacer(1, 6),
        Paragraph('Model analisis berikut digunakan untuk menggambarkan kebutuhan SCP secara visual. Gambar-gambar diagram akan dilengkapi pada tahap berikutnya.', styles['Normal']),
    ]
    pages.append(p36)

    # ==========================================
    # PAGE 37 (Body Page 33): A.1 USECASE DIAGRAM & UC1-UC5
    # ==========================================
    t_uc1 = create_table([
        [Paragraph('ID', styles['TH']), Paragraph('Nama Use Case', styles['TH']), Paragraph('Aktor Utama', styles['TH']), Paragraph('Prakondisi', styles['TH']), Paragraph('Ringkasan Alur Utama', styles['TH'])],
        [Paragraph('UC1', styles['TD']), Paragraph('Login', styles['TD']), Paragraph('Seluruh peran', styles['TD']), Paragraph('Akun aktif terdaftar', styles['TD']), Paragraph('Pengguna memasukkan identitas dan kata sandi, lalu diarahkan ke dashboard', styles['TD'])],
        [Paragraph('UC2', styles['TD']), Paragraph('Mengubah Profil dan Kata Sandi', styles['TD']), Paragraph('Seluruh peran', styles['TD']), Paragraph('Sudah login', styles['TD']), Paragraph('Pengguna memperbarui profil atau kata sandi, lalu sistem menyimpannya', styles['TD'])],
        [Paragraph('UC3', styles['TD']), Paragraph('Mengelola Master Data', styles['TD']), Paragraph('Waka Kurikulum, Super Admin', styles['TD']), Paragraph('Sudah login', styles['TD']), Paragraph('Pengguna menambah, mengubah, atau menghapus kelas, ruangan, mapel, dan tahun ajaran', styles['TD'])],
        [Paragraph('UC4', styles['TD']), Paragraph('Mengelola Pengguna dan Hak Akses', styles['TD']), Paragraph('Super Admin, Waka Kurikulum', styles['TD']), Paragraph('Sudah login', styles['TD']), Paragraph('Pengguna membuat akun dan mengaitkan kelas binaan', styles['TD'])],
        [Paragraph('UC5', styles['TD']), Paragraph('Mengganti Putaran Blok', styles['TD']), Paragraph('Waka Kurikulum, Super Admin', styles['TD']), Paragraph('Sudah login', styles['TD']), Paragraph('Pengguna mengganti putaran aktif, sistem', styles['TD'])],
    ], [40, 130, 110, 100, 124])

    p37 = [
        Paragraph('A.1 Usecase Diagram', styles['H2']),
        Spacer(1, 4),
        get_image_flowable('extracted_imgs/p36_0_IM244.jpg', 440, 230),
        Spacer(1, 4),
        Paragraph('Daftar use case beserta aktor utama dan prakondisinya adalah sebagai berikut:', styles['Normal']),
        Spacer(1, 4),
        t_uc1
    ]
    pages.append(p37)

    # ==========================================
    # PAGE 38 (Body Page 34): TABEL USECASE CONT (UC6-UC16)
    # ==========================================
    t_uc2 = create_table([
        [Paragraph('ID', styles['TH']), Paragraph('Nama Use Case', styles['TH']), Paragraph('Aktor Utama', styles['TH']), Paragraph('Prakondisi', styles['TH']), Paragraph('Ringkasan Alur Utama', styles['TH'])],
        [Paragraph('', styles['TD']), Paragraph('', styles['TD']), Paragraph('', styles['TD']), Paragraph('', styles['TD']), Paragraph('menyaring seluruh tampilan', styles['TD'])],
        [Paragraph('UC6', styles['TD']), Paragraph('Mengunci Jadwal', styles['TD']), Paragraph('Waka Kurikulum, Super Admin', styles['TD']), Paragraph('Sudah login', styles['TD']), Paragraph('Pengguna mengaktifkan kunci, akses ubah jadwal bagi Guru ditutup', styles['TD'])],
        [Paragraph('UC7', styles['TD']), Paragraph('Membuat Jadwal Mengajar', styles['TD']), Paragraph('Guru, Waka Kurikulum, Super Admin', styles['TD']), Paragraph('Jadwal belum terkunci', styles['TD']), Paragraph('Pengguna mengisi form jadwal, sistem memeriksa bentrok, lalu menyimpan', styles['TD'])],
        [Paragraph('UC8', styles['TD']), Paragraph('Mengimpor Jadwal Massal', styles['TD']), Paragraph('Guru, Waka Kurikulum, Super Admin', styles['TD']), Paragraph('Berkas import tersedia', styles['TD']), Paragraph('Pengguna mengunggah berkas, sistem memproses dan melaporkan hasilnya', styles['TD'])],
        [Paragraph('UC9', styles['TD']), Paragraph('Membuat Jurnal Mengajar', styles['TD']), Paragraph('Guru', styles['TD']), Paragraph('Ada jadwal pada putaran aktif', styles['TD']), Paragraph('Guru mengisi materi, catatan, dan foto, lalu sistem menyimpan dan membuat presensi', styles['TD'])],
        [Paragraph('UC10', styles['TD']), Paragraph('Mengelola Presensi Siswa', styles['TD']), Paragraph('Guru', styles['TD']), Paragraph('Jurnal sudah dibuat', styles['TD']), Paragraph('Guru melihat dan mengubah status presensi siswa', styles['TD'])],
        [Paragraph('UC11', styles['TD']), Paragraph('Mengajukan Izin', styles['TD']), Paragraph('Siswa & Orang Tua, Wali Kelas', styles['TD']), Paragraph('Sudah login', styles['TD']), Paragraph('Pengguna mengisi form izin dan mengunggah bukti, status menjadi Pending', styles['TD'])],
        [Paragraph('UC12', styles['TD']), Paragraph('Memverifikasi Izin', styles['TD']), Paragraph('Wali Kelas, Guru BK', styles['TD']), Paragraph('Ada izin berstatus Pending', styles['TD']), Paragraph('Pengguna memeriksa bukti, lalu menyetujui atau menolak dengan konfirmasi', styles['TD'])],
        [Paragraph('UC13', styles['TD']), Paragraph('Melihat Alert Jurnal', styles['TD']), Paragraph('Guru, Waka Kurikulum, Super Admin', styles['TD']), Paragraph('Ada jadwal yang belum berjurnal', styles['TD']), Paragraph('Sistem menampilkan banner peringatan saat dashboard dimuat', styles['TD'])],
        [Paragraph('UC14', styles['TD']), Paragraph('Mencetak Laporan Presensi', styles['TD']), Paragraph('Waka Kurikulum, Wali Kelas, Guru, Guru BK, Super Admin', styles['TD']), Paragraph('Sudah login', styles['TD']), Paragraph('Pengguna memilih kelas dan periode, lalu mencetak laporan', styles['TD'])],
        [Paragraph('UC15', styles['TD']), Paragraph('Melihat Dashboard dan Widget', styles['TD']), Paragraph('Seluruh peran', styles['TD']), Paragraph('Sudah login', styles['TD']), Paragraph('Sistem menampilkan widget sesuai peran pengguna', styles['TD'])],
        [Paragraph('UC16', styles['TD']), Paragraph('Mengakses REST API Mobile', styles['TD']), Paragraph('Guru, Wali Kelas, Guru BK, Siswa & Orang Tua, Super Admin', styles['TD']), Paragraph('Token Sanctum valid', styles['TD']), Paragraph('Aplikasi mobile mengirim permintaan dan menerima respons JSON', styles['TD'])],
    ], [40, 130, 110, 100, 124])

    p38 = [
        t_uc2
    ]
    pages.append(p38)

    # ==========================================
    # PAGE 39 (Body Page 35): A.2 & A.3 ACTIVITY DIAGRAM
    # ==========================================
    p39 = [
        Paragraph('A.2 Usecase Description', styles['H2']),
        Spacer(1, 4),
        Paragraph('Deskripsi rinci setiap use case mengikuti alur pada tabel di atas dan kebutuhan fungsional pada Bagian 4. Setiap use case dapat dirinci menjadi alur normal dan alur alternatif apabila diperlukan pada tahap desain lanjutan.', styles['Normal']),
        Spacer(1, 6),
        Paragraph('A.3 Activity Diagram', styles['H2']),
        Spacer(1, 4),
        get_image_flowable('extracted_imgs/p38_0_IM250.jpg', 360, 180),
        Paragraph('Gambar Activity Diagram Pencegahan Bentrok Jadwal', styles['Caption']),
        Spacer(1, 4),
        get_image_flowable('extracted_imgs/p38_1_IM251.jpg', 360, 180),
        Paragraph('Gambar Activity Diagram Pengajuan Dan Verifikasi Izin', styles['Caption']),
    ]
    pages.append(p39)

    # ==========================================
    # PAGE 40 (Body Page 36): A.4 CLASS DIAGRAM & APPENDIX B (B.1)
    # ==========================================
    p40 = [
        Paragraph('A.4 Class Diagram', styles['H2']),
        Spacer(1, 6),
        get_image_flowable('extracted_imgs/p39_0_IM256.jpg', 460, 310),
        Spacer(1, 14),
        Paragraph('Appendix B: Desain Antarmuka Sistem', styles['H1']),
        Spacer(1, 8),
        Paragraph('B.1 Wireframe', styles['H2']),
    ]
    pages.append(p40)

    # ==========================================
    # PAGE 41 (Body Page 37): WIREFRAME B.1 & B.2
    # ==========================================
    p41 = [
        get_image_flowable('extracted_imgs/p40_0_IM261.jpg', 420, 195),
        Paragraph('Gambar Wireframe Login Page', styles['Caption']),
        Spacer(1, 6),
        get_image_flowable('extracted_imgs/p40_1_IM262.jpg', 440, 205),
        Paragraph('Gambar Wireframe Dashboard Waka', styles['Caption']),
    ]
    pages.append(p41)

    # ==========================================
    # PAGE 42 (Body Page 38): WIREFRAME B.3 & B.4
    # ==========================================
    p42 = [
        get_image_flowable('extracted_imgs/p41_0_IM265.jpg', 440, 200),
        Paragraph('Gambar Wireframe Form Input Jadwal Mengajar', styles['Caption']),
        Spacer(1, 6),
        get_image_flowable('extracted_imgs/p41_1_IM266.jpg', 440, 200),
        Paragraph('Gambar Wireframe Form Input Jurnal Mengajar', styles['Caption']),
    ]
    pages.append(p42)

    # ==========================================
    # PAGE 43 (Body Page 39): WIREFRAME B.5 & B.6
    # ==========================================
    p43 = [
        get_image_flowable('extracted_imgs/p42_0_IM269.jpg', 440, 200),
        Paragraph('Gambar Wireframe Form Verifikasi Izin.', styles['Caption']),
        Spacer(1, 6),
        get_image_flowable('extracted_imgs/p42_1_IM270.jpg', 440, 200),
        Paragraph('Gambar Wireframe Laporan Presensi', styles['Caption']),
    ]
    pages.append(p43)

    # ==========================================
    # PAGE 44 (Body Page 40): B.2 USER INTERFACE (B.7 & B.8)
    # ==========================================
    p44 = [
        Paragraph('B.2 User Interface', styles['H2']),
        Spacer(1, 6),
        get_image_flowable('extracted_imgs/p43_0_IM273.jpg', 420, 180),
        Paragraph('Gambar User Interface Login Page', styles['Caption']),
        Spacer(1, 6),
        get_image_flowable('extracted_imgs/p43_1_IM274.jpg', 420, 180),
        Paragraph('Gambar User Interface Dashboard Guru', styles['Caption']),
    ]
    pages.append(p44)

    # ==========================================
    # PAGE 45 (Body Page 41): USER INTERFACE (B.9 & B.10)
    # ==========================================
    p45 = [
        get_image_flowable('extracted_imgs/p44_0_IM278.jpg', 420, 180),
        Paragraph('Gambar User Interface Waka Kurikulum', styles['Caption']),
        Spacer(1, 6),
        get_image_flowable('extracted_imgs/p44_1_IM279.jpg', 420, 180),
        Paragraph('Gambar User Interface Cetak Laporan Presensi', styles['Caption']),
    ]
    pages.append(p45)

    return pages


def generate():
    pages = get_all_pages()
    print(f"Total defined pages: {len(pages)}")

    story = []
    for i, p_story in enumerate(pages):
        story.extend(p_story)
        if i < len(pages) - 1:
            story.append(PageBreak())

    output_pdf = r'c:\laragon\www\ukk-project\SRS_School_Class_Progress_Mahirsya_Bhanu_Adyatma.pdf'
    doc = SimpleDocTemplate(
        output_pdf,
        pagesize=letter,
        leftMargin=MARGIN,
        rightMargin=MARGIN,
        topMargin=MARGIN,
        bottomMargin=MARGIN
    )

    print("Building exact 45-page PDF for Mahirsya Bhanu Adyatma...")
    doc.build(story, canvasmaker=NumberedSRS45)
    print(f"Build complete: {output_pdf}")

    # Also copy to docs/
    docs_dir = r'c:\laragon\www\ukk-project\docs'
    os.makedirs(docs_dir, exist_ok=True)
    import shutil
    shutil.copy2(output_pdf, os.path.join(docs_dir, 'SRS_School_Class_Progress_Mahirsya_Bhanu_Adyatma.pdf'))
    print("Copied to docs/ directory as well.")


if __name__ == '__main__':
    generate()
