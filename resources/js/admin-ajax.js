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
                    document.getElementById(rowId15);

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
