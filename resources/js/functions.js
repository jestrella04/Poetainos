'use strict';

import * as bootstrap from 'bootstrap';

window.loadComments = loadComments;

export function loadComments(url) {
    let loading = document.querySelector('#loading-comments');

    loading.classList.remove('d-none');

    axios.get(url)
        .then(response => {
            let loadMore = document.querySelector('#load-more button');
            let embedComments = document.querySelector('#embed-comments .comment-list');

            embedComments.insertAdjacentHTML('afterbegin', response.data.comments);

            if (null !== response.data.next && '' !== response.data.next) {
                loadMore.dataset.whHref = response.data.next;
                loadMore.parentElement.classList.remove('d-none');
            } else {
                loadMore.dataset.whHref = '';
                loadMore.parentElement.classList.add('d-none');
            }

            if (location.hash.includes('#comment-')) {
                document.querySelector(location.hash).scrollIntoView({behavior: 'smooth', block: 'end'});
            }

            tribute.attach(document.querySelectorAll('.commentbox'));
        })
        .catch(error => {
            //
        })
        .then(() => {
            loading.classList.add('d-none');
        });
}

export function readImage(file, callback) {
    let reader = new FileReader();

    reader.onloadend = () => callback(reader.result);
    reader.readAsDataURL(file);
}

export function previewAvatar(dataUriString) {
    let avatar = document.querySelector('.avatar-preview');

    avatar.src = dataUriString;
}

export function isImage(file) {
    if (file.type.startsWith('image/')) {
        return true;
    }

    return false;
}

export function handleForm(form, action) {
    if ('submit' === action) {
        // Display the wait cursor
        document.body.classList.add('cursor-wait');

        // Prevent double posting
        form.elements['submit'].disabled = true;

        // Hide all the error helpers
        form.querySelectorAll('.text-danger').forEach(helper => {
            helper.innerHTML = '';
            helper.classList.add('d-none');
        });
    } else if ('response' === action) {
        // Re-enable submit
        form.elements['submit'].disabled = false;

        // Display the standard cursor
        document.body.classList.remove('cursor-wait');
    }
}

export function handleFormErrors(errors) {
    Object.keys(errors).forEach(key => {
        let formHelper = document.querySelector(`#${key}-error`);

        formHelper.innerHTML = '';

        // Update form error helpers and display then accordingly
        errors[key].forEach(msg => {
            formHelper.innerHTML = `${formHelper.innerHTML}${msg}<br>`;
            formHelper.classList.remove('d-none');
        });
    });
}

export function resetAdminFormCreate(form) {
    form.id.value = '-1';
    if ('object' === typeof(form.name)) form.name.value = '';
    if ('object' === typeof(form.parent)) form.parent.value = '';
    if ('object' === typeof(form.description)) form.description.value = '';
    if ('object' === typeof(form.title)) form.title.value = '';
    if ('object' === typeof(form.text)) form.text.value = '';
}

export function showModal(targetModal, options = {}) {
    new bootstrap.Modal(targetModal, options).show();
}

export function showToast(options = {}) {
    let delay = options.delay || 6000;
    let theme = options.theme || null;
    let selector = options.selector || '#toast-reuse';
    let message = options.message || '';
    let toast = document.querySelector(selector);

    // Check if toast exists in the DOM
    if (null !== toast) {
        // Reset theming
        toast.classList.remove('success', 'danger');

        if (null !== theme) {
            toast.classList.add(theme);
        }

        // Replace message, if provided
        if ('' !== message) {
            toast.querySelector('.toast-body').innerHTML = message;
        }

        new bootstrap.Toast(selector, {
            'delay': delay
        }).show();
    }
}

export function isInViewport(el) {
    const rect = el.getBoundingClientRect();
    const windowHeight = (window.innerHeight || document.documentElement.clientHeight);
    const windowWidth = (window.innerWidth || document.documentElement.clientWidth);
    const vertInView = (rect.top <= windowHeight) && ((rect.top + rect.height) >= 0);
    const horInView = (rect.left <= windowWidth) && ((rect.left + rect.width) >= 0);

    return (vertInView && horInView);
}

export function isNil(value) {
    return null == value;
}

export function isEmpty(value) {
    return '' === value;
}

export function isNilOrEmpty(value) {
    if (null == value || '' === value) {
        return true;
    }

    return false;
}

export async function getUserList(wildcard, callback) {
    let response = await axios.post(`users/query/${wildcard}`);
    callback(response.data);
}

export function clearSelections(selectElement) {
    Array.from(selectElement.options).forEach(option => {
        option.selected = false;
    });
}
