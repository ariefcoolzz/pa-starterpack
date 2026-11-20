/**
 * Template pa_satker — skrip minimum, tanpa dependensi.
 *
 * 1. Buka/tutup menu ponsel.
 * 2. Titik navigasi hero mengikuti slide yang terlihat (tanpa autoplay: hemat data).
 * 3. Panel aksesibilitas: ukuran teks, kontras tinggi, garis bawah tautan.
 *    Pilihan disimpan di localStorage agar bertahan antarhalaman.
 *
 * Catatan: panel ini pelengkap, bukan pengganti pembaca layar. Dukungan pembaca
 * layar berasal dari struktur semantik halaman (landmark, heading, alt, label).
 */
(function () {
    'use strict';

    var TAHAP = ['1rem', '1.125rem', '1.25rem', '1.4rem'];
    var KUNCI = { font: 'pa-font', kontras: 'pa-kontras', garis: 'pa-garis' };

    function simpan(kunci, nilai) {
        try {
            if (nilai === null) {
                localStorage.removeItem(kunci);
            } else {
                localStorage.setItem(kunci, nilai);
            }
        } catch (e) {
            /* mode privat: abaikan */
        }
    }

    function baca(kunci) {
        try {
            return localStorage.getItem(kunci);
        } catch (e) {
            return null;
        }
    }

    function terapkanFont(indeks) {
        var i = Math.max(0, Math.min(TAHAP.length - 1, indeks));
        document.documentElement.style.setProperty('--pa-step', TAHAP[i]);
        simpan(KUNCI.font, i === 0 ? null : String(i));
        document.querySelectorAll('[data-a11y="font"]').forEach(function (btn) {
            btn.setAttribute('aria-pressed', Number(btn.dataset.tahap) === i ? 'true' : 'false');
        });

        return i;
    }

    function terapkanKelas(kelas, aktif) {
        document.body.classList.toggle(kelas, aktif);
        var aksi = kelas === 'pa-contrast' ? 'contrast' : 'garis';
        document.querySelectorAll('[data-a11y="' + aksi + '"]').forEach(function (btn) {
            btn.setAttribute('aria-pressed', aktif ? 'true' : 'false');
        });
        simpan(kelas === 'pa-contrast' ? KUNCI.kontras : KUNCI.garis, aktif ? '1' : null);
    }

    document.addEventListener('DOMContentLoaded', function () {
        /* --- menu ponsel -------------------------------------------------- */
        var toggle = document.querySelector('.pa-navtoggle');
        var nav = document.getElementById('pa-nav');

        if (toggle && nav) {
            toggle.addEventListener('click', function () {
                var open = nav.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                // Panel menu panjang; sembunyikan tombol aksesibilitas agar tidak
                // menutupi butir menu di bagian bawah.
                document.body.classList.toggle('pa-nav-open', open);
            });
        }

        /* --- hero --------------------------------------------------------- */
        var slides = document.querySelector('.pa-hero__slides');
        var dots = document.querySelectorAll('.pa-hero__dots a');

        if (slides && dots.length) {
            slides.addEventListener('scroll', function () {
                var index = Math.round(slides.scrollLeft / slides.clientWidth);
                dots.forEach(function (dot, i) {
                    dot.style.background = i === index ? 'var(--pa-primary)' : 'var(--pa-line)';
                });
            }, { passive: true });
        }

        /* --- panel aksesibilitas ----------------------------------------- */
        var panel = document.getElementById('pa-a11y-panel');
        var opener = document.querySelector('[data-a11y="open"]');

        if (panel && opener) {
            opener.addEventListener('click', function () {
                var akanBuka = panel.hasAttribute('hidden');
                if (akanBuka) {
                    panel.removeAttribute('hidden');
                } else {
                    panel.setAttribute('hidden', '');
                }
                opener.setAttribute('aria-expanded', akanBuka ? 'true' : 'false');
                if (akanBuka) {
                    var pertama = panel.querySelector('button');
                    if (pertama) {
                        pertama.focus();
                    }
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !panel.hasAttribute('hidden')) {
                    panel.setAttribute('hidden', '');
                    opener.setAttribute('aria-expanded', 'false');
                    opener.focus();
                }
            });

            document.addEventListener('click', function (e) {
                if (!panel.hasAttribute('hidden') && !panel.contains(e.target) && !opener.contains(e.target)) {
                    panel.setAttribute('hidden', '');
                    opener.setAttribute('aria-expanded', 'false');
                }
            });
        }

        terapkanFont(Number(baca(KUNCI.font) || 0));

        if (baca(KUNCI.kontras) === '1') {
            terapkanKelas('pa-contrast', true);
        }
        if (baca(KUNCI.garis) === '1') {
            terapkanKelas('pa-underline', true);
        }

        document.querySelectorAll('[data-a11y]').forEach(function (btn) {
            var aksi = btn.dataset.a11y;

            if (aksi === 'font') {
                btn.addEventListener('click', function () {
                    terapkanFont(Number(btn.dataset.tahap));
                });
            }

            if (aksi === 'contrast') {
                btn.addEventListener('click', function () {
                    terapkanKelas('pa-contrast', !document.body.classList.contains('pa-contrast'));
                });
            }

            if (aksi === 'garis') {
                btn.addEventListener('click', function () {
                    terapkanKelas('pa-underline', !document.body.classList.contains('pa-underline'));
                });
            }

            if (aksi === 'reset') {
                btn.addEventListener('click', function () {
                    terapkanFont(0);
                    terapkanKelas('pa-contrast', false);
                    terapkanKelas('pa-underline', false);
                });
            }
        });
    });

    /* --- video: muat iframe hanya setelah diklik ------------------------- */
    document.querySelectorAll('[data-pa-video]').forEach(function (kotak) {
        var tombol = kotak.querySelector('.pa-video__play');
        var id = (kotak.dataset.id || '').trim();

        if (!tombol) { return; }

        // Tanpa ID yang sah tombol play menyesatkan: ganti dengan label status.
        if (!/^[\w-]{11}$/.test(id)) {
            var label = document.createElement('span');
            label.className = 'pa-video__kosong';
            label.textContent = 'Video profil segera hadir';
            kotak.appendChild(label);
            tombol.remove();
            return;
        }

        tombol.addEventListener('click', function () {

            var frame = document.createElement('iframe');
            frame.src = 'https://www.youtube-nocookie.com/embed/' + id + '?autoplay=1&rel=0';
            frame.title = 'Video profil satuan kerja';
            frame.allow = 'accelerometer; autoplay; encrypted-media; picture-in-picture';
            frame.allowFullscreen = true;
            kotak.innerHTML = '';
            kotak.appendChild(frame);
        });
    });

    /* --- galeri: dialog lightbox tanpa pustaka ---------------------------- */
    var tautanGaleri = document.querySelectorAll('[data-pa-lightbox]');

    if (tautanGaleri.length && typeof HTMLDialogElement === 'function') {
        var dialog = document.createElement('dialog');
        dialog.className = 'pa-lightbox';
        dialog.innerHTML = '<button type="button" aria-label="Tutup">×</button><img alt="">';
        document.body.appendChild(dialog);

        var gambar = dialog.querySelector('img');
        var tutup = dialog.querySelector('button');

        tutup.addEventListener('click', function () { dialog.close(); });
        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) { dialog.close(); }
        });

        tautanGaleri.forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                gambar.src = a.getAttribute('href');
                gambar.alt = a.querySelector('img') ? a.querySelector('img').alt : '';
                dialog.showModal();
            });
        });
    }
})();
