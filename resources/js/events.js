import * as bootstrap from 'bootstrap';
import * as fx from './functions';
import * as push from './push';
import '@pwabuilder/pwaupdate';
import '@pwabuilder/pwainstall';
import autoGrow from '@ivanhanak_com/js-textarea-autogrow';
import Tribute from 'tributejs';
import axios from 'axios';
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

    if (null !== mainCategory && undefined !== mainCategory) {
        mainCategory.dispatchEvent(new Event('change', { bubbles: true }));
    }
});

// Wait for the DOM to be readay
document.addEventListener('DOMContentLoaded', () => {
    // Check for user session
    let userToken = document.querySelector('meta[name="user-token"]');

    if (null !== userToken && undefined !== userToken) {
        userToken = atob(userToken.content);
    }

    // Enable scrolling on the document
    document.body.classList.remove('overflow-hidden');

    // Create the side menu for small screens
    fx.createSideMenu();

    // Hide Whatsapp sharer on Desktop
    if (!fx.isMobile()) {
        document.querySelectorAll('.whatsapp-link').forEach(link => {
            link.classList.add('d-none');
        })
    }

    // Set Tags selector
    let tagsSelector = '.tags-select';

    // Initialize Tags
    Tags.init(tagsSelector, {
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
            shareBtn.attributes['data-bs-toggle'].value = '';
        });
    }

    // Disable tracking if the opt-out cookie exists.
    if (null !== window.analytics_id && undefined !== window.analytics_id) {
        let disableStr = `ga-disable-${window.analytics_id}`;

        if (document.cookie.indexOf(`${disableStr}=true`) > -1) {
            window[disableStr] = true;
        }
    }

    // Set what modal element should be displayed
    let modalForm = document.querySelector('.form-wrapper');

    // Working with admin modal forms
    if (null !== modalForm && modalForm.matches('.modal')) {
        let targetForm = modalForm.querySelector('form');
        let modalTitleElement = modalForm.querySelector('.modal-title');

        // Change title if updating
        modalForm.addEventListener('show.bs.modal', event => {
            let relatedTarget = event.relatedTarget;

            if (null === relatedTarget) {
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
                if (subscription && null !== pushDisable && undefined !== pushDisable) {
                    pushDisable.classList.remove('d-none');
                }

                // Hide the push enable button
                if (!subscription && null !== pushEnable && undefined !== pushEnable) {
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
    if (null !== userToken && undefined !== userToken) {
        Echo.private(`notifications.${userToken}`).listen('NotificationEvent', (payload) => {
            document.querySelectorAll('.unread').forEach((badge) => {
                badge.classList.remove('d-none');
            });

            document.querySelectorAll('.unread-count').forEach((count) => {
                count.innerHTML = payload.notifications.unread;
            });
        });
    }

    // Listen to the window resize event and act accordingly
    window.addEventListener('resize', () => {
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
            sideMenu.forEach(aside => {
                aside.classList.remove('show');
            });
        }
    });

    // Listen to the on click event on the page and act accordingly
    document.addEventListener('click', event => {
        let element = event.target;

        // Bubble up click event on certain elements
        let bubble = element.closest('a, label, button, .btn, .avatar-chooser') || false;

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

        // Populate and/or show the side menu
        if (element.matches('#toggler')) {
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
            element.querySelector('.icon-badge').classList.toggle('rotate');
            document.body.classList.toggle('overflow-hidden');
        }

        // Hide the side menu when clicking off bounds
        if (element.matches('#side-menu-overlay')) {
            document.querySelector('#toggler').click();
        }

        // Dynamically load comments for a writing
        if (null !== element.parentElement && undefined !== element.parentElement) {
            if (element.parentElement.matches['#load-more']) {
                let url = element.attributes['data-wh-href'].value;

                fx.loadComments(url);
            }
        }

        // Show/hide the comment reply form
        if (element.matches('.badge-reply')) {
            let target = element.attributes['data-wh-target'].value;
            let targetForm = document.querySelector(target);
            targetForm.classList.toggle('d-none');

            if (!targetForm.matches('.d-none')) {
                targetForm.querySelector('.form-control').focus();
            }
        }

        // Trigger the cover chooser
        if (element.matches('#cover-chooser')) {
            event.preventDefault();
            let fileChooser = document.querySelector(element.attributes['data-wh-target'].value);

            fileChooser.click();
        }

        // Trigger the avatar chooser
        if (element.matches('.avatar-chooser')) {
            event.preventDefault();
            let fileChooser = document.querySelector(element.attributes['data-wh-target'].value);

            fileChooser.click();
        }

        // Counters
        if (element.matches('.btn-counter')) {
            event.preventDefault();

            // Liking a writing
            if (element.matches('.like')) {
                if (element.hasAttribute('data-wh-target') && element.hasAttribute('data-wh-id') && element.hasAttribute('data-wh-value')) {
                    let url = element.attributes['data-wh-target'].value;
                    let id = element.attributes['data-wh-id'].value;
                    let value = element.attributes['data-wh-value'].value;
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
                if (element.hasAttribute('data-wh-target') && element.hasAttribute('data-wh-id')) {
                    let url = element.attributes['data-wh-target'].value;
                    let id = element.attributes['data-wh-id'].value;
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
                navigator.share({
                    title: element.attributes['data-wh-writing-title'].value,
                    url: element.attributes['data-wh-url'].value
                });
            }
        }

        // Share links
        if (element.matches('.share-link')) {
            event.preventDefault();

            let url = element.attributes['href'].value;

            if (element.matches('.copy-to-clipboard-link')) {
                navigator.clipboard.writeText(url);
            } else {
                let params = 'scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=500,height=500,left=100,top=100';
                let sharer = open(url, 'sharer', params);
            }
        }

        // Admin edit link
        if (element.matches('.admin-edit')) {
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
        if (element.matches('.admin-content-delete') || element.matches('.user-content-delete')) {
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
        if (element.matches('#btn-modal-delete')) {
            let url = element.attributes['data-wh-delete-url'].value;
            let redirect = element.attributes['data-wh-redirect-url'].value;
            let params = new FormData();

            params.append('_method', 'delete');

            if (null !== redirect && '' !== redirect) {
                params.append('redirect', true);
            }

            // Post the form to the server
            axios.post(url, params)
                .then(response => {
                    if (null !== redirect && '' !== redirect) {
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
        if (element.hasAttribute('href') && '#ga-optout' === element.attributes['href'].value) {
            event.preventDefault();
            let disableStr = `ga-disable-${analytics_id}`;

            document.cookie = `${disableStr}=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/`;
            window[disableStr] = true;
            alert('Google Analytics tracking disabled');
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
            let url = element.attributes['action'].value;

            // Post the form to the server
            axios.post(url, params)
                .then(response => {
                    let method = element.elements['_method'] || false;

                    // Form posted successfully, let's reset it
                    if (!method) {
                        element.reset();

                        /* if (null !== slimSelect && undefined !== slimSelect) {
                            slimSelect.set([]);
                        } */
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
            let url = element.attributes['action'].value;

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
            let url = element.attributes['action'].value;
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

                    if (null !== commentsEmpty && undefined !== commentsEmpty) {
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
            let url = element.attributes['action'].value;
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
            let url = element.attributes['action'].value;

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
            const maxFileSizeKb = element.attributes['data-wh-max-size'].value;
            let info = document.querySelector(element.attributes['data-wh-target'].value);
            let error = element.parentElement.querySelector('#selected-error');

            if (null !== file && '' !== file && fileSizeKb <= maxFileSizeKb && fx.isImage(file)) {
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
        if (element.matches('#avatar-remove')) {
            if (element.checked) {
                let avatarInput = document.querySelector('#avatar');

                fx.previewAvatar('');
                avatarInput.value = '';
            }
        }

        // Update alternative categories
        if (element.matches('#main-category') && '' !== element.value) {
            let mainCategoryId = element.value;
            let subCategories = document.querySelector('#categories');

            Array.from(subCategories.options).forEach(option => {
                option.selected = false;

                if (option.dataset.parentId == mainCategoryId) {
                    option.disabled = false;
                    option.hidden = false;
                } else {
                    option.disabled = true;
                    option.hidden = true;
                }
            });

            Tags.getInstance(subCategories).reset();
            Tags.getInstance(subCategories).resetSuggestions();
            //subCategories.disabled = false;
        }
    });

    // Listen to the window scroll event and act accordingly
    window.addEventListener('scroll', event => {
        let header = document.querySelector('header');
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
    document.addEventListener('input', event => {
        let element = event.target;

        if (element.matches('.autogrow')) {
            autoGrow(element);
        }
    });

    async function getUserList(wildcard, callback) {
        let response = await axios.post(`users/query/${wildcard}`);
        callback(response.data);
    }

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
        values: getUserList
    });

    tribute.attach(document.querySelectorAll('.commentbox'));
    window.tribute = tribute; // Hack to make Tribute available when loading comments. Needs rework
});
