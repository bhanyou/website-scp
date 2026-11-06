"""
generate_srs_pdf.py
Full build script for SRS PDF of School Class Progress (SCP).
Generates an IEEE 830-compliant document customized to the exact Laravel + Flutter project.
Author: Mahirsya Bhanu Adyatma (Absen 22)
"""

import os
import shutil
from reportlab.lib.pagesizes import letter
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, PageBreak, KeepTogether, Table, TableStyle
)
from reportlab.pdfgen import canvas
from srs_base import (
    PAGE_WIDTH, PAGE_HEIGHT, MARGIN, USABLE_WIDTH,
    TopRuleFlowable, BookmarkFlowable, NumberedSRS, get_image_flowable, create_table
)
from srs_styles import get_styles
from srs_content_part1 import (
    build_cover, build_revision_history, build_bab1, build_bab2, build_bab3
)
from srs_content_part2 import (
    build_bab4, build_bab5, build_bab6
)
from srs_content_part3 import (
    build_appendix_a, build_appendix_b, build_appendix_c
)

styles = get_styles()


def build_pdf_content(store, is_final=False):
    story = []

    # 1. Cover
    story.append(TopRuleFlowable(USABLE_WIDTH, height=3))
    story.append(Spacer(1, 100))
    story.append(Paragraph('Spesifikasi Kebutuhan<br/>Perangkat Lunak', styles['CoverTitle']))
    story.append(Spacer(1, 30))
    story.append(Paragraph('Untuk', styles['CoverSub']))
    story.append(Spacer(1, 20))
    story.append(Paragraph('School Class Progress (SCP)', styles['CoverAppName']))
    story.append(Spacer(1, 80))
    story.append(Paragraph('<b>Versi 1.0 disetujui</b>', styles['CoverMeta']))
    story.append(Spacer(1, 30))
    story.append(Paragraph('<b>Disiapkan oleh:</b><br/>Mahirsya Bhanu Adyatma<br/>No. Absen: 22', styles['CoverMeta']))
    story.append(Spacer(1, 30))
    story.append(Paragraph('<b>SMK Negeri 1 Bantul</b>', styles['CoverMeta']))
    story.append(Spacer(1, 30))
    story.append(Paragraph('10 Oktober 2026', styles['CoverMeta']))
    story.append(PageBreak())

    # 2. Table of Contents
    story.append(Paragraph('Table of Contents', styles['H1']))
    story.append(Spacer(1, 4))

    toc_data = [
        ('Table of Contents', '', 'ii', True, 0),
        ('Revision History', 'rev_hist', 'iii', True, 0),
        ('1. Pendahuluan', 'sec_1', '', True, 0),
        ('1.1 Tujuan', 'sec_1_1', '', False, 1),
        ('1.2 Definisi dan Istilah', 'sec_1_2', '', False, 1),
        ('1.3 Suggestions Pembaca yang disarankan membaca dokumen ini', 'sec_1_3', '', False, 1),
        ('1.4 Ruang Lingkup Sistem', 'sec_1_4', '', False, 1),
        ('1.5 Sistem Penomoran', 'sec_1_5', '', False, 1),
        ('1.6 Referensi', 'sec_1_6', '', False, 1),
        ('1.7 Ikhtisar Dokumen', 'sec_1_7', '', False, 1),
        ('2. Deskripsi Umum Sistem', 'sec_2', '', True, 0),
        ('2.1 Perspektif Perangkat Lunak', 'sec_2_1', '', False, 1),
        ('2.2 Fungsi Perangkat Lunak', 'sec_2_2', '', False, 1),
        ('2.3 Karakteristik dan Klasifikasi Pengguna', 'sec_2_3', '', False, 1),
        ('2.4 Lingkungan Operasi', 'sec_2_4', '', False, 1),
        ('2.5 Batasan Desain dan Implementasi', 'sec_2_5', '', False, 1),
        ('2.6 Dokumentasi bagi Pengguna', 'sec_2_6', '', False, 1),
        ('2.7 Asumsi dan Ketergantungan', 'sec_2_7', '', False, 1),
        ('3. Kebutuhan Antarmuka Eksternal', 'sec_3', '', True, 0),
        ('3.1 Antarmuka Pengguna', 'sec_3_1', '', False, 1),
        ('3.2 Antarmuka Perangkat Keras', 'sec_3_2', '', False, 1),
        ('3.3 Antarmuka Perangkat Lunak', 'sec_3_3', '', False, 1),
        ('3.4 Antarmuka Komunikasi', 'sec_3_4', '', False, 1),
        ('4. Fitur Sistem', 'sec_4', '', True, 0),
        ('4.1 Autentikasi dan Manajemen Profile', 'sec_4_1', '', False, 1),
        ('4.2 Manajemen Data Master dan Struktur Akademik', 'sec_4_2', '', False, 1),
        ('4.3 Manajemen Pengguna dan Akun', 'sec_4_3', '', False, 1),
        ('4.4 Penjadwalan Pelajaran dan Pencegahan Bentrok', 'sec_4_4', '', False, 1),
        ('4.5 Pengaturan Siklus Blok dan Kunci Jadwal', 'sec_4_5', '', False, 1),
        ('4.6 Import Jadwal Pelajaran', 'sec_4_6', '', False, 1),
        ('4.7 Jurnal Mengajar dan Bukti Foto Kegiatan', 'sec_4_7', '', False, 1),
        ('4.8 Presensi dan Sinkronisasi Otomatis Dua Arah', 'sec_4_8', '', False, 1),
        ('4.9 Pengajuan dan Verifikasi Izin Siswa', 'sec_4_9', '', False, 1),
        ('4.10 Alert Keterlambatan Pengisian Jurnal Mengajar', 'sec_4_10', '', False, 1),
        ('4.11 Rekapitulasi dan Cetak Laporan Presensi', 'sec_4_11', '', False, 1),
        ('4.12 Layanan REST API untuk Aplikasi Mobile Flutter', 'sec_4_12', '', False, 1),
        ('4.13 Pemulihan Kata Sandi dengan OTP Email', 'sec_4_13', '', False, 1),
        ('5. Kebutuhan Non Fungsional Lainnya', 'sec_5', '', True, 0),
        ('5.1 Kebutuhan Kinerja', 'sec_5_1', '', False, 1),
        ('5.2 Kebutuhan Keamanan', 'sec_5_2', '', False, 1),
        ('5.3 Kebutuhan Perlindungan Keamanan', 'sec_5_3', '', False, 1),
        ('5.4 Atribut Kualitas Perangkat Lunak', 'sec_5_4', '', False, 1),
        ('5.5 Aturan Bisnis', 'sec_5_5', '', False, 1),
        ('6. Kebutuhan Lain', 'sec_6', '', True, 0),
        ('6.1 Kebutuhan Basis Data', 'sec_6_1', '', False, 1),
        ('6.2 Kebutuhan Internasionalisasi', 'sec_6_2', '', False, 1),
        ('6.3 Kebutuhan Penyimpanan Berkas', 'sec_6_3', '', False, 1),
        ('6.4 Kebutuhan Pengujian dan Verifikasi', 'sec_6_4', '', False, 1),
        ('6.5 Akun Default Pengujian', 'sec_6_5', '', False, 1),
        ('Appendix A: Model Analisa', 'app_a', '', True, 0),
        ('A.1 Usecase Diagram', 'app_a_1', '', False, 1),
        ('A.2 Usecase Description', 'app_a_2', '', False, 1),
        ('A.3 Activity Diagram', 'app_a_3', '', False, 1),
        ('A.4 Class Diagram / ERD', 'app_a_4', '', False, 1),
        ('Appendix B: Desain Antarmuka Sistem', 'app_b', '', True, 0),
        ('B.1 Wireframe', 'app_b_1', '', False, 1),
        ('B.2 User Interface', 'app_b_2', '', False, 1),
        ('Appendix C: Daftar Yang Akan Ditentukan Kemudian (TBD)', 'app_c', '', True, 0),
    ]

    toc_rows = []
    c_temp = canvas.Canvas('dummy.pdf')
    c_temp.setFont('Helvetica', 8)
    dw = c_temp.stringWidth('. ', 'Helvetica', 8)

    for title, key, direct_page, is_bold, indent in toc_data:
        font_name = 'Helvetica-Bold' if is_bold else 'Helvetica'
        font_size = 8.5 if is_bold else 8
        p_style = styles['TOCItemBold'] if is_bold else styles['TOCItem']
        r_style = styles['TOCPageBold'] if is_bold else styles['TOCPage']
        prefix = '&nbsp;&nbsp;&nbsp;&nbsp;' if indent == 1 else ''
        indent_px = 15 if indent == 1 else 0

        if direct_page:
            page_str = direct_page
        elif key in store:
            # arabic page is p - 3
            page_str = str(store[key] - 3)
        else:
            page_str = '...'

        c_temp.setFont(font_name, font_size)
        text_w = c_temp.stringWidth(title, font_name, font_size) + indent_px
        avail_w = 445 - text_w
        num_dots = max(0, int(avail_w / dw))
        dots = '. ' * num_dots

        toc_text = f"{prefix}{title} <font color='#888888'>{dots}</font>"
        toc_rows.append([Paragraph(toc_text, p_style), Paragraph(page_str, r_style)])

    t_toc = Table(toc_rows, colWidths=[460, 44])
    t_toc.setStyle(TableStyle([
        ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
        ('TOPPADDING', (0, 0), (-1, -1), 0),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 0),
        ('LEFTPADDING', (0, 0), (-1, -1), 0),
        ('RIGHTPADDING', (0, 0), (-1, -1), 0),
    ]))

    story.append(t_toc)
    story.append(PageBreak())

    # 3. Revision History
    story.extend(build_revision_history(styles, store))

    # 4. Bab 1
    story.extend(build_bab1(styles, store))
    story.append(PageBreak())

    # 5. Bab 2
    story.extend(build_bab2(styles, store))
    story.append(PageBreak())

    # 6. Bab 3
    story.extend(build_bab3(styles, store))
    story.append(PageBreak())

    # 7. Bab 4
    story.extend(build_bab4(styles, store))
    story.append(PageBreak())

    # 8. Bab 5
    story.extend(build_bab5(styles, store))
    story.append(PageBreak())

    # 9. Bab 6
    story.extend(build_bab6(styles, store))
    story.append(PageBreak())

    # 10. Appendix A
    story.extend(build_appendix_a(styles, store))
    story.append(PageBreak())

    # 11. Appendix B
    story.extend(build_appendix_b(styles, store))
    story.append(PageBreak())

    # 12. Appendix C
    story.extend(build_appendix_c(styles, store))

    return story


