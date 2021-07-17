import * as bootstrap from 'bootstrap';
import * as fx from './functions';
import * as push from './push';
import SlimSelect from 'slim-select';
import '@pwabuilder/pwaupdate';
import '@pwabuilder/pwainstall';
import autoGrow, { initializeTextAreaAutoGrow } from '@ivanhanak_com/js-textarea-autogrow';

// PWA Builder goodies
const installComponent = document.createElement('pwa-install');
const updateComponent = document.createElement('pwa-update');

document.body.appendChild(installComponent);
document.body.appendChild(updateComponent);

installComponent.manifestpath = '/manifest.json';
installComponent.explainer = 'Puedes instalar esta aplicacion web en tu dispositivo y disfrutar de una experiencia nativa en tu sistema.';
installComponent.featuresheader = 'Funcionalidades Principales';
installComponent.descriptionheader = 'Descripción';
installComponent.installbuttontext = 'Instalar';
installComponent.cancelbuttontext = 'Cancelar';
installComponent.iosinstallinfotext = 'Presiona el botón compartir y después "Añadir a la pantalla principal"';
installComponent.getInstalledStatus();

// Wait for the page to be fully loaded
window.addEventListener('load', () => {
    // Display the flash messages toast
    fx.showToast({
        'selector': '#toast-flash',
    });

    // Dispatch change event to enforce the app loads
    // alternative categories when editing a writing
    let mainCategory = document.querySelector('#main-category');

    if (null !== mainCategory && undefined !== mainCategory) {
        mainCategory.dispatchEvent(new Event('change', { bubbles: true }));
    }
});

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

    // Set element where to initialize Slim Select
    let slimSelector = document.querySelector('#writing-form #categories');

    // Initialize Slim Select
    if (null !== slimSelector && undefined !== slimSelector) {
        var slimSelect = new SlimSelect({
            select: slimSelector,
            showSearch: false,
            closeOnSelect: false,
            allowDeselectOption: true,
            hideSelectedOption: true,
            deselectLabel: '<i class="fas fa-times-circle"></i>',
        });

        slimSelect.disable();
    }

    // Initialize tooltips
    [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]')).map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    //Initialize popovers
    [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]')).map(function (popoverTriggerEl) {
        let helpText = popoverTriggerEl.parentElement.querySelector('.help').innerHTML;
        let options = {
            delay: 100,
            trigger: 'focus',
            placement: 'top',
            content: helpText,
            html: true,
        };

        return new bootstrap.Popover(popoverTriggerEl, options);
    });

    // Disable share dropdown if Share API is supported
    if (navigator.share) {
        [].slice.call(document.querySelectorAll('.dropdown .share')).map(function (shareBtn) {
            shareBtn.attributes['data-bs-toggle'].value = '';
        });
    }

    // Disable tracking if the opt-out cookie exists.
    if (null !== window.analytics_id && undefined !== window.analytics_id) {
        let disableStr = 'ga-disable-' + window.analytics_id;

        if (document.cookie.indexOf(disableStr + '=true') > -1) {
            window[disableStr] = true;
        }
    }

    // Set what modal element should be displayed
    let modalForm = document.querySelector('.form-wrapper');

    // Working with admin modal forms
    if (null !== modalForm && modalForm.classList.contains('modal')) {
        let targetForm = modalForm.querySelector('form');
        let modalTitleElement = modalForm.querySelector('.modal-title');

        // Change title if updating
        modalForm.addEventListener('show.bs.modal', (event) => {
            let relatedTarget = event.relatedTarget;

            if (null === relatedTarget) {
                modalTitleElement.querySelector('.create').classList.add('d-none');
                modalTitleElement.querySelector('.update').classList.remove('d-none');
            }
        });

        // Do some cleaning when the modal is closed
        modalForm.addEventListener('hidden.bs.modal', function () {
            // Reset title
            modalTitleElement.querySelector('.create').classList.remove('d-none');
            modalTitleElement.querySelector('.update').classList.add('d-none');

            // Reset form to default empty values
            fx.resetAdminFormCreate(targetForm);

            // Hide all the error helpers
            targetForm.querySelectorAll('.text-danger').forEach(function (helper) {
                helper.innerHTML = '';
                helper.classList.add('d-none');
            });
        });
    }

    // Check if subscribed to push notifications
    let pushEnable = document.querySelector('.push-enable');
    let pushDisable = document.querySelector('.push-disable');

    navigator.serviceWorker.ready.then(registration => {
        registration.pushManager.getSubscription()
            .then(subscription => {
                if (subscription && null !== pushDisable && undefined !== pushDisable) {
                    pushDisable.classList.remove('d-none');
                }

                if (! subscription && null !== pushEnable && undefined !== pushEnable) {
                    pushEnable.classList.remove('d-none');
                }
            })
            .catch(e => {
                console.log('Error thrown checking subscription status.', e);
            });
    });

    // Listen to the toast show event and act accordingly
    document.querySelector('.toast').addEventListener('show.bs.toast', (event) => {
        event.target.closest('.toast-wrapper').classList.add('show');
    }, false);

    // Listen to the toast hidden event and act accordingly
    document.querySelector('.toast').addEventListener('hidden.bs.toast', (event) => {
        event.target.closest('.toast-wrapper').classList.remove('show');
    }, false);

    // Reset toast look/info when closed
    document.querySelector('#toast-reuse').addEventListener('hidden.bs.toast', (event) => {
        // Set default look & feel
        event.target.classList.remove('success', 'danger');

        // Remove last message
        event.target.querySelector('.toast-body').innerHTML = '';
    }, false);

    // Listen to the window resize event and act accordingly
    window.addEventListener('resize', function () {
        let overlay = document.querySelector('#side-menu-overlay');
        let toggler = document.querySelector('#toggler i');
        let sideMenu = document.querySelectorAll('.side-menu');

        document.body.classList.remove('overflow-hidden');

        if (null !== overlay && undefined !== overlay) {
            overlay.classList.add('d-none');
        }

        if (null !== toggler && undefined !== toggler) {
            toggler.classList.remove('fa-times');
        }

        if (null !== sideMenu && undefined !== sideMenu) {
            sideMenu.forEach(function (aside) {
                aside.classList.remove('show');
            });
        }
    });

    // Listen to the on click event on the page and act accordingly
    document.addEventListener('click', (event) => {
        let element = event.target;

        // Bubble up click event on certain elements
        let bubble = element.closest('a, label, button, .btn, .avatar-chooser') || false;

        if (bubble) {
            element = bubble;
        }

        // Enable push notifications
        if (element.classList.contains('push-enable')) {
            push.subscribe();
            document.querySelectorAll('.btn-push').forEach(function (pushBtn) {
                pushBtn.classList.add('d-none');
            });

            element.dispatchEvent(new Event('focusout', { bubbles: true }));
            document.querySelector('.push-disable').classList.remove('d-none');
        }

        // Disable push notifications
        if (element.classList.contains('push-disable')) {
            push.unsubscribe();
            document.querySelectorAll('.btn-push').forEach(function (pushBtn) {
                pushBtn.classList.add('d-none');
            });

            element.dispatchEvent(new Event('focusout', { bubbles: true }));
            document.querySelector('.push-enable').classList.remove('d-none');
        }

        // Scroll to the top of the document
        if (element.hasAttribute('id') && 'back-to-top' === element.attributes['id'].value) {
            element.dispatchEvent(new Event('focusout', { bubbles: true }));
            document.querySelector('body').scrollIntoView({ behavior: 'smooth', block: 'end' });
        }

        // Scroll to the main nav
        if (element.hasAttribute('id') && 'jump-to-nav' === element.attributes['id'].value) {
            event.preventDefault();
            document.querySelector('#nav-main').scrollIntoView({ behavior: 'smooth' });
        }

        // Populate and/or show the side menu
        if (element.hasAttribute('id') && 'toggler' === element.attributes['id'].value) {
            let targetNav = document.querySelector(element.attributes['data-wh-target'].value);
            let dataSource = null;
            let sourceNav = null;

            if (element.hasAttribute('data-wh-source')) {
                dataSource = element.attributes['data-wh-source'].value;
            }

            if (null !== dataSource) {
                sourceNav = document.querySelector(dataSource);
            }

            if (null !== sourceNav && '' === targetNav.innerHTML) {
                targetNav.innerHTML = sourceNav.innerHTML;
            }

            document.querySelector('#side-menu-overlay').classList.toggle('d-none');
            targetNav.classList.toggle('show');
            element.querySelector('i').classList.toggle('fa-times');
            element.querySelector('.icon-badge-container').classList.toggle('rotate');
            document.body.classList.toggle('overflow-hidden');
        }

        // Hide the side menu when clicking off bounds
        if (element.hasAttribute('id') && 'side-menu-overlay' === element.attributes['id'].value) {
            document.querySelector('#toggler').click();
        }

        // Dynamically load comments for a writing
        if (null !== element.parentElement && undefined !== element.parentElement) {
            if (element.parentElement.hasAttribute('id') && 'load-more' === element.parentElement.attributes['id'].value) {
                let url = element.attributes['data-wh-href'].value;

                fx.loadComments(url);
            }
        }

        // Show/hide the comment reply form
        if (element.classList.contains('badge-reply')) {
            let target = element.attributes['data-wh-target'].value;
            let targetForm = document.querySelector(target);
            targetForm.classList.toggle('d-none');

            if (!targetForm.classList.contains('d-none')) {
                targetForm.querySelector('.form-control').focus();
            }
        }

        // Trigger the cover chooser
        if (element.hasAttribute('id') && 'cover-chooser' === element.attributes['id'].value) {
            event.preventDefault();
            let fileChooser = document.querySelector(element.attributes['data-wh-target'].value);

            fileChooser.click();
        }

        // Trigger the avatar chooser
        if (element.classList.contains('avatar-chooser')) {
            event.preventDefault();
            let fileChooser = document.querySelector(element.attributes['data-wh-target'].value);

            fileChooser.click();
        }

        // Counters
        if (element.classList.contains('btn-counter')) {
            event.preventDefault();

            // Liking a writing
            if (element.classList.contains('like')) {
                if (element.hasAttribute('data-wh-target') && element.hasAttribute('data-wh-id') && element.hasAttribute('data-wh-value')) {
                    let url = element.attributes['data-wh-target'].value;
                    let id = element.attributes['data-wh-id'].value;
                    let value = element.attributes['data-wh-value'].value;
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
                if (element.hasAttribute('data-wh-target') && element.hasAttribute('data-wh-id')) {
                    let url = element.attributes['data-wh-target'].value;
                    let id = element.attributes['data-wh-id'].value;
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

            // Share button (if Share API is supported)
            if (element.classList.contains('share') && navigator.share) {
                navigator.share({
                    title: element.attributes['data-wh-writing-title'].value,
                    url: element.attributes['data-wh-url'].value
                });
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

        // Admin edit link
        if (element.classList.contains('admin-edit')) {
            event.preventDefault();

            let targetModal = document.querySelector(element.attributes['data-wh-target-modal'].value);
            let targetModel = element.attributes['data-wh-target-model'].value;
            let targetForm = document.querySelector(element.attributes['data-wh-target-form'].value);
            let targetData = JSON.parse(element.attributes['data-wh-target-form-data'].value);

            if ('category' === targetModel) {
                targetForm.id.value = targetData.id;
                targetForm.name.value = targetData.name;
                targetForm.parent.value = targetData.parent_id;
                targetForm.description.value = targetData.description;
            }

            if ('page' === targetModel) {
                targetForm.id.value = targetData.id;
                targetForm.title.value = targetData.title;
                targetForm.text.value = targetData.text;
            }

            fx.showModal(targetModal, {
                'backdrop': 'static'
            });
        }

        // Deleting a record (confirmation prompt)
        if (element.classList.contains('admin-content-delete') || element.classList.contains('user-content-delete')) {
            event.preventDefault();

            let targetModal = element.attributes['href'].value;
            let btnDelete = document.querySelector('#btn-modal-delete');
            let warningDelete = document.querySelector('#content-delete-warning');

            if (null !== warningDelete && element.hasAttribute('data-wh-warning')) {
                warningDelete.innerHTML = element.attributes['data-wh-warning'].value;
                warningDelete.parentElement.classList.remove('d-none');
            }

            if (null !== btnDelete && element.hasAttribute('data-wh-target')) {
                btnDelete.attributes['data-wh-delete-url'].value = element.attributes['data-wh-target'].value;
            }

            if (null !== btnDelete && element.hasAttribute('data-wh-redirect')) {
                btnDelete.attributes['data-wh-redirect-url'].value = element.attributes['data-wh-redirect'].value;
            }

            fx.showModal(targetModal, {
                'backdrop': 'static'
            });
        }

        // Deleting a record
        if (element.hasAttribute('id') && 'btn-modal-delete' === element.attributes['id'].value) {
            let url = element.attributes['data-wh-delete-url'].value;
            let redirect = element.attributes['data-wh-redirect-url'].value;
            let params = new FormData();

            params.append('_method', 'delete');

            if (null !== redirect && '' !== redirect) {
                params.append('redirect', true);
            }

            // Post the form to the server
            axios.post(url, params)
                .then(function (response) {
                    if (null !== redirect && '' !== redirect) {
                        location.assign(redirect);
                    } else {
                        fx.showToast({
                            'message': response.data.message,
                            'theme': 'success'
                        });
                    }
                })
                .catch(function (error) {
                    fx.showToast({
                        'message': error.response.data.message,
                        'theme': 'danger'
                    });
                })
                .then(function () {
                    //
                });
        }

        // Opting out of GA tracking
        if (element.hasAttribute('href') && '#ga-optout' === element.attributes['href'].value) {
            event.preventDefault();
            let disableStr = 'ga-disable-' + analytics_id;

            document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
            window[disableStr] = true;
            alert('Google Analytics tracking disabled');
        }
    });

    // Listen to the on submit event on the page and act accordingly
    document.addEventListener('submit', (event) => {
        let element = event.target;
        let id = '';

        if (element.hasAttribute('id')) {
            id = element.attributes['id'].value;
        }

        // Post the writing create/update form
        if ('writing-form' === id) {
            event.preventDefault();
            fx.handleForm(element, 'submit');

            // Initialize form and helpers
            let params = new FormData(element);
            let url = element.attributes['action'].value;

            // Post the form to the server
            axios.post(url, params)
                .then(function (response) {
                    let method = element.elements['_method'] || false;

                    // Form posted successfully, let's reset it
                    if (!method) {
                        element.reset();

                        if (null !== slimSelect && undefined !== slimSelect) {
                            slimSelect.set([]);
                        }
                    }

                    // Update file helpers
                    element.querySelector('#selected-file').classList.add('d-none');
                    element.querySelector('#selected-error').classList.add('d-none');

                    // Show toast
                    fx.showToast({
                        'theme': 'success',
                        'message': response.data.message
                    });
                })
                .catch(function (error) {
                    // Oh no, something went wrong
                    let errors = error.response.data.errors;

                    // Handle the error messages
                    fx.handleFormErrors(errors);

                    // Show toast
                    fx.showToast({
                        'theme': 'danger',
                        'message': error.response.data.message
                    });
                })
                .then(function () {
                    fx.handleForm(element, 'response');

                    // Scroll back to the form header
                    document.querySelector('#writing-form-wrapper .title').scrollIntoView({ behavior: 'smooth', block: 'end' });
                });
        }

        // Post the user profile update form
        if ('profile-form' === id) {
            event.preventDefault();
            fx.handleForm(element, 'submit');

            // Initialize form and helpers
            let params = new FormData(element);
            let url = element.attributes['action'].value;

            // Post the form to the server
            axios.post(url, params)
                .then(function (response) {
                    // Show toast
                    fx.showToast({
                        'message': response.data.message,
                        'theme': 'success'
                    });
                })
                .catch(function (error) {
                    // Oh no, something went wrong
                    let errors = error.response.data.errors;

                    // Handle the error messages
                    fx.handleFormErrors(errors);

                    // Show toast
                    fx.showToast({
                        'message': error.response.data.message,
                        'theme': 'danger'
                    });
                })
                .then(function () {
                    fx.handleForm(element, 'response');

                    // Scroll back to the form header
                    document.querySelector('#profile-form-wrapper .title').scrollIntoView({ behavior: 'smooth', block: 'end' });
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

            // Post the form to the server
            axios.post(url, params)
                .then(function (response) {
                    // Reset form
                    if ('create' === response.data.action) {
                        element.reset.click();
                    }

                    fx.showToast({
                        'message': response.data.message,
                        'theme': 'success'
                    });
                })
                .catch(function (error) {
                    // Oh no, something went wrong
                    let errors = error.response.data.errors;

                    // Handle the error messages
                    fx.handleFormErrors(errors);

                    fx.showToast({
                        'message': error.response.data.message,
                        'theme': 'danger'
                    });
                })
                .then(function () {
                    fx.handleForm(element, 'response');
                });
        }
    });

    // Listen to the on change event on the page and act accordingly
    document.addEventListener('change', (event) => {
        let element = event.target;

        // Trigger the cover file validation
        if (element.hasAttribute('id') && 'cover' === element.attributes['id'].value) {
            const file = element.files[0];
            const fileSizeKb = parseInt(file.size / 1024);
            const maxFileSizeKb = element.attributes['data-wh-max-size'].value;
            let info = document.querySelector(element.attributes['data-wh-target'].value);
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
            const maxFileSizeKb = element.attributes['data-wh-max-size'].value;
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

        // Populate alternative categories
        if (element.hasAttribute('id') && 'main-category' === element.attributes['id'].value && '' !== element.value) {
            // Disable alternative categories select box
            if (null !== slimSelect && undefined !== slimSelect) {
                slimSelect.disable();
            }

            // Clear previous options
            if (null !== slimSelect && undefined !== slimSelect) {
                slimSelect.set([]);
            }

            document.querySelector('#categories').innerHTML = '';

            let selectedIndex = element.selectedIndex;
            let currentCategories = JSON.parse(document.querySelector('#categories').attributes['data-wh-selected'].value);
            let descendants = JSON.parse(element.options[selectedIndex].attributes['data-wh-descendants'].value);

            if (Object.keys(descendants).length > 0) {
                // Add new options
                for (let idx in descendants) {
                    let descendant = descendants[idx];
                    let option = document.createElement('option');
                    let targetSelect = document.querySelector('#categories');

                    option.setAttribute('value', descendant.id);
                    option.innerHTML = descendant.name;

                    if (currentCategories.includes(descendant.id)) {
                        option.selected = 'selected';
                    }

                    targetSelect.appendChild(option);
                }

                if (null !== slimSelect && undefined !== slimSelect) {
                    slimSelect.enable();
                }
            }
        }
    });

    // Listen to the focusout event on the page and act accordingly
    document.addEventListener('focusout', (event) => {
        let element = event.target;

        // Trim start/end commas from entered tags (if any)
        if (element.hasAttribute('id') && 'tags' === element.attributes['id'].value) {
            let trimTags = element.value.replace(/^\,+|\,+$/g, '');
            element.value = trimTags;
        }
    });

    // Listen to the window scroll event and act accordingly
    window.addEventListener('scroll', (event) => {
        let header = document.querySelector('.header');
        let mainWrapper = document.querySelector('.main-wrapper');
        let jumpToMainNav = document.querySelector('#jump-to-nav');
        let jumpToHeader = document.querySelector('#back-to-top-wrapper');

        if (null !== header && null !== jumpToHeader) {
            if (fx.isInViewport(header)) {
                jumpToHeader.classList.remove('fade-in')
                jumpToHeader.classList.add('fade-out')
            } else {
                jumpToHeader.classList.remove('fade-out')
                jumpToHeader.classList.add('fade-in')
            }
        }

        if (null !== mainWrapper && null !== jumpToMainNav) {
            if (fx.isInViewport(mainWrapper)) {
                jumpToMainNav.classList.remove('fade-in')
                jumpToMainNav.classList.add('fade-out')
            } else {
                jumpToMainNav.classList.remove('fade-out')
                jumpToMainNav.classList.add('fade-in')
            }
        }
    });

    // Listen to the input event and act accordingly
    document.addEventListener('input', (event) => {
        let element = event.target;

        if (element.classList.contains('autogrow')) {
            autoGrow(element);
        }
    });
});
