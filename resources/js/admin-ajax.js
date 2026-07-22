// Admin AJAX Helper Functions
// Make functions available globally

window.showNotification = function (message, type = "success") {
    const notification = document.createElement("div");
    notification.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-lg z-50 ${type === "success" ? "bg-green-500" : "bg-red-500"} text-white`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
};

window.showErrors = function (modalId, errors) {
    const errorDiv = document.getElementById(modalId + "_errors");
    const errorList = errorDiv.querySelector("ul");
    errorList.innerHTML = "";

    if (errors && typeof errors === "object") {
        Object.keys(errors).forEach((key) => {
            const errorMessages = Array.isArray(errors[key])
                ? errors[key]
                : [errors[key]];
            errorMessages.forEach((message) => {
                const li = document.createElement("li");
                li.textContent = message;
                errorList.appendChild(li);
            });
        });
    }

    errorDiv.classList.remove("hidden");
};

window.hideErrors = function (modalId) {
    const errorDiv = document.getElementById(modalId + "_errors");
    if (errorDiv) {
        errorDiv.classList.add("hidden");
        errorDiv.querySelector("ul").innerHTML = "";
    }
};

window.setupModalForm = function (modalId, resourceName, singularName) {
    const form = document.getElementById(modalId + "_form");
    if (!form) {
        console.error('Form not found:', modalId + "_form");
        return;
    }

    // Prevent duplicate event listeners
    if (form.hasAttribute('data-form-setup')) {
        return;
    }
    form.setAttribute('data-form-setup', 'true');

    // Helper function to find submit button
    function findSubmitButton() {
        const formId = modalId + "_form";
        
        // Method 1: Look for button with form attribute matching our form
        let submitBtn = document.querySelector(`button[type="submit"][form="${formId}"]`);
        
        // Method 2: Look inside the modal
        if (!submitBtn) {
            const modal = document.getElementById(modalId);
            if (modal) {
                submitBtn = modal.querySelector('button[type="submit"]');
            }
        }
        
        // Method 3: Look inside the form itself
        if (!submitBtn) {
            submitBtn = form.querySelector('button[type="submit"]');
        }
        
        return submitBtn;
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(form);

        // Form create penjualan: ambil transaction_ids dari DOM (cari di modal agar pasti ketemu)
        const modal = document.getElementById(modalId);
        const selectedTransactions = modal ? modal.querySelector("#selected-transactions") : null;
        if (selectedTransactions) {
            formData.delete("transaction_ids[]");
            selectedTransactions.querySelectorAll(".selected-transaction-item").forEach(function (el) {
                const id = el.getAttribute("data-transaction-id");
                if (id) {
                    formData.append("transaction_ids[]", id);
                }
            });
        }
        // Fallback: input hidden di dalam form
        if (!selectedTransactions || formData.getAll("transaction_ids[]").length === 0) {
            formData.delete("transaction_ids[]");
            const transactionIdsInputs = document.querySelectorAll(
                "#" + modalId + "_form input[name=\"transaction_ids[]\"]"
            );
            transactionIdsInputs.forEach(function (input) {
                if (input.value) {
                    formData.append("transaction_ids[]", input.value);
                }
            });
        }

        const url = form.action;
        const method = formData.get("_method") || "POST";
        const isEdit = method === "PUT";
        
        // Debug: Log transaction_ids being sent
        const transactionIds = [];
        formData.getAll('transaction_ids[]').forEach(id => transactionIds.push(id));
        console.log('Sending transaction_ids:', transactionIds);

        // Find submit button - try to find it fresh each time
        const submitBtn = findSubmitButton();
        const submitText = document.getElementById(modalId + "_submit_text");
        const loading = document.getElementById(modalId + "_loading");

        // Debug logging
        console.log('Form submit:', { 
            url, 
            method, 
            modalId, 
            formId: modalId + "_form",
            hasSubmitBtn: !!submitBtn,
            formId: form.id,
            modalExists: !!document.getElementById(modalId)
        });

        if (!submitBtn) {
            console.error('Submit button not found for modal:', modalId, {
                formId: modalId + "_form",
                modalExists: !!document.getElementById(modalId),
                buttonsInModal: document.getElementById(modalId) ? document.getElementById(modalId).querySelectorAll('button').length : 0,
                allButtons: document.querySelectorAll('button[type="submit"]').length
            });
            showNotification('Tombol simpan tidak ditemukan. Silakan refresh halaman.', 'error');
            return;
        }

        // Safely disable submit button
        if (submitBtn && typeof submitBtn.disabled !== 'undefined') {
            submitBtn.disabled = true;
        }
        if (submitText) submitText.classList.add("hidden");
        if (loading) loading.classList.remove("hidden");
        hideErrors(modalId);

        // Ensure URL is set correctly
        if (!url || url === '#') {
            console.error('Form action URL is missing or invalid:', url);
            showNotification('URL form tidak valid. Silakan refresh halaman.', 'error');
            if (submitBtn) submitBtn.disabled = false;
            if (submitText) submitText.classList.remove("hidden");
            if (loading) loading.classList.add("hidden");
            return;
        }

        fetch(url, {
            method: method === "PUT" ? "POST" : "POST",
            body: formData,
            headers: {
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                ).content,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        })
            .then((response) => {
                if (!response.ok) {
                    return response.text().then((text) => {
                        try {
                            const err = JSON.parse(text);
                            throw err;
                        } catch (e) {
                            throw { message: text || 'Terjadi kesalahan saat menyimpan data.' };
                        }
                    });
                }
                return response.json();
            })
            .then((data) => {
                if (data.success) {
                    closeModal(modalId);
                    showNotification(
                        data.message ||
                            `${singularName} berhasil ${isEdit ? "diperbarui" : "ditambahkan"}.`,
                        "success",
                    );
                    setTimeout(() => location.reload(), 500);
                } else {
                    if (data.errors) {
                        showErrors(modalId, data.errors);
                    } else {
                        showNotification(
                            data.message || "Terjadi kesalahan.",
                            "error",
                        );
                    }
                }
            })
            .catch((error) => {
                console.error("Error:", error);
                if (error.errors) {
                    showErrors(modalId, error.errors);
                } else {
                    showNotification(
                        error.message ||
                            "Terjadi kesalahan saat menyimpan data.",
                        "error",
                    );
                }
            })
            .finally(() => {
                // Re-find submit button in case DOM changed
                const finalSubmitBtn = findSubmitButton();
                if (finalSubmitBtn && typeof finalSubmitBtn.disabled !== 'undefined') {
                    finalSubmitBtn.disabled = false;
                }
                if (submitText) submitText.classList.remove("hidden");
                if (loading) loading.classList.add("hidden");
            });
    });
};

window.openResourceModal = function (
    modalId,
    resourceName,
    singularName,
    id = null,
) {
    const formContent = document.getElementById(modalId + "_form_content");
    const title = document.querySelector(`#${modalId} h3`);
    const form = document.getElementById(modalId + "_form");
    const submitText = document.getElementById(modalId + "_submit_text");
    const errorDiv = document.getElementById(modalId + "_errors");

    // Clear previous form content and errors
    if (formContent) formContent.innerHTML = "";
    if (errorDiv) {
        errorDiv.classList.add("hidden");
        const errorList = errorDiv.querySelector("ul");
        if (errorList) errorList.innerHTML = "";
    }
    // Remove form setup flag to allow re-initialization
    if (form) {
        form.removeAttribute('data-form-setup');
    }

    if (id) {
        title.textContent = `Edit ${singularName}`;
        form.action = `/admin/${resourceName}/${id}`;

        // Remove existing _method input if any
        const existingMethod = form.querySelector('input[name="_method"]');
        if (!existingMethod) {
            const methodInput = document.createElement("input");
            methodInput.type = "hidden";
            methodInput.name = "_method";
            methodInput.value = "PUT";
            form.appendChild(methodInput);
        }
        if (submitText) submitText.textContent = "Update";

        fetch(`/admin/${resourceName}/${id}/edit`, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        })
            .then((response) => {
                if (!response.ok) {
                    return response.text().then((text) => {
                        try {
                            const err = JSON.parse(text);
                            throw new Error(
                                err.message || "Failed to load form",
                            );
                        } catch (e) {
                            throw new Error(
                                "Failed to load form: " + response.status,
                            );
                        }
                    });
                }
                return response.json();
            })
            .then((data) => {
                if (data.html) {
                    formContent.innerHTML = data.html;
                    // Ensure form action is set before setting up form
                    if (form && (!form.action || form.action === '#')) {
                        form.action = `/admin/${resourceName}/${id}`;
                    }
                    // Open modal first, then setup form after a short delay to ensure DOM is ready
                    openModal(modalId);
                    setTimeout(() => {
                        setupModalForm(modalId, resourceName, singularName);
                    }, 50);
                    // Setup PPN calculator setelah form dimuat
                    setTimeout(function () {
                        if (typeof window.setupPPNCalculator === "function") {
                            window.setupPPNCalculator();
                        }
                        // Fallback langsung setup PPN calculator
                        const subtotalInput =
                            document.getElementById("subtotal");
                        if (
                            subtotalInput &&
                            !subtotalInput.hasAttribute("data-ppn-setup")
                        ) {
                            const ppnInput =
                                document.getElementById("ppn_amount");
                            const totalDisplay =
                                document.getElementById("total_display");
                            if (ppnInput && totalDisplay) {
                                const PPN_RATE = 0.11;
                                function calc() {
                                    const st =
                                        parseFloat(subtotalInput.value) || 0;
                                    const ppn = Math.round(st * PPN_RATE);
                                    ppnInput.value = ppn;
                                    totalDisplay.value =
                                        "Rp " +
                                        new Intl.NumberFormat("id-ID").format(
                                            st + ppn,
                                        );
                                }
                                subtotalInput.addEventListener("input", calc);
                                subtotalInput.addEventListener("change", calc);
                                subtotalInput.addEventListener("keyup", calc);
                                subtotalInput.setAttribute(
                                    "data-ppn-setup",
                                    "true",
                                );
                                calc();
                            }
                        }
                        // Initialize sale items form if exists
                        if (typeof window.initSaleItemsForm === "function") {
                            window.initSaleItemsForm();
                        }
                        // Initialize subtotal calculator if exists
                        if (typeof window.initSubtotalCalculator === "function") {
                            window.initSubtotalCalculator();
                        }
                        // Initialize sale transactions form if exists
                        if (typeof window.initSaleTransactionsForm === "function") {
                            window.initSaleTransactionsForm();
                        }
                        if (
                            resourceName.indexOf("sale-transactions") !== -1 &&
                            typeof window.initSaleTransactionAllocationForm === "function"
                        ) {
                            window.initSaleTransactionAllocationForm();
                        }
                        if (resourceName === "projects" && typeof window.initProjectForm === "function") {
                            window.initProjectForm();
                        }
                    }, 100);
                } else {
                    throw new Error("No HTML content received");
                }
            })
            .catch((error) => {
                console.error("Error loading form:", error);
                showNotification(
                    "Gagal memuat form edit: " + error.message,
                    "error",
                );
            });
    } else {
        title.textContent = `Tambah ${singularName}`;
        form.action = `/admin/${resourceName}`;
        const methodInput = form.querySelector('input[name="_method"]');
        if (methodInput) methodInput.remove();
        if (submitText) submitText.textContent = "Simpan";

        fetch(`/admin/${resourceName}/create`, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        })
            .then((response) => {
                if (!response.ok) {
                    return response.text().then((text) => {
                        try {
                            const err = JSON.parse(text);
                            throw new Error(
                                err.message || "Failed to load form",
                            );
                        } catch (e) {
                            throw new Error(
                                "Failed to load form: " + response.status,
                            );
                        }
                    });
                }
                return response.json();
            })
            .then((data) => {
                if (data.html) {
                    formContent.innerHTML = data.html;
                    // Ensure form action is set before setting up form
                    if (form && (!form.action || form.action === '#')) {
                        form.action = `/admin/${resourceName}`;
                    }
                    // Open modal first, then setup form after a short delay to ensure DOM is ready
                    openModal(modalId);
                    setTimeout(() => {
                        setupModalForm(modalId, resourceName, singularName);
                    }, 50);
                    // Setup PPN calculator setelah form dimuat
                    setTimeout(function () {
                        if (typeof window.setupPPNCalculator === "function") {
                            window.setupPPNCalculator();
                        }
                        // Fallback langsung setup PPN calculator
                        const subtotalInput =
                            document.getElementById("subtotal");
                        if (
                            subtotalInput &&
                            !subtotalInput.hasAttribute("data-ppn-setup")
                        ) {
                            const ppnInput =
                                document.getElementById("ppn_amount");
                            const totalDisplay =
                                document.getElementById("total_display");
                            if (ppnInput && totalDisplay) {
                                const PPN_RATE = 0.11;
                                function calc() {
                                    const st =
                                        parseFloat(subtotalInput.value) || 0;
                                    const ppn = Math.round(st * PPN_RATE);
                                    ppnInput.value = ppn;
                                    totalDisplay.value =
                                        "Rp " +
                                        new Intl.NumberFormat("id-ID").format(
                                            st + ppn,
                                        );
                                }
                                subtotalInput.addEventListener("input", calc);
                                subtotalInput.addEventListener("change", calc);
                                subtotalInput.addEventListener("keyup", calc);
                                subtotalInput.setAttribute(
                                    "data-ppn-setup",
                                    "true",
                                );
                                calc();
                            }
                        }
                        // Initialize sale items form if exists
                        if (typeof window.initSaleItemsForm === "function") {
                            window.initSaleItemsForm();
                        }
                        // Initialize subtotal calculator if exists
                        if (typeof window.initSubtotalCalculator === "function") {
                            window.initSubtotalCalculator();
                        }
                        // Initialize sale transactions form if exists
                        if (typeof window.initSaleTransactionsForm === "function") {
                            window.initSaleTransactionsForm();
                        }
                        // Form create penjualan: script di partial tidak jalan (innerHTML), jadi load daftar pending di sini
                        if (resourceName === "sales") {
                            loadPendingListInModal(modalId);
                        }
                        if (
                            resourceName.indexOf("sale-transactions") !== -1 &&
                            typeof window.initSaleTransactionAllocationForm === "function"
                        ) {
                            window.initSaleTransactionAllocationForm();
                        }
                        if (resourceName === "projects" && typeof window.initProjectForm === "function") {
                            window.initProjectForm();
                        }
                    }, 100);
                } else {
                    throw new Error("No HTML content received");
                }
            })
            .catch((error) => {
                console.error("Error loading form:", error);
                showNotification(
                    "Gagal memuat form: " + error.message,
                    "error",
                );
            });
    }
};

function loadPendingListInModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    const listEl = modal.querySelector("#pending-transactions-list");
    if (!listEl) return;

    fetch("/admin/sales/pending-transactions/list", {
        headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
    })
        .then((r) => r.json())
        .then((data) => {
            if (!data.items || data.items.length === 0) {
                listEl.innerHTML =
                    '<p class="text-sm text-gray-500 text-center py-4">Belum ada transaksi. Tutup modal ini, lalu gunakan tombol &quot;Tambah Transaksi&quot; di halaman untuk menambah ke daftar.</p>';
            } else {
                let html = "";
                data.items.forEach((it) => {
                    html += '<div class="flex items-center justify-between border border-gray-200 rounded-lg p-3 bg-white">';
                    html += '<div class="flex-1"><span class="font-medium text-sm">' + (it.description || "") + "</span>";
                    html += ' <span class="text-xs text-gray-500">Qty: ' + it.quantity + " × Rp " + new Intl.NumberFormat("id-ID").format(it.unit_price) + " = Rp " + new Intl.NumberFormat("id-ID").format(it.subtotal) + "</span></div>";
                    html += "</div>";
                });
                listEl.innerHTML = html;
            }
            const subtotalInput = modal.querySelector("#subtotal");
            const ppnInput = modal.querySelector("#ppn_amount");
            const totalDisplay = modal.querySelector("#total_display");
            const totalVal = data.total != null ? data.total : 0;
            const subtotalVal = data.subtotal != null ? data.subtotal : 0;
            const ppnVal = data.ppn_amount != null ? data.ppn_amount : 0;
            if (subtotalInput) subtotalInput.value = Math.round(subtotalVal);
            if (ppnInput) ppnInput.value = ppnVal;
            if (totalDisplay) totalDisplay.value = "Rp " + new Intl.NumberFormat("id-ID").format(totalVal);
            const form = modal.querySelector("form");
            const submitBtn = form ? document.querySelector('button[type="submit"][form="' + form.id + '"]') : null;
            if (submitBtn) submitBtn.disabled = !data.items || data.items.length === 0;
        })
        .catch(() => {
            listEl.innerHTML =
                '<p class="text-sm text-gray-500 text-center py-4">Gagal memuat daftar. Coba tutup dan buka lagi.</p>';
        });
}