def generate():
    store = {}
    temp_pdf = r'c:\laragon\www\ukk-project\srs_pass1.pdf'
    output_pdf = r'c:\laragon\www\ukk-project\SRS_School_Class_Progress_Mahirsya_Bhanu_Adyatma.pdf'

    print("Pass 1: Collecting page bookmarks...")
    doc1 = SimpleDocTemplate(
        temp_pdf,
        pagesize=letter,
        leftMargin=MARGIN,
        rightMargin=MARGIN,
        topMargin=MARGIN,
        bottomMargin=MARGIN
    )
    story1 = build_pdf_content(store, is_final=False)
    doc1.build(story1, canvasmaker=NumberedSRS)
    print(f"Pass 1 finished. Recorded {len(store)} bookmarks.")

    print("Pass 2: Building final SRS PDF with resolved page numbers...")
    doc2 = SimpleDocTemplate(
        output_pdf,
        pagesize=letter,
        leftMargin=MARGIN,
        rightMargin=MARGIN,
        topMargin=MARGIN,
        bottomMargin=MARGIN
    )
    story2 = build_pdf_content(store, is_final=True)
    doc2.build(story2, canvasmaker=NumberedSRS)

    if os.path.exists(temp_pdf):
        try:
            os.remove(temp_pdf)
        except Exception:
            pass

    if os.path.exists('dummy.pdf'):
        try:
            os.remove('dummy.pdf')
        except Exception:
            pass

    print(f"PDF Generation Completed successfully: {output_pdf}")
    return output_pdf


if __name__ == '__main__':
    generate()
