document.addEventListener('DOMContentLoaded', async function() {
    const layoutBtns = document.querySelectorAll('.layout-btn');
    const inputCopies = document.getElementById('inputCopies');
    const previewPage = document.getElementById('previewPage');
    const previewPageInfo = document.getElementById('previewPageInfo');
    const btnExportPDF = document.getElementById('btnExportPDF');
    const btnExportJPG = document.getElementById('btnExportJPG');
    const btnPrintNative = document.getElementById('btnPrintNative');
    const btnShare = document.getElementById('btnShare');
    const loadingIndicator = document.getElementById('loadingIndicator');

    let currentLayout = parseInt(localStorage.getItem("preferred_print_layout")) || window.exportData.defaultLayout;
    let memosData = [];
    let settings = {};
    let logoUrl = null;
    let signatureUrl = null;

    // Initialize layout selection
    function updateLayoutSelection() {
        layoutBtns.forEach(btn => {
            if (parseInt(btn.dataset.layout) === currentLayout) {
                btn.classList.add('selected');
            } else {
                btn.classList.remove('selected');
            }
        });
        updatePreview();
    }

    layoutBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            currentLayout = parseInt(btn.dataset.layout);
            localStorage.setItem("preferred_print_layout", currentLayout);
            updateLayoutSelection();
        });
    });

    inputCopies.addEventListener('input', updatePreview);

    // Fetch Memo Data
    async function fetchData() {
        try {
            const response = await fetch('api/get-memos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ids: window.exportData.ids })
            });
            const data = await response.json();
            if (data.memos) {
                memosData = data.memos;
                settings = data.settings || {};
                logoUrl = data.logo_url;
                signatureUrl = data.signature_url;
                updatePreview();
            }
        } catch (e) {
            console.error("Failed to fetch memos", e);
            alert("Failed to load memo data.");
        }
    }

    function escapeHTML(str) {
        if (!str) return '';
        return String(str).replace(/[&<>'"]/g,
            tag => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            }[tag] || tag)
        );
    }

    // Build array of memos to render based on copies
    function getRenderList() {
        const copies = parseInt(inputCopies.value) || 1;
        let list = [];
        for (let c = 0; c < copies; c++) {
            list = list.concat(memosData);
        }
        return list;
    }

    function updatePreview() {
        if (memosData.length === 0) return;

        previewPage.className = `preview-page grid-${currentLayout}`;
        previewPage.innerHTML = '';

        const renderList = getRenderList();
        const totalItems = renderList.length;
        const totalPages = Math.ceil(totalItems / currentLayout);

        previewPageInfo.textContent = `Page 1 of ${totalPages} (Total ${totalItems} memo slots)`;

        // Show only first page preview to save DOM elements
        const itemsToShow = Math.min(currentLayout, renderList.length);

        for (let i = 0; i < currentLayout; i++) {
            const cell = document.createElement('div');
            cell.className = 'preview-cell';

            if (i < itemsToShow) {
                const m = renderList[i];
                cell.innerHTML = `
                    <div class="preview-cell-inner">
                        <strong>${escapeHTML(settings.company_name || 'SWAGAT XEROX CENTER')}</strong>
                        <div>Memo: ${escapeHTML(m.memo_no)}</div>
                        <div>₹${m.total}</div>
                    </div>
                `;
            } else {
                cell.innerHTML = `<div class="preview-cell-inner" style="background:#fff;"></div>`; // Empty slot
            }
            previewPage.appendChild(cell);
        }
    }

    // Generator logic
    function generateHtmlForPrint(renderList) {
        let html = '';
        const totalItems = renderList.length;
        const totalPages = Math.ceil(totalItems / currentLayout);

        // Settings helpers
        const lAlign = settings.logo_alignment ? settings.logo_alignment.toLowerCase() : 'right';
        const sAlign = settings.signature_alignment ? settings.signature_alignment.toLowerCase() : 'right';
        const sWidth = settings.signature_width || 120;

        let printed = 0;

        for (let p = 0; p < totalPages; p++) {
            html += `<div class="page grid-${currentLayout}">`;

            for (let i = 0; i < currentLayout; i++) {
                if (printed >= totalItems) {
                    html += `<div class="memo-wrapper" style="border:none;"></div>`;
                    continue;
                }
                const m = renderList[printed];
                printed++;

                // Construct items table
                let itemsHtml = '';
                let itemCount = 0;
                if(m.items) {
                    m.items.forEach((item, idx) => {
                        itemCount++;
                        itemsHtml += `
                            <tr>
                                <td class="text-center">${idx + 1}</td>
                                <td>${escapeHTML(item.desc)}</td>
                                <td class="text-right">${item.qty}</td>
                                <td class="text-right">${parseFloat(item.rate).toFixed(2)}</td>
                                <td class="text-right">${parseFloat(item.amount).toFixed(2)}</td>
                            </tr>
                        `;
                    });
                }
                // Fill empty
                const minRows = (currentLayout >= 6) ? 2 : 5;
                while (itemCount < minRows) {
                    itemsHtml += '<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>';
                    itemCount++;
                }

                const dDate = m.date ? new Date(m.date) : new Date();
                const fDate = `${String(dDate.getDate()).padStart(2, '0')}-${String(dDate.getMonth() + 1).padStart(2, '0')}-${dDate.getFullYear()}`;

                html += `
                <div class="memo-wrapper">
                    <div class="memo-inner">
                        <div class="header align-${lAlign}">
                            ${logoUrl ? `<img src="${logoUrl}" alt="Logo" class="memo-logo">` : ''}
                            <h1>${escapeHTML(settings.company_name || 'SWAGAT XEROX CENTER')}</h1>
                            <p class="subtitle">${escapeHTML(settings.company_subtitle || '')}</p>
                        </div>

                        <div class="title">CASH MEMO / PAID</div>

                        <div class="meta">
                            <div style="flex:1;">
                                <div><b>Memo No:</b> ${escapeHTML(m.memo_no)}</div>
                                <div><b>Customer:</b> ${escapeHTML(m.customer_name || '')}</div>
                                <div><b>Address:</b> ${escapeHTML(m.address || '')}</div>
                                <div><b>Mobile:</b> ${escapeHTML(m.mobile_number || '')}</div>
                            </div>
                            <div>
                                <div><b>Date:</b> ${fDate}</div>
                                <div><b>Payment:</b> ${escapeHTML(m.payment_method || 'Cash')}</div>
                            </div>
                        </div>

                        <table class="memo-table">
                            <thead>
                                <tr>
                                    <th width="5%">No.</th>
                                    <th width="55%">Item / Particulars</th>
                                    <th width="10%" class="text-right">Qty</th>
                                    <th width="15%" class="text-right">Rate</th>
                                    <th width="15%" class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsHtml}
                            </tbody>
                        </table>

                        <div class="totals">
                            <div class="totals-row">
                                <span>Subtotal:</span>
                                <span>₹${parseFloat(m.subtotal || 0).toFixed(2)}</span>
                            </div>
                            ${parseFloat(m.discount || 0) > 0 ? `
                            <div class="totals-row">
                                <span>Discount:</span>
                                <span>- ₹${parseFloat(m.discount).toFixed(2)}</span>
                            </div>` : ''}
                            <div class="totals-row final-total">
                                <span>TOTAL:</span>
                                <span>₹${parseFloat(m.total || 0).toFixed(2)}</span>
                            </div>
                        </div>

                        <div class="footer">
                            <div class="paid-stamp">PAID</div>
                            <div class="signature-box pos-${sAlign}">
                                ${signatureUrl ? `<img src="${signatureUrl}" alt="Signature" class="memo-signature" style="max-width:${sWidth}px;">` : '<br><br><br>'}
                                <div class="auth-text">Authorized Signature</div>
                            </div>
                        </div>
                    </div>
                </div>`;
            }
            html += `</div>`;
        }
        return html;
    }

    function setupRenderContainer(htmlContent) {
        const rc = document.getElementById('renderContainer');
        // Inject styles similar to print.css but for DOM rendering
        rc.innerHTML = `
            <style>
                .render-wrapper { width: 210mm; background: white; color: black; font-family: sans-serif; }
                .page { width: 210mm; height: 297mm; box-sizing: border-box; position: relative;}

                .grid-1 { display: grid; grid-template-columns: 1fr; grid-template-rows: 1fr; padding: 10mm; }
                .grid-2 { display: grid; grid-template-columns: 1fr; grid-template-rows: 1fr 1fr; }
                .grid-3 { display: grid; grid-template-columns: 1fr; grid-template-rows: 1fr 1fr 1fr; }
                .grid-4 { display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; }
                .grid-6 { display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr 1fr; }

                .memo-wrapper { border: 1px dashed #ccc; padding: 5mm; box-sizing: border-box; overflow: hidden; display: flex; flex-direction: column;}
                .memo-inner { font-size: 12px; line-height: 1.4; display:flex; flex-direction: column; height: 100%; }

                .grid-1 .memo-inner { font-size: 16px; }
                .grid-2 .memo-inner { font-size: 14px; }
                .grid-6 .memo-inner { font-size: 10px; }

                .header { margin-bottom: 5px; border-bottom: 1px solid #000; padding-bottom: 5px; }
                .header img { max-height: 40px; margin-bottom: 5px; }
                .header h1 { margin: 0; font-size: 1.5em; }
                .header p { margin: 0; font-size: 0.8em; }

                .title { text-align: center; font-weight: bold; margin: 5px 0; border: 1px solid #000; padding: 2px; }
                .meta { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.9em;}
                .memo-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; flex-grow: 1; font-size: 0.9em;}
                .memo-table th, .memo-table td { border: 1px solid #000; padding: 2px 4px; text-align: left; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }

                .totals { border-top: 2px solid #000; padding-top: 5px; font-size: 0.9em;}
                .totals-row { display: flex; justify-content: space-between; margin-bottom: 2px;}
                .final-total { font-weight: bold; font-size: 1.1em; border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 2px 0;}

                .footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: auto; padding-top: 10px; }
                .paid-stamp { border: 2px solid #000; padding: 5px 10px; font-weight: bold; font-size: 1.2em; transform: rotate(-5deg); display: inline-block;}
                .signature-box { text-align: center; font-size: 0.8em; }

                .align-left { text-align: left; }
                .align-center { text-align: center; }
                .align-right { text-align: right; }
                .pos-left { margin-right: auto; margin-left: 0; }
                .pos-center { margin-left: auto; margin-right: auto; }
                .pos-right { margin-left: auto; margin-right: 0; }
            </style>
            <div class="render-wrapper" id="renderWrapper">${htmlContent}</div>
        `;
        return document.getElementById('renderWrapper');
    }

    function toggleLoading(show) {
        loadingIndicator.style.display = show ? 'block' : 'none';
    }

    async function generateCanvasForPages() {
        const renderList = getRenderList();
        const htmlStr = generateHtmlForPrint(renderList);
        const wrapper = setupRenderContainer(htmlStr);

        const pages = wrapper.querySelectorAll('.page');
        const canvases = [];

        for (let i = 0; i < pages.length; i++) {
            const canvas = await html2canvas(pages[i], {
                scale: 2, // High quality
                useCORS: true,
                logging: false
            });
            canvases.push(canvas);
        }

        document.getElementById('renderContainer').innerHTML = ''; // Clean up
        return canvases;
    }

    btnExportJPG.addEventListener('click', async () => {
        toggleLoading(true);
        try {
            const canvases = await generateCanvasForPages();

            canvases.forEach((canvas, index) => {
                const link = document.createElement('a');
                link.download = `Swagat-Xerox-Page-${index+1}.jpg`;
                link.href = canvas.toDataURL('image/jpeg', 0.9);
                link.click();
            });
        } catch(e) {
            console.error(e);
            alert("Error generating JPG.");
        }
        toggleLoading(false);
    });

    btnExportPDF.addEventListener('click', async () => {
        toggleLoading(true);
        try {
            const canvases = await generateCanvasForPages();
            const { jsPDF } = window.jspdf;

            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4'
            });

            canvases.forEach((canvas, index) => {
                if (index > 0) pdf.addPage();

                const imgData = canvas.toDataURL('image/jpeg', 0.9);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

                pdf.addImage(imgData, 'JPEG', 0, 0, pdfWidth, pdfHeight);
            });

            let filename = 'Swagat-Xerox.pdf';
            if(window.exportData.ids.length === 1) {
                filename = `Swagat-Xerox-Memo-${window.exportData.ids[0]}.pdf`;
            } else {
                filename = `Swagat-Xerox-${window.exportData.ids.length}-Memos.pdf`;
            }

            pdf.save(filename);
        } catch(e) {
            console.error(e);
            alert("Error generating PDF.");
        }
        toggleLoading(false);
    });

    btnPrintNative.addEventListener('click', () => {
        // We will navigate to print.php with query params
        const url = new URL('print.php', window.location.href);
        window.exportData.ids.forEach(id => url.searchParams.append('id[]', id));
        url.searchParams.set('layout', currentLayout);
        url.searchParams.set('copies', inputCopies.value);
        window.location.href = url.toString();
    });

    btnShare.addEventListener('click', async () => {
        toggleLoading(true);
        try {
            // Check native share support for files
            if (!navigator.canShare) {
                alert("Your browser does not support direct file sharing. Please download the PDF/JPG and share it from your phone.");
                toggleLoading(false);
                return;
            }

            const canvases = await generateCanvasForPages();
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

            canvases.forEach((canvas, index) => {
                if (index > 0) pdf.addPage();
                const imgData = canvas.toDataURL('image/jpeg', 0.8);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
                pdf.addImage(imgData, 'JPEG', 0, 0, pdfWidth, pdfHeight);
            });

            // Convert to Blob
            const blob = pdf.output('blob');
            let filename = 'Swagat-Xerox.pdf';
            if(window.exportData.ids.length === 1) {
                filename = `Swagat-Xerox-Memo-${window.exportData.ids[0]}.pdf`;
            }

            const file = new File([blob], filename, { type: 'application/pdf' });

            if (navigator.canShare({ files: [file] })) {
                await navigator.share({
                    files: [file],
                    title: 'Swagat Xerox Memo',
                    text: 'Here is your memo from Swagat Xerox Center.'
                });
            } else {
                alert("Your browser does not support file sharing directly.");
            }
        } catch (e) {
            console.error(e);
            alert("Sharing failed or was cancelled.");
        }
        toggleLoading(false);
    });

    fetchData();
});
