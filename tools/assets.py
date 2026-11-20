#!/usr/bin/env python
"""Generator aset visual starterpack PA (deterministik, tanpa foto berhak cipta).

Pemakaian:
    python tools/assets.py --out=D:/projects/pa-starterpack/assets

Menghasilkan:
  slider/hero-1..3.jpg (+ .webp)   1600x700
  ikon/*.svg                       11 ikon 24x24 (stroke currentColor)
  flyer/*.png                      4 lembar A4 2480x3508 bertanda CONTOH
  logo/logo-satker-placeholder.svg + .png (512)
  og/og-default.jpg                1200x630

Tidak ada angka acak: setiap elemen memakai koordinat tetap sehingga hash
berkas stabil antar build (mudah dibandingkan di rilis).
"""

from __future__ import annotations

import argparse
import textwrap
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

PRIMARY = (5, 110, 79)          # #056e4f
PRIMARY_DARK = (3, 77, 55)      # #034d37
PRIMARY_LIGHT = (230, 243, 239) # #e6f3ef
SECONDARY = (255, 153, 0)       # #ff9900
INK = (26, 31, 28)
MUTED = (90, 103, 99)
WHITE = (255, 255, 255)
LINE = (219, 227, 224)

FONT_REG = "C:/Windows/Fonts/segoeui.ttf"
FONT_BOLD = "C:/Windows/Fonts/segoeuib.ttf"


def font(size: int, bold: bool = False) -> ImageFont.FreeTypeFont:
    return ImageFont.truetype(FONT_BOLD if bold else FONT_REG, size)


def gradient(size: tuple[int, int], top: tuple[int, int, int], bottom: tuple[int, int, int]) -> Image.Image:
    w, h = size
    img = Image.new("RGB", size, top)
    draw = ImageDraw.Draw(img)
    for y in range(h):
        t = y / max(h - 1, 1)
        color = tuple(int(top[i] + (bottom[i] - top[i]) * t) for i in range(3))
        draw.line([(0, y), (w, y)], fill=color)
    return img


def wrapped(draw: ImageDraw.ImageDraw, text: str, fnt: ImageFont.FreeTypeFont, width: int) -> list[str]:
    words, lines, current = text.split(), [], ""
    for word in words:
        trial = f"{current} {word}".strip()
        if draw.textlength(trial, font=fnt) <= width:
            current = trial
        else:
            if current:
                lines.append(current)
            current = word
    if current:
        lines.append(current)
    return lines


def draw_text_block(draw, xy, text, fnt, fill, width, line_gap=10):
    x, y = xy
    for line in wrapped(draw, text, fnt, width):
        draw.text((x, y), line, font=fnt, fill=fill)
        y += fnt.size + line_gap
    return y


