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
    if (!form) return;

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(form);
        const url = form.action;
        const method = formData.get("_method") || "POST";
        const isEdit = method === "PUT";

        const submitBtn = form.querySelector('button[type="submit"]');
        const submitText = document.getElementById(modalId + "_submit_text");
        const loading = document.getElementById(modalId + "_loading");

        submitBtn.disabled = true;
        if (submitText) submitText.classList.add("hidden");
        if (loading) loading.classList.remove("hidden");
        hideErrors(modalId);

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
                    return response.json().then((err) => {
                        throw err;
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
                submitBtn.disabled = false;
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
                    openModal(modalId);
                    setupModalForm(modalId, resourceName, singularName);
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
                    openModal(modalId);
                    setupModalForm(modalId, resourceName, singularName);
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

                let row =
                    document.getElementById(rowId1) ||
                    document.getElementById(rowId2) ||
                    document.getElementById(rowId3) ||
                    document.getElementById(rowId4) ||
                    document.getElementById(rowId5) ||
                    document.getElementById(rowId6) ||
                    document.getElementById(rowId7);

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
