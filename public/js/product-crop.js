(function () {
    const form = document.querySelector('.cat-layout');
    const fileInput = document.getElementById('product-image');
    const drop = document.getElementById('product-drop');
    const choose = document.getElementById('product-choose');
    const preview = document.getElementById('product-preview');
    const placeholder = document.getElementById('product-placeholder');
    const ready = document.getElementById('crop-ready');
    const error = document.getElementById('crop-error');
    const modalEl = document.getElementById('cropModal');
    const cropImage = document.getElementById('crop-image');
    const zoom = document.getElementById('crop-zoom');
    const apply = document.getElementById('crop-apply');

    if (!form || !fileInput || !drop || !choose || !modalEl || !window.bootstrap || !window.Cropper) {
        return;
    }

    const modal = new bootstrap.Modal(modalEl);
    const maxBytes = 4 * 1024 * 1024;
    const allowed = ['image/jpeg', 'image/png', 'image/webp'];
    let cropper = null;
    let objectUrl = null;
    let previewUrl = null;
    let committed = null;
    let applying = false;
    let ignoreChange = false;
    let lastZoom = 0;

    function setError(message) {
        error.textContent = message || '';
    }

    function showPreview(url, message) {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        if (url.startsWith('blob:')) {
            previewUrl = url;
        }

        preview.src = url;
        preview.hidden = false;
        placeholder.hidden = true;
        ready.textContent = message;
        ready.classList.remove('d-none');
    }

    function remember(file) {
        committed = file;
        ignoreChange = true;
        const transfer = new DataTransfer();
        transfer.items.add(file);
        fileInput.files = transfer.files;
        window.setTimeout(function () {
            ignoreChange = false;
        }, 0);
    }

    function revert() {
        if (!committed) {
            ignoreChange = true;
            fileInput.value = '';
            window.setTimeout(function () {
                ignoreChange = false;
            }, 0);
            return;
        }

        remember(committed);
    }

    function openCrop(file) {
        if (!file) {
            return;
        }

        if (!allowed.includes(file.type)) {
            setError('Upload a JPG, PNG, or WebP image.');
            revert();
            return;
        }

        if (file.size > maxBytes) {
            setError('The image must be 4 MB or smaller.');
            revert();
            return;
        }

        setError('');

        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
        }

        objectUrl = URL.createObjectURL(file);
        cropImage.src = objectUrl;
        modal.show();
    }

    choose.addEventListener('click', function (event) {
        event.stopPropagation();
        fileInput.value = '';
        fileInput.click();
    });

    drop.addEventListener('click', function (event) {
        if (event.target.closest('button, a, input, label')) {
            return;
        }

        fileInput.click();
    });

    ['dragenter', 'dragover'].forEach(function (name) {
        drop.addEventListener(name, function (event) {
            event.preventDefault();
            drop.classList.add('is-drag');
        });
    });

    ['dragleave', 'drop'].forEach(function (name) {
        drop.addEventListener(name, function (event) {
            event.preventDefault();
            drop.classList.remove('is-drag');
        });
    });

    drop.addEventListener('drop', function (event) {
        const file = event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files[0];
        openCrop(file);
    });

    fileInput.addEventListener('change', function () {
        if (ignoreChange) {
            return;
        }

        const file = fileInput.files && fileInput.files[0];
        if (!file) {
            return;
        }

        openCrop(file);
    });

    window.addEventListener('focus', function () {
        window.setTimeout(function () {
            if ((!fileInput.files || fileInput.files.length === 0) && committed && !modalEl.classList.contains('show')) {
                remember(committed);
            }
        }, 250);
    });

    modalEl.addEventListener('shown.bs.modal', function () {
        if (cropper) {
            cropper.destroy();
        }

        lastZoom = 0;
        zoom.value = '0';
        cropper = new Cropper(cropImage, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            background: false,
            responsive: true,
            guides: true,
            center: true,
        });
    });

    modalEl.addEventListener('hide.bs.modal', function () {
        if (!applying) {
            revert();
        }

        applying = false;
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }

        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }

        cropImage.removeAttribute('src');
    });

    zoom.addEventListener('input', function () {
        if (!cropper) {
            return;
        }

        const value = Number(zoom.value);
        cropper.zoom((value - lastZoom) / 100);
        lastZoom = value;
    });

    document.getElementById('crop-rotate-left').addEventListener('click', function () {
        if (cropper) {
            cropper.rotate(-90);
        }
    });

    document.getElementById('crop-rotate-right').addEventListener('click', function () {
        if (cropper) {
            cropper.rotate(90);
        }
    });

    document.getElementById('crop-reset').addEventListener('click', function () {
        if (!cropper) {
            return;
        }

        cropper.reset();
        zoom.value = '0';
        lastZoom = 0;
    });

    apply.addEventListener('click', function () {
        if (!cropper) {
            return;
        }

        const canvas = cropper.getCroppedCanvas({
            width: 800,
            height: 800,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        if (!canvas) {
            setError('Crop the image, then try again.');
            return;
        }

        apply.disabled = true;
        canvas.toBlob(function (blob) {
            apply.disabled = false;

            if (!blob) {
                setError('Crop the image, then try again.');
                return;
            }

            const file = new File([blob], 'product.jpg', { type: 'image/jpeg', lastModified: Date.now() });
            applying = true;
            remember(file);
            showPreview(URL.createObjectURL(blob), 'Square crop ready to save.');
            setError('');
            modal.hide();
        }, 'image/jpeg', 0.92);
    });

    form.addEventListener('submit', function (event) {
        if ((!fileInput.files || fileInput.files.length === 0) && committed) {
            remember(committed);
        }

        const hasFile = fileInput.files && fileInput.files.length > 0;
        const hasCurrent = preview && !preview.hidden && preview.getAttribute('src');

        if (!hasFile && !hasCurrent) {
            event.preventDefault();
            setError('Choose an image and crop it before saving.');
            return;
        }

        const button = form.querySelector('[type="submit"]');
        if (button) {
            window.setTimeout(function () {
                button.disabled = true;
                button.textContent = 'Saving...';
            }, 0);
        }
    });
})();
