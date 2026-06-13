{{--
  Variabel yang WAJIB diset oleh parent sebelum @include:
    $pdfUrl     : string  → URL ke endpoint PDF struk
    $receiptLines: array  → baris-baris struk (format di bawah)

  Format receiptLines:
    ['t'=>'center',     'text'=>'...']
    ['t'=>'center_lg',  'text'=>'...']   ← bold + double-height
    ['t'=>'center_sm',  'text'=>'...']   ← font kecil
    ['t'=>'title',      'text'=>'...']   ← bold centered
    ['t'=>'kv',   'label'=>'...', 'value'=>'...']
    ['t'=>'kv_bold','label'=>'...','value'=>'...']
    ['t'=>'kv_sm', 'label'=>'...','value'=>'...']   ← italic / small note
    ['t'=>'sep']        ← ---------- (solid)
    ['t'=>'sep_dbl']    ← ========== (double)
    ['t'=>'sep_dot']    ← ·········· (dotted)
    ['t'=>'empty']      ← baris kosong
--}}

{{-- ===== STYLE MODAL ===== --}}
<style>
.cm-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;display:flex;align-items:flex-end;justify-content:center;}
.cm-box{background:#fff;width:100%;max-width:440px;border-radius:16px 16px 0 0;padding:22px 18px 30px;font-family:'Segoe UI',Arial,sans-serif;max-height:92vh;overflow-y:auto;}
.cm-title{font-size:17px;font-weight:700;margin-bottom:2px;color:#111;}
.cm-sub{font-size:12px;color:#666;margin-bottom:14px;}
.cm-section-label{font-size:10px;font-weight:700;letter-spacing:.08em;color:#555;margin:10px 0 6px;}
.cm-options{display:flex;flex-direction:column;gap:7px;margin-bottom:10px;}
.cm-option{display:flex;align-items:center;gap:10px;border:2px solid #ddd;border-radius:10px;padding:10px 14px;cursor:pointer;transition:.15s;}
.cm-option.active{border-color:#2563eb;background:#eff6ff;}
.cm-option input[type=radio]{accent-color:#2563eb;width:17px;height:17px;flex-shrink:0;}
.cm-option-text{font-size:14px;font-weight:600;color:#222;}
.cm-option-text small{font-weight:400;color:#666;font-size:12px;margin-left:4px;}
.cm-hint{background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:8px 12px;font-size:11.5px;color:#92400e;margin-bottom:12px;}
.cm-font-row{display:flex;gap:10px;margin-bottom:14px;}
.cm-font-btn{flex:1;padding:9px;border:2px solid #ddd;border-radius:9px;background:#fff;font-size:13px;font-weight:600;cursor:pointer;color:#444;transition:.15s;}
.cm-font-btn small{display:block;font-size:10px;font-weight:400;color:#999;}
.cm-font-btn.active{border-color:#7c3aed;background:#f5f3ff;color:#5b21b6;}
.cm-font-btn.active small{color:#7c3aed;}
.cm-btn{display:block;width:100%;padding:13px;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;margin-bottom:9px;transition:.15s;}
.cm-btn-green{background:#16a34a;color:#fff;}
.cm-btn-green:hover{background:#15803d;}
.cm-btn-blue{background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;gap:8px;}
.cm-btn-blue:hover{background:#1d4ed8;}
.cm-btn-orange{background:#f97316;color:#fff;}
.cm-btn-orange:hover{background:#ea580c;}
.cm-btn-close{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
.cm-btn-close:hover{background:#e5e7eb;}
.cm-status{padding:9px 12px;border-radius:8px;font-size:12px;font-weight:600;text-align:center;margin-bottom:8px;}
.cm-status.error{background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;}
.cm-status.ok{background:#f0fdf4;color:#16a34a;border:1px solid #86efac;}
.cm-status.info{background:#eff6ff;color:#2563eb;border:1px solid #93c5fd;}
.cm-spinner{display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:6px;}
@keyframes spin{to{transform:rotate(360deg)}}
@media(min-width:440px){.cm-box{border-radius:16px;margin-bottom:20px;}}
</style>

{{-- ===== TOMBOL TRIGGER (menggantikan tombol lama) ===== --}}
<div style="display:flex;flex-wrap:wrap;justify-content:center;gap:8px;margin-top:16px;" class="no-print">
    <button onclick="cmOpen()" style="padding:9px 20px;background:#16a34a;color:#fff;border:none;border-radius:8px;font-weight:700;font-size:13px;cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,.15);">
        Cetak Struk
    </button>
    <button onclick="window.close()" style="padding:9px 20px;background:#6b7280;color:#fff;border:none;border-radius:8px;font-weight:700;font-size:13px;cursor:pointer;">
        Tutup
    </button>
</div>

{{-- ===== MODAL ===== --}}
<div id="cmOverlay" class="cm-overlay" style="display:none;" onclick="if(event.target===this)cmClose()">
  <div class="cm-box">
    <div class="cm-title">Cetak Struk</div>
    <div class="cm-sub">Pilih pengaturan sebelum cetak</div>

    <div class="cm-section-label">UKURAN KERTAS</div>
    <div class="cm-options" id="cmPaperOptions">
      <label class="cm-option active" onclick="cmSelectPaper(this,'58')">
        <input type="radio" name="cmPaper" value="58" checked>
        <div class="cm-option-text">58mm <small>(Thermal kecil)</small></div>
      </label>
      <label class="cm-option" onclick="cmSelectPaper(this,'80')">
        <input type="radio" name="cmPaper" value="80">
        <div class="cm-option-text">80mm <small>(Thermal standar)</small></div>
      </label>
      <label class="cm-option" onclick="cmSelectPaper(this,'a4')">
        <input type="radio" name="cmPaper" value="a4">
        <div class="cm-option-text">A4 <small>(Printer biasa)</small></div>
      </label>
    </div>
    <div class="cm-hint">Jika hasil cetak turun baris/berantakan, coba pilih 58mm.</div>

    <div class="cm-section-label">UKURAN HURUF</div>
    <div class="cm-font-row">
      <button class="cm-font-btn active" id="cmFontNormal" onclick="cmSetFont('normal')">
        Normal <small>(standar)</small>
      </button>
      <button class="cm-font-btn" id="cmFontLarge" onclick="cmSetFont('large')">
        Besar <small>(lansia)</small>
      </button>
    </div>

    <button class="cm-btn cm-btn-green" onclick="cmCetakBiasa()">Cetak Struk</button>
    <button class="cm-btn cm-btn-blue" id="cmBtnBT" onclick="cmCetakBluetooth()">
        <span id="cmBTLabel">Bluetooth</span>
    </button>
    <div id="cmBtWarning" style="display:none;background:#fef3c7;border:1px solid #f59e0b;border-radius:8px;padding:10px 12px;font-size:12px;color:#92400e;margin-bottom:9px;">
        ⚠️ <strong>Browser ini tidak mendukung Bluetooth.</strong><br>
        Untuk cetak via Bluetooth, buka halaman ini menggunakan <strong>Chrome Android</strong>.<br>
        <a href="https://play.google.com/store/apps/details?id=com.android.chrome" target="_blank" style="color:#b45309;font-weight:700;">→ Download Chrome di Play Store</a>
    </div>
    <button class="cm-btn cm-btn-orange" onclick="cmBukaTab()">Buka di Tab (Bagikan / Print App)</button>

    <div id="cmStatus" class="cm-status" style="display:none;"></div>

    <button class="cm-btn cm-btn-close" onclick="cmClose()">Tutup</button>
  </div>
</div>

{{-- ===== JAVASCRIPT ===== --}}
<script>
// ─── State ─────────────────────────────────────────────────────────────────
let cmPaper    = '58';
let cmFont     = 'normal';
let cmBtDevice = null;
let cmBtChar   = null;
const CM_PDF_URL = @json($pdfUrl);

// ─── Receipt lines dari PHP ─────────────────────────────────────────────────
const CM_LINES = @json($receiptLines);

// ─── Modal open/close ───────────────────────────────────────────────────────
function cmOpen()  {
    document.getElementById('cmOverlay').style.display='flex';
    // Cek dukungan Web Bluetooth
    const btSupported = ('bluetooth' in navigator);
    const btBtn     = document.getElementById('cmBtnBT');
    const btWarning = document.getElementById('cmBtWarning');
    if (!btSupported) {
        btBtn.disabled = true;
        btBtn.style.opacity = '0.45';
        btBtn.style.cursor  = 'not-allowed';
        btWarning.style.display = 'block';
    } else {
        btBtn.disabled = false;
        btBtn.style.opacity = '1';
        btBtn.style.cursor  = 'pointer';
        btWarning.style.display = 'none';
    }
}
function cmClose() { document.getElementById('cmOverlay').style.display='none'; }

// ─── Pilih kertas ───────────────────────────────────────────────────────────
function cmSelectPaper(el, val) {
    cmPaper = val;
    document.querySelectorAll('.cm-option').forEach(o => o.classList.remove('active'));
    el.classList.add('active');
}

// ─── Pilih font ─────────────────────────────────────────────────────────────
function cmSetFont(f) {
    cmFont = f;
    document.getElementById('cmFontNormal').classList.toggle('active', f==='normal');
    document.getElementById('cmFontLarge').classList.toggle('active',  f==='large');
}

// ─── Status ─────────────────────────────────────────────────────────────────
function cmSetStatus(msg, type='info') {
    const el = document.getElementById('cmStatus');
    el.textContent = msg;
    el.className = 'cm-status ' + type;
    el.style.display = 'block';
}

// ─── Cetak biasa (CSS Print) ─────────────────────────────────────────────────
function cmCetakBiasa() {
    // Terapkan ukuran halaman sesuai pilihan
    let size = '80mm auto';
    if (cmPaper === '58') size = '58mm auto';
    if (cmPaper === 'a4') size = 'A4';

    // Inject/update @page style
    let styleEl = document.getElementById('cm-page-style');
    if (!styleEl) {
        styleEl = document.createElement('style');
        styleEl.id = 'cm-page-style';
        document.head.appendChild(styleEl);
    }
    styleEl.textContent = `@page { size: ${size}; margin: ${cmPaper==='a4'?'10mm':'2mm'}; }
    body { font-size: ${cmFont==='large'?'11pt':'8pt'} !important; }`;

    cmClose();
    setTimeout(() => window.print(), 200);
}

// ─── Buka tab PDF ────────────────────────────────────────────────────────────
function cmBukaTab() {
    window.open(CM_PDF_URL, '_blank');
    cmSetStatus('PDF dibuka di tab baru. Bagikan / cetak dari sana.', 'info');
}

// ─── ESC/POS Generator ──────────────────────────────────────────────────────
class EscPos {
    constructor(cols) {
        this.cols = cols;
        this.buf  = [];
    }
    _b(...bytes) { bytes.forEach(b => this.buf.push(b)); return this; }
    _s(str) {
        for (let i=0;i<str.length;i++) {
            const c = str.charCodeAt(i);
            this.buf.push(c < 128 ? c : 63); // non-ASCII → '?'
        }
        return this;
    }
    init()         { return this._b(0x1b,0x40)._b(0x1b,0x74,0x00); }
    lf()           { return this._b(0x0a); }
    feed(n=3)      { for(let i=0;i<n;i++) this._b(0x0a); return this; }
    cut()          { return this._b(0x1d,0x56,0x41,0x05); }
    align(a)       { const m={L:0,C:1,R:2}; return this._b(0x1b,0x61,m[a]||0); }
    bold(on)       { return this._b(0x1b,0x45,on?1:0); }
    dblH(on)       { return this._b(0x1b,0x21,on?0x10:0x00); }
    dblS(on)       { return this._b(0x1b,0x21,on?0x30:0x00); }
    small(on)      { return this._b(0x1b,0x21,on?0x01:0x00); }
    text(s)        { return this._s(s).lf(); }
    center(s,lg)   {
        if(lg) this.dblH(true);
        const pad = Math.max(0,Math.floor((this.cols-(lg?Math.floor(s.length*0.7):s.length))/2));
        this.align('C')._s(' '.repeat(pad)+s).lf();
        if(lg) this.dblH(false);
        return this;
    }
    sep(c='-')     { return this._s(c.repeat(this.cols)).lf(); }
    kv(label,value,bld=false) {
        const lw = Math.round(this.cols*0.45);
        const vw = this.cols - lw - 2;
        const l = label.substring(0,lw).padEnd(lw);
        const v = this._wrap(value, vw);
        if(bld) this.bold(true);
        this._s(l+': '+v[0]).lf();
        for(let i=1;i<v.length;i++) this._s(' '.repeat(lw+2)+v[i]).lf();
        if(bld) this.bold(false);
        return this;
    }
    _wrap(s, w) {
        const r=[]; let cur='';
        s.split(' ').forEach(word => {
            if((cur+word).length>w){ if(cur)r.push(cur.trimEnd()); cur=''; }
            cur += word+' ';
        });
        if(cur.trim()) r.push(cur.trimEnd());
        return r.length ? r : [''];
    }
    bytes() { return new Uint8Array(this.buf); }
}

// ─── Generate ESC/POS dari receiptLines ─────────────────────────────────────
function cmBuildEscPos(cols, largeFnt) {
    const ep = new EscPos(cols);
    ep.init();
    CM_LINES.forEach(ln => {
        switch(ln.t) {
            case 'center':    ep.align('C').text(ln.text); break;
            case 'center_lg': ep.align('C').bold(true).dblH(true).text(ln.text).dblH(false).bold(false); break;
            case 'center_sm': ep.align('C').small(true).text(ln.text).small(false); break;
            case 'title':     ep.align('C').bold(true).text(ln.text).bold(false); break;
            case 'kv':        ep.align('L').kv(ln.label, ln.value, false); break;
            case 'kv_bold':   ep.align('L').kv(ln.label, ln.value, true); break;
            case 'kv_sm':     ep.align('L').small(true).kv(ln.label, ln.value, false).small(false); break;
            case 'sep':       ep.sep('-'); break;
            case 'sep_dbl':   ep.sep('='); break;
            case 'sep_dot':   ep.sep('.'); break;
            case 'empty':     ep.lf(); break;
        }
        // Font besar: naikkan sedikit (hanya teks konten, bukan garis)
        if(largeFnt && ['kv','kv_bold','center','title'].includes(ln.t)) {
            // sudah dicetak — tidak bisa di-undo setelah push, jadi handled di init
        }
    });
    ep.feed(4).cut();
    return ep.bytes();
}

// ─── Known BT Printer Services ──────────────────────────────────────────────
const BT_PROFILES = [
    // Generic Serial / Xprinter / many Chinese printers
    { svc: '000018f0-0000-1000-8000-00805f9b34fb',
      chr: '00002af1-0000-1000-8000-00805f9b34fb' },
    // Star Micronics / some Android BT printers  
    { svc: 'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
      chr: 'bef8d6c9-9c21-4c9e-b632-bd58c1009f9f' },
    // BLE Serial Port (many modern BT printers)
    { svc: '49535343-fe7d-4ae5-8fa9-9fafd205e455',
      chr: '49535343-8841-43f4-a8d4-ecbe34729bb3' },
    // Generic 0xFF00
    { svc: '0000ff00-0000-1000-8000-00805f9b34fb',
      chr: '0000ff02-0000-1000-8000-00805f9b34fb' },
    // Rongta / Hoin / some others
    { svc: '0000ffe0-0000-1000-8000-00805f9b34fb',
      chr: '0000ffe1-0000-1000-8000-00805f9b34fb' },
];

async function cmConnectBT() {
    if (!('bluetooth' in navigator)) {
        throw new Error('Web Bluetooth tidak didukung browser ini. Gunakan Chrome Android.');
    }
    cmSetStatus('Mencari printer...', 'info');

    const device = await navigator.bluetooth.requestDevice({
        acceptAllDevices: true,
        optionalServices: BT_PROFILES.map(p => p.svc),
    });

    cmSetStatus('Menghubungkan ke ' + device.name + '...', 'info');
    const server = await device.gatt.connect();

    // Coba setiap service profile sampai ada yang berhasil
    let foundChar = null;
    for (const profile of BT_PROFILES) {
        try {
            const svc = await server.getPrimaryService(profile.svc);
            const chr = await svc.getCharacteristic(profile.chr);
            foundChar = chr;
            break;
        } catch (_) { /* coba berikutnya */ }
    }
    if (!foundChar) throw new Error('Printer terhubung tapi service tidak dikenal.');

    cmBtDevice = device;
    cmBtChar   = foundChar;

    device.addEventListener('gattserverdisconnected', () => {
        cmBtDevice = null; cmBtChar = null;
        cmSetStatus('Printer terputus.', 'error');
        document.getElementById('cmBTLabel').textContent = 'Bluetooth';
    });

    return foundChar;
}

async function cmWriteChunked(char, data) {
    const CHUNK = 100; // bytes per write — aman untuk semua BLE MTU
    for (let i = 0; i < data.length; i += CHUNK) {
        await char.writeValue(data.slice(i, i + CHUNK));
        await new Promise(r => setTimeout(r, 30));
    }
}

// ─── Cetak via Bluetooth ─────────────────────────────────────────────────────
async function cmCetakBluetooth() {
    const btn = document.getElementById('cmBtnBT');
    const lbl = document.getElementById('cmBTLabel');

    try {
        // Jika belum terhubung → scan & connect dulu
        if (!cmBtChar || !cmBtDevice?.gatt?.connected) {
            lbl.innerHTML = '<span class="cm-spinner"></span> Menghubungkan...';
            btn.disabled = true;
            cmBtChar = await cmConnectBT();
            lbl.textContent = '[OK] ' + cmBtDevice.name;
            cmSetStatus('Terhubung: ' + cmBtDevice.name, 'ok');
            btn.disabled = false;
        }

        // Tentukan cols berdasar kertas
        const cols = cmPaper === '58' ? 32 : (cmPaper === '80' ? 48 : 64);
        const largeFnt = cmFont === 'large';

        lbl.innerHTML = '<span class="cm-spinner"></span> Mencetak...';
        btn.disabled = true;

        const data = cmBuildEscPos(cols, largeFnt);
        await cmWriteChunked(cmBtChar, data);

        cmSetStatus('Berhasil dicetak ke ' + cmBtDevice.name, 'ok');
        lbl.textContent = '[OK] ' + cmBtDevice.name;
    } catch(e) {
        cmSetStatus('Gagal: ' + (e.message || 'Tidak dapat terhubung.'), 'error');
        lbl.textContent = 'Bluetooth';
        cmBtDevice = null; cmBtChar = null;
    } finally {
        btn.disabled = false;
    }
}
</script>