window.refreshPendingInSaleModal = function () {
    loadPendingListInModal("saleModal");
};

window.deleteResource = function (resourceName, id, singularName) {
    if (!confirm(`Apakah Anda yakin ingin menghapus ${singularName} ini?`))
        return;

    fetch(`/admin/${resourceName}/${id}`, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
        },
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                // Try different row ID patterns
                const rowId1 = `${resourceName.slice(0, -1)}Row_${id}`;
                const rowId2 = `${resourceName}Row_${id}`;
                const rowId3 = `caseStudyRow_${id}`;
                const rowId4 = `slideRow_${id}`;
                const rowId5 = `contributorRow_${id}`;
                const rowId6 = `supplierRow_${id}`;
                const rowId7 = `customerRow_${id}`;
                const rowId8 = `heroTextRow_${id}`;
                const rowId9 = `servicesRow_${id}`;
                const rowId10 = `employeesRow_${id}`;
                const rowId11 = `salesRow_${id}`;
                const rowId12 = `purchasesRow_${id}`;
                const rowId13 = `journal-entriesRow_${id}`;
                const rowId14 = `cash-transactionsRow_${id}`;
                const rowId15 = `sale-transactionsRow_${id}`;
                const rowId16 = `projectsRow_${id}`;

                let row =
                    document.getElementById(rowId1) ||
                    document.getElementById(rowId2) ||
                    document.getElementById(rowId3) ||
                    document.getElementById(rowId4) ||
                    document.getElementById(rowId5) ||
                    document.getElementById(rowId6) ||
                    document.getElementById(rowId7) ||
                    document.getElementById(rowId8) ||
                    document.getElementById(rowId9) ||
                    document.getElementById(rowId10) ||
                    document.getElementById(rowId11) ||
                    document.getElementById(rowId12) ||
                    document.getElementById(rowId13) ||
                    document.getElementById(rowId14) ||
                    document.getElementById(rowId15) ||
                    document.getElementById(rowId16);

                if (row) row.remove();
                showNotification(
                    data.message || `${singularName} berhasil dihapus.`,
                    "success",
                );

                // Check if table/grid is empty
                const tbody = document.querySelector("tbody");
                const grid = document.querySelector(".grid");
                const container = tbody || grid;

                if (container && container.children.length === 0) {
                    if (tbody) {
                        tbody.innerHTML = `<tr><td colspan="100%" class="px-6 py-4 text-center text-gray-500">Belum ada data. <button onclick="openResourceModal('${resourceName}Modal', '${resourceName}', '${singularName}')" class="text-purple-600 hover:text-purple-800">Tambah yang pertama</button></td></tr>`;
                    } else if (grid) {
                        grid.innerHTML = `<div class="col-span-full bg-white rounded-lg shadow border border-gray-200 p-12 text-center"><p class="text-gray-500 mb-4">Belum ada data.</p><button onclick="openResourceModal('${resourceName}Modal', '${resourceName}', '${singularName}')" class="text-purple-600 hover:text-purple-800 font-semibold">Tambah yang pertama</button></div>`;
                    }
                }
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            showNotification("Gagal menghapus data.", "error");
        });
};

