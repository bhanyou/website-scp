"""
generate_45p_srs.py
Script to generate the EXACT 45-page SRS PDF matching the friend's template page-by-page.
Author: Mahirsya Bhanu Adyatma (Absen 22)
SMK Negeri 1 Bantul
"""

import os
from reportlab.lib.pagesizes import letter
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, PageBreak, KeepTogether, Table, TableStyle
)
from reportlab.pdfgen import canvas
from srs_base import (
    PAGE_WIDTH, PAGE_HEIGHT, MARGIN, USABLE_WIDTH,
    TopRuleFlowable, BookmarkFlowable, get_image_flowable, create_table
)
from srs_styles import get_styles

styles = get_styles()


class NumberedSRS45(canvas.Canvas):
    """
    45-Page Canvas:
    - Page 1: Cover (Karl Wiegers copyright notice at footer)
    - Page 2: Table of Contents (Header: Page ii)
    - Page 3: Riwayat Perubahan (Header: Page iii)
    - Page 4: Blank separator page (no header/footer)
    - Page 5 to 45: Main body pages (Header: Page 1 to Page 41)
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
            # Body pages (p=5 -> Page 1, ..., p=45 -> Page 41)
            arabic_page = p - 4
            self.saveState()
            self.setFont('Helvetica-Oblique', 8.5)
            self.setFillColor(colors.HexColor('#444444'))
            self.drawString(MARGIN, PAGE_HEIGHT - 36, 'Software Requirements Specification for SCP')
            self.drawRightString(PAGE_WIDTH - MARGIN, PAGE_HEIGHT - 36, f'Page {arabic_page}')
            self.restoreState()


print("NumberedSRS45 canvas ready.")
