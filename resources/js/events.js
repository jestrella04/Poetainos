// Wait for the DOM to be readay
document.addEventListener('DOMContentLoaded', () => {
    // Enable scrolling on the document
    document.body.classList.remove('overflow-hidden');

    // Copy elements from the top nav to side menu
    // used on small form factor screens
    let overlay = document.createElement('div');
    let sideMenu = document.createElement('div');

    overlay.id = 'side-menu-overlay';
    sideMenu.id = 'side-menu';
    overlay.classList.add('d-none');
    sideMenu.classList.add('d-none');

    document.body.insertAdjacentElement('afterbegin', sideMenu);
    document.body.insertAdjacentElement('afterbegin', overlay);

    // Listen to the on click event on the page and act accordingly
    document.addEventListener('click', function(event) {
        let element = event.target;

        // Was a button clicked?
        let button = element.closest('button, .btn');

        if (null !== button) {
            element = button;
        }

        // Scroll to the top of the document
        if (element.hasAttribute('id') && 'back-to-top' === element.attributes['id'].value) {
            document.querySelector('.header').scrollIntoView({ behavior: 'smooth', block: 'end'});
        }

        // Display the side menu on small screens
        if (element.hasAttribute('id') && 'toggler' === element.attributes['id'].value) {
            let targetNav = document.querySelector(element.attributes['data-target'].value);
            let sideNav = document.querySelector('#side-menu');

            if ('' == sideNav.innerHTML) {
                sideNav.innerHTML = targetNav.innerHTML;
            }

            document.querySelector('#side-menu-overlay').classList.toggle('d-none');
            sideNav.classList.toggle('d-none');
            document.body.classList.add('overflow-hidden');
        }

        // Hide the side menu when clicking off bounds
        if (element.hasAttribute('id') && 'side-menu-overlay' === element.attributes['id'].value) {
            document.querySelector('#side-menu').classList.toggle('d-none');
            document.querySelector('#side-menu-overlay').classList.toggle('d-none');
            document.body.classList.remove('overflow-hidden');
        }

        // Dynamically load comments for a writing
        if (element.parentElement.hasAttribute('id') && 'load-more' === element.parentElement.attributes['id'].value) {
            let url = element.attributes['data-href'].value;

            loadComments(url);
        }

        // Show/hide the comment reply form
        if (element.classList.contains('badge-reply')) {
            let target = element.attributes['data-target'].value;
            let targetForm = document.querySelector(target);
            targetForm.classList.toggle('d-none');
        }

        // Hide alerts
        if (element.classList.contains('close')) {
            let parentAlert = element.closest('.alert');

            if (null !== parentAlert) {
                parentAlert.classList.add('d-none');
            }
        }
    });

    // Listen to the on submit event on the page and act accordingly
    document.addEventListener('submit', function(event) {
        let element = event.target;

        // Post the writing create/update form
        if ('writing-form' === element.attributes['id'].value) {
            event.preventDefault();

            let params = new FormData(element);
            let url = element.attributes['action'].value;
            let errorAlert = element.querySelector('.alert-danger');
            let successAlert = element.querySelector('.alert-success');
            let successLink = successAlert.querySelector('#writing-success-link');

            element.querySelectorAll('.text-danger').forEach(function(helper) {
                helper.innerHTML = '';
                helper.classList.add('d-none');
            });

            axios.post(url, params)
            .then(function (response) {
                element.reset();
                successLink.attributes['href'].value = response.data.url;
                successAlert.classList.remove('d-none');
                errorAlert.classList.add('d-none');
            })
            .catch(function (error) {
                let errors = error.response.data.errors;

                successAlert.classList.add('d-none');
                errorAlert.classList.remove('d-none');

                Object.keys(errors).forEach(function(key) {
                    let formHelper = document.querySelector('#' + key + '-error');

                    formHelper.innerHTML = '';

                    errors[key].forEach(function(msg) {
                        formHelper.innerHTML = formHelper.innerHTML + msg + '<br>';
                        formHelper.classList.remove('d-none');
                    });
                });
            })
            .then(function () {
                document.querySelector('#writing-form-wrapper').scrollIntoView({ behavior: 'smooth', block: 'start'});
            });
        }

        // Post the comment form
        if ('post-comment-form' === element.attributes['id'].value) {
            event.preventDefault();

            let params = new FormData(element);
            let url = element.attributes['action'].value;
            let commentList = document.querySelector('#embed-comments .comment-list');
            let postCommentSuccess = document.querySelector('#post-comment-success');
            let postCommentError = document.querySelector('#post-comment-error');

            axios.post(url, params)
            .then(function (response) {
                element.reset();
                commentList.insertAdjacentHTML('beforeend', response.data);
                postCommentSuccess.classList.remove('d-none');
                postCommentError.classList.add('d-none');
            })
            .catch(function (error) {
                postCommentError.textContent = error.response.data.errors.comment[0];
                postCommentSuccess.classList.add('d-none');
                postCommentError.classList.remove('d-none');
            });
        }

        // Post the comment reply form
        if (element.classList.contains('reply-form')) {
            event.preventDefault();

            let params = new FormData(element);
            let url = element.attributes['action'].value;
            let commentReplyList = document.querySelector('#reply-list-' + element.comment_id.value);
            let commentReplyError = document.querySelector('#reply-error-' + element.comment_id.value);

            axios.post(url, params)
            .then(function (response) {
                element.reset();
                commentReplyList.insertAdjacentHTML('beforeend', response.data);
                element.classList.add('d-none');
                commentReplyError.classList.add('d-none');
            })
            .catch(function (error) {
                commentReplyError.textContent = error.response.data.errors.reply[0];
                commentReplyError.classList.remove('d-none');
            });
        }
    });
});
