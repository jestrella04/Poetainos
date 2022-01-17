'use strict';

import * as bootstrap from 'bootstrap';
import * as fx from './functions';
import * as push from './push';
import '@pwabuilder/pwaupdate';
import '@pwabuilder/pwainstall';
import autoGrow from '@ivanhanak_com/js-textarea-autogrow';
import Tribute from 'tributejs';
import Tags from 'bootstrap5-tags';

// PWA Builder goodies
const installComponent = document.createElement('pwa-install');
const updateComponent = document.createElement('pwa-update');
const isInstalled = installComponent.getInstalledStatus();

document.querySelector('footer').appendChild(installComponent);
document.querySelector('footer').appendChild(updateComponent);

// Customizing displayed messages
installComponent.manifestpath = '/manifest.json';
installComponent.explainer = 'Puedes instalar esta aplicacion web en tu dispositivo y disfrutar de una experiencia nativa.';
installComponent.featuresheader = 'Funcionalidades Principales';
installComponent.descriptionheader = 'Descripción';
installComponent.installbuttontext = 'Instalar';
installComponent.cancelbuttontext = 'Cancelar';
installComponent.iosinstallinfotext = 'Presiona el botón compartir y después "Añadir a la pantalla principal"';
updateComponent.updatemessage = "Hay una actualización disponible";

// Wait for the page to be fully loaded
window.addEventListener('load', () => {
    // Display the flash messages toast
    fx.showToast({
        'selector': '#toast-flash',
    });

    // Dispatch change event to enforce the app loads
    // alternative categories when editing a writing
    let mainCategory = document.querySelector('#main-category');
    let subCategories = document.querySelector('#categories');
    let tagsInstance = Tags.getInstance(subCategories);

    if (!fx.isNil(mainCategory) && !fx.isNilOrEmpty(mainCategory.value)) {
        subCategories.disabled = false;
        fx.subCategoriesUpdate(subCategories, mainCategory);
        tagsInstance.resetSuggestions();
        tagsInstance.resetState();
    }
});