window.openModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove("hidden");
        modal.style.display = "flex";
        document.body.style.overflow = "hidden";
    }
};

window.closeModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add("hidden");
        modal.style.display = "none";
        document.body.style.overflow = "auto";
        // Reset form
        const form = document.getElementById(modalId + "_form");
        if (form) {
            form.reset();
            // Clear errors
            hideErrors(modalId);
        }
    }
};

// Close modal when clicking outside
document.addEventListener("click", function (e) {
    if (
        e.target.classList.contains("fixed") &&
        e.target.id &&
        e.target.id.includes("Modal")
    ) {
        closeModal(e.target.id);
    }
});

/**
 * Project form helpers. Kept here because scripts inside AJAX-injected form
 * HTML (innerHTML) are not executed by the browser.
 */
window.initProjectForm = function () {
    const subtotalInput = document.getElementById("subtotal");
    const taxSelect = document.getElementById("tax_id");
    const taxAmountDisplay = document.getElementById("tax_amount_display");
    const totalDisplay = document.getElementById("total_display");
    const paymentMethod = document.getElementById("payment_method");
    const installmentSection = document.getElementById("installment-section");
    const termsContainer = document.getElementById("terms-container");
    const percentTotal = document.getElementById("terms-percent-total");
    const amountTotal = document.getElementById("terms-amount-total");
    const termsBaseEl = document.getElementById("terms-base-total");
    const termsBaseLabel = document.getElementById("terms-base-total-label");
    const btnAddTerm = document.getElementById("btn-add-term");

    // Terms-only page has no subtotal; full project form has both.
    if (!termsContainer && !subtotalInput) return;

    let syncingTermFields = false;

    function formatRupiah(n) {
        return (
            "Rp " +
            new Intl.NumberFormat("id-ID").format(Math.max(0, Math.round(n)))
        );
    }

    function parseNum(raw) {
        return parseFloat(String(raw || "").replace(",", ".")) || 0;
    }

    function getTermsBaseTotal() {
        if (termsBaseEl) {
            return parseNum(termsBaseEl.value);
        }
        return 0;
    }

    function setTermsBaseTotal(total) {
        if (termsBaseEl) termsBaseEl.value = String(Math.round(total));
        if (termsBaseLabel) termsBaseLabel.textContent = formatRupiah(total);
    }

    function amountFromPercent(pct) {
        const base = getTermsBaseTotal();
        if (base <= 0) return 0;
        return Math.round((base * pct) / 100);
    }

    function percentFromAmount(amount) {
        const base = getTermsBaseTotal();
        if (base <= 0) return 0;
        return Math.round((amount / base) * 10000) / 100;
    }

    function syncAmountFromPercent(percentInput, amountInput) {
        if (!percentInput || !amountInput || syncingTermFields) return;
        syncingTermFields = true;
        amountInput.value = String(amountFromPercent(parseNum(percentInput.value)));
        syncingTermFields = false;
    }

    function syncPercentFromAmount(percentInput, amountInput) {
        if (!percentInput || !amountInput || syncingTermFields) return;
        syncingTermFields = true;
        percentInput.value = String(percentFromAmount(parseNum(amountInput.value)));
        syncingTermFields = false;
    }

    function syncAllAmountsFromPercents() {
        const dpPct = document.getElementById("dp_percentage");
        const dpAmt = document.getElementById("dp_amount");
        syncAmountFromPercent(dpPct, dpAmt);
        if (!termsContainer) return;
        termsContainer.querySelectorAll(".term-row").forEach(function (row) {
            syncAmountFromPercent(
                row.querySelector(".term-percentage"),
                row.querySelector(".term-amount"),
            );
        });
        updatePercentTotal();
    }

    function calculateTotals() {
        if (!subtotalInput) return;
        const subtotal = parseFloat(subtotalInput.value) || 0;
        const stock = termsBaseEl
            ? parseNum(termsBaseEl.dataset.stock)
            : 0;
        const selected = taxSelect
            ? taxSelect.options[taxSelect.selectedIndex]
            : null;
        const rate =
            selected && selected.value
                ? parseFloat(selected.dataset.rate || 0)
                : 0;
        const calculation = selected
            ? selected.dataset.calculation || "addition"
            : "addition";
        // Display jasa-only tax/total in project form fields (existing UX).
        const jasaTax = Math.round((subtotal * rate) / 100);
        const jasaTotal =
            calculation === "deduction"
                ? subtotal - jasaTax
                : subtotal + jasaTax;
        if (taxAmountDisplay) taxAmountDisplay.value = formatRupiah(jasaTax);
        if (totalDisplay) totalDisplay.value = formatRupiah(jasaTotal);

        // Terms use full DPP (jasa + stok) + pajak, matching project.total.
        const dpp = subtotal + stock;
        const fullTax = Math.round((dpp * rate) / 100);
        const fullTotal =
            calculation === "deduction" ? dpp - fullTax : dpp + fullTax;
        setTermsBaseTotal(fullTotal);
        syncAllAmountsFromPercents();
    }

    function updatePercentTotal() {
        if (!termsContainer) return;
        let sumPct = 0;
        let sumAmt = 0;
        const dpInput = document.getElementById("dp_percentage");
        const dpAmt = document.getElementById("dp_amount");
        if (dpInput && !dpInput.disabled) {
            sumPct += parseNum(dpInput.value);
        }
        if (dpAmt && !dpAmt.disabled) {
            sumAmt += parseNum(dpAmt.value);
        }
        termsContainer.querySelectorAll(".term-row").forEach(function (row) {
            const pctEl = row.querySelector(".term-percentage");
            const amtEl = row.querySelector(".term-amount");
            if (pctEl && !pctEl.disabled) sumPct += parseNum(pctEl.value);
            if (amtEl && !amtEl.disabled) sumAmt += parseNum(amtEl.value);
        });
        const paidEl = document.getElementById("paid-terms-percent");
        const paidAmtEl = document.getElementById("paid-terms-amount");
        const paidPct = paidEl
            ? parseNum(paidEl.value)
            : parseNum(termsContainer.dataset.paidPercent);
        const paidAmt = paidAmtEl
            ? parseNum(paidAmtEl.value)
            : parseNum(termsContainer.dataset.paidAmount);
        sumPct = Math.round((sumPct + paidPct) * 100) / 100;
        sumAmt = Math.round(sumAmt + paidAmt);
        if (percentTotal) {
            percentTotal.textContent = sumPct.toFixed(2);
            percentTotal.className =
                Math.abs(sumPct - 100) < 0.005
                    ? "font-semibold text-green-600"
                    : "font-semibold text-red-600";
        }
        if (amountTotal) {
            const base = getTermsBaseTotal();
            amountTotal.textContent = formatRupiah(sumAmt);
            amountTotal.className =
                base > 0 && Math.abs(sumAmt - base) <= 1
                    ? "font-semibold text-green-600"
                    : "font-semibold text-red-600";
        }
    }

    function reindexTerms() {
        if (!termsContainer) return;
        termsContainer.querySelectorAll(".term-row").forEach(function (row, index) {
            row.querySelectorAll("input").forEach(function (input) {
                const name = input.getAttribute("name");
                if (!name) return;
                input.setAttribute(
                    "name",
                    name.replace(/terms\[\d+\]/, "terms[" + index + "]"),
                );
            });
        });
    }

    function createTermRow(index) {
        const row = document.createElement("div");
        row.className =
            "term-row grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 items-end border border-gray-200 rounded-lg p-3 bg-gray-50";
        row.innerHTML =
            '<div><label class="block text-xs text-gray-600 mb-1">Label Termin</label>' +
            '<input type="text" name="terms[' +
            index +
            '][label]" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Termin ' +
            (index + 1) +
            '"></div>' +
            '<div><label class="block text-xs text-gray-600 mb-1">Persentase (%)</label>' +
            '<input type="number" name="terms[' +
            index +
            '][percentage]" min="0" max="100" step="0.01" value="0" class="term-percentage w-full px-3 py-2 border border-gray-300 rounded-md text-sm"></div>' +
            '<div><label class="block text-xs text-gray-600 mb-1">Nominal (Rp)</label>' +
            '<input type="number" min="0" step="1" value="0" class="term-amount w-full px-3 py-2 border border-gray-300 rounded-md text-sm" inputmode="numeric"></div>' +
            '<div><label class="block text-xs text-gray-600 mb-1">Jatuh Tempo</label>' +
            '<input type="date" name="terms[' +
            index +
            '][due_date]" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"></div>' +
            '<div><button type="button" class="btn-remove-term w-full px-3 py-2 bg-red-50 text-red-600 rounded-md text-sm hover:bg-red-100">Hapus</button></div>';
        return row;
    }

    function syncInstallmentVisibility() {
        if (!paymentMethod || !installmentSection) return;
        const isInstallment = paymentMethod.value === "installment";
        if (isInstallment) {
            installmentSection.classList.remove("hidden");
        } else {
            installmentSection.classList.add("hidden");
        }
        installmentSection.querySelectorAll("input, select, textarea, button").forEach(function (el) {
            if (el.id === "paid-terms-percent" || el.id === "paid-terms-amount" || el.id === "terms-base-total") return;
            if (el.tagName === "BUTTON" || el.type !== "hidden") {
                el.disabled = !isInstallment;
            }
        });
        updatePercentTotal();
    }

    if (btnAddTerm && termsContainer && btnAddTerm.dataset.projectBound !== "1") {
        btnAddTerm.addEventListener("click", function (e) {
            e.preventDefault();
            const emptyHint = document.getElementById("terms-empty-hint");
            if (emptyHint) emptyHint.remove();
            const index = termsContainer.querySelectorAll(".term-row").length;
            termsContainer.appendChild(createTermRow(index));
            updatePercentTotal();
        });
        btnAddTerm.dataset.projectBound = "1";
    }

    if (paymentMethod && paymentMethod.dataset.projectBound !== "1") {
        paymentMethod.addEventListener("change", syncInstallmentVisibility);
        paymentMethod.dataset.projectBound = "1";
    }

    if (subtotalInput && subtotalInput.dataset.projectCalcBound !== "1") {
        subtotalInput.addEventListener("input", calculateTotals);
        subtotalInput.addEventListener("change", calculateTotals);
        subtotalInput.dataset.projectCalcBound = "1";
    }
    if (taxSelect && taxSelect.dataset.projectCalcBound !== "1") {
        taxSelect.addEventListener("change", calculateTotals);
        taxSelect.dataset.projectCalcBound = "1";
    }

    const dpPercentage = document.getElementById("dp_percentage");
    const dpAmount = document.getElementById("dp_amount");
    if (dpPercentage && dpPercentage.dataset.projectBound !== "1") {
        dpPercentage.addEventListener("input", function () {
            syncAmountFromPercent(dpPercentage, dpAmount);
            updatePercentTotal();
        });
        dpPercentage.addEventListener("change", function () {
            syncAmountFromPercent(dpPercentage, dpAmount);
            updatePercentTotal();
        });
        dpPercentage.dataset.projectBound = "1";
    }
    if (dpAmount && dpAmount.dataset.projectBound !== "1") {
        dpAmount.addEventListener("input", function () {
            syncPercentFromAmount(dpPercentage, dpAmount);
            updatePercentTotal();
        });
        dpAmount.addEventListener("change", function () {
            syncPercentFromAmount(dpPercentage, dpAmount);
            updatePercentTotal();
        });
        dpAmount.dataset.projectBound = "1";
    }

    if (termsContainer && termsContainer.dataset.projectTermsBound !== "1") {
        termsContainer.addEventListener("input", function (e) {
            const t = e.target;
            if (!t) return;
            const row = t.closest(".term-row");
            if (!row) return;
            if (t.classList.contains("term-percentage")) {
                syncAmountFromPercent(
                    t,
                    row.querySelector(".term-amount"),
                );
                updatePercentTotal();
            } else if (t.classList.contains("term-amount")) {
                syncPercentFromAmount(
                    row.querySelector(".term-percentage"),
                    t,
                );
                updatePercentTotal();
            }
        });
        termsContainer.addEventListener("change", function (e) {
            const t = e.target;
            if (!t) return;
            const row = t.closest(".term-row");
            if (!row) return;
            if (t.classList.contains("term-percentage")) {
                syncAmountFromPercent(
                    t,
                    row.querySelector(".term-amount"),
                );
                updatePercentTotal();
            } else if (t.classList.contains("term-amount")) {
                syncPercentFromAmount(
                    row.querySelector(".term-percentage"),
                    t,
                );
                updatePercentTotal();
            }
        });
        termsContainer.addEventListener("click", function (e) {
            const btn = e.target.closest(".btn-remove-term");
            if (!btn) return;
            e.preventDefault();
            const row = btn.closest(".term-row");
            if (!row) return;
            row.remove();
            reindexTerms();
            if (
                termsContainer.querySelectorAll(".term-row").length === 0 &&
                !document.getElementById("terms-empty-hint")
            ) {
                const hint = document.createElement("p");
                hint.id = "terms-empty-hint";
                hint.className = "text-xs text-gray-500";
                hint.textContent =
                    'Belum ada termin tambahan. Klik “+ Tambah Termin” bila perlu.';
                termsContainer.appendChild(hint);
            }
            updatePercentTotal();
        });
        termsContainer.dataset.projectTermsBound = "1";
    }

    if (subtotalInput) {
        calculateTotals();
    } else {
        updatePercentTotal();
    }
    syncInstallmentVisibility();
};
/**
 * Alokasi stok modal: purchase dropdown fills description, cost (harga grosir), qty max, subtotal.
 * Inline <script> in AJAX-loaded form HTML does not run.
 */
