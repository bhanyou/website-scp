"""
srs_styles.py
ParagraphStyle definitions for IEEE-standard SRS PDF for School Class Progress (SCP).
"""

from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle


def get_styles():
    base = getSampleStyleSheet()

    styles = {
        'Normal': ParagraphStyle(
            'SRSNormal',
            parent=base['Normal'],
            fontName='Helvetica',
            fontSize=9.5,
            leading=13.5,
            textColor=colors.black
        ),
        'Italic': ParagraphStyle(
            'SRSItalic',
            parent=base['Normal'],
            fontName='Helvetica-Oblique',
            fontSize=9,
            leading=13,
            textColor=colors.black
        ),
        'Bullet': ParagraphStyle(
            'SRSBullet',
            parent=base['Normal'],
            fontName='Helvetica',
            fontSize=9.5,
            leading=13.5,
            leftIndent=15,
            firstLineIndent=-10,
            spaceAfter=3,
            textColor=colors.black
        ),
        'CoverTitle': ParagraphStyle(
            'SRSCoverTitle',
            parent=base['Normal'],
            fontName='Helvetica-Bold',
            fontSize=22,
            leading=26,
            alignment=1,  # Center
            textColor=colors.black
        ),
        'CoverSub': ParagraphStyle(
            'SRSCoverSub',
            parent=base['Normal'],
            fontName='Helvetica',
            fontSize=13,
            leading=18,
            alignment=1,  # Center
            textColor=colors.black
        ),
        'CoverAppName': ParagraphStyle(
            'SRSCoverAppName',
            parent=base['Normal'],
            fontName='Helvetica-Bold',
            fontSize=20,
            leading=24,
            alignment=1,  # Center
            textColor=colors.black
        ),
        'CoverMeta': ParagraphStyle(
            'SRSCoverMeta',
            parent=base['Normal'],
            fontName='Helvetica-Bold',
            fontSize=10.5,
            leading=15,
            alignment=2,  # Right
            textColor=colors.black
        ),
        'H1': ParagraphStyle(
            'SRSH1',
            parent=base['Normal'],
            fontName='Helvetica-Bold',
            fontSize=14,
            leading=18,
            spaceBefore=12,
            spaceAfter=8,
            textColor=colors.black
        ),
        'H2': ParagraphStyle(
            'SRSH2',
            parent=base['Normal'],
            fontName='Helvetica-Bold',
            fontSize=11,
            leading=15,
            spaceBefore=10,
            spaceAfter=6,
            textColor=colors.black
        ),
        'H3': ParagraphStyle(
            'SRSH3',
            parent=base['Normal'],
            fontName='Helvetica-Bold',
            fontSize=9.5,
            leading=13,
            spaceBefore=8,
            spaceAfter=4,
            textColor=colors.black
        ),
        'TH': ParagraphStyle(
            'SRSTH',
            parent=base['Normal'],
            fontName='Helvetica-Bold',
            fontSize=8.5,
            leading=11,
            alignment=0,  # Left
            textColor=colors.black
        ),
        'TD': ParagraphStyle(
            'SRSTD',
            parent=base['Normal'],
            fontName='Helvetica',
            fontSize=8.5,
            leading=11,
            alignment=0,
            textColor=colors.black
        ),
        'TDBold': ParagraphStyle(
            'SRSTDBold',
            parent=base['Normal'],
            fontName='Helvetica-Bold',
            fontSize=8.5,
            leading=11,
            alignment=0,
            textColor=colors.black
        ),
        'Caption': ParagraphStyle(
            'SRSCaption',
            parent=base['Normal'],
            fontName='Helvetica-Bold',
            fontSize=9,
            leading=12,
            alignment=1,  # Center
            spaceBefore=6,
            spaceAfter=10,
            textColor=colors.black
        ),
        'TOCItem': ParagraphStyle(
            'SRSTOCItem',
            parent=base['Normal'],
            fontName='Helvetica',
            fontSize=7.2,
            leading=9.0,
            textColor=colors.black
        ),
        'TOCItemBold': ParagraphStyle(
            'SRSTOCItemBold',
            parent=base['Normal'],
            fontName='Helvetica-Bold',
            fontSize=7.5,
            leading=9.5,
            textColor=colors.HexColor('#0f172a')
        ),
        'TOCPage': ParagraphStyle(
            'SRSTOCPage',
            parent=base['Normal'],
            fontName='Helvetica',
            fontSize=7.2,
            leading=9.0,
            alignment=2,  # Right
            textColor=colors.HexColor('#1f2937')
        ),
        'TOCPageBold': ParagraphStyle(
            'SRSTOCPageBold',
            parent=base['Normal'],
            fontName='Helvetica-Bold',
            fontSize=7.5,
            leading=9.5,
            alignment=2,  # Right
            textColor=colors.HexColor('#0f172a')
        ),
    }

    return styles


print("Style definitions ready.")