// Wait for the DOM to be ready
document.addEventListener('DOMContentLoaded', () => {
    // Check for user session
    let userToken = document.querySelector('meta[name="user-token"]');

    if (!fx.isNil(userToken)) {
        userToken = atob(userToken.content);
    }

    // Initialize Tags
    Tags.init('.tags-select', {
        allowClear: true,
        suggestionsThreshold: 0,
    });

    // Initialize tooltips
    [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]')).map(tooltipTriggerEl => {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]')).map(popoverTriggerEl => {
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
        [].slice.call(document.querySelectorAll('.dropdown .share')).map(shareBtn => {
            shareBtn.dataset.bsToggle = '';
        });
    }

    // Disable tracking if the opt-out cookie exists.
    if (!fx.isNil(window.analytics_id)) {
        let disableStr = `ga-disable-${window.analytics_id}`;

        if (document.cookie.indexOf(`${disableStr}=true`) > -1) {
            window[disableStr] = true;
        }
    }

    // Set what modal element should be displayed
    let modalForm = document.querySelector('.form-wrapper');

    // Working with admin modal forms
    if (!fx.isNil(modalForm) && modalForm.matches('.modal')) {
        let targetForm = modalForm.querySelector('form');
        let modalTitleElement = modalForm.querySelector('.modal-title');

        // Change title if updating
        modalForm.addEventListener('show.bs.modal', event => {
            let relatedTarget = event.relatedTarget;

            if (fx.isNil(relatedTarget)) {
                modalTitleElement.querySelector('.create').classList.add('d-none');
                modalTitleElement.querySelector('.update').classList.remove('d-none');
            }
        });

        // Do some cleaning when the modal is closed
        modalForm.addEventListener('hidden.bs.modal', () => {
            // Reset title
            modalTitleElement.querySelector('.create').classList.remove('d-none');
            modalTitleElement.querySelector('.update').classList.add('d-none');

            // Reset form to default empty values
            fx.resetAdminFormCreate(targetForm);

            // Hide all the error helpers
            targetForm.querySelectorAll('.text-danger').forEach(helper => {
                helper.innerHTML = '';
                helper.classList.add('d-none');
            });
        });
    }

    // Push buttons
    let pushEnable = document.querySelector('.push-enable');
    let pushDisable = document.querySelector('.push-disable');

    // Managing push subscriptions
    navigator.serviceWorker.ready.then(registration => {
        registration.pushManager.getSubscription()
            .then(subscription => {
                // Keep subscription in sync with server
                if (subscription) {
                    push.subscribe();
                }

                // Hide the push disable button
                if (subscription && !fx.isNil(pushDisable)) {
                    pushDisable.classList.remove('d-none');
                }

                // Hide the push enable button
                if (!subscription && !fx.isNil(pushEnable)) {
                    pushEnable.classList.remove('d-none');
                }
            })
            .catch(e => {
                console.log('Error thrown checking subscription status.', e);
            });
    });

    // Listen to the toast show event and act accordingly
    document.querySelector('.toast').addEventListener('show.bs.toast', event => {
        event.target.closest('.toast-wrapper').classList.add('show');
    }, false);

    // Listen to the toast hidden event and act accordingly
    document.querySelector('.toast').addEventListener('hidden.bs.toast', event => {
        event.target.closest('.toast-wrapper').classList.remove('show');
    }, false);

    // Reset toast look/info when closed
    document.querySelector('#toast-reuse').addEventListener('hidden.bs.toast', event => {
        // Set default look & feel
        event.target.classList.remove('success', 'danger');

        // Remove last message
        event.target.querySelector('.toast-body').innerHTML = '';
    }, false);

    // Listen for new user notification events coming from the server
    if (!fx.isNil(userToken)) {
        Echo.private(`notifications.${userToken}`).listen('NotificationEvent', payload => {
            document.querySelectorAll('.unread').forEach(badge => {
                badge.classList.remove('d-none');
            });

            document.querySelectorAll('.unread-count').forEach(count => {
                count.innerHTML = payload.notifications.unread;
            });
        });
    }

    // Listen to the on click event on the page and act accordingly
    document.addEventListener('click', event => {
        let element = event.target;

        // Bubble up click event on certain elements
        let bubble = element.closest('a, label, button, .badge, .avatar-chooser') || false;

        if (bubble) {
            element = bubble;
        }

        // Enable push notifications
        if (element.matches('.push-enable')) {
            event.preventDefault();
            push.subscribe();
            document.querySelectorAll('.btn-push').forEach(pushBtn => {
                pushBtn.classList.add('d-none');
            });

            element.dispatchEvent(new Event('focusout', { bubbles: true }));
            document.querySelector('.push-disable').classList.remove('d-none');
        }

        // Disable push notifications
        if (element.matches('.push-disable')) {
            event.preventDefault();
            push.unsubscribe();
            document.querySelectorAll('.btn-push').forEach(pushBtn => {
                pushBtn.classList.add('d-none');
            });

            element.dispatchEvent(new Event('focusout', { bubbles: true }));
            document.querySelector('.push-enable').classList.remove('d-none');
        }

        // Scroll to the main nav
        if (element.matches('.jump-to-nav')) {
            // Check if triggered from footer btn and hide tooltip
            if (element.matches('#back-to-top')) {
                bootstrap.Tooltip.getInstance(element).hide();
            }

            document.querySelector('body').scrollIntoView({ behavior: 'smooth' });
        }

        // Dynamically load comments for a writing
        if (!fx.isNil(element.parentElement)) {
            if (element.parentElement.matches['#load-more']) {
                let url = element.dataset.whHref;

                fx.loadComments(url);
            }
        }

        // Show/hide the comment reply form
        if (element.matches('.badge-reply')) {
            let target = element.dataset.whTarget;
            let targetForm = document.querySelector(target);
            targetForm.classList.toggle('d-none');

            if (!targetForm.matches('.d-none')) {
                targetForm.querySelector('.form-control').focus();
            }
        }

        // Trigger the cover chooser
        if (element.matches('#cover-chooser')) {
            event.preventDefault();
            let fileChooser = document.querySelector(element.dataset.whTarget);

            fileChooser.click();
        }

        // Trigger the avatar chooser
        if (element.matches('.avatar-chooser')) {
            event.preventDefault();
            let fileChooser = document.querySelector(element.dataset.whTarget);

            fileChooser.click();
        }

        // Counters
        if (element.closest('.stats')) {
            // Liking a writing
            if (element.matches('.like')) {
                event.preventDefault();

                if ('whTarget' in element.dataset && 'whId' in element.dataset && 'whValue' in element.dataset) {
                    let url = element.dataset.whTarget;
                    let id = element.dataset.whId;
                    let value = element.dataset.whValue;
                    let params = new FormData();

                    params.append('id', id);
                    params.append('value', value);

                    axios.post(url, params)
                        .then(response => {
                            let created = response.data.created;
                            let count = response.data.count;

                            if (created > 0) {
                                element.classList.add('voted');
                                element.querySelector('.counter').textContent = count;
                            }
                        })
                        .catch(error => {
                            //
                        })
                        .then(() => {
                            //
                        });
                }
            }

            // Adding to shelf
            if (element.matches('.shelf')) {
                event.preventDefault();

                if ('whTarget' in element.dataset && 'whId' in element.dataset) {
                    let url = element.dataset.whTarget;
                    let id = element.dataset.whId;
                    let params = new FormData();

                    params.append('id', id);

                    axios.post(url, params)
                        .then(response => {
                            let count = response.data.count;

                            if (count > 0) {
                                element.classList.add('shelved');
                                element.querySelector('.counter').textContent = count;
                            }
                        })
                        .catch(error => {
                            //
                        })
                        .then(() => {
                            //
                        });
                }
            }

            // Share button (if Share API is supported)
            if (element.matches('.share') && navigator.share) {
                event.preventDefault();

                navigator.share({
                    title: element.dataset.whWritingTitle,
                    url: element.dataset.whUrl
                });
            }
        }

        // Share links
        if (element.matches('.share-link')) {
            event.preventDefault();

            let url = element.href;

            if (element.matches('.copy-to-clipboard-link')) {
                navigator.clipboard.writeText(url);
            } else {
                let params = 'scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=500,height=500,left=100,top=100';

                open(url, 'sharer', params);
            }
        }

        // Admin edit link
        if (element.matches('.admin-edit')) {
            event.preventDefault();

            let targetModal = document.querySelector(element.dataset.whTargetModal);
            let targetModel = element.dataset.whTargetModel;
            let targetForm = document.querySelector(element.dataset.whTargetForm);
            let targetData = JSON.parse(element.dataset.whTargetFormData);

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
        if (element.matches('.admin-content-delete') || element.matches('.user-content-delete')) {
            event.preventDefault();

            let targetModal = element.attributes['href'].value;
            let btnDelete = document.querySelector('#btn-modal-delete');
            let warningDelete = document.querySelector('#content-delete-warning');

            if (!fx.isNil(warningDelete) && 'whWarning' in element.dataset) {
                warningDelete.innerHTML = element.dataset.whWarning;
                warningDelete.parentElement.classList.remove('d-none');
            }

            if (!fx.isNil(btnDelete) && 'whTarget' in element.dataset) {
                btnDelete.dataset.whDeleteUrl = element.dataset.whTarget;
            }

            if (!fx.isNil(btnDelete) && 'whRedirect' in element.dataset) {
                btnDelete.dataset.whRedirectUrl = element.dataset.whRedirect;
            }

            fx.showModal(targetModal, {
                'backdrop': 'static'
            });
        }

        // Deleting a record
        if (element.matches('#btn-modal-delete')) {
            let url = element.dataset.whDeleteUrl;
            let redirect = element.dataset.whRedirectUrl;
            let params = new FormData();

            params.append('_method', 'delete');

            if (!fx.isNilOrEmpty(redirect)) {
                params.append('redirect', true);
            }

            // Post the form to the server
            axios.post(url, params)
                .then(response => {
                    if (!fx.isNilOrEmpty(redirect)) {
                        location.assign(redirect);
                    } else {
                        fx.showToast({
                            'message': response.data.message,
                            'theme': 'success'
                        });
                    }
                })
                .catch(error => {
                    fx.showToast({
                        'message': error.response.data.message,
                        'theme': 'danger'
                    });
                })
                .then(() => {
                    //
                });
        }

        // Opting out of GA tracking
        if (element.hasAttribute('href') && '#ga-optout' === element.href) {
            event.preventDefault();
            let disableStr = `ga-disable-${analytics_id}`;

            document.cookie = `${disableStr}=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/`;
            window[disableStr] = true;
            alert('Google Analytics tracking disabled');
        }

        // Toggle main sidebar
        if (element.closest('.sidebar-toggle')) {
            element.closest('.sidebar-toggle').querySelector('i').classList.toggle('rotate');
        }
    });

    // Listen to the on submit event on the page and act accordingly
    document.addEventListener('submit', event => {
        let element = event.target;

        // Post the writing create/update form
        if (element.matches('#writing-form')) {
            event.preventDefault();
            fx.handleForm(element, 'submit');

            // Initialize form and helpers
            let params = new FormData(element);
            let url = element.action;

            // Post the form to the server
            axios.post(url, params)
                .then(response => {
                    let method = element.elements['_method'] || false;

                    // Form posted successfully, let's reset it
                    if (!method) {
                        element.reset();

                        let subCategories = document.querySelector('#categories');
                        let tagsInstance = Tags.getInstance(subCategories);

                        subCategories.disabled = true;
                        tagsInstance.resetState();
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
                .catch(error => {
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
                .then(() => {
                    fx.handleForm(element, 'response');

                    // Scroll back to the form header
                    document.querySelector('#writing-form-wrapper .title').scrollIntoView({ behavior: 'smooth', block: 'end' });
                });
        }

        // Post the user profile update form
        if (element.matches('#profile-form')) {
            event.preventDefault();
            fx.handleForm(element, 'submit');

            // Initialize form and helpers
            let params = new FormData(element);
            let url = element.action;

            // Post the form to the server
            axios.post(url, params)
                .then(response => {
                    // Show toast
                    fx.showToast({
                        'message': response.data.message,
                        'theme': 'success'
                    });
                })
                .catch(error => {
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
                .then(() => {
                    fx.handleForm(element, 'response');

                    // Scroll back to the form header
                    document.querySelector('#profile-form-wrapper .title').scrollIntoView({ behavior: 'smooth', block: 'end' });
                });
        }

        // Post the comment form
        if (element.matches('#post-comment-form')) {
            event.preventDefault();

            let params = new FormData(element);
            let url = element.action;
            let commentList = document.querySelector('#embed-comments .comment-list');
            let postCommentSuccess = document.querySelector('#post-comment-success');
            let postCommentError = document.querySelector('#post-comment-error');
            let commentsEmpty = document.querySelector('.comments-empty');

            // Display the wait cursor
            document.body.classList.add('cursor-wait');

            axios.post(url, params)
                .then(response => {
                    element.reset();
                    commentList.insertAdjacentHTML('beforeend', response.data);
                    postCommentSuccess.classList.remove('d-none');
                    postCommentError.classList.add('d-none');

                    if (!fx.isNil(commentsEmpty)) {
                        commentsEmpty.classList.add('d-none');
                    }
                })
                .catch(error => {
                    postCommentError.textContent = error.response.data.errors.comment[0];
                    postCommentSuccess.classList.add('d-none');
                    postCommentError.classList.remove('d-none');
                })
                .then(() => {
                    // Display the standard cursor
                    document.body.classList.remove('cursor-wait');
                });
        }

        // Post the comment reply form
        if (element.matches('.reply-form')) {
            event.preventDefault();

            let params = new FormData(element);
            let url = element.action;
            let commentReplyList = document.querySelector(`#reply-list-${element.comment_id.value}`);
            let commentReplyError = document.querySelector(`#reply-error-${element.comment_id.value}`);

            // Display the wait cursor
            document.body.classList.add('cursor-wait');

            axios.post(url, params)
                .then(response => {
                    element.reset();
                    commentReplyList.insertAdjacentHTML('beforeend', response.data);
                    element.classList.add('d-none');
                    commentReplyError.classList.add('d-none');
                })
                .catch(error => {
                    commentReplyError.textContent = error.response.data.errors.reply[0];
                    commentReplyError.classList.remove('d-none');
                })
                .then(() => {
                    // Display the standard cursor
                    document.body.classList.remove('cursor-wait');
                });
        }

        let adminForms = ['admin-settings-form', 'admin-types-form', 'admin-categories-form', 'admin-pages-form'];

        // Save data from admin panel
        if (adminForms.includes(element.id)) {
            event.preventDefault();
            fx.handleForm(element, 'submit');

            // Initialize form and helpers
            let params = new FormData(element);
            let url = element.action;

            // Post the form to the server
            axios.post(url, params)
                .then(response => {
                    // Reset form
                    if ('create' === response.data.action) {
                        element.reset.click();
                    }

                    fx.showToast({
                        'message': response.data.message,
                        'theme': 'success'
                    });
                })
                .catch(error => {
                    // Oh no, something went wrong
                    let errors = error.response.data.errors;

                    // Handle the error messages
                    fx.handleFormErrors(errors);

                    fx.showToast({
                        'message': error.response.data.message,
                        'theme': 'danger'
                    });
                })
                .then(() => {
                    fx.handleForm(element, 'response');
                });
        }
    });

    // Listen to the on change event on the page and act accordingly
    document.addEventListener('change', event => {
        let element = event.target;

        // Trigger the cover file validation
        if (element.matches('#cover')) {
            const file = element.files[0];
            const fileSizeKb = parseInt(file.size / 1024);
            const maxFileSizeKb = element.dataset.whMaxSize;
            let info = document.querySelector(element.dataset.whTarget);
            let error = element.parentElement.querySelector('#selected-error');

            if (!fx.isNilOrEmpty(file) && fileSizeKb <= maxFileSizeKb && fx.isImage(file)) {
                info.textContent = `${file.name} [ ${fileSizeKb} kb]`;
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
        if (element.matches('#avatar')) {
            const file = element.files[0];
            const fileSizeKb = parseInt(file.size / 1024);
            const maxFileSizeKb = element.dataset.whMaxSize;
            let error = document.querySelector('#avatar-error');

            if (!fx.isNilOrEmpty(file) && fileSizeKb <= maxFileSizeKb && fx.isImage(file)) {
                fx.readImage(file, fx.previewAvatar);
                error.classList.add('d-none');
            } else {
                element.value = '';
                fx.previewAvatar('');
                error.classList.remove('d-none');
            }
        }

        // Trigger the avatar delete checkbox validation
        if (element.matches('#avatar-remove')) {
            if (element.checked) {
                let avatarInput = document.querySelector('#avatar');

                fx.previewAvatar('');
                avatarInput.value = '';
            }
        }

        // Update alternative categories
        if (element.matches('#main-category')) {
            let subCategories = document.querySelector('#categories');
            let tagsInstance = Tags.getInstance(subCategories);

            fx.clearSelections(subCategories);

            if (fx.isNilOrEmpty(element.value)) {
                subCategories.disabled = true;
            } else {
                fx.subCategoriesUpdate(subCategories, element);
                subCategories.disabled = false;
            }

            tagsInstance.removeAll();
            tagsInstance.resetSuggestions();
            tagsInstance.resetState();
        }
    });

    // Listen to the window scroll event and act accordingly
    window.addEventListener('scroll', event => {
        let header = document.querySelector('header');
        let mainWrapper = document.querySelector('.main-wrapper');
        let jumpToMainNav = document.querySelector('#jump-to-nav');
        let jumpToHeader = document.querySelector('#back-to-top-wrapper');

        if (!fx.isNil(header) && !fx.isNil(jumpToHeader)) {
            if (fx.isInViewport(header)) {
                jumpToHeader.classList.remove('fade-in');
                jumpToHeader.classList.add('fade-out');
            } else {
                jumpToHeader.classList.remove('fade-out');
                jumpToHeader.classList.add('fade-in');
            }
        }

        if (!fx.isNil(mainWrapper) && !fx.isNil(jumpToMainNav)) {
            if (fx.isInViewport(mainWrapper)) {
                jumpToMainNav.classList.remove('fade-in');
                jumpToMainNav.classList.add('fade-out');
            } else {
                jumpToMainNav.classList.remove('fade-out');
                jumpToMainNav.classList.add('fade-in');
            }
        }
    });

    // Listen to the input event and act accordingly
    document.addEventListener('input', event => {
        let element = event.target;

        if (element.matches('.autogrow')) {
            autoGrow(element);
        }
    });

    let tribute = new Tribute({
        trigger: '@',
        menuShowMinLength: 1,
        lookup: 'username',
        fillAttr: 'username',
        searchOpts: {
            pre: '<span>',
            post: '</span>',
            skip: true
        },
        noMatchTemplate: () => '<span class="d-none"></span>',
        loadingItemTemplate: () => `<div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>`,
        menuItemTemplate: item => `${item.original.name ?? item.original.username} <span class="text-muted">@${item.original.username}</span>`,
        values: fx.getUserList
    });

    tribute.attach(document.querySelectorAll('.commentbox'));
    window.tribute = tribute; // Hack to make Tribute available when loading comments. Needs rework
});
