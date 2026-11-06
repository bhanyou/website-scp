"""
srs_base.py
Base setup, layout definitions, custom Flowables, Canvas numbering, and helper functions
for generating the IEEE-standard SRS PDF for School Class Progress (SCP).
"""

import os
from PIL import Image as PILImage
from reportlab.lib.pagesizes import letter
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, KeepTogether, Image, Flowable
)
from reportlab.pdfgen import canvas

PAGE_WIDTH, PAGE_HEIGHT = letter
MARGIN = 54  # 0.75 inch = 54 pt
USABLE_WIDTH = PAGE_WIDTH - 2 * MARGIN  # 504 pt


class TopRuleFlowable(Flowable):
    """Draws the prominent black horizontal bar at the top of the cover page."""
    def __init__(self, width=USABLE_WIDTH, height=3):
        super().__init__()
        self.width = width
        self.height = height

    def draw(self):
        self.canv.saveState()
        self.canv.setFillColor(colors.black)
        self.canv.rect(0, 0, self.width, self.height, fill=1, stroke=0)
        self.canv.restoreState()


class BookmarkFlowable(Flowable):
    """Stores the current page number for TOC two-pass resolution."""
    def __init__(self, key, store):
        super().__init__()
        self.key = key
        self.store = store
        self.width = 0
        self.height = 0

    def draw(self):
        self.store[self.key] = self.canv._pageNumber


class NumberedSRS(canvas.Canvas):
    """
    Two-pass canvas for IEEE SRS document:
    - Page 1: Cover page (Karl Wiegers copyright notice at footer)
    - Page 2 to 3: Preliminary pages (Header + Roman numeral footer: Page ii, Page iii)
    - Page 4+: Main body (Header + Arabic page numbering starting at Page 1)
    """
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
            # Cover page footer
            self.saveState()
            self.setFont('Helvetica-Oblique', 8)
            self.setFillColor(colors.HexColor('#333333'))
            self.drawString(
                MARGIN, 36,
                'Copyright \u00a9 1999 by Karl E. Wiegers. Permission is granted to use, modify, and distribute this document.'
            )
            self.restoreState()
            return
        elif p <= 3:
            # Preliminary pages (TOC & Revision History)
            self.saveState()
            self.setFont('Helvetica-Oblique', 8.5)
            self.setFillColor(colors.HexColor('#444444'))
            self.drawString(MARGIN, PAGE_HEIGHT - 36, 'Software Requirements Specification for SCP')
            roman = self.to_roman(p)
            self.drawRightString(PAGE_WIDTH - MARGIN, PAGE_HEIGHT - 36, f'Page {roman}')
            self.restoreState()
            return
        else:
            # Main body pages starting from Bab 1 = Page 1
            arabic_page = p - 3
            self.saveState()
            self.setFont('Helvetica-Oblique', 8.5)
            self.setFillColor(colors.HexColor('#444444'))
            self.drawString(MARGIN, PAGE_HEIGHT - 36, 'Software Requirements Specification for SCP')
            self.drawRightString(PAGE_WIDTH - MARGIN, PAGE_HEIGHT - 36, f'Page {arabic_page}')
            self.restoreState()


def get_image_flowable(path, max_width=470, max_height=300):
    """Safely loads and scales an image maintaining original aspect ratio."""
    if not os.path.exists(path):
        return Paragraph(f'[Image not found: {path}]', getSampleStyleSheet()['Normal'])
    with PILImage.open(path) as img:
        w, h = img.size
        aspect = h / float(w)
        target_w = min(max_width, w)
        target_h = target_w * aspect
        if target_h > max_height:
            target_h = max_height
            target_w = target_h / aspect
        return Image(path, width=target_w, height=target_h)


def create_table(data, col_widths, is_header=True, custom_style=None):
    """Creates a neatly styled table with thin grid lines and padded cells."""
    base_style = [
        ('GRID', (0, 0), (-1, -1), 0.5, colors.HexColor('#666666')),
        ('VALIGN', (0, 0), (-1, -1), 'TOP'),
        ('TOPPADDING', (0, 0), (-1, -1), 3),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 3),
        ('LEFTPADDING', (0, 0), (-1, -1), 4),
        ('RIGHTPADDING', (0, 0), (-1, -1), 4),
    ]
    if is_header:
        base_style.extend([
            ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#EAEAEA')),
            ('BOTTOMPADDING', (0, 0), (-1, 0), 4),
        ])
    if custom_style:
        base_style.extend(custom_style)
    return Table(data, colWidths=col_widths, style=TableStyle(base_style))


print("Base setup completed.")
