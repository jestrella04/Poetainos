window.loadComments = loadComments;

export function loadComments(url) {
    let loading = document.querySelector('#loading-comments');

    loading.classList.remove('d-none');

    fetch(url).then(function (response) {
        response.text().then(function (json) {
            let loadMore = document.querySelector('#load-more button');
            let embedComments = document.querySelector('#embed-comments');

            json = JSON.parse(json);

            embedComments.insertAdjacentHTML('afterbegin', json.data);
            loading.classList.add('d-none');

            if (null !== json.next && '' !== json.next) {
                loadMore.attributes['data-href'].value = json.next;
                loadMore.parentElement.classList.remove('d-none');
            } else {
                loadMore.attributes['data-href'].value = '';
                loadMore.parentElement.classList.add('d-none');
            }
        });
    });
}

export function createSideMenu() {
    let overlay = document.createElement('div');
    let sideMenu = document.createElement('div');

    overlay.id = 'side-menu-overlay';
    sideMenu.id = 'side-menu';
    overlay.classList.add('d-none');
    sideMenu.classList.add('d-none');

    document.body.insertAdjacentElement('afterbegin', sideMenu);
    document.body.insertAdjacentElement('afterbegin', overlay);
}

export function readImage(file, callback) {
    let reader = new FileReader();

    reader.onloadend = function () {
        callback(reader.result);
    }

    reader.readAsDataURL(file);
}

export function previewAvatar(dataUriString) {
    let avatar = document.querySelector('.avatar-preview');

    avatar.src = dataUriString;
}

export function isImage(file) {
    if (file.type.startsWith('image/')){
        return true;
    }

    return false;
}

export function handleForm(form, action) {
    if ('submit' === action) {
        // Display the wait cursor
        document.body.classList.add('cursor-wait');

        // Prevent double posting
        form.querySelector('#submit').disabled = true;

        // Hide all the error helpers
        form.querySelectorAll('.text-danger').forEach(function(helper) {
            helper.innerHTML = '';
            helper.classList.add('d-none');
        });
    } else if ('response' === action) {
        // Re-enable submit
        form.querySelector('#submit').disabled = false;

        // Display the standard cursor
        document.body.classList.remove('cursor-wait');
    }
}
