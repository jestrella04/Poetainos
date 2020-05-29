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
