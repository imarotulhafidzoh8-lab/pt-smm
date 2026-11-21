<?php
// ============================================================
// FILE    : frontend/pengguna/pemesanan.php
// FUNGSI  : Formulir Pemesanan Produk oleh Pengguna
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_pengguna();

$page_title = 'Formulir Pemesanan Produk';
$user_page  = 'pemesanan';
$id_user    = (int)$_SESSION['id_user'];

$id_produk = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil semua produk aktif
$semua_produk = [];
try {
    $stmt = $pdo->query("
        SELECT id_produk, nama_produk, harga, stok, satuan, gambar, berat
        FROM produk
        WHERE status_produk = 'Aktif' AND stok > 0
        ORDER BY nama_produk ASC
    ");
    $semua_produk = $stmt->fetchAll();
} catch (PDOException $e) {
    $semua_produk = [];
}

// Ambil produk terpilih
$produk_terpilih = null;
if ($id_produk > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, k.nama_kategori
            FROM produk p
            JOIN kategori k ON p.id_kategori = k.id_kategori
            WHERE p.id_produk = ? AND p.status_produk = 'Aktif'
            LIMIT 1
        ");
        $stmt->execute([$id_produk]);
        $produk_terpilih = $stmt->fetch();
    } catch (PDOException $e) {}
}

if (!$produk_terpilih && !empty($semua_produk)) {
    $produk_terpilih = $semua_produk[0];
    $id_produk = (int)$produk_terpilih['id_produk'];
}

// Data profil pengguna untuk prefill
$user_profile = null;
try {
    $stmt = $pdo->prepare("SELECT nama_lengkap, email, no_hp, alamat FROM users WHERE id_user = ? LIMIT 1");
    $stmt->execute([$id_user]);
    $user_profile = $stmt->fetch();
} catch (PDOException $e) {}

// Token sekali pakai mencegah satu form menghasilkan pesanan ganda.
if (!isset($_SESSION['order_submit_tokens']) || !is_array($_SESSION['order_submit_tokens'])) {
    $_SESSION['order_submit_tokens'] = [];
}
$token_now = time();
foreach ($_SESSION['order_submit_tokens'] as $saved_token => $created_at) {
    if ((int)$created_at < $token_now - 3600) unset($_SESSION['order_submit_tokens'][$saved_token]);
}
$order_form_token = bin2hex(random_bytes(32));
$_SESSION['order_submit_tokens'][$order_form_token] = $token_now;
while (count($_SESSION['order_submit_tokens']) > 10) {
    $oldest_token = array_key_first($_SESSION['order_submit_tokens']);
    unset($_SESSION['order_submit_tokens'][$oldest_token]);
}

require_once __DIR__ . '/../includes/pengguna-header.php';
?>

