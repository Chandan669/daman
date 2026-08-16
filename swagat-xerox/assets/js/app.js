document.addEventListener('DOMContentLoaded', function() {
    const itemsBody = document.getElementById('itemsBody');
    const addItemBtn = document.getElementById('addItemBtn');

    const inputCustomer = document.getElementById('input-customer');
    const inputAddress = document.getElementById('input-address');
    const inputMobile = document.getElementById('input-mobile');
    const inputDate = document.getElementById('input-date');
    const inputPayment = document.getElementById('input-payment');
    const inputDiscount = document.getElementById('input-discount');

    const prevCustomer = document.getElementById('prev-customer');
    const prevAddress = document.getElementById('prev-address');
    const prevMobile = document.getElementById('prev-mobile');
    const prevDate = document.getElementById('prev-date');
    const prevPayment = document.getElementById('prev-payment');

    const prevSubtotal = document.getElementById('prev-subtotal');
    const prevDiscount = document.getElementById('prev-discount');
    const prevTotal = document.getElementById('prev-total');

    const inputSubtotal = document.getElementById('input-subtotal');
    const inputTotal = document.getElementById('input-total');

    const prevItemsBody = document.getElementById('prev-items-body');

    if (!itemsBody) return;

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        if (isNaN(d)) return dateStr;
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        return `${day}-${month}-${year}`;
    }

    function escapeHTML(str) {
        return str.replace(/[&<>'"]/g,
            tag => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            }[tag] || tag)
        );
    }

    function createRow(desc = '', qty = '', rate = '') {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="item_desc[]" placeholder="Item description" value="${escapeHTML(desc)}" class="item-desc" autocomplete="off"></td>
            <td><input type="number" name="item_qty[]" placeholder="Qty" value="${qty}" class="item-qty" min="0" step="any"></td>
            <td><input type="number" name="item_rate[]" placeholder="Rate" value="${rate}" class="item-rate" min="0" step="any"></td>
            <td><button type="button" class="btn-sm btn-danger remove-btn">X</button></td>
        `;
        itemsBody.appendChild(tr);
        attachRowListeners(tr);
    }

    function attachRowListeners(tr) {
        tr.querySelector('.remove-btn').addEventListener('click', function() {
            tr.remove();
            calculateTotals();
        });

        const inputs = tr.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', calculateTotals);
        });
    }

    function calculateTotals() {
        let subtotal = 0;
        const previewRowsHTML = [];

        const rows = itemsBody.querySelectorAll('tr');
        let index = 1;

        rows.forEach(row => {
            const desc = row.querySelector('.item-desc').value;
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const rate = parseFloat(row.querySelector('.item-rate').value) || 0;

            if (desc.trim() !== '') {
                const amount = qty * rate;
                subtotal += amount;

                previewRowsHTML.push(`
                    <tr>
                        <td style="text-align:center;">${index}</td>
                        <td>${escapeHTML(desc)}</td>
                        <td style="text-align:right;">${qty}</td>
                        <td style="text-align:right;">${rate.toFixed(2)}</td>
                        <td style="text-align:right;">${amount.toFixed(2)}</td>
                    </tr>
                `);
                index++;
            }
        });

        const discount = parseFloat(inputDiscount.value) || 0;
        const total = subtotal - discount;

        inputSubtotal.value = subtotal.toFixed(2);
        inputTotal.value = total.toFixed(2);

        prevSubtotal.textContent = subtotal.toFixed(2);
        prevDiscount.textContent = discount.toFixed(2);
        prevTotal.textContent = total.toFixed(2);

        prevItemsBody.innerHTML = previewRowsHTML.join('');
    }

    function updateBasicPreview() {
        if(prevCustomer) prevCustomer.textContent = inputCustomer.value;
        if(prevAddress) prevAddress.textContent = inputAddress.value;
        if(prevMobile) prevMobile.textContent = inputMobile.value;
        if(prevDate) prevDate.textContent = formatDate(inputDate.value);
        if(prevPayment) prevPayment.textContent = inputPayment.value;
    }

    [inputCustomer, inputAddress, inputMobile, inputDate, inputPayment].forEach(el => {
        if (el) {
            el.addEventListener('input', updateBasicPreview);
            el.addEventListener('change', updateBasicPreview);
        }
    });

    if (inputDiscount) {
        inputDiscount.addEventListener('input', calculateTotals);
    }

    if (addItemBtn) {
        addItemBtn.addEventListener('click', () => {
            createRow();
        });
    }

    if (window.memoData && window.memoData.isEdit && window.memoData.items && window.memoData.items.length > 0) {
        window.memoData.items.forEach(item => {
            createRow(item.desc, item.qty, item.rate);
        });
    } else {
        const defaultRows = (window.memoData && window.memoData.defaultRows) ? window.memoData.defaultRows : 5;
        for (let i = 0; i < defaultRows; i++) {
            createRow();
        }
    }

    updateBasicPreview();
    calculateTotals();

    const saveAndPrintBtn = document.getElementById('saveAndPrintBtn');
    const memoForm = document.getElementById('memoForm');

    if (saveAndPrintBtn && memoForm) {
        saveAndPrintBtn.addEventListener('click', function() {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'action';
            input.value = 'print';
            memoForm.appendChild(input);
            memoForm.submit();
        });
    }
});
