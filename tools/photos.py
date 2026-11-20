#!/usr/bin/env python
"""Menyiapkan aset situs dari foto asli satker (deterministik, tanpa crop manual).

Sumber:  assets/foto/sumber/{gedung,ptsp,mediasi}.png
Keluaran:
  assets/slider/hero-1..3.jpg|webp        1600x700  (latar hero beranda)
  assets/konten/{gedung-kantor,ruang-ptsp,ruang-mediasi}.jpg|webp  1200x800
  assets/konten/*-640.jpg                  versi ringan untuk kartu berita
  assets/og/og-default.jpg                 1200x630

Fokus crop bisa diatur lewat FOKUS di bawah (x, y dalam 0..1). Semua nilai
tetap supaya hasil build bisa dibandingkan hash-nya.

Pemakaian:
    python tools/photos.py
"""

from __future__ import annotations

from pathlib import Path

from PIL import Image, ImageFilter

ROOT = Path(__file__).resolve().parent.parent
SUMBER = ROOT / "assets" / "foto" / "sumber"

# (berkas sumber, x fokus, y fokus)
FOKUS = {
    "gedung": (0.50, 0.55),
    "ptsp": (0.50, 0.50),
    "mediasi": (0.45, 0.50),
}


def crop_ratio(img: Image.Image, ratio: float, fx: float, fy: float) -> Image.Image:
    w, h = img.size
    if w / h > ratio:                       # terlalu lebar → potong sisi
        new_w = int(h * ratio)
        left = int((w - new_w) * fx)
        box = (left, 0, left + new_w, h)
    else:                                   # terlalu tinggi → potong atas/bawah
        new_h = int(w / ratio)
        top = int((h - new_h) * fy)
        box = (0, top, w, top + new_h)

    return img.crop(box)


def simpan(img: Image.Image, path: Path, quality: int = 82) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    img = img.filter(ImageFilter.UnsharpMask(radius=1.1, percent=60, threshold=3))
    img.save(path.with_suffix(".jpg"), "JPEG", quality=quality, optimize=True, progressive=True)
    img.save(path.with_suffix(".webp"), "WEBP", quality=80, method=6)


def main() -> None:
    hero = {
        "gedung": ("hero-1", 1600, 700),
        "ptsp": ("hero-2", 1600, 700),
        "mediasi": ("hero-3", 1600, 700),
    }

    for name, (out, w, h) in hero.items():
        src = Image.open(SUMBER / f"{name}.png").convert("RGB")
        fx, fy = FOKUS[name]
        img = crop_ratio(src, w / h, fx, fy).resize((w, h), Image.LANCZOS)
        simpan(img, ROOT / "assets" / "slider" / out, quality=80)

    konten = {
        "gedung": "gedung-kantor",
        "ptsp": "ruang-ptsp",
        "mediasi": "ruang-mediasi",
    }

    for name, out in konten.items():
        src = Image.open(SUMBER / f"{name}.png").convert("RGB")
        fx, fy = FOKUS[name]
        img = crop_ratio(src, 3 / 2, fx, fy).resize((1200, 800), Image.LANCZOS)
        simpan(img, ROOT / "assets" / "konten" / out)

        kecil = img.resize((640, 427), Image.LANCZOS)
        (ROOT / "assets" / "konten").mkdir(parents=True, exist_ok=True)
        kecil.save(ROOT / "assets" / "konten" / f"{out}-640.jpg", "JPEG", quality=80, optimize=True, progressive=True)

    src = Image.open(SUMBER / "gedung.png").convert("RGB")
    og = crop_ratio(src, 1200 / 630, 0.5, 0.5).resize((1200, 630), Image.LANCZOS)
    og.save(ROOT / "assets" / "og" / "og-default.jpg", "JPEG", quality=84, optimize=True, progressive=True)

    for p in sorted((ROOT / "assets").rglob("*")):
        if p.is_file() and p.suffix in {".jpg", ".webp"} and "foto" not in p.parts:
            print(f"  {p.relative_to(ROOT).as_posix():52s} {p.stat().st_size // 1024:5d} KB")


if __name__ == "__main__":
    main()
