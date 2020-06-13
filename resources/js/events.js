import * as fx from './functions';
import BSN from "bootstrap.native";

// Wait for the DOM to be readay
document.addEventListener('DOMContentLoaded', () => {
    // Enable scrolling on the document
    document.body.classList.remove('overflow-hidden');

    // Create the side menu for small screens
    fx.createSideMenu();

    // Hide Whatsapp sharer on Desktop
    if (!fx.isMobile()) {
        document.querySelectorAll('.whatsapp-link').forEach(function (link) {
            link.classList.add('d-none');
        })
    }

    // Listen to the on click event on the page and act accordingly
    document.addEventListener('click', function (event) {
        let element = event.target;

        // Was a button or file chooser clicked?
        let bubble = element.closest('button, .btn, .avatar-chooser') || false;

        if (bubble) {
            element = bubble;
        }

        // Scroll to the top of the document
        if (element.hasAttribute('id') && 'back-to-top' === element.attributes['id'].value) {
            document.querySelector('.header').scrollIntoView({ behavior: 'smooth', block: 'end' });
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

            fx.loadComments(url);
        }

        // Show/hide the comment reply form
        if (element.classList.contains('badge-reply')) {
            let target = element.attributes['data-target'].value;
            let targetForm = document.querySelector(target);
            targetForm.classList.toggle('d-none');

            if (!targetForm.classList.contains('d-none')) {
                targetForm.querySelector('.form-control').focus();
            }
        }

        // Hide alerts
        if (element.classList.contains('close')) {
            let parentAlert = element.closest('.alert');

            if (null !== parentAlert) {
                parentAlert.classList.add('d-none');
            }
        }

        // Trigger the cover chooser
        if (element.hasAttribute('id') && 'cover-chooser' === element.attributes['id'].value) {
            event.preventDefault();
            let fileChooser = document.querySelector(element.attributes['data-target'].value);

            fileChooser.click();
        }

        // Trigger the avatar chooser
        if (element.classList.contains('avatar-chooser')) {
            event.preventDefault();
            let fileChooser = document.querySelector(element.attributes['data-target'].value);

            fileChooser.click();
        }

        // Counters
        if (element.classList.contains('btn-counter')) {
            event.preventDefault();

            // Liking a writing
            if (element.classList.contains('like')) {
                if (element.hasAttribute('data-target') && element.hasAttribute('data-id') && element.hasAttribute('data-value')) {
                    let url = element.attributes['data-target'].value;
                    let id = element.attributes['data-id'].value;
                    let value = element.attributes['data-value'].value;
                    let params = new FormData();

                    params.append('id', id);
                    params.append('value', value);

                    axios.post(url, params)
                        .then(function (response) {
                            let created = response.data.created;
                            let count = response.data.count;

                            if (created > 0) {
                                element.classList.add('voted');
                                element.querySelector('.counter').textContent = count;
                            }
                        })
                        .catch(function (error) {
                            //
                        })
                        .then(function () {
                            //
                        });
                }
            }

            // Adding to shelf
            if (element.classList.contains('shelf')) {
                if (element.hasAttribute('data-target') && element.hasAttribute('data-id')) {
                    let url = element.attributes['data-target'].value;
                    let id = element.attributes['data-id'].value;
                    let params = new FormData();

                    params.append('id', id);

                    axios.post(url, params)
                        .then(function (response) {
                            let count = response.data.count;

                            if (count > 0) {
                                element.classList.add('shelved');
                                element.querySelector('.counter').textContent = count;
                            }
                        })
                        .catch(function (error) {
                            //
                        })
                        .then(function () {
                            //
                        });
                }
            }

            // Share button
            if (element.classList.contains('share')) {
                // Check if Share API is supported
                if (navigator.share) {
                    navigator.share({
                        title: element.attributes['data-writing-title'].value,
                        url: element.attributes['data-url'].value
                    });
                } else {
                    new BSN.Dropdown(element).toggle();
                }
            }
        }

        // Share links
        if (element.classList.contains('share-link')) {
            event.preventDefault();

            let url = element.attributes['href'].value;

            if (element.classList.contains('copy-to-clipboard-link')) {
                navigator.clipboard.writeText(url);
            } else {
                let params = 'scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=500,height=500,left=100,top=100';
                let sharer = open(url, 'sharer', params);
            }
        }
    });

    // Listen to the on submit event on the page and act accordingly
    document.addEventListener('submit', function (event) {
        let element = event.target;
        let id = element.attributes['id'].value;

        // Post the writing create/update form
        if ('writing-form' === id) {
            event.preventDefault();
            fx.handleForm(element, 'submit');

            // Initialize form and helpers
            let params = new FormData(element);
            let url = element.attributes['action'].value;
            let errorAlert = element.querySelector('.alert-danger');
            let successAlert = element.querySelector('.alert-success');
            let successLink = successAlert.querySelector('#writing-success-link');

            // Post the form to the server
            axios.post(url, params)
                .then(function (response) {
                    let method = element.elements['_method'] || false;

                    // Form posted successfully, let's reset it
                    if (! method) {
                        element.reset();
                    }

                    // Update file helpers
                    element.querySelector('#selected-file').classList.add('d-none');
                    element.querySelector('#selected-error').classList.add('d-none');

                    // Update alerts
                    successLink.attributes['href'].value = response.data.url;
                    successAlert.classList.remove('d-none');
                    errorAlert.classList.add('d-none');
                })
                .catch(function (error) {
                    // Oh no, something went wrong
                    let errors = error.response.data.errors;

                    // Update alerts
                    successAlert.classList.add('d-none');
                    errorAlert.classList.remove('d-none');

                    // Handle the error messages
                    fx.handleFormErrors(errors);
                })
                .then(function () {
                    fx.handleForm(element, 'response');

                    // Scroll back to the form header
                    document.querySelector('#writing-form-wrapper h3').scrollIntoView({ behavior: 'smooth', block: 'end' });
                });
        }

        // Post the user profile update form
        if ('profile-form' === id) {
            event.preventDefault();
            fx.handleForm(element, 'submit');

            // Initialize form and helpers
            let params = new FormData(element);
            let url = element.attributes['action'].value;
            let errorAlert = element.querySelector('.alert-danger');
            let successAlert = element.querySelector('.alert-success');
            let successLink = successAlert.querySelector('#profile-success-link');

            // Post the form to the server
            axios.post(url, params)
                .then(function (response) {
                    // Update alerts
                    successLink.attributes['href'].value = response.data.url;
                    successAlert.classList.remove('d-none');
                    errorAlert.classList.add('d-none');
                })
                .catch(function (error) {
                    // Oh no, something went wrong
                    let errors = error.response.data.errors;

                    // Update alerts
                    successAlert.classList.add('d-none');
                    errorAlert.classList.remove('d-none');

                    // Handle the error messages
                    fx.handleFormErrors(errors);
                })
                .then(function () {
                    fx.handleForm(element, 'response');

                    // Scroll back to the form header
                    document.querySelector('#profile-form-wrapper h3').scrollIntoView({ behavior: 'smooth', block: 'end' });
                });
        }

        // Post the comment form
        if ('post-comment-form' === id) {
            event.preventDefault();

            let params = new FormData(element);
            let url = element.attributes['action'].value;
            let commentList = document.querySelector('#embed-comments .comment-list');
            let postCommentSuccess = document.querySelector('#post-comment-success');
            let postCommentError = document.querySelector('#post-comment-error');
            let commentsEmpty = document.querySelector('.comments-empty');

            // Display the wait cursor
            document.body.classList.add('cursor-wait');

            axios.post(url, params)
                .then(function (response) {
                    element.reset();
                    commentList.insertAdjacentHTML('beforeend', response.data);
                    postCommentSuccess.classList.remove('d-none');
                    postCommentError.classList.add('d-none');

                    if (null !== commentsEmpty && '' !== commentsEmpty) {
                        commentsEmpty.classList.add('d-none');
                    }
                })
                .catch(function (error) {
                    postCommentError.textContent = error.response.data.errors.comment[0];
                    postCommentSuccess.classList.add('d-none');
                    postCommentError.classList.remove('d-none');
                })
                .then(function () {
                    // Display the standard cursor
                    document.body.classList.remove('cursor-wait');
                });
        }

        // Post the comment reply form
        if (element.classList.contains('reply-form')) {
            event.preventDefault();

            let params = new FormData(element);
            let url = element.attributes['action'].value;
            let commentReplyList = document.querySelector('#reply-list-' + element.comment_id.value);
            let commentReplyError = document.querySelector('#reply-error-' + element.comment_id.value);

            // Display the wait cursor
            document.body.classList.add('cursor-wait');

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
                })
                .then(function () {
                    // Display the standard cursor
                    document.body.classList.remove('cursor-wait');
                });
        }

        let adminForms = ['admin-settings-form', 'admin-types-form', 'admin-categories-form', 'admin-pages-form'];

        // Save data from admin panel
        if (adminForms.includes(id)) {
            event.preventDefault();
            fx.handleForm(element, 'submit');

            // Initialize form and helpers
            let params = new FormData(element);
            let url = element.attributes['action'].value;
            let errorAlert = element.querySelector('.alert-danger');
            let successAlert = element.querySelector('.alert-success');

            // Post the form to the server
            axios.post(url, params)
                .then(function (response) {
                    // Reset form
                    if ('create' === response.data.action) {
                        element.reset.click();
                    }

                    // Update alerts
                    successAlert.classList.remove('d-none');
                    errorAlert.classList.add('d-none');
                })
                .catch(function (error) {
                    // Oh no, something went wrong
                    let errors = error.response.data.errors;

                    // Update alerts
                    successAlert.classList.add('d-none');
                    errorAlert.classList.remove('d-none');

                    // Handle the error messages
                    fx.handleFormErrors(errors);
                })
                .then(function () {
                    fx.handleForm(element, 'response');
                });
        }
    });

    // Listen to the on change event on the page and act accordingly
    document.addEventListener('change', function (event) {
        let element = event.target;

        // Trigger the cover file validation
        if (element.hasAttribute('id') && 'cover' === element.attributes['id'].value) {
            const file = element.files[0];
            const fileSizeKb = parseInt(file.size / 1024);
            const maxFileSizeKb = element.attributes['data-max-size'].value;
            let info = document.querySelector(element.attributes['data-target'].value);
            let error = element.parentElement.querySelector('#selected-error');

            if (null !== file && '' !== file && fileSizeKb <= maxFileSizeKb && fx.isImage(file)) {
                info.textContent = file.name + ' [' + fileSizeKb + 'kb]';
                info.classList.remove('d-none');
                error.classList.add('d-none');
            } else {
                element.value = '';
                info.textContent = '';
                info.classList.add('d-none');
                error.classList.remove('d-none');
            }
        }

        // Trigger the avatar file validation
        if (element.hasAttribute('id') && 'avatar' === element.attributes['id'].value) {
            const file = element.files[0];
            const fileSizeKb = parseInt(file.size / 1024);
            const maxFileSizeKb = element.attributes['data-max-size'].value;
            let error = document.querySelector('#avatar-error');

            if (null !== file && '' !== file && fileSizeKb <= maxFileSizeKb && fx.isImage(file)) {
                fx.readImage(file, fx.previewAvatar);
                error.classList.add('d-none');
            } else {
                element.value = '';
                fx.previewAvatar('');
                error.classList.remove('d-none');
            }
        }

        // Trigger the avatar delete checkbox validation
        if (element.hasAttribute('id') && 'avatar-remove' === element.attributes['id'].value) {
            if (element.checked) {
                let avatarInput = document.querySelector('#avatar');

                fx.previewAvatar('');
                avatarInput.value = '';
            }
        }
    });
});