<style>
.order-form-shell { padding: 1.5rem; }
.order-form-title { font-size: 1.25rem; letter-spacing: -.02em; }
.order-form-section-title { font-size: .95rem; font-weight: 700; color: #0f172a; margin: 0; }
.order-form-shell .form-label { margin-bottom: .4rem; }
.order-form-shell .form-control, .order-form-shell .form-select { min-height: 44px; border-color: #dbe3ed; border-radius: 10px; }
.order-form-shell textarea.form-control { min-height: auto; }
.order-form-shell .form-control:focus, .order-form-shell .form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 .2rem rgba(59,130,246,.12); }
.order-product-summary, .order-total-box { background: #f8fafc; border: 1px solid #e8edf3; border-radius: 12px; }
.cart-item-row { display: grid; grid-template-columns: minmax(0, 1fr) 92px 40px; gap: 8px; align-items: end; padding: 12px; border: 1px solid #e8edf3; border-radius: 12px; background: #fff; }
.cart-item-row + .cart-item-row { margin-top: 8px; }
.cart-item-product { min-width: 0; }
.cart-item-price { grid-column: 1 / 2; margin-top: -3px; color: #64748b; font-size: 12px; }
.cart-remove-btn { width: 40px; height: 44px; border: 1px solid #fee2e2; border-radius: 10px; background: #fff7f7; color: #dc2626; }
@media (max-width: 575.98px) {
    .order-form-shell { padding: 1rem !important; border-radius: 16px !important; }
    .order-form-title { font-size: 1.1rem; }
    .order-form-shell .row { --bs-gutter-x: .75rem; --bs-gutter-y: 1.25rem; }
    .cart-item-row { grid-template-columns: minmax(0, 1fr) 78px 36px; gap: 6px; padding: 9px; }
    .cart-item-row .form-label { font-size: 11px; }
    .cart-item-row .form-select, .cart-item-row .form-control, .cart-remove-btn { min-height: 42px; }
    .cart-remove-btn { width: 36px; }
    #btnCekOngkir { white-space: nowrap; font-size: .82rem; padding-right: .7rem; padding-left: .7rem; }
    #displayTotalBayar { font-size: 1rem !important; }
}
</style>

<div class="order-form-shell bg-white border border-light-subtle rounded-4 shadow-xs mb-4">
    <div class="border-bottom border-light-subtle pb-3 mb-4">
        <h4 class="order-form-title fw-bold text-dark mb-0">Buat Pesanan</h4>
    </div>

    <?php if (empty($semua_produk)): ?>
        <div class="alert alert-warning border-0 rounded-3">
            <i class="fas fa-exclamation-triangle me-2"></i> Saat ini tidak ada produk aktif dengan stok tersedia.
        </div>
    <?php else: ?>
        <form action="<?= base_url('backend/pengguna/proses-pemesanan.php') ?>" method="POST" id="formPemesanan">
            <input type="hidden" name="order_form_token" value="<?= e($order_form_token) ?>">
            <!-- Input tersembunyi: kurir terpilih dari Biteship -->
            <input type="hidden" name="kurir_code"    id="hiddenKurirCode"    value="">
            <input type="hidden" name="kurir_name"    id="hiddenKurirName"    value="">
            <input type="hidden" name="kurir_service" id="hiddenKurirService" value="">
            <input type="hidden" name="ongkir"        id="hiddenOngkir"       value="0">
            <input type="hidden" name="kode_pos_tujuan" id="hiddenKodePos"    value="">

            <div class="row g-4">
                <!-- ============================================================
                     KOLOM KIRI: Produk & Kurir
                ============================================================ -->
                <div class="col-lg-6">
                    <h5 class="order-form-section-title mb-3">Produk</h5>

                    <div id="cartItems" class="mb-3"></div>
                    <button type="button" id="addCartItem" class="btn btn-sm btn-outline-primary rounded-pill mb-3">
                        <i class="fas fa-plus me-1"></i> Tambah Produk
                    </button>

                    <?php if ($produk_terpilih): ?>
                        <!-- Rincian Harga -->
                        <div class="order-total-box p-3 mb-0">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Subtotal Produk:</span>
                                <span id="displaySubtotal" class="fw-semibold text-dark">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-2">
                                <span>Ongkos Kirim:</span>
                                <span id="displayOngkir"><span class="text-muted fst-italic">— Pilih kurir di kanan —</span></span>
                            </div>
                            <hr class="my-2 border-light-subtle">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark">Total Pembayaran:</span>
                                <span class="fs-5 fw-bold text-dark" id="displayTotalBayar">Rp 0</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ============================================================
                     KOLOM KANAN: Penerima & Ongkir Biteship
                ============================================================ -->
                <div class="col-lg-6">
                    <h5 class="order-form-section-title mb-3">Pengiriman</h5>

                    <!-- Nama Penerima -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Nama Penerima <span class="text-danger">*</span></label>
                        <input type="text" name="nama_penerima" class="form-control"
                               value="<?= e($user_profile['nama_lengkap'] ?? '') ?>"
                               placeholder="Nama lengkap" required>
                    </div>

                    <!-- No HP -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Nomor HP <span class="text-danger">*</span></label>
                        <input type="tel" name="no_hp_penerima" class="form-control"
                               value="<?= e($user_profile['no_hp'] ?? '') ?>"
                               placeholder="08xxxxxxxxxx" required>
                    </div>

                    <!-- Kode Pos Tujuan (input untuk trigger ongkir) -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">
                            <i class="fas fa-map-pin text-dark me-1"></i>Kode Pos <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="text" id="inputKodePos" class="form-control"
                                   pattern="\d{5}" maxlength="5" placeholder="Contoh: 50231" required>
                            <button type="button" id="btnCekOngkir" class="btn text-white" style="background:#1e3a5f; border-color:#1e3a5f;" onclick="cekOngkir()">
                                <i class="fas fa-search me-1"></i> Cek
                            </button>
                        </div>
                    </div>

                    <!-- Hasil Daftar Kurir dari Biteship -->
                    <div id="boxKurirResult" class="mb-3" style="display:none">
                        <label class="form-label small fw-bold text-dark"><i class="fas fa-shipping-fast text-dark me-1"></i>Pilih Kurir <span class="text-danger">*</span></label>
                        <div id="listKurir" class="d-flex flex-column gap-2"></div>
                    </div>

                    <!-- Loading spinner -->
                    <div id="spinnerOngkir" class="text-center py-3" style="display:none">
                        <div class="spinner-border spinner-border-sm text-dark" role="status"></div>
                        <span class="ms-2 small text-muted">Menghitung ongkir...</span>
                    </div>

                    <!-- Error box -->
                    <div id="errorOngkir" class="alert alert-warning small py-2 rounded-3" style="display:none"></div>

                    <!-- Alamat Lengkap -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Alamat Lengkap <span class="text-danger">*</span></label>
                        <textarea name="alamat_pengiriman" class="form-control" rows="3"
                                  placeholder="Jalan, nomor rumah, kelurahan, kecamatan, kota" required><?= e($user_profile['alamat'] ?? '') ?></textarea>
                    </div>

                    <!-- Catatan -->
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-dark">Catatan <span class="text-muted fw-normal">(opsional)</span></label>
                        <textarea name="catatan" class="form-control" rows="2"
                                  placeholder="Contoh: titip di satpam"></textarea>
                    </div>

                    <!-- Tombol Submit -->
                    <button type="submit" id="btnSubmitPesan" class="btn rounded-pill py-2 w-100 d-inline-flex align-items-center justify-content-center gap-2 text-white" style="background:#1e3a5f; border-color:#1e3a5f; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);" disabled>
                        <span>Buat Pesanan</span>
                        <i class="fas fa-arrow-right" style="transform: rotate(-45deg); font-size: 13px;"></i>
                    </button>
                    <small id="infoSubmit" class="visually-hidden" aria-live="polite">Pilih kurir untuk melanjutkan.</small>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const products = <?= json_encode(array_map(static function ($p) {
        return ['id' => (int)$p['id_produk'], 'name' => $p['nama_produk'], 'price' => (float)$p['harga'], 'stock' => (int)$p['stok'], 'unit' => $p['satuan'], 'weight' => (int)($p['berat'] ?? 500)];
    }, $semua_produk), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const cart = document.getElementById('cartItems');
    const fmt = n => 'Rp ' + Number(n).toLocaleString('id-ID');
    const rows = () => [...cart.querySelectorAll('.cart-item-row')];
    const chosen = () => rows().map(row => ({
        row,
        product: products.find(p => p.id === Number(row.querySelector('.cart-product-select').value)),
        qty: Math.max(1, parseInt(row.querySelector('.cart-item-qty').value, 10) || 1)
    })).filter(i => i.product);
    const resetShipping = () => {
        requestId++;
        document.getElementById('boxKurirResult').style.display = 'none';
        document.getElementById('listKurir').innerHTML = '';
        document.getElementById('spinnerOngkir').style.display = 'none';
        document.getElementById('errorOngkir').style.display = 'none';
        ['hiddenKurirCode','hiddenKurirName','hiddenKurirService','hiddenKodePos'].forEach(id => document.getElementById(id).value = '');
        document.getElementById('hiddenOngkir').value = '0';
        document.getElementById('displayOngkir').textContent = 'Belum dipilih';
        document.getElementById('btnSubmitPesan').disabled = true;
        document.getElementById('infoSubmit').textContent = 'Pilih kurir untuk melanjutkan.';
        document.getElementById('btnSubmitPesan').dataset.selected = '';
    };
    let requestId = 0;
    function refresh() {
        rows().forEach(row => {
            const product = products.find(p => p.id === Number(row.querySelector('.cart-product-select').value));
            const qty = row.querySelector('.cart-item-qty');
            if (product) {
                qty.max = product.stock;
                qty.value = Math.max(1, Math.min(product.stock, parseInt(qty.value, 10) || 1));
            }
        });
        const items = chosen();
        const used = items.map(i => i.product.id);
        rows().forEach(row => {
            const select = row.querySelector('.cart-product-select');
            [...select.options].forEach(o => o.disabled = Number(o.value) !== Number(select.value) && used.includes(Number(o.value)));
            const item = products.find(p => p.id === Number(select.value));
            const qty = row.querySelector('.cart-item-qty');
            if (!item) return;
            qty.max = item.stock;
            if (+qty.value > item.stock) qty.value = item.stock;
            row.querySelector('.cart-item-price').textContent = `${fmt(item.price)} / ${item.unit} · stok ${item.stock}`;
            const hidden = row.querySelector('input[type=hidden]');
            hidden.name = `items[${item.id}]`; hidden.value = qty.value;
        });
        const subtotal = items.reduce((n, i) => n + i.product.price * i.qty, 0);
        document.getElementById('displaySubtotal').textContent = fmt(subtotal);
        document.getElementById('displayTotalBayar').textContent = fmt(subtotal + Number(document.getElementById('hiddenOngkir').value || 0));
        document.getElementById('addCartItem').disabled = used.length >= products.length;
    }
    function addRow(preferredId) {
        const used = chosen().map(i => i.product.id);
        const product = products.find(p => p.id === Number(preferredId) && !used.includes(p.id)) || products.find(p => !used.includes(p.id));
        if (!product) return;
        const row = document.createElement('div'); row.className = 'cart-item-row';
        const productWrap = document.createElement('div'); productWrap.className = 'cart-item-product';
        const productLabel = document.createElement('label'); productLabel.className = 'form-label small fw-semibold text-dark mb-0'; productLabel.textContent = 'Produk';
        const select = document.createElement('select'); select.className = 'form-select cart-product-select';
        products.forEach(p => { const o = document.createElement('option'); o.value = p.id; o.textContent = p.name; o.selected = p.id === product.id; select.append(o); });
        const qtyWrap = document.createElement('div');
        const qtyLabel = document.createElement('label'); qtyLabel.className = 'form-label small fw-semibold text-dark mb-0'; qtyLabel.textContent = 'Jumlah';
        const qty = document.createElement('input'); qty.type = 'number'; qty.min = '1'; qty.max = product.stock; qty.value = '1'; qty.className = 'form-control cart-item-qty'; qtyWrap.append(qtyLabel, qty);
        const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'cart-remove-btn'; remove.setAttribute('aria-label','Hapus produk'); remove.innerHTML = '<i class="fas fa-trash-alt"></i>';
        const price = document.createElement('div'); price.className = 'cart-item-price';
        const hidden = document.createElement('input'); hidden.type = 'hidden';
        productWrap.append(productLabel, select);
        row.append(productWrap, qtyWrap, remove, price, hidden); cart.append(row);
        select.addEventListener('change', () => { refresh(); resetShipping(); });
        qty.addEventListener('input', () => { if (+qty.value > +qty.max) qty.value = qty.max; refresh(); resetShipping(); });
        remove.addEventListener('click', () => { row.remove(); refresh(); resetShipping(); });
        refresh();
    }
    document.getElementById('addCartItem').addEventListener('click', () => addRow());
    addRow(<?= (int)$id_produk ?>);
    document.getElementById('formPemesanan').addEventListener('submit', function (event) {
        if (this.dataset.submitting === '1') {
            event.preventDefault();
            return;
        }
        this.dataset.submitting = '1';
        const submitButton = document.getElementById('btnSubmitPesan');
        submitButton.disabled = true;
        submitButton.querySelector('span').textContent = 'Memproses pesanan...';
        submitButton.querySelector('i').className = 'fas fa-spinner fa-spin';
    });
    window.cekOngkir = function () {
        const postal = document.getElementById('inputKodePos').value.trim();
        if (!/^\d{5}$/.test(postal)) { alert('Masukkan 5 digit kode pos.'); return; }
        const items = chosen(); if (!items.length) return;
        const weight = items.reduce((n, i) => n + i.product.weight * i.qty, 0);
        const value = items.reduce((n, i) => n + i.product.price * i.qty, 0);
        resetShipping();
        const thisRequest = ++requestId;
        document.getElementById('spinnerOngkir').style.display = 'block';
        const url = '<?= base_url('backend/api/cek-ongkir.php') ?>?kode_pos=' + encodeURIComponent(postal) + '&berat=' + weight + '&harga=' + value;
        fetch(url).then(r => r.json()).then(data => {
            if (thisRequest !== requestId) return;
            document.getElementById('spinnerOngkir').style.display = 'none';
            if (!data.success || !data.rates?.length) throw new Error(data.message || 'Tarif pengiriman tidak ditemukan.');
            const list = document.getElementById('listKurir'); list.replaceChildren();
            data.rates.forEach((rate, index) => {
                const id = 'kurir_' + index;
                const label = document.createElement('label'); label.className = 'd-flex align-items-center gap-3 p-3 border rounded-3 kurir-option'; label.style.cursor = 'pointer';
                const radio = document.createElement('input'); radio.type = 'radio'; radio.name = '_kurir_radio'; radio.id = id; radio.className = 'form-check-input mt-0';
                const info = document.createElement('span'); info.className = 'flex-grow-1';
                const name = document.createElement('strong'); name.className = 'd-block'; name.textContent = rate.courier_name;
                const service = document.createElement('small'); service.className = 'text-muted'; service.textContent = rate.courier_service_name + (rate.duration ? ' · ' + rate.duration + ' hari' : '');
                info.append(name, service);
                const price = document.createElement('strong'); price.className = 'text-primary text-nowrap'; price.textContent = fmt(rate.price);
                label.append(radio, info, price); list.append(label);
                label.addEventListener('click', () => window.pilihKurir(rate, postal, radio, label));
            });
            document.getElementById('boxKurirResult').style.display = 'block';
        }).catch(error => {
            if (thisRequest !== requestId) return;
            document.getElementById('spinnerOngkir').style.display = 'none';
            const box = document.getElementById('errorOngkir'); box.textContent = error.message; box.style.display = 'block';
        });
    };
    window.pilihKurir = function (rate, postal, radio, label) {
        radio.checked = true;
        document.querySelectorAll('.kurir-option').forEach(el => { el.style.background = ''; el.style.borderColor = ''; });
        label.style.background = '#eff6ff'; label.style.borderColor = '#3b82f6';
        document.getElementById('hiddenKurirCode').value = rate.courier_code;
        document.getElementById('hiddenKurirName').value = rate.courier_name;
        document.getElementById('hiddenKurirService').value = rate.courier_service;
        document.getElementById('hiddenOngkir').value = rate.price;
        document.getElementById('hiddenKodePos').value = postal;
        document.getElementById('displayOngkir').textContent = fmt(rate.price);
        const subtotal = chosen().reduce((n, i) => n + i.product.price * i.qty, 0);
        document.getElementById('displayTotalBayar').textContent = fmt(subtotal + Number(rate.price));
        document.getElementById('btnSubmitPesan').disabled = false;
        document.getElementById('infoSubmit').textContent = 'Kurir dipilih: ' + rate.courier_name;
        document.getElementById('btnSubmitPesan').dataset.selected = '1';
    };
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!document.getElementById('selectProduk')) return;
    const inputJumlah   = document.getElementById('inputJumlahPesan');
    const inputHarga    = document.getElementById('inputHargaSatuan');
    const inputBerat    = document.getElementById('inputBeratSatuan');
    const dispSubtotal  = document.getElementById('displaySubtotal');
    const dispOngkir    = document.getElementById('displayOngkir');
    const dispTotal     = document.getElementById('displayTotalBayar');
    const dispBerat     = document.getElementById('displayTotalBerat');
    const btnSubmit     = document.getElementById('btnSubmitPesan');
    const infoSubmit    = document.getElementById('infoSubmit');

    // Hitung subtotal tiap kali jumlah berubah
    if (inputJumlah) {
        inputJumlah.addEventListener('input', function () {
            hitungSubtotal();
            resetKurirResult(); // reset kurir kalau jumlah berubah
        });
    }

    function hitungSubtotal() {
        const qty    = Math.max(1, parseInt(inputJumlah ? inputJumlah.value : 1) || 1);
        const harga  = parseFloat(inputHarga ? inputHarga.value : 0) || 0;
        const berat  = parseInt(inputBerat ? inputBerat.value : 500) || 500;
        const beratTotal = qty * berat;

        if (dispSubtotal) dispSubtotal.textContent = formatRupiah(qty * harga);
        if (dispBerat)    dispBerat.textContent    = beratTotal.toLocaleString('id-ID');
        if (dispTotal)    dispTotal.textContent    = formatRupiah(qty * harga); // akan diupdate setelah pilih kurir
    }

    function resetKurirResult() {
        document.getElementById('boxKurirResult').style.display = 'none';
        document.getElementById('listKurir').innerHTML = '';
        document.getElementById('hiddenKurirCode').value    = '';
        document.getElementById('hiddenKurirName').value    = '';
        document.getElementById('hiddenKurirService').value = '';
        document.getElementById('hiddenOngkir').value       = '0';
        document.getElementById('hiddenKodePos').value      = '';
        if (dispOngkir) dispOngkir.innerHTML = '<span class="text-secondary fst-italic">— Pilih kurir di bawah —</span>';
        if (btnSubmit)  btnSubmit.disabled = true;
        if (infoSubmit) infoSubmit.textContent = 'Pilih kurir terlebih dahulu untuk melanjutkan.';
    }

    function formatRupiah(val) {
        return 'Rp ' + Number(val).toLocaleString('id-ID');
    }

    // Global function: dipanggil oleh tombol "Cek Ongkir"
    window.cekOngkir = function () {
        const kodePos = (document.getElementById('inputKodePos').value || '').trim();
        if (!/^\d{5}$/.test(kodePos)) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Kode Pos Tidak Valid',
                    text: 'Masukkan 5 digit kode pos yang valid, contoh: 35141',
                    confirmButtonText: 'Mengerti'
                });
            } else {
                alert('Masukkan 5 digit kode pos yang valid, contoh: 50231');
            }
            return;
        }

        const qty   = Math.max(1, parseInt(inputJumlah ? inputJumlah.value : 1) || 1);
        const berat = parseInt(inputBerat ? inputBerat.value : 500) || 500;
        const harga = parseFloat(inputHarga ? inputHarga.value : 100000) || 100000;
        const beratTotal = qty * berat;
        const nilaiBarang = qty * harga;

        document.getElementById('spinnerOngkir').style.display = 'block';
        document.getElementById('boxKurirResult').style.display = 'none';
        document.getElementById('errorOngkir').style.display = 'none';
        resetKurirResult();

        const url = '<?= base_url('backend/api/cek-ongkir.php') ?>?kode_pos=' + kodePos + '&berat=' + beratTotal + '&harga=' + nilaiBarang;

        fetch(url)
            .then(r => r.json())
            .then(data => {
                document.getElementById('spinnerOngkir').style.display = 'none';
                if (!data.success || !data.rates || data.rates.length === 0) {
                    const errBox = document.getElementById('errorOngkir');
                    errBox.style.display = 'block';
                    errBox.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>' + (data.message || 'Tarif pengiriman tidak ditemukan untuk kode pos ini. Pastikan kode pos sudah benar.');
                    return;
                }

                // Tampilkan daftar kurir
                const listEl = document.getElementById('listKurir');
                listEl.innerHTML = '';

                data.rates.forEach(function (r, idx) {
                    const id = 'kurir_' + idx;
                    const durasiLabel = r.duration ? r.duration + ' hari' : '';
                    listEl.innerHTML += `
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 cursor-pointer kurir-option" for="${id}"
                               style="cursor:pointer; transition:.2s;" 
                               onclick="pilihKurir('${r.courier_code}', '${escapeStr(r.courier_name)}', '${r.courier_service}', ${r.price}, '${kodePos}', '${id}')">
                            <input type="radio" class="form-check-input mt-0 flex-shrink-0" id="${id}" name="_kurir_radio" value="${idx}" style="width:18px;height:18px">
                            <div class="flex-grow-1">
                                <div class="fw-bold text-navy">${escapeStr(r.courier_name)}</div>
                                <small class="text-muted">${escapeStr(r.courier_service_name)} ${durasiLabel ? '· Est. ' + durasiLabel : ''}</small>
                            </div>
                            <div class="fw-bold text-primary text-nowrap">${formatRupiah(r.price)}</div>
                        </label>`;
                });

                document.getElementById('boxKurirResult').style.display = 'block';
            })
            .catch(function () {
                document.getElementById('spinnerOngkir').style.display = 'none';
                const errBox = document.getElementById('errorOngkir');
                errBox.style.display = 'block';
                errBox.innerHTML = '<i class="fas fa-wifi me-1"></i>Gagal menghubungi server. Periksa koneksi internet.';
            });
    };

    window.pilihKurir = function (code, name, service, price, kodePos, radioId) {
        // Aktifkan radio
        const radio = document.getElementById(radioId);
        if (radio) radio.checked = true;

        // Highlight pilihan
        document.querySelectorAll('.kurir-option').forEach(el => {
            el.style.background = '';
            el.style.borderColor = '';
        });
        if (radio) {
            radio.closest('label').style.background = '#eff6ff';
            radio.closest('label').style.borderColor = '#3b82f6';
        }

        // Simpan ke hidden inputs
        document.getElementById('hiddenKurirCode').value    = code;
        document.getElementById('hiddenKurirName').value    = name;
        document.getElementById('hiddenKurirService').value = service;
        document.getElementById('hiddenOngkir').value       = price;
        document.getElementById('hiddenKodePos').value      = kodePos;

        // Update tampilan ongkir & total
        const qty   = Math.max(1, parseInt(inputJumlah ? inputJumlah.value : 1) || 1);
        const harga = parseFloat(inputHarga ? inputHarga.value : 0) || 0;
        const subtotal = qty * harga;
        const grandTotal = subtotal + price;

        if (dispOngkir) dispOngkir.textContent = formatRupiah(price);
        if (dispTotal)  dispTotal.textContent  = formatRupiah(grandTotal);

        // Aktifkan tombol submit
        if (btnSubmit)  btnSubmit.disabled = false;
        if (infoSubmit) infoSubmit.textContent = 'Kurir: ' + name + ' — Klik tombol untuk melanjutkan.';
    };

    function escapeStr(str) {
        return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

    // Trigger hitung subtotal awal
    hitungSubtotal();
});
</script>

<?php require_once __DIR__ . '/../includes/pengguna-footer.php'; ?>