def courthouse(draw, box, color, alpha_layer=None):
    """Siluet gedung peradilan sederhana (garis, bukan foto)."""
    x0, y0, x1, y1 = box
    w = x1 - x0
    h = y1 - y0
    base_y = y1
    # alas
    draw.rectangle([x0, base_y - int(h * .06), x1, base_y], fill=color)
    # badan + tiang
    draw.rectangle([x0 + int(w * .07), base_y - int(h * .62), x1 - int(w * .07), base_y - int(h * .06)], outline=color, width=6)
    for i in range(5):
        cx = x0 + int(w * .14) + int(w * .18 * i)
        draw.line([(cx, base_y - int(h * .58)), (cx, base_y - int(h * .14))], fill=color, width=6)
    # pedimen
    draw.polygon(
        [(x0 + int(w * .03), base_y - int(h * .62)), (x1 - int(w * .03), base_y - int(h * .62)),
         (x0 + w // 2, base_y - int(h * .86))],
        outline=color,
        width=6,
    )
    # puncak
    draw.line([(x0 + w // 2, base_y - int(h * .86)), (x0 + w // 2, base_y - int(h * .96))], fill=color, width=6)


def arc_pattern(img: Image.Image, center, radii, color, width=3):
    overlay = Image.new("RGBA", img.size, (0, 0, 0, 0))
    d = ImageDraw.Draw(overlay)
    for r in radii:
        d.arc([center[0] - r, center[1] - r, center[0] + r, center[1] + r], 180, 360, fill=color + (70,), width=width)
    return Image.alpha_composite(img.convert("RGBA"), overlay).convert("RGB")


def make_slider(out: Path, index: int, title: str, subtitle: str, badge: str, accent: tuple[int, int, int]):
    size = (1600, 700)
    img = gradient(size, PRIMARY_DARK if index != 2 else PRIMARY, PRIMARY)
    # pola lengkung
    img = arc_pattern(img, (1500, 700), [260, 380, 500, 620], WHITE)
    # bidang diagonal kanan
    overlay = Image.new("RGBA", size, (0, 0, 0, 0))
    od = ImageDraw.Draw(overlay)
    od.polygon([(980, 0), (1600, 0), (1600, 700), (820, 700)], fill=(3, 77, 55, 110))
    od.polygon([(1180, 0), (1600, 0), (1600, 210)], fill=accent + (60,))
    img = Image.alpha_composite(img.convert("RGBA"), overlay).convert("RGB")
    draw = ImageDraw.Draw(img)

    # Teks tidak digambar di dalam gambar: disediakan HTML agar responsif,
    # terbaca pembaca layar, dan bisa diubah tanpa membuka editor gambar.


    # ornamen kanan
    if index == 2:
        # simbol aksesibilitas
        c = (1230, 330)
        draw.ellipse([c[0] - 150, c[1] - 150, c[0] + 150, c[1] + 150], outline=SECONDARY, width=10)
        draw.ellipse([c[0] - 26, c[1] - 108, c[0] + 26, c[1] - 56], fill=WHITE)
        draw.line([(c[0], c[1] - 46), (c[0], c[1] + 20)], fill=WHITE, width=14)
        draw.line([(c[0] - 70, c[1] - 10), (c[0], c[1] + 20)], fill=WHITE, width=14)
        draw.line([(c[0] + 70, c[1] - 10), (c[0], c[1] + 20)], fill=WHITE, width=14)
        draw.arc([c[0] - 90, c[1] + 10, c[0] + 90, c[1] + 170], 20, 160, fill=WHITE, width=14)
    elif index == 1:
        # kartu dokumen digital
        draw.rounded_rectangle([1080, 190, 1430, 560], 24, fill=(255, 255, 255))
        draw.rounded_rectangle([1108, 226, 1402, 268], 8, fill=PRIMARY_LIGHT)
        for i in range(5):
            draw.rounded_rectangle([1108, 300 + i * 44, 1108 + (250 if i % 2 == 0 else 180), 316 + i * 44], 6, fill=LINE)
        draw.rounded_rectangle([1108, 500, 1250, 532], 8, fill=SECONDARY)
    else:
        courthouse(draw, (1080, 190, 1440, 560), WHITE)

    # garis aksen bawah
    draw.rectangle([0, 700 - 10, 1600, 700], fill=SECONDARY)

    img.save(out / f"hero-{index}.jpg", "JPEG", quality=82, optimize=True, progressive=True)
    img.save(out / f"hero-{index}.webp", "WEBP", quality=80, method=6)


ICONS = {
    "sipp": '<circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.5 15.5 21 21"/><path d="M8 9h5M8 12h5"/>',
    "ecourt": '<rect x="3" y="4.5" width="18" height="11" rx="2"/><path d="M2 20h20"/><path d="M9 12.5h6"/>',
    "putusan": '<path d="M4 4.5h7.5A2.5 2.5 0 0 1 14 7v13H6.5A2.5 2.5 0 0 1 4 17.5z"/><path d="M14 7a2.5 2.5 0 0 1 2.5-2.5H21V20h-7"/><path d="M7.5 9h3M7.5 12.5h3"/>',
    "siwas": '<path d="M12 3 19 6v6c0 4.4-2.9 7.4-7 9-4.1-1.6-7-4.6-7-9V6z"/><path d="M12 9v4"/><path d="M12 16.2v.1"/>',
    "prodeo": '<path d="M12 8.4c1.2-2.4 4.6-2.2 5.4.2.7 2.2-1.6 4-5.4 6.6-3.8-2.6-6.1-4.4-5.4-6.6.8-2.4 4.2-2.6 5.4-.2z"/><path d="M3 17.5h4l4 2.2h5.5a2 2 0 0 0 0-4H13"/>',
    "posbakum": '<path d="M20 12.5c0 3.6-3.6 6.5-8 6.5-1 0-2-.15-2.9-.42L4 20l1.1-3.2A6.2 6.2 0 0 1 4 12.5C4 8.9 7.6 6 12 6s8 2.9 8 6.5z"/><path d="M9 11h.1M12 11h.1M15 11h.1"/>',
    "jadwal": '<rect x="3.5" y="5" width="17" height="15.5" rx="2"/><path d="M8 3v4M16 3v4M3.5 10h17"/><path d="M8 14h2M12 14h2M16 14h.1M8 17.5h2M12 17.5h2"/>',
    "panjar": '<rect x="2.5" y="6.5" width="19" height="12" rx="2.5"/><path d="M2.5 10h19"/><circle cx="17" cy="14.5" r="1.2"/>',
    "statistik": '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
    "survei": '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V2.8h6V4"/><path d="M8.5 11l1.8 1.8 3.6-3.6"/><path d="M8.5 16.5h7"/>',
    "disabilitas": '<circle cx="12" cy="4.8" r="2.2"/><path d="M12 8v5.5"/><path d="M8.5 10.5 12 13.5l3.5-3"/><path d="M7 14.5a5.5 5.5 0 1 0 8.2 5"/>',
}


def make_icons(out: Path):
    out.mkdir(parents=True, exist_ok=True)
    for name, body in ICONS.items():
        svg = (
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="48" height="48" '
            'fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" '
            f'stroke-linejoin="round" aria-hidden="true">{body}</svg>\n'
        )
        (out / f"{name}.svg").write_text(svg, encoding="utf-8")


def a4() -> Image.Image:
    return Image.new("RGB", (2480, 3508), WHITE)


def flyer_header(img: Image.Image, title: str, kicker: str) -> int:
    draw = ImageDraw.Draw(img)
    draw.rectangle([0, 0, 2480, 620], fill=PRIMARY)
    draw.rectangle([0, 620, 2480, 640], fill=SECONDARY)
    draw.text((160, 150), kicker.upper(), font=font(52, True), fill=(214, 232, 225))
    y = draw_text_block(draw, (160, 240), title, font(104, True), WHITE, 2100, 6)
    draw.text((160, max(y + 40, 480)), "PENGADILAN AGAMA", font=font(56, True), fill=SECONDARY)
    return 780


def flyer_footer(img: Image.Image):
    draw = ImageDraw.Draw(img)
    draw.rectangle([0, 3230, 2480, 3508], fill=PRIMARY_DARK)
    draw.text((160, 3290), "CONTOH — ganti dengan dokumen resmi satker", font=font(54, True), fill=SECONDARY)
    draw.text((160, 3380), "Standar Tata Kelola Website: SK Dirjen Badilag No. 2722/DJA/SK.TI1/X/2026",
              font=font(40), fill=(200, 220, 212))
    # pita miring CONTOH
    overlay = Image.new("RGBA", img.size, (0, 0, 0, 0))
    od = ImageDraw.Draw(overlay)
    band = Image.new("RGBA", (1400, 180), (255, 255, 255, 26))
    bd = ImageDraw.Draw(band)
    bd.text((40, 30), "CONTOH", font=font(120, True), fill=(255, 255, 255, 40))
    band = band.rotate(28, expand=True)
    overlay.alpha_composite(band, (1500, 900))
    img.paste(Image.alpha_composite(img.convert("RGBA"), overlay).convert("RGB"), (0, 0))


def flyer_steps(img: Image.Image, top: int, steps: list[tuple[str, str]], bottom: int = 3150):
    draw = ImageDraw.Draw(img)
    slot = (bottom - top) // max(len(steps), 1)
    for i, (head, body) in enumerate(steps, start=1):
        y = top + (i - 1) * slot
        draw.ellipse([160, y, 160 + 132, y + 132], fill=PRIMARY_LIGHT, outline=PRIMARY, width=5)
        draw.text((160 + 66, y + 66), str(i), font=font(64, True), fill=PRIMARY, anchor="mm")
        draw.text((340, y + 8), head, font=font(64, True), fill=INK)
        draw_text_block(draw, (340, y + 96), body, font(50), MUTED, 1960, 10)


def callout(img: Image.Image, top: int, title: str, items: list[str], height: int = 420):
    draw = ImageDraw.Draw(img)
    draw.rounded_rectangle([160, top, 2320, top + height], 28, fill=PRIMARY_LIGHT, outline=PRIMARY, width=4)
    draw.text((220, top + 52), title, font=font(54, True), fill=PRIMARY)
    y = top + 140
    for item in items:
        draw.ellipse([220, y + 18, 244, y + 42], fill=SECONDARY)
        draw.text((280, y), item, font=font(44), fill=INK)
        y += 72


def flyer_table(img: Image.Image, top: int, head: list[str], rows: list[list[str]], widths: list[int]):
    draw = ImageDraw.Draw(img)
    x0 = 160
    row_h = 110
    x = x0
    for i, cell in enumerate(head):
        draw.rectangle([x, top, x + widths[i], top + row_h], fill=PRIMARY)
        draw.text((x + 24, top + 32), cell, font=font(42, True), fill=WHITE)
        x += widths[i]
    y = top + row_h
    for r, row in enumerate(rows):
        x = x0
        fill = (247, 249, 248) if r % 2 == 0 else WHITE
        for i, cell in enumerate(row):
            draw.rectangle([x, y, x + widths[i], y + row_h], fill=fill, outline=LINE, width=2)
            draw.text((x + 24, y + 32), cell, font=font(40), fill=INK)
            x += widths[i]
        y += row_h
    return y


def make_flyers(out: Path):
    out.mkdir(parents=True, exist_ok=True)

    img = a4()
    top = flyer_header(img, "Alur Berperkara di Pengadilan Agama", "panduan pencari keadilan")
    flyer_steps(img, top, [
        ("Pendaftaran perkara", "Serahkan surat gugatan/permohonan di PTSP, lengkapi identitas dan bukti."),
        ("Pembayaran panjar", "Bayar panjar biaya perkara di bank/kasir, simpan bukti setor."),
        ("Penetapan majelis", "Ketua menetapkan majelis hakim dan hari sidang pertama."),
        ("Mediasi", "Wajib mediasi kecuali perkara tertentu; mediator bersertifikat."),
        ("Persidangan", "Pembuktian, saksi, dan kesimpulan para pihak."),
        ("Putusan & produk", "Salinan putusan diambil setelah berkekuatan hukum tetap."),
    ], bottom=2500)
    callout(img, 2620, "Dokumen yang perlu dibawa", [
        "KTP dan Kartu Keluarga para pihak",
        "Surat gugatan/permohonan sesuai jumlah pihak",
        "Bukti setor panjar biaya perkara",
        "Dokumen pendukung sesuai jenis perkara",
    ], height=470)
    flyer_footer(img)
    img.save(out / "alur-berperkara.png", "PNG", optimize=True)

    img = a4()
    top = flyer_header(img, "Panjar Biaya Perkara & Radius Panggilan", "transparansi biaya")
    y = flyer_table(img, top, ["Jenis perkara", "Panjar (contoh)", "Radius"], [
        ["Cerai gugat", "Rp 500.000", "I"],
        ["Cerai talak", "Rp 550.000", "I"],
        ["Itsbat nikah", "Rp 300.000", "II"],
        ["Waris", "Rp 750.000", "II"],
        ["Ekonomi syariah", "Rp 1.000.000", "III"],
    ], [1200, 620, 500])
    draw = ImageDraw.Draw(img)
    draw_text_block(draw, (160, y + 100),
                    "Angka di atas adalah contoh. Panjar dan radius yang berlaku ditetapkan dengan "
                    "Surat Keputusan Ketua Pengadilan dan wajib diperbarui setiap tahun anggaran.",
                    font(46), MUTED, 2150, 10)
    flyer_footer(img)
    img.save(out / "panjar-biaya.png", "PNG", optimize=True)

    img = a4()
    top = flyer_header(img, "Bantuan Hukum Cuma-Cuma: Prodeo & Posbakum", "hak pencari keadilan")
    flyer_steps(img, top, [
        ("Siapa yang berhak", "Masyarakat tidak mampu, dibuktikan surat keterangan dari kelurahan/desa."),
        ("Layanan Posbakum", "Konsultasi, pendampingan, dan pembuatan surat gugatan tanpa biaya."),
        ("Permohonan prodeo", "Ajukan bersama gugatan; majelis menetapkan layak atau tidak."),
        ("Yang perlu dibawa", "KTP, Kartu Keluarga, surat keterangan tidak mampu, dokumen perkara."),
    ], bottom=2400)
    callout(img, 2560, "Posbakum melayani", [
        "Konsultasi hukum dan pembuatan surat gugatan",
        "Pendampingan di luar dan di dalam persidangan",
        "Rujukan bantuan hukum bagi yang memenuhi syarat",
    ], height=400)
    flyer_footer(img)
    img.save(out / "prodeo-posbakum.png", "PNG", optimize=True)

    img = a4()
    top = flyer_header(img, "Layanan Ramah Kelompok Rentan & Disabilitas", "peradilan inklusif")
    flyer_steps(img, top, [
        ("Fasilitas fisik", "Jalur pemandu, ramp, kursi roda, toilet aksesibel, ruang tunggu prioritas."),
        ("Pendampingan", "Petugas pendamping dan juru bahasa isyarat atas permintaan."),
        ("Ruang sidang", "Ruang ramah anak dan perempuan, sidang jarak jauh bila memungkinkan."),
        ("Kontak layanan", "Hubungi PTSP untuk menyiapkan kebutuhan khusus sebelum hari sidang."),
    ], bottom=2400)
    callout(img, 2560, "Hubungi sebelum datang", [
        "Layanan Terpadu Satu Pintu (PTSP) pengadilan",
        "Sebutkan kebutuhan khusus saat mendaftar perkara",
        "Petugas akan menyiapkan sarana dan pendamping",
    ], height=400)
    flyer_footer(img)
    img.save(out / "layanan-disabilitas.png", "PNG", optimize=True)


def make_logo(out: Path):
    out.mkdir(parents=True, exist_ok=True)
    svg = """<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="512" height="512">
  <circle cx="256" cy="256" r="248" fill="#056e4f"/>
  <circle cx="256" cy="256" r="212" fill="none" stroke="#ff9900" stroke-width="8"/>
  <text x="256" y="300" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif"
        font-size="180" font-weight="700" fill="#ffffff">PA</text>
  <text x="256" y="366" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif"
        font-size="46" fill="#ff9900">PERADILAN AGAMA</text>
</svg>
"""
    (out / "logo-satker-placeholder.svg").write_text(svg, encoding="utf-8")

    img = Image.new("RGBA", (512, 512), (0, 0, 0, 0))
    d = ImageDraw.Draw(img)
    d.ellipse([8, 8, 504, 504], fill=PRIMARY)
    d.ellipse([44, 44, 468, 468], outline=SECONDARY, width=8)
    d.text((256, 236), "PA", font=font(180, True), fill=WHITE, anchor="mm")
    d.text((256, 356), "PERADILAN AGAMA", font=font(44), fill=SECONDARY, anchor="mm")
    img.save(out / "logo-satker-placeholder.png")


def make_og(out: Path):
    out.mkdir(parents=True, exist_ok=True)
    img = gradient((1200, 630), PRIMARY_DARK, PRIMARY)
    img = arc_pattern(img, (1200, 630), [200, 320, 440], WHITE)
    draw = ImageDraw.Draw(img)
    draw.rectangle([0, 620, 1200, 630], fill=SECONDARY)
    draw.text((80, 120), "PENGADILAN AGAMA", font=font(34, True), fill=(214, 232, 225))
    draw_text_block(draw, (80, 190), "Pelayanan Peradilan yang Cepat, Transparan, dan Akuntabel",
                    font(58, True), WHITE, 760, 10)
    draw.text((80, 480), "Portal resmi berbasis Joomla 6 · SK Dirjen Badilag 2722/2026",
              font=font(26), fill=(214, 232, 225))
    courthouse(draw, (880, 210, 1130, 470), WHITE)
    img.save(out / "og-default.jpg", "JPEG", quality=84, optimize=True, progressive=True)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--out", required=True)
    args = parser.parse_args()
    out = Path(args.out)

    (out / "slider").mkdir(parents=True, exist_ok=True)
    make_slider(out / "slider", 1, "Pelayanan Cepat, Transparan & Akuntabel",
                "Informasi perkara, jadwal sidang, dan produk pengadilan dalam satu pintu.",
                "PORTAL PELAYANAN DIGITAL", SECONDARY)
    make_slider(out / "slider", 2, "Daftar Perkara Lewat e-Court",
                "Pendaftaran, pembayaran panjar, dan persidangan elektronik tanpa antre.",
                "BERPERKARA SECARA ELEKTRONIK", SECONDARY)
    make_slider(out / "slider", 3, "Layanan Ramah Kelompok Rentan",
                "Fasilitas aksesibel, pendampingan, dan bantuan hukum cuma-cuma.",
                "PERADILAN INKLUSIF", SECONDARY)

    make_icons(out / "ikon")
    make_flyers(out / "flyer")
    make_logo(out / "logo")
    make_og(out / "og")

    print("aset dibuat:")
    for p in sorted(out.rglob("*")):
        if p.is_file():
            print(f"  {p.relative_to(out).as_posix():48s} {p.stat().st_size // 1024:5d} KB")


if __name__ == "__main__":
    main()
