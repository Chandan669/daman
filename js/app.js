/**
 * js/app.js
 * Application logic for BAZRIO Stock Manager
 */

// --- 1. UI NAVIGATION ---
function showView(viewId) {
    document.querySelectorAll('.view-container').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.nav-links a').forEach(el => el.classList.remove('active'));
    document.getElementById('view-' + viewId).classList.add('active');
    document.getElementById('nav-' + viewId).classList.add('active');
    refreshData();
}

function showAlert(msg, type='success') {
    const container = document.getElementById('alert-container');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = msg;
    container.appendChild(alert);
    setTimeout(() => alert.remove(), 3000);
}

// --- 2. FORMATTERS & SANITIZERS ---
function formatCurrency(amount) { return '₹' + parseFloat(amount).toFixed(2); }
function formatDate(dateString) { return new Date(dateString).toLocaleDateString('en-IN'); }
function escapeHTML(str) {
    const p = document.createElement('p');
    p.appendChild(document.createTextNode(str));
    return p.innerHTML;
}

// --- 3. PRODUCTS MASTER ---
function handleProductSubmit(e) {
    e.preventDefault();
    const pid = document.getElementById('p_id').value;
    const product = {
        name: document.getElementById('p_name').value,
        color: document.getElementById('p_color').value,
        size: document.getElementById('p_size').value,
        sleeve: document.getElementById('p_sleeve').value,
        costPrice: parseFloat(document.getElementById('p_cost').value),
        sellingPrice: parseFloat(document.getElementById('p_sell').value),
        openingStock: parseFloat(document.getElementById('p_open').value) || 0,
        minStock: parseFloat(document.getElementById('p_min').value) || 0
    };

    if (pid) {
        product.id = pid;
        db.updateProduct(product);
        showAlert('Product updated successfully!');
        cancelProductEdit();
    } else {
        db.addProduct(product);
        showAlert('Product added successfully!');
        document.getElementById('form-product').reset();
    }
    renderProducts();
    updateSelectors();
}

function editProduct(id) {
    const p = db.getProductById(id);
    if (!p) return;
    document.getElementById('p_id').value = p.id;
    document.getElementById('p_name').value = p.name;
    document.getElementById('p_color').value = p.color;
    document.getElementById('p_size').value = p.size;
    document.getElementById('p_sleeve').value = p.sleeve;
    document.getElementById('p_cost').value = p.costPrice;
    document.getElementById('p_sell').value = p.sellingPrice;
    document.getElementById('p_open').value = p.openingStock;
    document.getElementById('p_min').value = p.minStock;

    document.getElementById('btn_save_product').textContent = "Update Product";
    document.getElementById('btn_cancel_product').style.display = "inline-block";
    window.scrollTo(0, 0);
}

function cancelProductEdit() {
    document.getElementById('p_id').value = "";
    document.getElementById('form-product').reset();
    document.getElementById('btn_save_product').textContent = "Add Product";
    document.getElementById('btn_cancel_product').style.display = "none";
}

function deleteProduct(id) {
    if(confirm("Are you sure you want to delete this product? All related transactions will also be deleted!")) {
        db.deleteProduct(id);
        showAlert('Product deleted successfully!', 'success');
        refreshData();
    }
}