window.initSaleTransactionAllocationForm = function () {
    const purchaseSelect = document.getElementById("purchase_id");
    const descriptionInput = document.getElementById("description");
    const quantityInput = document.getElementById("quantity");
    const unitPriceInput = document.getElementById("unit_price");
    const subtotalDisplay = document.getElementById("subtotal_display");
    const purchaseInfo = document.getElementById("purchase-info");
    const quantityHint = document.getElementById("quantity-hint");
    const costDisplay = document.getElementById("cost_unit_price_display");

    if (!purchaseSelect || purchaseSelect.dataset.allocationBound === "1") {
        return;
    }
    purchaseSelect.dataset.allocationBound = "1";

    const detailsUrlTemplate = purchaseSelect.dataset.detailsUrl || "";
    const excludeId = purchaseSelect.dataset.excludeId || "";
    let maxQuantity = null;

    function formatRp(value) {
        return (
            "Rp " +
            new Intl.NumberFormat("id-ID").format(Math.round(Number(value) || 0))
        );
    }

    function calculateSubtotal() {
        const qty = parseFloat(quantityInput?.value || 0) || 0;
        const price = parseFloat(unitPriceInput?.value || 0) || 0;
        if (subtotalDisplay) {
            subtotalDisplay.value = formatRp(qty * price);
        }
    }

    function setCostDisplay(cost) {
        if (!costDisplay) return;
        if (cost === null || cost === undefined || cost === "") {
            costDisplay.value = "—";
            return;
        }
        costDisplay.value = formatRp(cost);
    }

    function applyPurchaseOption(option) {
        if (!option || !option.value) {
            maxQuantity = null;
            if (purchaseInfo) {
                purchaseInfo.classList.add("hidden");
                purchaseInfo.textContent = "";
            }
            setCostDisplay(null);
            if (quantityHint) {
                quantityHint.textContent = "Maksimal sesuai stok grosir tersisa";
            }
            return;
        }

        const description = option.dataset.description || "";
        const remaining = parseInt(option.dataset.remaining || "0", 10);
        const cost = parseFloat(option.dataset.cost || "0") || 0;

        maxQuantity = remaining;
        if (descriptionInput) descriptionInput.value = description;
        if (quantityInput) {
            quantityInput.max = remaining > 0 ? remaining : 1;
            if ((parseInt(quantityInput.value, 10) || 0) > remaining) {
                quantityInput.value = remaining > 0 ? remaining : 1;
            }
        }
        setCostDisplay(cost);
        if (quantityHint) {
            quantityHint.textContent = "Stok tersisa: " + remaining + " unit";
        }
        if (purchaseInfo) {
            purchaseInfo.textContent =
                "Supplier: " +
                (option.textContent.split("—")[0] || "").trim();
            purchaseInfo.classList.remove("hidden");
        }
        calculateSubtotal();
    }

    async function refreshPurchaseDetails(purchaseId) {
        if (!purchaseId || !detailsUrlTemplate) return;
        let url = detailsUrlTemplate.replace("__ID__", purchaseId);
        if (excludeId) {
            url +=
                "?exclude_sale_transaction_id=" +
                encodeURIComponent(excludeId);
        }
        try {
            const response = await fetch(url, {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });
            if (!response.ok) return;
            const data = await response.json();
            maxQuantity = data.remaining_quantity;
            if (descriptionInput) descriptionInput.value = data.description;
            if (quantityInput) {
                quantityInput.max =
                    data.remaining_quantity > 0 ? data.remaining_quantity : 1;
            }
            setCostDisplay(data.cost_unit_price);
            if (quantityHint) {
                quantityHint.textContent =
                    "Stok tersisa: " + data.remaining_quantity + " unit";
            }
            if (purchaseInfo) {
                purchaseInfo.textContent =
                    data.invoice_number +
                    " • " +
                    (data.supplier || "—") +
                    " • " +
                    (data.purchase_date || "");
                purchaseInfo.classList.remove("hidden");
            }
            calculateSubtotal();
        } catch (e) {
            // keep option-based values
        }
    }

    purchaseSelect.addEventListener("change", function () {
        const option = this.options[this.selectedIndex];
        applyPurchaseOption(option);
        if (option && option.value) {
            refreshPurchaseDetails(option.value);
        }
    });

    [quantityInput, unitPriceInput].forEach(function (input) {
        if (!input) return;
        input.addEventListener("input", calculateSubtotal);
        input.addEventListener("change", function () {
            if (
                input === quantityInput &&
                maxQuantity !== null &&
                (parseInt(input.value, 10) || 0) > maxQuantity
            ) {
                input.value = maxQuantity;
            }
            calculateSubtotal();
        });
    });

    const initialOption = purchaseSelect.options[purchaseSelect.selectedIndex];
    if (initialOption && initialOption.value) {
        applyPurchaseOption(initialOption);
        refreshPurchaseDetails(initialOption.value);
    } else {
        calculateSubtotal();
    }

    window.initSubtotalCalculator = calculateSubtotal;
};
