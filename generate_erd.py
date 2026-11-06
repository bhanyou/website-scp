import os
from PIL import Image, ImageDraw, ImageFont

def create_erd_image_monochrome():
    WIDTH = 2520
    HEIGHT = 1680
    
    # Kanvas gambar latar belakang putih bersih / off-white formal
    img = Image.new("RGBA", (WIDTH, HEIGHT), (248, 250, 252, 255))
    draw = ImageDraw.Draw(img)

    # Font setup
    try:
        font_title = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 36)
        font_sub = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 17)
        font_head = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 18)
        font_field = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 14)
        font_bold = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 14)
        font_badge = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 11)
        font_legend = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 13)
        font_legend_b = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 13)
    except:
        font_title = ImageFont.load_default()
        font_sub = ImageFont.load_default()
        font_head = ImageFont.load_default()
        font_field = ImageFont.load_default()
        font_bold = ImageFont.load_default()
        font_badge = ImageFont.load_default()
        font_legend = ImageFont.load_default()
        font_legend_b = ImageFont.load_default()

    # TEMA TUNGGAL (UNIFIED PROFESSIONAL DEEP NAVY / SLATE)
    C_PRIMARY = (30, 41, 59, 255)         # Deep Slate / Navy (#1E293B) - 1 WARNA UTAMA RESMI
    C_HEADER_BG = (15, 23, 42, 255)       # Dark Navy (#0F172A)
    C_BG_CARD = (255, 255, 255, 255)
    C_BORDER_CARD = (203, 213, 225, 255)  # Border abu-abu halus
    C_ROW_ALT = (248, 250, 252, 255)      # Zebra row halus
    C_TEXT_MAIN = (15, 23, 42, 255)       # Hitam teks tajam
    C_TEXT_MUTED = (100, 116, 139, 255)   # Tipe data abu-abu
    C_LINE = (71, 85, 105, 220)           # Garis relasi tunggal seragam

    # Badge Kunci Seragam (Formal Neutral)
    C_BADGE_BG = (241, 245, 249, 255)
    C_BADGE_BORDER = (148, 163, 184, 255)
    C_BADGE_TEXT = (30, 41, 59, 255)

    # 1. Header Banner Formal (1 Warna Seragam)
    draw.rectangle([0, 0, WIDTH, 120], fill=C_HEADER_BG)
    draw.rectangle([0, 116, WIDTH, 120], fill=C_PRIMARY)
    
    draw.text((60, 25), "ENTITY RELATIONSHIP DIAGRAM (ERD) - DATABASE SCHEMA", fill=(255, 255, 255, 255), font=font_title)
    draw.text((60, 72), "School Class Progress • Sistem Manajemen KBM, Jurnal Guru Realtime, Presensi & Putaran Blok SMK/SMA", fill=(148, 163, 184, 255), font=font_sub)

    # Data 12 Tabel Database (Semua menggunakan 1 warna header C_PRIMARY yang seragam)
    tables = {
        # Kolom 1
        "users": {
            "x": 60, "y": 160, "w": 400,
            "title": "users", "desc": "Autentikasi & Akun Multi-Role",
            "columns": [
                ("id", "BIGINT", "PK"),
                ("name", "VARCHAR(255)", ""),
                ("username", "VARCHAR(100)", "UQ"),
                ("email", "VARCHAR(255)", "UQ"),
                ("password", "VARCHAR(255)", ""),
                ("role", "ENUM('waka','guru','kesiswaan','siswa')", ""),
                ("phone", "VARCHAR(25)", ""),
                ("photo", "VARCHAR(255)", ""),
                ("created_at", "TIMESTAMP", ""),
            ]
        },
        "teachers": {
            "x": 60, "y": 570, "w": 400,
            "title": "teachers", "desc": "Profil Tenaga Pendidik / Guru",
            "columns": [
                ("id", "BIGINT", "PK"),
                ("user_id", "BIGINT", "FK"),
                ("nip", "VARCHAR(50)", "UQ"),
                ("name", "VARCHAR(255)", ""),
                ("phone", "VARCHAR(25)", ""),
                ("address", "TEXT", ""),
                ("created_at", "TIMESTAMP", ""),
            ]
        },
        "school_parents": {
            "x": 60, "y": 920, "w": 400,
            "title": "school_parents", "desc": "Data Orang Tua / Wali Siswa",
            "columns": [
                ("id", "BIGINT", "PK"),
                ("user_id", "BIGINT", "FK"),
                ("name", "VARCHAR(255)", ""),
                ("phone", "VARCHAR(25)", ""),
                ("created_at", "TIMESTAMP", ""),
            ]
        },
        "majors": {
            "x": 60, "y": 1180, "w": 400,
            "title": "majors", "desc": "Program Keahlian / Jurusan",
            "columns": [
                ("id", "BIGINT", "PK"),
                ("code", "VARCHAR(20)", "UQ"),
                ("name", "VARCHAR(100)", ""),
                ("created_at", "TIMESTAMP", ""),
            ]
        },

        # Kolom 2: Siswa & Kelas
        "classes": {
            "x": 510, "y": 160, "w": 420,
            "title": "classes", "desc": "Rombongan Belajar & Wali Kelas",
            "columns": [
                ("id", "BIGINT", "PK"),
                ("name", "VARCHAR(50)", "UQ"),
                ("grade", "VARCHAR(10)", ""),
                ("major", "VARCHAR(100)", ""),
                ("homeroom_teacher_id", "BIGINT", "FK"),
                ("created_at", "TIMESTAMP", ""),
            ]
        },
        "students": {
            "x": 510, "y": 480, "w": 420,
            "title": "students", "desc": "Data Peserta Didik",
            "columns": [
                ("id", "BIGINT", "PK"),
                ("user_id", "BIGINT", "FK"),
                ("class_id", "BIGINT", "FK"),
                ("parent_id", "BIGINT", "FK"),
                ("previous_class_id", "BIGINT", "FK"),
                ("nis", "VARCHAR(50)", "UQ"),
                ("name", "VARCHAR(255)", ""),
                ("status", "VARCHAR(20)", ""),
                ("phone", "VARCHAR(25)", ""),
                ("address", "TEXT", ""),
                ("created_at", "TIMESTAMP", ""),
            ]
        },
        "rooms": {
            "x": 510, "y": 980, "w": 420,
            "title": "rooms", "desc": "Ruang Teori & Lab Praktek",
            "columns": [
                ("id", "BIGINT", "PK"),
                ("code", "VARCHAR(30)", "UQ"),
                ("name", "VARCHAR(100)", ""),
                ("capacity", "INT", ""),
                ("created_at", "TIMESTAMP", ""),
            ]
        },
        "subjects": {
            "x": 510, "y": 1240, "w": 420,
            "title": "subjects", "desc": "Mata Pelajaran KBM",
            "columns": [
                ("id", "BIGINT", "PK"),
                ("code", "VARCHAR(30)", "UQ"),
                ("name", "VARCHAR(150)", ""),
                ("created_at", "TIMESTAMP", ""),
            ]
        },

        # Kolom 3: Siklus Blok & Jadwal KBM
        "block_cycles": {
            "x": 980, "y": 160, "w": 450,
            "title": "block_cycles", "desc": "Putaran Siklus Blok & Kunci Jadwal",
            "columns": [
                ("id", "BIGINT", "PK"),
                ("cycle", "VARCHAR(50)", ""),
                ("start_date", "DATE", ""),
                ("end_date", "DATE", ""),
                ("is_active", "BOOLEAN", ""),
                ("is_locked", "BOOLEAN", ""),
                ("locked_by", "BIGINT", "FK"),
                ("locked_at", "TIMESTAMP", ""),
                ("created_at", "TIMESTAMP", ""),
            ]
        },
        "schedules": {
            "x": 980, "y": 600, "w": 450,
            "title": "schedules", "desc": "Jadwal Mengajar KBM Blok",
            "columns": [
                ("id", "BIGINT", "PK"),
                ("block_cycle_id", "BIGINT", "FK"),
                ("teacher_id", "BIGINT", "FK"),
                ("subject_id", "BIGINT", "FK"),
                ("class_id", "BIGINT", "FK"),
                ("room_id", "BIGINT", "FK"),
                ("day", "ENUM('Senin'-'Jumat')", ""),
                ("start_time", "TIME", ""),
                ("end_time", "TIME", ""),
                ("created_at", "TIMESTAMP", ""),
            ]
        },
        "lesson_hours": {
            "x": 980, "y": 1100, "w": 450,
            "title": "lesson_hours", "desc": "Master Jam Pelajaran & Istirahat",
            "columns": [
                ("id", "BIGINT", "PK"),
                ("day", "VARCHAR(20)", ""),
                ("period", "INT", ""),
                ("start_time", "TIME", ""),
                ("end_time", "TIME", ""),
                ("is_break", "BOOLEAN", ""),
                ("created_at", "TIMESTAMP", ""),
            ]
        },

        # Kolom 4: Jurnal & Presensi
        "journals": {
            "x": 1480, "y": 160, "w": 480,
            "title": "journals", "desc": "Jurnal KBM + Bukti Foto Realtime",
            "columns": [
                ("id", "BIGINT", "PK"),
                ("schedule_id", "BIGINT", "FK"),
                ("teacher_id", "BIGINT", "FK"),
                ("journal_date", "DATE", ""),
                ("material", "TEXT", ""),
                ("activity", "TEXT", ""),
                ("notes", "TEXT", ""),
                ("photo_path", "VARCHAR(255)", ""),
                ("submitted_at", "TIMESTAMP", ""),
                ("created_at", "TIMESTAMP", ""),
            ]
        },
        "attendances": {
            "x": 1480, "y": 660, "w": 480,
            "title": "attendances", "desc": "Presensi Kehadiran Siswa per Sesi",
            "columns": [
                ("id", "BIGINT", "PK"),
                ("student_id", "BIGINT", "FK"),
                ("schedule_id", "BIGINT", "FK"),
                ("date", "DATE", ""),
                ("status", "ENUM('Hadir','Sakit','Izin','Alpa')", ""),
                ("check_in", "TIME", ""),
                ("note", "TEXT", ""),
                ("created_at", "TIMESTAMP", ""),
            ]
        },
        "permission_requests": {
            "x": 1480, "y": 1070, "w": 480,
            "title": "permission_requests", "desc": "Pengajuan & Verifikasi Izin Siswa",
            "columns": [
                ("id", "BIGINT", "PK"),
                ("student_id", "BIGINT", "FK"),
                ("parent_id", "BIGINT", "FK"),
                ("permission_date", "DATE", ""),
                ("end_date", "DATE", ""),
                ("type", "ENUM('Sakit','Izin')", ""),
                ("reason", "TEXT", ""),
                ("document_path", "VARCHAR(255)", ""),
                ("status", "ENUM('pending','approved','rejected')", ""),
                ("verified_by", "BIGINT", "FK"),
                ("verified_at", "TIMESTAMP", ""),
                ("verification_note", "TEXT", ""),
                ("created_at", "TIMESTAMP", ""),
            ]
        },
    }

    # Fungsi Menggambar Kartu Tabel ERD 1 Warna Seragam
    def draw_table_card(t):
        x, y, w = t["x"], t["y"], t["w"]
        cols = t["columns"]
        header_h = 56
        row_h = 28
        card_h = header_h + (len(cols) * row_h) + 12

        # Card Shadow (Soft)
        for offset in range(3, 0, -1):
            draw.rounded_rectangle(
                [x - offset, y - offset, x + w + offset, y + card_h + offset],
                radius=12,
                fill=(226, 232, 240, 50)
            )

        # Card Background
        draw.rounded_rectangle([x, y, x + w, y + card_h], radius=10, fill=C_BG_CARD, outline=C_BORDER_CARD, width=1)

        # Table Header Banner (1 WARNA SERAGAM UNTUK SEMUA TABEL)
        draw.rounded_rectangle([x, y, x + w, y + header_h], radius=10, fill=C_PRIMARY)
        draw.rectangle([x, y + 25, x + w, y + header_h], fill=C_PRIMARY)

        # Title & Deskripsi
        draw.text((x + 16, y + 8), t["title"], fill=(255, 255, 255, 255), font=font_head)
        draw.text((x + 16, y + 32), t["desc"], fill=(203, 213, 225, 255), font=font_badge)

        # Kolom-kolom
        cur_y = y + header_h + 6
        for i, (col_name, col_type, key_type) in enumerate(cols):
            # Zebra row background
            if i % 2 == 1:
                draw.rectangle([x + 2, cur_y - 2, x + w - 2, cur_y + row_h - 4], fill=C_ROW_ALT)

            # Badge Key (Format Seragam & Rapi)
            badge_x = x + 14
            if key_type in ["PK", "FK", "UQ"]:
                draw.rounded_rectangle([badge_x, cur_y, badge_x + 24, cur_y + 17], radius=3, fill=C_BADGE_BG, outline=C_BADGE_BORDER, width=1)
                draw.text((badge_x + 4, cur_y + 1), key_type, fill=C_BADGE_TEXT, font=font_badge)

            # Column Name
            name_x = x + 46
            is_bold = key_type in ["PK", "FK"]
            draw.text((name_x, cur_y + 1), col_name, fill=C_TEXT_MAIN, font=font_bold if is_bold else font_field)

            # Column Type (Right aligned)
            type_w = draw.textlength(col_type, font=font_field)
            draw.text((x + w - type_w - 14, cur_y + 1), col_type, fill=C_TEXT_MUTED, font=font_field)

            cur_y += row_h

    # Gambar seluruh kartu tabel
    for k, t in tables.items():
        draw_table_card(t)

    # 4. Gambar Garis Relasi Antar Tabel (1 Warna Seragam C_LINE)
    def draw_connector(start_xy, end_xy, label=""):
        x1, y1 = start_xy
        x2, y2 = end_xy
        
        mid_x = (x1 + x2) // 2
        draw.line([(x1, y1), (mid_x, y1)], fill=C_LINE, width=2)
        draw.line([(mid_x, y1), (mid_x, y2)], fill=C_LINE, width=2)
        draw.line([(mid_x, y2), (x2, y2)], fill=C_LINE, width=2)
        
        # Bulatan terminal
        draw.ellipse([x1 - 3, y1 - 3, x1 + 3, y1 + 3], fill=C_LINE)
        draw.ellipse([x2 - 3, y2 - 3, x2 + 3, y2 + 3], fill=C_LINE)

        if label:
            lx = mid_x + 6
            ly = (y1 + y2) // 2 - 8
            lw = draw.textlength(label, font=font_badge) + 8
            draw.rounded_rectangle([lx - 2, ly - 2, lx + lw, ly + 16], radius=3, fill=(255, 255, 255, 240), outline=C_BORDER_CARD)
            draw.text((lx + 4, ly), label, fill=C_PRIMARY, font=font_badge)

    # Seluruh relasi digambar menggunakan 1 warna garis yang konsisten
    draw_connector((460, 240), (510, 240), label="1 : 1")      # users -> teachers/classes
    draw_connector((460, 640), (510, 280), label="1 : N")      # teachers -> classes (wali)
    draw_connector((460, 680), (980, 680), label="1 : N")      # teachers -> schedules
    draw_connector((460, 720), (1480, 320), label="1 : N")     # teachers -> journals
    draw_connector((930, 260), (980, 740), label="1 : N")      # classes -> schedules
    draw_connector((930, 320), (510, 560), label="1 : N")      # classes -> students
    draw_connector((930, 1300), (980, 780), label="1 : N")     # subjects -> schedules
    draw_connector((930, 1040), (980, 820), label="1 : N")     # rooms -> schedules
    draw_connector((1430, 260), (1480, 260), label="1 : N")   # block_cycles -> schedules
    draw_connector((1430, 720), (1480, 280), label="1 : N")   # schedules -> journals
    draw_connector((1430, 780), (1480, 760), label="1 : N")   # schedules -> attendances
    draw_connector((930, 600), (1480, 720), label="1 : N")     # students -> attendances
    draw_connector((930, 660), (1480, 1140), label="1 : N")    # students -> permission_requests

    # 5. Kolom 5: Panel Informasi & Legenda (1 Warna Seragam)
    leg_x = 2000
    leg_y = 160
    leg_w = 460
    leg_h = 1380

    draw.rounded_rectangle([leg_x, leg_y, leg_x + leg_w, leg_y + leg_h], radius=12, fill=C_BG_CARD, outline=C_BORDER_CARD, width=1)
    
    # Header Legenda
    draw.rounded_rectangle([leg_x, leg_y, leg_x + leg_w, leg_y + 54], radius=12, fill=C_PRIMARY)
    draw.rectangle([leg_x, leg_y + 25, leg_x + leg_w, leg_y + 54], fill=C_PRIMARY)
    draw.text((leg_x + 18, leg_y + 16), "PANDUAN & KETERANGAN ERD", fill=(255, 255, 255, 255), font=font_head)

    cy = leg_y + 70
    # Bagian 1: Kunci Atribut
    draw.text((leg_x + 18, cy), "1. Notasi Kunci Atribut", fill=C_TEXT_MAIN, font=font_legend_b)
    cy += 28
    
    # PK Badge
    draw.rounded_rectangle([leg_x + 18, cy, leg_x + 48, cy + 20], radius=3, fill=C_BADGE_BG, outline=C_BADGE_BORDER, width=1)
    draw.text((leg_x + 24, cy + 2), "PK", fill=C_BADGE_TEXT, font=font_badge)
    draw.text((leg_x + 58, cy + 1), "Primary Key (Kunci Utama Unik Tabel)", fill=C_TEXT_MAIN, font=font_legend)
    cy += 28

    # FK Badge
    draw.rounded_rectangle([leg_x + 18, cy, leg_x + 48, cy + 20], radius=3, fill=C_BADGE_BG, outline=C_BADGE_BORDER, width=1)
    draw.text((leg_x + 24, cy + 2), "FK", fill=C_BADGE_TEXT, font=font_badge)
    draw.text((leg_x + 58, cy + 1), "Foreign Key (Kunci Relasi Antar Tabel)", fill=C_TEXT_MAIN, font=font_legend)
    cy += 28

    # UQ Badge
    draw.rounded_rectangle([leg_x + 18, cy, leg_x + 48, cy + 20], radius=3, fill=C_BADGE_BG, outline=C_BADGE_BORDER, width=1)
    draw.text((leg_x + 24, cy + 2), "UQ", fill=C_BADGE_TEXT, font=font_badge)
    draw.text((leg_x + 58, cy + 1), "Unique Index (Nilai Kolom Unik)", fill=C_TEXT_MAIN, font=font_legend)
    cy += 40

    # Bagian 2: Kardinalitas Hubungan
    draw.text((leg_x + 18, cy), "2. Kardinalitas Hubungan (Relationship)", fill=C_TEXT_MAIN, font=font_legend_b)
    cy += 26
    draw.line([(leg_x + 18, cy + 10), (leg_x + 58, cy + 10)], fill=C_LINE, width=2)
    draw.text((leg_x + 68, cy + 1), "1 : 1  = Satu entitas berelasi tepat ke satu entitas", fill=C_TEXT_MAIN, font=font_legend)
    cy += 26
    draw.line([(leg_x + 18, cy + 10), (leg_x + 58, cy + 10)], fill=C_LINE, width=2)
    draw.text((leg_x + 68, cy + 1), "1 : N  = Satu entitas induk memiliki banyak data anak", fill=C_TEXT_MAIN, font=font_legend)
    cy += 40

    # Bagian 3: Kelompok Modul Database
    draw.text((leg_x + 18, cy), "3. Struktur Entitas Tabel", fill=C_TEXT_MAIN, font=font_legend_b)
    cy += 26
    modules = [
        ("Otentikasi & Profil", "users"),
        ("Tenaga Pendidik", "teachers, classes (wali)"),
        ("Peserta Didik & Wali", "students, school_parents"),
        ("Master Akademik", "subjects, rooms, majors"),
        ("Siklus Blok & Alokasi", "block_cycles, lesson_hours"),
        ("Penjadwalan KBM", "schedules (Pusat Relasi)"),
        ("Jurnal Mengajar", "journals (Foto Realtime HP)"),
        ("Presensi Siswa", "attendances (H/S/I/A)"),
        ("Perizinan Siswa", "permission_requests"),
    ]
    for m_label, m_tables in modules:
        draw.rectangle([leg_x + 18, cy + 4, leg_x + 24, cy + 14], fill=C_PRIMARY)
        draw.text((leg_x + 32, cy), m_label, fill=C_TEXT_MAIN, font=font_legend_b)
        draw.text((leg_x + 210, cy), f": {m_tables}", fill=C_TEXT_MUTED, font=font_legend)
        cy += 25

    cy += 30
    # Bagian 4: Fitur Utama Sistem
    draw.text((leg_x + 18, cy), "4. Arsitektur Unggulan Sistem", fill=C_TEXT_MAIN, font=font_legend_b)
    cy += 26
    features = [
        "• Sistem Siklus Blok 2 Mingguan (Putaran 1 & 2)",
        "• Penguncian Jadwal Mandiri oleh Waka Kurikulum",
        "• Import & Export Massal CSV Jadwal & Master",
        "• Jurnal KBM Wajib Foto Kamera HP Realtime",
        "• Presensi Siswa Terintegrasi Langsung per Jam KBM",
        "• Cetak Bukti Lembar KBM Pas 1 Halaman A4 Kedinasan",
        "• Approval Izin Bertingkat (Wali Kelas & Kesiswaan/BK)",
    ]
    for feat in features:
        draw.text((leg_x + 18, cy), feat, fill=C_TEXT_MUTED, font=font_legend)
        cy += 24

    cy += 30
    # Info Footer Legenda
    draw.rounded_rectangle([leg_x + 18, cy, leg_x + leg_w - 18, cy + 80], radius=8, fill=(241, 245, 249, 255), outline=C_BORDER_CARD)
    draw.text((leg_x + 30, cy + 12), "Project: UKK School Class Progress", fill=C_TEXT_MAIN, font=font_legend_b)
    draw.text((leg_x + 30, cy + 34), "Backend: Laravel 11 • Mobile: Flutter", fill=C_TEXT_MUTED, font=font_legend)
    draw.text((leg_x + 30, cy + 54), "Format Skema: 1 Warna Monokrom / Formal", fill=C_PRIMARY, font=font_badge)

    # 6. Simpan File PNG Resolusi Tinggi
    output_dirs = [
        "public/images",
        "C:/Users/HP/.gemini/antigravity/brain/8a9f148b-361d-4e71-aa53-ad10bf5065ad"
    ]
    for d in output_dirs:
        os.makedirs(d, exist_ok=True)
    
    png_path_public = "public/images/erd_database.png"
    png_path_root = "erd_database.png"
    png_path_artifact = "C:/Users/HP/.gemini/antigravity/brain/8a9f148b-361d-4e71-aa53-ad10bf5065ad/erd_database.png"

    img_rgb = img.convert("RGB")
    img_rgb.save(png_path_public, format="PNG", quality=95)
    img_rgb.save(png_path_root, format="PNG", quality=95)
    img_rgb.save(png_path_artifact, format="PNG", quality=95)

    print("ERD Monochrome PNG generated successfully!")

if __name__ == "__main__":
    create_erd_image_monochrome()