function renderProducts() {
    const tbody = document.querySelector('#products-table tbody');
    if(!tbody) return;
    tbody.innerHTML = '';
    const products = db.getProducts();
    products.forEach(p => {
        const currentStock = db.getProductStock(p.id);
        const tr = document.createElement('tr');
        const safeName = escapeHTML(p.name);
        const safeColor = escapeHTML(p.color);
        tr.innerHTML = `
            <td><strong>${safeName}</strong><br><small>${safeColor} | Size: ${p.size} | ${p.sleeve}</small></td>
            <td>${formatCurrency(p.costPrice)}</td>
            <td>${formatCurrency(p.sellingPrice)}</td>
            <td style="color: ${currentStock <= p.minStock ? 'var(--accent-red)' : 'var(--accent-green)'}"><strong>${currentStock}</strong></td>
            <td>
                <button onclick="editProduct('${p.id}')" class="btn btn-info" style="padding: 5px 10px; font-size: 0.8em; margin-right: 5px;">Edit</button>
                <button onclick="deleteProduct('${p.id}')" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.8em;">Del</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function updateSelectors() {
    const products = db.getProducts();
    const inSelect = document.getElementById('in_product');
    const outSelect = document.getElementById('out_product');

    if(inSelect && outSelect) {
        let options = '<option value="">-- Select Product --</option>';
        products.forEach(p => {
            const stock = db.getProductStock(p.id);
            const safeName = escapeHTML(p.name);
            const safeColor = escapeHTML(p.color);
            const label = `${safeName} - ${safeColor} (${p.size}, ${p.sleeve}) [Stock: ${stock}]`;
            options += `<option value="${p.id}">${label}</option>`;
        });
        inSelect.innerHTML = options;
        outSelect.innerHTML = options;
    }
}

// --- 4. STOCK TRANSACTIONS (IN / OUT) ---
function calcTotal(prefix) {
    const qty = parseFloat(document.getElementById(`${prefix}_qty`).value) || 0;
    const rate = parseFloat(document.getElementById(`${prefix}_rate`).value) || 0;
    const paid = parseFloat(document.getElementById(`${prefix}_paid`).value) || 0;

    const total = qty * rate;
    const pending = total - paid;

    document.getElementById(`${prefix}_total`).value = total.toFixed(2);
    document.getElementById(`${prefix}_pending`).value = pending.toFixed(2);
}

function setOutRate() {
    const pid = document.getElementById('out_product').value;
    const p = db.getProductById(pid);
    if(p) {
        document.getElementById('out_rate').value = p.sellingPrice;
        calcTotal('out');
    }
}

document.getElementById('in_product')?.addEventListener('change', (e) => {
    const p = db.getProductById(e.target.value);
    if(p) {
        document.getElementById('in_rate').value = p.costPrice;
        calcTotal('in');
    }
});

function handleTxSubmit(e, type) {
    e.preventDefault();
    const prefix = type === 'IN' ? 'in' : 'out';
    const txId = document.getElementById(`${prefix}_id`).value;

    const pid = document.getElementById(`${prefix}_product`).value;
    if(!pid) {
        showAlert('Please select a product', 'warning');
        return;
    }

    const qty = parseFloat(document.getElementById(`${prefix}_qty`).value);

    if(type === 'OUT' && !txId) { // Only strict check on new OUTs
        const stock = db.getProductStock(pid);
        if(qty > stock) {
            showAlert(`Insufficient stock! Only ${stock} available.`, 'error');
            return;
        }
    }

    const tx = {
        date: document.getElementById(`${prefix}_date`).value,
        type: type,
        productId: pid,
        entityName: document.getElementById(`${prefix}_entity`).value,
        qty: qty,
        rate: parseFloat(document.getElementById(`${prefix}_rate`).value),
        total: parseFloat(document.getElementById(`${prefix}_total`).value),
        paid: parseFloat(document.getElementById(`${prefix}_paid`).value),
        pending: parseFloat(document.getElementById(`${prefix}_pending`).value)
    };

    if (txId) {
        tx.id = txId;
        db.updateTransaction(tx);
        showAlert(`Stock ${type} updated successfully!`);
        cancelTxEdit(prefix);
    } else {
        db.addTransaction(tx);
        showAlert(`Stock ${type} successful!`);
        document.getElementById(`form-${prefix}`).reset();
    }

    updateSelectors(); // Update stock counts in dropdowns

    // trigger 3d animation if on dashboard (will be handled by refreshData if integrated)
    if(typeof animateTshirt === 'function') animateTshirt();
}

function editTransaction(id) {
    const t = db.getTransactionById(id);
    if (!t) return;

    const prefix = t.type === 'IN' ? 'in' : 'out';

    // Navigate to the correct view
    showView(prefix === 'in' ? 'stockin' : 'stockout');

    document.getElementById(`${prefix}_id`).value = t.id;
    document.getElementById(`${prefix}_date`).value = t.date;
    document.getElementById(`${prefix}_entity`).value = t.entityName;
    document.getElementById(`${prefix}_product`).value = t.productId;
    document.getElementById(`${prefix}_qty`).value = t.qty;
    document.getElementById(`${prefix}_rate`).value = t.rate;
    document.getElementById(`${prefix}_total`).value = t.total;
    document.getElementById(`${prefix}_paid`).value = t.paid;
    document.getElementById(`${prefix}_pending`).value = t.pending;

    document.getElementById(`btn_save_${prefix}`).textContent = `Update Stock ${t.type}`;
    document.getElementById(`btn_cancel_${prefix}`).style.display = "inline-block";
    window.scrollTo(0, 0);
}

function cancelTxEdit(prefix) {
    document.getElementById(`${prefix}_id`).value = "";
    document.getElementById(`form-${prefix}`).reset();
    document.getElementById(`btn_save_${prefix}`).textContent = prefix === 'in' ? "Save Stock IN" : "Issue Stock OUT";
    document.getElementById(`btn_cancel_${prefix}`).style.display = "none";
}

function deleteTransaction(id) {
    if(confirm("Are you sure you want to delete this transaction?")) {
        db.deleteTransaction(id);
        showAlert('Transaction deleted successfully!', 'success');
        refreshData();
    }
}

// --- 5. DASHBOARD & LEDGER ---
function renderDashboard() {
    const products = db.getProducts();
    const txs = db.getTransactions();

    let totalStockVal = 0;
    let todayIn = 0;
    let todayOut = 0;
    let totalPending = 0;

    const today = new Date().toISOString().slice(0, 10);
    const lowStockHtml = [];

    products.forEach(p => {
        const stock = db.getProductStock(p.id);
        totalStockVal += stock * p.costPrice;

        if(stock <= p.minStock) {
            const safeName = escapeHTML(p.name);
            const safeColor = escapeHTML(p.color);
            lowStockHtml.push(`
                <tr>
                    <td>${safeName} - ${safeColor} (${p.size}, ${p.sleeve})</td>
                    <td style="color:var(--accent-red); font-weight:bold">${stock}</td>
                    <td>${p.minStock}</td>
                </tr>
            `);
        }
    });

    txs.forEach(t => {
        if(t.date === today) {
            if(t.type === 'IN') todayIn += parseFloat(t.qty);
            if(t.type === 'OUT') todayOut += parseFloat(t.qty);
        }
        totalPending += parseFloat(t.pending);
    });

    document.getElementById('kpi-stock-value').textContent = formatCurrency(totalStockVal);
    document.getElementById('kpi-today-in').textContent = todayIn;
    document.getElementById('kpi-today-out').textContent = todayOut;
    document.getElementById('kpi-total-pending').textContent = formatCurrency(totalPending);

    document.querySelector('#low-stock-table tbody').innerHTML = lowStockHtml.length ? lowStockHtml.join('') : '<tr><td colspan="3" style="text-align:center">No items are low on stock.</td></tr>';
}

function renderLedger() {
    const txs = db.getTransactions();
    const tbody = document.querySelector('#ledger-table tbody');
    const filterSelect = document.getElementById('ledger_filter');
    const filterValue = filterSelect.value;

    // Populate filter options dynamically based on unique entity names
    const entities = [...new Set(txs.map(t => t.entityName))];
    if (filterSelect.options.length <= 1) { // Only add if not already populated
        entities.forEach(e => {
            const opt = document.createElement('option');
            opt.value = e;
            opt.textContent = e;
            filterSelect.appendChild(opt);
        });
    }

    tbody.innerHTML = '';

    const filteredTxs = filterValue ? txs.filter(t => t.entityName === filterValue) : txs;

    filteredTxs.sort((a,b) => new Date(b.date) - new Date(a.date)).forEach(t => {
        const p = db.getProductById(t.productId);
        const pName = p ? `${escapeHTML(p.name)} (${p.size})` : 'Unknown';
        const safeEntity = escapeHTML(t.entityName);

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${formatDate(t.date)}</td>
            <td><strong>${safeEntity}</strong></td>
            <td><span style="color: ${t.type === 'IN' ? 'var(--accent-blue)' : 'var(--accent-green)'}">${t.type}</span></td>
            <td>${pName}</td>
            <td>${t.qty}</td>
            <td>${formatCurrency(t.total)}</td>
            <td>${formatCurrency(t.paid)}</td>
            <td style="color: ${t.pending > 0 ? 'var(--accent-red)' : 'var(--text-main)'}">${formatCurrency(t.pending)}</td>
            <td>
                <button onclick="editTransaction('${t.id}')" class="btn btn-info" style="padding: 5px 10px; font-size: 0.8em; margin-right: 5px;">Edit</button>
                <button onclick="deleteTransaction('${t.id}')" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.8em;">Del</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// --- 6. REPORTS & PDF ---
function generateReport() {
    const fromDate = document.getElementById('rep_from').value;
    const toDate = document.getElementById('rep_to').value;

    const txs = db.getTransactions();
    const filtered = txs.filter(t => {
        if(fromDate && t.date < fromDate) return false;
        if(toDate && t.date > toDate) return false;
        return true;
    });

    let sales = 0;
    let profit = 0;
    let pending = 0;
    const tbody = document.getElementById('report-table-body');
    tbody.innerHTML = '';

    filtered.sort((a,b) => new Date(a.date) - new Date(b.date)).forEach(t => {
        const p = db.getProductById(t.productId);
        if(!p) return;

        if(t.type === 'OUT') {
            sales += t.total;
            // Profit calculation: (Selling Price - Cost Price) * qty
            profit += (t.rate - p.costPrice) * t.qty;
        }
        pending += t.pending;

        const tr = document.createElement('tr');
        const safeName = escapeHTML(p.name);
        const safeColor = escapeHTML(p.color);
        tr.innerHTML = `
            <td style="padding:8px; border:1px solid #ccc;">${formatDate(t.date)}</td>
            <td style="padding:8px; border:1px solid #ccc;">${t.type}</td>
            <td style="padding:8px; border:1px solid #ccc;">${safeName} (${safeColor} / ${p.size})</td>
            <td style="padding:8px; border:1px solid #ccc;">${t.qty}</td>
            <td style="padding:8px; border:1px solid #ccc;">${formatCurrency(t.rate)}</td>
            <td style="padding:8px; border:1px solid #ccc;">${formatCurrency(t.total)}</td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('rep-sales').textContent = formatCurrency(sales);
    document.getElementById('rep-profit').textContent = formatCurrency(profit);
    document.getElementById('rep-pending').textContent = formatCurrency(pending);

    let dateStr = "All Time";
    if(fromDate || toDate) {
        dateStr = `${fromDate ? formatDate(fromDate) : 'Start'} to ${toDate ? formatDate(toDate) : 'Present'}`;
    }
    document.getElementById('pdf-dates').textContent = dateStr;

    document.getElementById('report-content').style.display = 'block';
}

function downloadPDF() {
    const element = document.getElementById('report-content');
    if(element.style.display === 'none') {
        showAlert('Please generate the report first!', 'warning');
        return;
    }

    const opt = {
      margin:       0.5,
      filename:     `BAZRIO_Report_${new Date().toISOString().slice(0,10)}.pdf`,
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2 },
      jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save();
}

function handleImport() {
    const file = document.getElementById('import_file').files[0];
    if(!file) {
        showAlert('Please select a file first', 'error');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const success = db.importBackup(e.target.result);
        if(success) {
            showAlert('Backup imported successfully!');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('Invalid backup file format.', 'error');
        }
    };
    reader.readAsText(file);
}

// --- 7. INITIALIZATION & 3D ---
function refreshData() {
    renderProducts();
    updateSelectors();
    renderDashboard();
    renderLedger();
}

// 3D Canvas Logic (Preserved and adapted from previous version)
const canvasContainer = document.getElementById('canvas-container');
if(canvasContainer) {
    const scene = new THREE.Scene();
    scene.background = new THREE.Color('#1e1e24');

    const camera = new THREE.PerspectiveCamera(75, canvasContainer.clientWidth / canvasContainer.clientHeight, 0.1, 1000);
    camera.position.z = 5;

    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(canvasContainer.clientWidth, canvasContainer.clientHeight);
    canvasContainer.appendChild(renderer.domElement);

    const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
    scene.add(ambientLight);
    const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
    dirLight.position.set(5, 5, 5);
    scene.add(dirLight);

    const tShirtGroup = new THREE.Group();
    const clothMat = new THREE.MeshPhongMaterial({ color: 0xff6b6b });

    const bodyGeo = new THREE.BoxGeometry(2, 2.5, 0.5);
    const bodyMesh = new THREE.Mesh(bodyGeo, clothMat);
    tShirtGroup.add(bodyMesh);

    const sleeveGeo = new THREE.BoxGeometry(0.8, 1, 0.5);
    const lSleeve = new THREE.Mesh(sleeveGeo, clothMat);
    lSleeve.position.set(-1.3, 0.5, 0);
    lSleeve.rotation.z = Math.PI / 6;
    tShirtGroup.add(lSleeve);

    const rSleeve = new THREE.Mesh(sleeveGeo, clothMat);
    rSleeve.position.set(1.3, 0.5, 0);
    rSleeve.rotation.z = -Math.PI / 6;
    tShirtGroup.add(rSleeve);

    scene.add(tShirtGroup);

    let bounce = 0;
    let isAnimating = false;

    function animate() {
        requestAnimationFrame(animate);
        tShirtGroup.rotation.y += 0.005;
        if (isAnimating) {
            bounce += 0.2;
            tShirtGroup.position.y = Math.sin(bounce) * 0.5;
            if (bounce > Math.PI * 2) {
                bounce = 0;
                isAnimating = false;
                tShirtGroup.position.y = 0;
            }
        } else {
            tShirtGroup.position.y = Math.sin(Date.now() * 0.002) * 0.1;
        }
        renderer.render(scene, camera);
    }
    animate();

    window.addEventListener('resize', () => {
        camera.aspect = canvasContainer.clientWidth / canvasContainer.clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(canvasContainer.clientWidth, canvasContainer.clientHeight);
    });

    window.animateTshirt = function() {
        isAnimating = true;
        const colors = [0xff6b6b, 0x1dd1a1, 0x5f27cd, 0x54a0ff, 0xfeca57];
        const randomColor = colors[Math.floor(Math.random() * colors.length)];
        tShirtGroup.children.forEach(child => {
            child.material.color.setHex(randomColor);
        });
    };
}

// Init call
refreshData();
